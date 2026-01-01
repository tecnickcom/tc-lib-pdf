<?php

/**
 * SignatureManager.php
 *
 * @since     2025-01-01
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Signature;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Multiple Signature Manager for PDF Documents
 *
 * Manages multiple digital signatures in PDF documents using incremental updates.
 * Uses phpseclib for cryptographic operations.
 *
 * @since     2025-01-01
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type SignatureInfo array{
 *     'name': string,
 *     'reason': string,
 *     'location': string,
 *     'contact': string,
 * }
 *
 * @phpstan-type SignatureAppearance array{
 *     'page': int,
 *     'x': float,
 *     'y': float,
 *     'width': float,
 *     'height': float,
 * }
 *
 * @phpstan-type SignatureConfig array{
 *     'certificate': string,
 *     'privateKey': string,
 *     'password': string,
 *     'hashAlgorithm': string,
 *     'certType': int,
 *     'info': SignatureInfo,
 *     'appearance': SignatureAppearance,
 * }
 */
class SignatureManager
{
    /**
     * ByteRange placeholder pattern
     */
    protected const BYTERANGE_PLACEHOLDER = '/ByteRange[0 ********** ********** **********]';

    /**
     * Maximum signature length (in hex characters)
     */
    protected const SIGNATURE_MAX_LENGTH = 11742;

    /**
     * PDF parser instance
     */
    protected ?PdfParser $parser = null;

    /**
     * CMS builder instance
     */
    protected ?CmsBuilder $cmsBuilder = null;

    /**
     * Current PDF content
     */
    protected string $pdfContent = '';

    /**
     * Current object number
     */
    protected int $currentObjectNumber = 0;

    /**
     * Unit conversion factor (points per unit)
     */
    protected float $kunit = 2.83464566929; // mm to points

    /**
     * Constructor
     *
     * @param string $unit Unit of measure ('mm', 'pt', 'cm', 'in')
     */
    public function __construct(string $unit = 'mm')
    {
        $this->kunit = match ($unit) {
            'pt' => 1.0,
            'cm' => 72.0 / 2.54,
            'in' => 72.0,
            default => 72.0 / 25.4, // mm
        };
    }

    /**
     * Load a PDF document
     *
     * @param string $pdfContent PDF content or file path
     * @return self
     */
    public function loadPdf(string $pdfContent): self
    {
        // Check if it's a file path
        if (file_exists($pdfContent)) {
            $content = file_get_contents($pdfContent);
            if ($content === false) {
                throw new PdfException('Unable to read PDF file');
            }
            $pdfContent = $content;
        }

        $this->pdfContent = $pdfContent;
        $this->parser = new PdfParser($pdfContent);
        $this->currentObjectNumber = $this->parser->getMaxObjectNumber();

        return $this;
    }

    /**
     * Add a signature field placeholder (without signing)
     *
     * @param string $fieldName Field name
     * @param int $page Page number (1-based)
     * @param float $x X position
     * @param float $y Y position
     * @param float $width Width
     * @param float $height Height
     * @return string Updated PDF content
     */
    public function addSignatureField(
        string $fieldName,
        int $page,
        float $x,
        float $y,
        float $width,
        float $height
    ): string {
        if ($this->parser === null) {
            throw new PdfException('No PDF loaded');
        }

        // Convert to points
        $rect = $this->calculateRect($x, $y, $width, $height);

        // Create signature field annotation
        $this->currentObjectNumber++;
        $sigFieldObjNum = $this->currentObjectNumber;

        $pages = $this->parser->getPages();
        if (!isset($pages[$page])) {
            throw new PdfException('Invalid page number: ' . $page);
        }
        $pageRef = $pages[$page]['objRef'];

        // Build signature field object
        $sigFieldObj = $this->buildSignatureFieldObject(
            $sigFieldObjNum,
            $fieldName,
            $pageRef,
            $rect
        );

        // Create incremental update
        $this->pdfContent = $this->createIncrementalUpdate(
            $sigFieldObj,
            $sigFieldObjNum,
            $page
        );

        // Update parser with new content
        $this->parser = new PdfParser($this->pdfContent);

        return $this->pdfContent;
    }

    /**
     * Sign the PDF document
     *
     * @param SignatureConfig $config Signature configuration
     * @param string|null $fieldName Existing field name to sign (null to create new)
     * @return string Signed PDF content
     */
    public function sign(array $config, ?string $fieldName = null): string
    {
        if ($this->parser === null) {
            throw new PdfException('No PDF loaded');
        }

        // Initialize CMS builder
        $this->cmsBuilder = new CmsBuilder(
            $config['certificate'],
            $config['privateKey'],
            $config['password'] ?? '',
            $config['hashAlgorithm'] ?? 'sha256'
        );

        // Find or create signature field
        if ($fieldName !== null) {
            return $this->signExistingField($fieldName, $config);
        }

        return $this->signWithNewField($config);
    }

