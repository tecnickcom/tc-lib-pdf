<?php

/**
 * VariableFont.php
 *
 * OpenType Variable Font support for PDF generation.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Font;

/**
 * Variable Font support
 *
 * OpenType Variable Fonts contain multiple styles in a single file,
 * with continuous variation along axes like weight, width, and slant.
 *
 * Standard variation axes (registered tags):
 * - wght: Weight (thin to black)
 * - wdth: Width (condensed to expanded)
 * - slnt: Slant (upright to oblique)
 * - ital: Italic (upright to italic)
 * - opsz: Optical size
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type TVariationAxis array{
 *     'tag': string,
 *     'name': string,
 *     'minValue': float,
 *     'defaultValue': float,
 *     'maxValue': float,
 * }
 *
 * @phpstan-type TNamedInstance array{
 *     'name': string,
 *     'coordinates': array<string, float>,
 * }
 */
class VariableFont
{
    /**
     * Standard registered variation axes
     */
    public const AXIS_WEIGHT = 'wght';
    public const AXIS_WIDTH = 'wdth';
    public const AXIS_SLANT = 'slnt';
    public const AXIS_ITALIC = 'ital';
    public const AXIS_OPTICAL_SIZE = 'opsz';

    /**
     * Common weight values
     */
    public const WEIGHT_THIN = 100;
    public const WEIGHT_EXTRA_LIGHT = 200;
    public const WEIGHT_LIGHT = 300;
    public const WEIGHT_REGULAR = 400;
    public const WEIGHT_MEDIUM = 500;
    public const WEIGHT_SEMI_BOLD = 600;
    public const WEIGHT_BOLD = 700;
    public const WEIGHT_EXTRA_BOLD = 800;
    public const WEIGHT_BLACK = 900;

    /**
     * Common width values
     */
    public const WIDTH_ULTRA_CONDENSED = 50;
    public const WIDTH_EXTRA_CONDENSED = 62.5;
    public const WIDTH_CONDENSED = 75;
    public const WIDTH_SEMI_CONDENSED = 87.5;
    public const WIDTH_NORMAL = 100;
    public const WIDTH_SEMI_EXPANDED = 112.5;
    public const WIDTH_EXPANDED = 125;
    public const WIDTH_EXTRA_EXPANDED = 150;
    public const WIDTH_ULTRA_EXPANDED = 200;

    /**
     * Font file path
     */
    protected string $fontPath = '';

    /**
     * Font file contents
     */
    protected string $fontData = '';

    /**
     * Variation axes defined in the font
     *
     * @var array<string, TVariationAxis>
     */
    protected array $axes = [];

    /**
     * Named instances defined in the font
     *
     * @var array<string, TNamedInstance>
     */
    protected array $instances = [];

    /**
     * Current axis values
     *
     * @var array<string, float>
     */
    protected array $coordinates = [];

    /**
     * Whether the font has been parsed
     */
    protected bool $parsed = false;

    /**
     * Constructor
     *
     * @param string $fontPath Path to the font file
     */
    public function __construct(string $fontPath = '')
    {
        if ($fontPath !== '' && file_exists($fontPath)) {
            $this->fontPath = $fontPath;
            $this->fontData = file_get_contents($fontPath) ?: '';
        }
    }

    /**
     * Load font from file path
     *
     * @param string $fontPath Path to the font file
     * @return self
     */
    public function load(string $fontPath): self
    {
        if (file_exists($fontPath)) {
            $this->fontPath = $fontPath;
            $this->fontData = file_get_contents($fontPath) ?: '';
            $this->parsed = false;
        }
        return $this;
    }

    /**
     * Load font from data
     *
     * @param string $data Font binary data
     * @return self
     */
    public function loadData(string $data): self
    {
        $this->fontData = $data;
        $this->fontPath = '';
        $this->parsed = false;
        return $this;
    }

    /**
     * Check if the font is a variable font (has fvar table)
     *
     * @return bool True if the font has an fvar table
     */
    public function isVariableFont(): bool
    {
        if ($this->fontData === '') {
            return false;
        }

        return $this->findTable('fvar') !== null;
    }

    /**
     * Parse the font's variation data
     *
     * @return self
     */
    public function parse(): self
    {
        if ($this->parsed || $this->fontData === '') {
            return $this;
        }

        $fvarData = $this->getTableData('fvar');
        if ($fvarData === null) {
            $this->parsed = true;
            return $this;
        }

        // Parse fvar table
        $this->parseFvarTable($fvarData);
        $this->parsed = true;

        // Set default coordinates
        foreach ($this->axes as $tag => $axis) {
            $this->coordinates[$tag] = $axis['defaultValue'];
        }

        return $this;
    }

