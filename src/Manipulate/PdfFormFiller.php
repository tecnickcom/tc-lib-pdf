<?php

/**
 * PdfFormFiller.php
 *
 * PDF form field filling class for filling and flattening PDF forms.
 *
 * @category    Library
 * @package     PdfManipulate
 * @subpackage  FormFiller
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2024-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */

namespace Com\Tecnick\Pdf\Manipulate;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * PDF Form Filler class
 *
 * Provides functionality to fill and flatten PDF form fields.
 *
 * Example usage:
 * ```php
 * $filler = new PdfFormFiller();
 * $filler->loadFile('form.pdf')
 *        ->setFieldValue('name', 'John Doe')
 *        ->setFieldValue('email', 'john@example.com')
 *        ->flattenFields();
 * $filler->applyToFile('filled_form.pdf');
 * ```
 *
 * @category    Library
 * @package     PdfManipulate
 */
class PdfFormFiller
{
    /**
     * Original PDF content
     *
     * @var string
     */
    protected string $pdfContent = '';

    /**
     * PDF version
     *
     * @var string
     */
    protected string $pdfVersion = '1.7';

    /**
     * Object number counter
     *
     * @var int
     */
    protected int $objectNumber = 1;

    /**
     * Parsed form fields
     *
     * @var array<string, array{
     *     objNum: int,
     *     name: string,
     *     type: string,
     *     value: mixed,
     *     rect: array{0: float, 1: float, 2: float, 3: float},
     *     page: int,
     *     options?: array<string>,
     *     maxLen?: int,
     *     flags?: int,
     *     defaultValue?: mixed
     * }>
     */
    protected array $formFields = [];

    /**
     * Field values to set
     *
     * @var array<string, mixed>
     */
    protected array $fieldValues = [];

    /**
     * Whether to flatten fields after filling
     *
     * @var bool
     */
    protected bool $shouldFlatten = false;

    /**
     * Parsed PDF objects
     *
     * @var array<int, array{offset: int, content: string}>
     */
    protected array $objects = [];

    /**
     * Load PDF from file
     *
     * @param string $filePath Path to PDF file
     * @return static
     * @throws PdfException If file cannot be read
     */
    public function loadFile(string $filePath): static
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new PdfException("Cannot read file: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new PdfException("Failed to read file: {$filePath}");
        }