    /**
     * Sign an existing signature field
     *
     * @param string $fieldName Field name to sign
     * @param SignatureConfig $config Signature configuration
     * @return string Signed PDF content
     */
    protected function signExistingField(string $fieldName, array $config): string
    {
        $fields = $this->parser->getUnsignedFields();

        $targetField = null;
        foreach ($fields as $field) {
            if ($field['name'] === $fieldName) {
                $targetField = $field;
                break;
            }
        }

        if ($targetField === null) {
            throw new PdfException('Unsigned field not found: ' . $fieldName);
        }

        return $this->createSignedUpdate($targetField, $config);
    }

    /**
     * Sign with a new signature field
     *
     * @param SignatureConfig $config Signature configuration
     * @return string Signed PDF content
     */
    protected function signWithNewField(array $config): string
    {
        $appearance = $config['appearance'] ?? [
            'page' => 1,
            'x' => 0,
            'y' => 0,
            'width' => 0,
            'height' => 0,
        ];

        $rect = $this->calculateRect(
            $appearance['x'],
            $appearance['y'],
            $appearance['width'],
            $appearance['height']
        );

        $pages = $this->parser->getPages();
        $page = $appearance['page'] ?? 1;

        if (!isset($pages[$page])) {
            throw new PdfException('Invalid page number: ' . $page);
        }

        $fieldData = [
            'objNum' => 0, // Will be assigned
            'name' => $config['info']['name'] ?? 'Signature',
            'page' => $page,
            'rect' => $rect,
            'signed' => false,
        ];

        return $this->createSignedUpdate($fieldData, $config);
    }

    /**
     * Create a signed incremental update
     *
     * @param array{
     *     'objNum': int,
     *     'name': string,
     *     'page': int,
     *     'rect': array<float>,
     *     'signed': bool,
     * } $field Field data
     * @param SignatureConfig $config Signature configuration
     * @return string Signed PDF content
     */
    protected function createSignedUpdate(array $field, array $config): string
    {
        // Calculate object numbers
        $this->currentObjectNumber++;
        $sigFieldObjNum = $field['objNum'] > 0 ? $field['objNum'] : $this->currentObjectNumber;

        if ($field['objNum'] === 0) {
            $this->currentObjectNumber++;
        }

        $sigValueObjNum = $this->currentObjectNumber;
        $this->currentObjectNumber++;
        $sigAppearanceObjNum = $this->currentObjectNumber;

        $pages = $this->parser->getPages();
        $pageRef = $pages[$field['page']]['objRef'] ?? ($pages[1]['objRef'] ?? '1 0 R');

        // Build signature value object with placeholder
        $sigValueObj = $this->buildSignatureValueObject(
            $sigValueObjNum,
            $config
        );

        // Build signature field object (widget annotation)
        $sigFieldObj = $this->buildSignedFieldObject(
            $sigFieldObjNum,
            $field['name'],
            $pageRef,
            $field['rect'],
            $sigValueObjNum
        );

        // Build appearance object (empty for now)
        $sigAppearanceObj = $this->buildAppearanceObject($sigAppearanceObjNum);

        // Combine objects
        $objects = $sigFieldObj . $sigValueObj . $sigAppearanceObj;

        // Create incremental update (without signature yet)
        $pdfWithPlaceholder = $this->createIncrementalUpdateForSigning(
            $objects,
            [$sigFieldObjNum, $sigValueObjNum, $sigAppearanceObjNum],
            $field['page']
        );

        // Now calculate ByteRange and sign
        return $this->finalizeSignature($pdfWithPlaceholder, $sigValueObjNum);
    }

    /**
     * Build signature field object (widget annotation)
     *
     * @param int $objNum Object number
     * @param string $name Field name
     * @param string $pageRef Page reference
     * @param array<float> $rect Rectangle coordinates
     * @return string PDF object string
     */
    protected function buildSignatureFieldObject(
        int $objNum,
        string $name,
        string $pageRef,
        array $rect
    ): string {
        $rectStr = sprintf('%.4f %.4f %.4f %.4f', ...$rect);

        return $objNum . " 0 obj\n"
            . "<<\n"
            . "/Type /Annot\n"
            . "/Subtype /Widget\n"
            . "/FT /Sig\n"
            . "/F 132\n"
            . "/Rect [" . $rectStr . "]\n"
            . "/P " . $pageRef . "\n"
            . "/T (" . $this->escapeString($name) . ")\n"
            . ">>\n"
            . "endobj\n";
    }