    /**
     * Get all variation axes
     *
     * @return array<string, TVariationAxis>
     */
    public function getAxes(): array
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->axes;
    }

    /**
     * Get a specific axis definition
     *
     * @param string $tag Axis tag (e.g., 'wght', 'wdth')
     * @return TVariationAxis|null
     */
    public function getAxis(string $tag): ?array
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->axes[$tag] ?? null;
    }

    /**
     * Check if font has a specific axis
     *
     * @param string $tag Axis tag
     * @return bool
     */
    public function hasAxis(string $tag): bool
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return isset($this->axes[$tag]);
    }

    /**
     * Get named instances
     *
     * @return array<string, TNamedInstance>
     */
    public function getInstances(): array
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->instances;
    }

    /**
     * Set axis value
     *
     * @param string $tag Axis tag
     * @param float $value Axis value
     * @return self
     */
    public function setAxisValue(string $tag, float $value): self
    {
        if (!$this->parsed) {
            $this->parse();
        }

        if (isset($this->axes[$tag])) {
            // Clamp to valid range
            $axis = $this->axes[$tag];
            $value = max($axis['minValue'], min($axis['maxValue'], $value));
            $this->coordinates[$tag] = $value;
        }

        return $this;
    }

    /**
     * Set multiple axis values
     *
     * @param array<string, float> $values Axis values
     * @return self
     */
    public function setAxisValues(array $values): self
    {
        foreach ($values as $tag => $value) {
            $this->setAxisValue($tag, $value);
        }
        return $this;
    }

    /**
     * Get current axis values
     *
     * @return array<string, float>
     */
    public function getCoordinates(): array
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->coordinates;
    }

    /**
     * Apply a named instance
     *
     * @param string $instanceName Name of the instance
     * @return self
     */
    public function applyInstance(string $instanceName): self
    {
        if (!$this->parsed) {
            $this->parse();
        }

        if (isset($this->instances[$instanceName])) {
            $this->coordinates = $this->instances[$instanceName]['coordinates'];
        }

        return $this;
    }

    /**
     * Set weight axis value
     *
     * @param float $weight Weight value (100-900)
     * @return self
     */
    public function setWeight(float $weight): self
    {
        return $this->setAxisValue(self::AXIS_WEIGHT, $weight);
    }

    /**
     * Set width axis value
     *
     * @param float $width Width value (50-200)
     * @return self
     */
    public function setWidth(float $width): self
    {
        return $this->setAxisValue(self::AXIS_WIDTH, $width);
    }

    /**
     * Set slant axis value
     *
     * @param float $slant Slant value (typically -20 to 20)
     * @return self
     */
    public function setSlant(float $slant): self
    {
        return $this->setAxisValue(self::AXIS_SLANT, $slant);
    }

    /**
     * Set italic axis value
     *
     * @param float $italic Italic value (0 or 1)
     * @return self
     */
    public function setItalic(float $italic): self
    {
        return $this->setAxisValue(self::AXIS_ITALIC, $italic);
    }

    /**
     * Get variation description string
     *
     * @return string CSS font-variation-settings style string
     */
    public function getVariationSettings(): string
    {
        if (!$this->parsed) {
            $this->parse();
        }

        $parts = [];
        foreach ($this->coordinates as $tag => $value) {
            $parts[] = sprintf("'%s' %.1f", $tag, $value);
        }

        return implode(', ', $parts);
    }

    /**
     * Find a table in the font file
     *
     * @param string $tag Table tag (4 characters)
     * @return array{offset: int, length: int}|null
     */
    protected function findTable(string $tag): ?array
    {
        if (strlen($this->fontData) < 12) {
            return null;
        }

        // Read number of tables from font header
        $numTables = $this->readUInt16(4);

        // Search table directory
        for ($i = 0; $i < $numTables; $i++) {
            $offset = 12 + ($i * 16);
            if ($offset + 16 > strlen($this->fontData)) {
                break;
            }

            $tableTag = substr($this->fontData, $offset, 4);
            if ($tableTag === $tag) {
                return [
                    'offset' => $this->readUInt32($offset + 8),
                    'length' => $this->readUInt32($offset + 12),
                ];
            }
        }

        return null;
    }

    /**
     * Get table data
     *
     * @param string $tag Table tag
     * @return string|null
     */
    protected function getTableData(string $tag): ?string
    {
        $table = $this->findTable($tag);
        if ($table === null) {
            return null;
        }

        if ($table['offset'] + $table['length'] > strlen($this->fontData)) {
            return null;
        }

        return substr($this->fontData, $table['offset'], $table['length']);
    }

    /**
     * Parse the fvar table
     *
     * @param string $data Table data
     */
    protected function parseFvarTable(string $data): void
    {
        if (strlen($data) < 16) {
            return;
        }

        // fvar header:
        // uint16 majorVersion
        // uint16 minorVersion
        // Offset16 axesArrayOffset
        // uint16 reserved
        // uint16 axisCount
        // uint16 axisSize (should be 20)
        // uint16 instanceCount
        // uint16 instanceSize

        $axisOffset = $this->readUInt16FromData($data, 4);
        $axisCount = $this->readUInt16FromData($data, 8);
        $axisSize = $this->readUInt16FromData($data, 10);
        $instanceCount = $this->readUInt16FromData($data, 12);
        $instanceSize = $this->readUInt16FromData($data, 14);

        // Parse axis definitions
        for ($i = 0; $i < $axisCount; $i++) {
            $offset = $axisOffset + ($i * $axisSize);
            if ($offset + 20 > strlen($data)) {
                break;
            }

            $tag = substr($data, $offset, 4);
            $minValue = $this->readFixed($data, $offset + 4);
            $defaultValue = $this->readFixed($data, $offset + 8);
            $maxValue = $this->readFixed($data, $offset + 12);
            // uint16 flags at offset + 16
            $axisNameID = $this->readUInt16FromData($data, $offset + 18);

            $this->axes[$tag] = [
                'tag' => $tag,
                'name' => $this->getAxisName($tag),
                'minValue' => $minValue,
                'defaultValue' => $defaultValue,
                'maxValue' => $maxValue,
            ];
        }

        // Parse named instances
        $instanceOffset = $axisOffset + ($axisCount * $axisSize);
        for ($i = 0; $i < $instanceCount; $i++) {
            $offset = $instanceOffset + ($i * $instanceSize);
            if ($offset + 4 + ($axisCount * 4) > strlen($data)) {
                break;
            }

            $subfamilyNameID = $this->readUInt16FromData($data, $offset);
            // uint16 flags at offset + 2

            $coordinates = [];
            $coordOffset = $offset + 4;
            foreach (array_keys($this->axes) as $tag) {
                $coordinates[$tag] = $this->readFixed($data, $coordOffset);
                $coordOffset += 4;
            }

            $instanceName = "Instance $i";
            $this->instances[$instanceName] = [
                'name' => $instanceName,
                'coordinates' => $coordinates,
            ];
        }
    }

    /**
     * Get human-readable axis name
     *
     * @param string $tag Axis tag
     * @return string
     */
    protected function getAxisName(string $tag): string
    {
        return match ($tag) {
            'wght' => 'Weight',
            'wdth' => 'Width',
            'slnt' => 'Slant',
            'ital' => 'Italic',
            'opsz' => 'Optical Size',
            'GRAD' => 'Grade',
            'XTRA' => 'X Transparent',
            'YTRA' => 'Y Transparent',
            'XOPQ' => 'X Opaque',
            'YOPQ' => 'Y Opaque',
            default => $tag,
        };
    }

    /**
     * Read unsigned 16-bit integer
     *
     * @param int $offset Offset in font data
     * @return int
     */
    protected function readUInt16(int $offset): int
    {
        if ($offset + 2 > strlen($this->fontData)) {
            return 0;
        }
        return unpack('n', substr($this->fontData, $offset, 2))[1] ?? 0;
    }

    /**
     * Read unsigned 16-bit integer from data string
     *
     * @param string $data Data string
     * @param int $offset Offset
     * @return int
     */
    protected function readUInt16FromData(string $data, int $offset): int
    {
        if ($offset + 2 > strlen($data)) {
            return 0;
        }
        return unpack('n', substr($data, $offset, 2))[1] ?? 0;
    }

    /**
     * Read unsigned 32-bit integer
     *
     * @param int $offset Offset in font data
     * @return int
     */
    protected function readUInt32(int $offset): int
    {
        if ($offset + 4 > strlen($this->fontData)) {
            return 0;
        }
        return unpack('N', substr($this->fontData, $offset, 4))[1] ?? 0;
    }

    /**
     * Read Fixed (16.16) value
     *
     * @param string $data Data string
     * @param int $offset Offset
     * @return float
     */
    protected function readFixed(string $data, int $offset): float
    {
        if ($offset + 4 > strlen($data)) {
            return 0.0;
        }

        $integer = unpack('n', substr($data, $offset, 2))[1] ?? 0;
        $fraction = unpack('n', substr($data, $offset + 2, 2))[1] ?? 0;

        // Handle signed integer part
        if ($integer >= 0x8000) {
            $integer -= 0x10000;
        }

        return $integer + ($fraction / 65536.0);
    }
}