        return $this->loadContent($content);
    }

    /**
     * Load PDF from string content
     *
     * @param string $content PDF content
     * @return static
     * @throws PdfException If content is not valid PDF
     */
    public function loadContent(string $content): static
    {
        if (!str_starts_with($content, '%PDF-')) {
            throw new PdfException('Invalid PDF content');
        }

        $this->pdfContent = $content;
        $this->extractVersion();
        $this->parseFormFields();

        return $this;
    }

    /**
     * Extract PDF version
     *
     * @return void
     */
    protected function extractVersion(): void
    {
        if (preg_match('/%PDF-(\d+\.\d+)/', $this->pdfContent, $matches)) {
            $this->pdfVersion = $matches[1];
        }
    }

    /**
     * Parse form fields from PDF
     *
     * @return void
     */
    protected function parseFormFields(): void
    {
        $this->formFields = [];
        $this->objects = [];

        // Parse all objects first
        $this->parseAllObjects();

        // Find AcroForm in catalog
        $acroFormRef = $this->findAcroFormReference();
        if ($acroFormRef === null) {
            return; // No form fields
        }

        // Parse the AcroForm dictionary
        $acroFormContent = $this->getObjectContent($acroFormRef);
        if ($acroFormContent === null) {
            return;
        }

        // Get Fields array
        if (preg_match('/\/Fields\s*\[(.*?)\]/s', $acroFormContent, $matches)) {
            $fieldsStr = $matches[1];
            // Extract object references
            preg_match_all('/(\d+)\s+\d+\s+R/', $fieldsStr, $fieldRefs);

            foreach ($fieldRefs[1] as $objNum) {
                $this->parseFieldObject((int) $objNum);
            }
        }
    }

    /**
     * Parse all objects from PDF
     *
     * @return void
     */
    protected function parseAllObjects(): void
    {
        // Match all object definitions
        preg_match_all('/(\d+)\s+\d+\s+obj\s*(.*?)\s*endobj/s', $this->pdfContent, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] as $i => $objNumMatch) {
            $objNum = (int) $objNumMatch[0];
            $this->objects[$objNum] = [
                'offset' => $objNumMatch[1],
                'content' => $matches[2][$i][0],
            ];
        }
    }

    /**
     * Find AcroForm reference in catalog
     *
     * @return int|null Object number or null
     */
    protected function findAcroFormReference(): ?int
    {
        // Find the catalog object (root)
        if (preg_match('/\/Root\s+(\d+)\s+\d+\s+R/', $this->pdfContent, $matches)) {
            $catalogNum = (int) $matches[1];
            $catalogContent = $this->getObjectContent($catalogNum);

            if ($catalogContent && preg_match('/\/AcroForm\s+(\d+)\s+\d+\s+R/', $catalogContent, $acroMatches)) {
                return (int) $acroMatches[1];
            }

            // Check for inline AcroForm
            if ($catalogContent && preg_match('/\/AcroForm\s*<</', $catalogContent)) {
                // Return the catalog number itself for inline processing
                return $catalogNum;
            }
        }

        return null;
    }

    /**
     * Get object content by number
     *
     * @param int $objNum Object number
     * @return string|null Object content or null
     */
    protected function getObjectContent(int $objNum): ?string
    {
        return $this->objects[$objNum]['content'] ?? null;
    }

    /**
     * Parse a field object
     *
     * @param int $objNum Object number
     * @param int $page Page number (default 1)
     * @return void
     */
    protected function parseFieldObject(int $objNum, int $page = 1): void
    {
        $content = $this->getObjectContent($objNum);
        if ($content === null) {
            return;
        }

        // Check for Kids (field hierarchy)
        if (preg_match('/\/Kids\s*\[(.*?)\]/s', $content, $matches)) {
            preg_match_all('/(\d+)\s+\d+\s+R/', $matches[1], $kidRefs);
            foreach ($kidRefs[1] as $kidNum) {
                $this->parseFieldObject((int) $kidNum, $page);
            }
            return;
        }

        // Extract field properties
        $field = [
            'objNum' => $objNum,
            'name' => '',
            'type' => 'Tx', // Default to text
            'value' => '',
            'rect' => [0, 0, 0, 0],
            'page' => $page,
        ];

        // Field name (T)
        if (preg_match('/\/T\s*\(([^)]*)\)/', $content, $matches)) {
            $field['name'] = $this->decodePdfString($matches[1]);
        } elseif (preg_match('/\/T\s*<([^>]*)>/', $content, $matches)) {
            $field['name'] = $this->decodeHexString($matches[1]);
        }

        // Skip if no name
        if (empty($field['name'])) {
            return;
        }

        // Field type (FT)
        if (preg_match('/\/FT\s*\/(\w+)/', $content, $matches)) {
            $field['type'] = $matches[1];
        }

        // Current value (V)
        if (preg_match('/\/V\s*\(([^)]*)\)/', $content, $matches)) {
            $field['value'] = $this->decodePdfString($matches[1]);
        } elseif (preg_match('/\/V\s*<([^>]*)>/', $content, $matches)) {
            $field['value'] = $this->decodeHexString($matches[1]);
        } elseif (preg_match('/\/V\s*\/(\w+)/', $content, $matches)) {
            $field['value'] = $matches[1]; // Name value (for checkboxes, radio buttons)
        }

        // Rectangle (Rect)
        if (preg_match('/\/Rect\s*\[\s*([\d.\s-]+)\s*\]/', $content, $matches)) {
            $coords = preg_split('/\s+/', trim($matches[1]));
            if (count($coords) >= 4) {
                $field['rect'] = [
                    (float) $coords[0],
                    (float) $coords[1],
                    (float) $coords[2],
                    (float) $coords[3],
                ];
            }
        }

        // Options (Opt) for choice fields
        if (preg_match('/\/Opt\s*\[(.*?)\]/s', $content, $matches)) {
            $field['options'] = $this->parseOptionsArray($matches[1]);
        }

        // Max length (MaxLen)
        if (preg_match('/\/MaxLen\s+(\d+)/', $content, $matches)) {
            $field['maxLen'] = (int) $matches[1];
        }

        // Field flags (Ff)
        if (preg_match('/\/Ff\s+(\d+)/', $content, $matches)) {
            $field['flags'] = (int) $matches[1];
        }

        // Default value (DV)
        if (preg_match('/\/DV\s*\(([^)]*)\)/', $content, $matches)) {
            $field['defaultValue'] = $this->decodePdfString($matches[1]);
        }

        $this->formFields[$field['name']] = $field;
    }

    /**
     * Parse options array for choice fields
     *
     * @param string $optStr Options string
     * @return array<string>
     */
    protected function parseOptionsArray(string $optStr): array
    {
        $options = [];
        preg_match_all('/\(([^)]*)\)/', $optStr, $matches);
        foreach ($matches[1] as $opt) {
            $options[] = $this->decodePdfString($opt);
        }
        return $options;
    }

    /**
     * Decode PDF string escape sequences
     *
     * @param string $str Encoded string
     * @return string Decoded string
     */
    protected function decodePdfString(string $str): string
    {
        $str = str_replace(['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'], ["\n", "\r", "\t", '(', ')', '\\'], $str);
        // Handle octal codes
        $str = preg_replace_callback('/\\\\([0-7]{1,3})/', fn($m) => chr(octdec($m[1])), $str);
        return $str;
    }

    /**
     * Decode hex string
     *
     * @param string $hex Hex string
     * @return string Decoded string
     */
    protected function decodeHexString(string $hex): string
    {
        $hex = preg_replace('/\s+/', '', $hex);
        return hex2bin($hex) ?: '';
    }

    /**
     * Get all form fields
     *
     * @return array<string, array{
     *     name: string,
     *     type: string,
     *     value: mixed,
     *     rect: array{0: float, 1: float, 2: float, 3: float},
     *     options?: array<string>,
     *     maxLen?: int
     * }>
     */
    public function getFormFields(): array
    {
        $result = [];
        foreach ($this->formFields as $name => $field) {
            $result[$name] = [
                'name' => $field['name'],
                'type' => $this->getFieldTypeName($field['type']),
                'value' => $field['value'],
                'rect' => $field['rect'],
            ];
            if (isset($field['options'])) {
                $result[$name]['options'] = $field['options'];
            }
            if (isset($field['maxLen'])) {
                $result[$name]['maxLen'] = $field['maxLen'];
            }
        }
        return $result;
    }

    /**
     * Get field type name
     *
     * @param string $type PDF field type
     * @return string Human-readable type
     */
    protected function getFieldTypeName(string $type): string
    {
        return match ($type) {
            'Tx' => 'text',
            'Btn' => 'button',
            'Ch' => 'choice',
            'Sig' => 'signature',
            default => $type,
        };
    }

    /**
     * Get field names
     *
     * @return array<string>
     */
    public function getFieldNames(): array
    {
        return array_keys($this->formFields);
    }

    /**
     * Get field count
     *
     * @return int
     */
    public function getFieldCount(): int
    {
        return count($this->formFields);
    }

    /**
     * Check if a field exists
     *
     * @param string $name Field name
     * @return bool
     */
    public function hasField(string $name): bool
    {
        return isset($this->formFields[$name]);
    }

    /**
     * Get current field value
     *
     * @param string $name Field name
     * @return mixed Field value or null
     */
    public function getFieldValue(string $name): mixed
    {
        if (isset($this->fieldValues[$name])) {
            return $this->fieldValues[$name];
        }
        return $this->formFields[$name]['value'] ?? null;
    }

    /**
     * Set field value
     *
     * @param string $name Field name
     * @param mixed $value Field value
     * @return static
     * @throws PdfException If field not found
     */
    public function setFieldValue(string $name, mixed $value): static
    {
        if (!isset($this->formFields[$name])) {
            throw new PdfException("Field not found: {$name}");
        }

        $this->fieldValues[$name] = $value;
        return $this;
    }

    /**
     * Set multiple field values
     *
     * @param array<string, mixed> $values Field values
     * @return static
     */
    public function setFieldValues(array $values): static
    {
        foreach ($values as $name => $value) {
            if (isset($this->formFields[$name])) {
                $this->fieldValues[$name] = $value;
            }
        }
        return $this;
    }

    /**
     * Enable or disable field flattening
     *
     * @param bool $flatten Whether to flatten
     * @return static
     */
    public function flattenFields(bool $flatten = true): static
    {
        $this->shouldFlatten = $flatten;
        return $this;
    }

    /**
     * Clear all field value changes
     *
     * @return static
     */
    public function clearFieldValues(): static
    {
        $this->fieldValues = [];
        return $this;
    }

    /**
     * Apply changes and return modified PDF
     *
     * @return string Modified PDF content
     * @throws PdfException If no PDF loaded
     */
    public function apply(): string
    {
        if (empty($this->pdfContent)) {
            throw new PdfException('No PDF loaded');
        }

        if (empty($this->formFields)) {
            // No form fields - return original if no modifications
            return $this->pdfContent;
        }

        return $this->rebuildPdf();
    }

    /**
     * Apply changes and save to file
     *
     * @param string $outputPath Output file path
     * @return bool True on success
     * @throws PdfException If operation fails
     */
    public function applyToFile(string $outputPath): bool
    {
        $content = $this->apply();
        $result = file_put_contents($outputPath, $content);
        return $result !== false;
    }

    /**
     * Rebuild PDF with modified field values
     *
     * @return string Modified PDF content
     */
    protected function rebuildPdf(): string
    {
        $pdf = $this->pdfContent;

        foreach ($this->fieldValues as $name => $value) {
            if (!isset($this->formFields[$name])) {
                continue;
            }

            $field = $this->formFields[$name];
            $objNum = $field['objNum'];

            // Modify the object content
            $pdf = $this->updateFieldValue($pdf, $objNum, $value, $field['type']);
        }

        if ($this->shouldFlatten) {
            $pdf = $this->flattenFormFields($pdf);
        }

        return $pdf;
    }

    /**
     * Update field value in PDF content
     *
     * @param string $pdf PDF content
     * @param int $objNum Object number
     * @param mixed $value New value
     * @param string $type Field type
     * @return string Updated PDF content
     */
    protected function updateFieldValue(string $pdf, int $objNum, mixed $value, string $type): string
    {
        // Find the object
        $pattern = '/(' . $objNum . '\s+0\s+obj\s*<<)(.*?)(>>\s*endobj)/s';

        if (!preg_match($pattern, $pdf, $matches)) {
            return $pdf;
        }

        $objStart = $matches[1];
        $objContent = $matches[2];
        $objEnd = $matches[3];

        // Encode the value
        $encodedValue = $this->encodePdfString($value);

        // Update or add /V entry
        if (preg_match('/\/V\s*\([^)]*\)/', $objContent)) {
            $objContent = preg_replace('/\/V\s*\([^)]*\)/', '/V (' . $encodedValue . ')', $objContent);
        } elseif (preg_match('/\/V\s*<[^>]*>/', $objContent)) {
            $objContent = preg_replace('/\/V\s*<[^>]*>/', '/V (' . $encodedValue . ')', $objContent);
        } elseif (preg_match('/\/V\s*\/\w+/', $objContent)) {
            // Handle name values (for buttons/checkboxes)
            if ($type === 'Btn') {
                $objContent = preg_replace('/\/V\s*\/\w+/', '/V /' . $value, $objContent);
            } else {
                $objContent = preg_replace('/\/V\s*\/\w+/', '/V (' . $encodedValue . ')', $objContent);
            }
        } else {
            // Add /V entry before the end
            $objContent .= "\n/V (" . $encodedValue . ")\n";
        }

        // Also update appearance if needed (simplified - just mark as needs appearance)
        if (!preg_match('/\/NeedAppearances\s+true/', $pdf)) {
            // Find AcroForm and add NeedAppearances
            $pdf = preg_replace(
                '/(\/AcroForm\s*<<)/',
                '$1 /NeedAppearances true',
                $pdf,
                1
            );
        }

        $newObj = $objStart . $objContent . $objEnd;
        return preg_replace($pattern, $newObj, $pdf, 1);
    }

    /**
     * Encode string for PDF
     *
     * @param mixed $value Value to encode
     * @return string Encoded string
     */
    protected function encodePdfString(mixed $value): string
    {
        $str = (string) $value;
        // Escape special characters
        $str = str_replace('\\', '\\\\', $str);
        $str = str_replace('(', '\\(', $str);
        $str = str_replace(')', '\\)', $str);
        $str = str_replace("\r", '\\r', $str);
        $str = str_replace("\n", '\\n', $str);
        return $str;
    }

    /**
     * Flatten form fields (make them non-editable)
     *
     * @param string $pdf PDF content
     * @return string Modified PDF content
     */
    protected function flattenFormFields(string $pdf): string
    {
        // Remove /AcroForm from catalog to flatten
        // This is a simplified approach - proper flattening would render fields to content streams

        // Mark fields as read-only by setting Ff flag bit 1
        foreach ($this->formFields as $field) {
            $objNum = $field['objNum'];
            $pattern = '/(' . $objNum . '\s+0\s+obj\s*<<)(.*?)(>>\s*endobj)/s';

            if (preg_match($pattern, $pdf, $matches)) {
                $objContent = $matches[2];

                // Set or update Ff (field flags) - bit 1 = read-only
                if (preg_match('/\/Ff\s+(\d+)/', $objContent, $ffMatch)) {
                    $currentFlags = (int) $ffMatch[1];
                    $newFlags = $currentFlags | 1; // Set read-only bit
                    $objContent = preg_replace('/\/Ff\s+\d+/', '/Ff ' . $newFlags, $objContent);
                } else {
                    $objContent .= "\n/Ff 1\n";
                }

                $pdf = preg_replace($pattern, $matches[1] . $objContent . $matches[3], $pdf, 1);
            }
        }

        return $pdf;
    }

    /**
     * Get field type for a specific field
     *
     * @param string $name Field name
     * @return string|null Field type or null
     */
    public function getFieldType(string $name): ?string
    {
        if (!isset($this->formFields[$name])) {
            return null;
        }
        return $this->getFieldTypeName($this->formFields[$name]['type']);
    }

    /**
     * Get field options (for choice fields)
     *
     * @param string $name Field name
     * @return array<string>
     */
    public function getFieldOptions(string $name): array
    {
        return $this->formFields[$name]['options'] ?? [];
    }

    /**
     * Check if field is required (has Required flag)
     *
     * @param string $name Field name
     * @return bool
     */
    public function isFieldRequired(string $name): bool
    {
        $flags = $this->formFields[$name]['flags'] ?? 0;
        return ($flags & 2) !== 0; // Bit 2 = Required
    }

    /**
     * Check if field is read-only
     *
     * @param string $name Field name
     * @return bool
     */
    public function isFieldReadOnly(string $name): bool
    {
        $flags = $this->formFields[$name]['flags'] ?? 0;
        return ($flags & 1) !== 0; // Bit 1 = ReadOnly
    }

    /**
     * Get the field's bounding rectangle
     *
     * @param string $name Field name
     * @return array{0: float, 1: float, 2: float, 3: float}|null
     */
    public function getFieldRect(string $name): ?array
    {
        return $this->formFields[$name]['rect'] ?? null;
    }
}