    /**
     * Build signed signature field object (with /V reference)
     *
     * @param int $objNum Object number
     * @param string $name Field name
     * @param string $pageRef Page reference
     * @param array<float> $rect Rectangle coordinates
     * @param int $sigValueObjNum Signature value object number
     * @return string PDF object string
     */
    protected function buildSignedFieldObject(
        int $objNum,
        string $name,
        string $pageRef,
        array $rect,
        int $sigValueObjNum
    ): string {
        $rectStr = sprintf('%.4f %.4f %.4f %.4f', ...$rect);

        return $objNum . " 0 obj\n"
            . "<<\n"
            . "/Type /Annot\n"
            . "/Subtype /Widget\n"
            . "/FT /Sig\n"
            . "/F 132\n"
            . "/Rect [" . $rectStr . "]\n"
            . "/P " . $pageRef . "\n"
            . "/T (" . $this->escapeString($name) . ")\n"
            . "/V " . $sigValueObjNum . " 0 R\n"
            . ">>\n"
            . "endobj\n";
    }

    /**
     * Build signature value object
     *
     * @param int $objNum Object number
     * @param SignatureConfig $config Signature configuration
     * @return string PDF object string
     */
    protected function buildSignatureValueObject(int $objNum, array $config): string
    {
        $info = $config['info'] ?? [];
        $certType = $config['certType'] ?? 2;

        $obj = $objNum . " 0 obj\n"
            . "<<\n"
            . "/Type /Sig\n"
            . "/Filter /Adobe.PPKLite\n"
            . "/SubFilter /adbe.pkcs7.detached\n"
            . self::BYTERANGE_PLACEHOLDER . "\n"
            . "/Contents <" . str_repeat('0', self::SIGNATURE_MAX_LENGTH) . ">\n";

        // Add signature info
        if (!empty($info['name'])) {
            $obj .= "/Name (" . $this->escapeString($info['name']) . ")\n";
        }
        if (!empty($info['reason'])) {
            $obj .= "/Reason (" . $this->escapeString($info['reason']) . ")\n";
        }
        if (!empty($info['location'])) {
            $obj .= "/Location (" . $this->escapeString($info['location']) . ")\n";
        }
        if (!empty($info['contact'])) {
            $obj .= "/ContactInfo (" . $this->escapeString($info['contact']) . ")\n";
        }

        // Add signing time
        $obj .= "/M (D:" . gmdate('YmdHis') . "Z)\n";

        // Add reference for certification signature
        if ($certType > 0) {
            $obj .= "/Reference [\n"
                . "<<\n"
                . "/Type /SigRef\n"
                . "/TransformMethod /DocMDP\n"
                . "/TransformParams <<\n"
                . "/Type /TransformParams\n"
                . "/P " . $certType . "\n"
                . "/V /1.2\n"
                . ">>\n"
                . ">>\n"
                . "]\n";
        }

        $obj .= ">>\n"
            . "endobj\n";

        return $obj;
    }

    /**
     * Build appearance object (empty/invisible)
     *
     * @param int $objNum Object number
     * @return string PDF object string
     */
    protected function buildAppearanceObject(int $objNum): string
    {
        return $objNum . " 0 obj\n"
            . "<<\n"
            . "/Type /XObject\n"
            . "/Subtype /Form\n"
            . "/BBox [0 0 0 0]\n"
            . "/Length 0\n"
            . ">>\n"
            . "stream\n"
            . "endstream\n"
            . "endobj\n";
    }

    /**
     * Create incremental update for adding objects
     *
     * @param string $objects PDF objects to add
     * @param int $lastObjNum Last object number added
     * @param int $page Page number for annotation
     * @return string Updated PDF content
     */
    protected function createIncrementalUpdate(
        string $objects,
        int $lastObjNum,
        int $page
    ): string {
        $prevXref = strlen($this->pdfContent);
        $trailer = $this->parser->getTrailer();

        // Start with existing content (remove trailing whitespace after %%EOF)
        $baseContent = rtrim($this->pdfContent);
        if (!str_ends_with($baseContent, '%%EOF')) {
            $baseContent .= "\n%%EOF";
        }

        $update = "\n";
        $update .= $objects;

        // Build xref
        $xrefPos = strlen($baseContent) + strlen($update);
        $update .= "xref\n";
        $update .= $lastObjNum . " 1\n";
        $update .= sprintf("%010d %05d n \n", strlen($baseContent) + 1, 0);

        // Build trailer
        $update .= "trailer\n";
        $update .= "<<\n";
        $update .= "/Size " . ($lastObjNum + 1) . "\n";
        $update .= "/Root " . $trailer['Root'] . "\n";
        if (isset($trailer['Info'])) {
            $update .= "/Info " . $trailer['Info'] . "\n";
        }
        $update .= "/Prev " . $this->parser->getXrefPosition() . "\n";
        $update .= ">>\n";
        $update .= "startxref\n";
        $update .= $xrefPos . "\n";
        $update .= "%%EOF\n";

        return $baseContent . $update;
    }

    /**
     * Create incremental update for signing
     *
     * @param string $objects PDF objects to add
     * @param array<int> $objNums Object numbers added
     * @param int $page Page number
     * @return string Updated PDF content
     */
    protected function createIncrementalUpdateForSigning(
        string $objects,
        array $objNums,
        int $page
    ): string {
        $trailer = $this->parser->getTrailer();

        // Start with existing content
        $baseContent = rtrim($this->pdfContent);
        if (!str_ends_with($baseContent, '%%EOF')) {
            $baseContent .= "\n%%EOF";
        }

        $update = "\n";

        // Calculate object positions
        $objectPositions = [];
        $currentPos = strlen($baseContent) + 1;

        foreach (explode("\nendobj\n", $objects) as $objPart) {
            if (empty(trim($objPart))) {
                continue;
            }

            if (preg_match('/^(\d+)\s+0\s+obj/', trim($objPart), $m)) {
                $objectPositions[(int)$m[1]] = $currentPos;
            }

            $currentPos += strlen($objPart) + 8; // +8 for "\nendobj\n"
        }

        $update .= $objects;

        // Build xref
        $xrefPos = strlen($baseContent) + strlen($update);
        $update .= "xref\n";

        // Write xref entries for new objects
        $firstObj = min($objNums);
        $lastObj = max($objNums);

        $update .= "0 1\n";
        $update .= "0000000000 65535 f \n";

        foreach ($objNums as $objNum) {
            $update .= $objNum . " 1\n";
            $pos = $objectPositions[$objNum] ?? (strlen($baseContent) + 1);
            $update .= sprintf("%010d %05d n \n", $pos, 0);
        }

        // Build trailer
        $update .= "trailer\n";
        $update .= "<<\n";
        $update .= "/Size " . ($lastObj + 1) . "\n";
        $update .= "/Root " . $trailer['Root'] . "\n";
        if (isset($trailer['Info'])) {
            $update .= "/Info " . $trailer['Info'] . "\n";
        }
        $update .= "/Prev " . $this->parser->getXrefPosition() . "\n";
        $update .= ">>\n";
        $update .= "startxref\n";
        $update .= $xrefPos . "\n";
        $update .= "%%EOF\n";

        return $baseContent . $update;
    }

    /**
     * Finalize the signature by calculating ByteRange and signing
     *
     * @param string $pdfContent PDF content with placeholder
     * @param int $sigValueObjNum Signature value object number
     * @return string Signed PDF content
     */
    protected function finalizeSignature(string $pdfContent, int $sigValueObjNum): string
    {
        // Find the ByteRange placeholder
        $byteRangePlaceholder = self::BYTERANGE_PLACEHOLDER;
        $byteRangePos = strpos($pdfContent, $byteRangePlaceholder);

        if ($byteRangePos === false) {
            throw new PdfException('ByteRange placeholder not found');
        }

        // Find the Contents placeholder
        $contentsPattern = '/\/Contents\s*<(' . str_repeat('0', self::SIGNATURE_MAX_LENGTH) . ')>/';
        if (!preg_match($contentsPattern, $pdfContent, $matches, PREG_OFFSET_CAPTURE)) {
            throw new PdfException('Contents placeholder not found');
        }

        $contentsPos = (int)$matches[1][1];
        $contentsLen = self::SIGNATURE_MAX_LENGTH;

        // Calculate ByteRange values
        $byteRange = [
            0,                              // Start of document
            $contentsPos - 1,               // Before '<'
            $contentsPos + $contentsLen + 1, // After '>'
            strlen($pdfContent) - ($contentsPos + $contentsLen + 1)
        ];

        // Create ByteRange string
        $byteRangeStr = sprintf(
            '/ByteRange[0 %d %d %d]',
            $byteRange[1],
            $byteRange[2],
            $byteRange[3]
        );

        // Pad to match placeholder length
        $byteRangeStr = str_pad($byteRangeStr, strlen($byteRangePlaceholder), ' ');

        // Replace ByteRange placeholder
        $pdfContent = substr_replace(
            $pdfContent,
            $byteRangeStr,
            $byteRangePos,
            strlen($byteRangePlaceholder)
        );

        // Recalculate positions after replacement
        $contentsPos = strpos($pdfContent, '<' . str_repeat('0', self::SIGNATURE_MAX_LENGTH) . '>');
        if ($contentsPos === false) {
            throw new PdfException('Contents position lost after ByteRange update');
        }

        $byteRange[1] = $contentsPos;
        $byteRange[2] = $contentsPos + self::SIGNATURE_MAX_LENGTH + 2;
        $byteRange[3] = strlen($pdfContent) - $byteRange[2];

        // Extract data to sign (everything except signature contents)
        $dataToSign = substr($pdfContent, $byteRange[0], $byteRange[1])
            . substr($pdfContent, $byteRange[2], $byteRange[3]);

        // Create signature
        $signature = $this->cmsBuilder->createSignedData($dataToSign);

        // Convert to hex
        $signatureHex = strtoupper(bin2hex($signature));

        // Pad signature to max length
        if (strlen($signatureHex) > self::SIGNATURE_MAX_LENGTH) {
            throw new PdfException('Signature too large');
        }

        $signatureHex = str_pad($signatureHex, self::SIGNATURE_MAX_LENGTH, '0');

        // Replace signature placeholder
        $pdfContent = substr($pdfContent, 0, $contentsPos + 1)
            . $signatureHex
            . substr($pdfContent, $contentsPos + 1 + self::SIGNATURE_MAX_LENGTH);

        $this->pdfContent = $pdfContent;

        return $pdfContent;
    }

    /**
     * Get list of all signature fields
     *
     * @return array<int, array{
     *     'objNum': int,
     *     'name': string,
     *     'page': int,
     *     'rect': array<float>,
     *     'signed': bool,
     * }> Signature fields
     */
    public function getSignatureFields(): array
    {
        if ($this->parser === null) {
            return [];
        }

        return $this->parser->getSignatureFields();
    }

    /**
     * Get unsigned signature fields
     *
     * @return array<int, array{
     *     'objNum': int,
     *     'name': string,
     *     'page': int,
     *     'rect': array<float>,
     *     'signed': bool,
     * }> Unsigned fields
     */
    public function getUnsignedFields(): array
    {
        if ($this->parser === null) {
            return [];
        }

        return $this->parser->getUnsignedFields();
    }

    /**
     * Get the current PDF content
     *
     * @return string PDF content
     */
    public function getPdfContent(): string
    {
        return $this->pdfContent;
    }

    /**
     * Save PDF to file
     *
     * @param string $filePath File path
     * @return bool Success
     */
    public function save(string $filePath): bool
    {
        return file_put_contents($filePath, $this->pdfContent) !== false;
    }

    /**
     * Calculate rectangle in points
     *
     * @param float $x X position
     * @param float $y Y position
     * @param float $width Width
     * @param float $height Height
     * @return array<float> Rectangle [x1, y1, x2, y2]
     */
    protected function calculateRect(
        float $x,
        float $y,
        float $width,
        float $height
    ): array {
        $x1 = $x * $this->kunit;
        $y1 = $y * $this->kunit;
        $x2 = ($x + $width) * $this->kunit;
        $y2 = ($y + $height) * $this->kunit;

        return [$x1, $y1, $x2, $y2];
    }

    /**
     * Escape string for PDF
     *
     * @param string $str String to escape
     * @return string Escaped string
     */
    protected function escapeString(string $str): string
    {
        return strtr($str, [
            '\\' => '\\\\',
            '(' => '\\(',
            ')' => '\\)',
            "\r" => '\\r',
            "\n" => '\\n',
            "\t" => '\\t',
        ]);
    }

    /**
     * Add multiple signatures to a PDF
     *
     * @param array<SignatureConfig> $signatures Array of signature configurations
     * @return string Signed PDF content
     */
    public function signMultiple(array $signatures): string
    {
        foreach ($signatures as $config) {
            $fieldName = $config['info']['name'] ?? null;
            $this->sign($config, $fieldName);

            // Reload parser for next signature
            $this->parser = new PdfParser($this->pdfContent);
            $this->currentObjectNumber = $this->parser->getMaxObjectNumber();
        }

        return $this->pdfContent;
    }
}
