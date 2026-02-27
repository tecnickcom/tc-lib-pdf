<?php

/**
 * TableCell.php
 *
 * Represents a cell in a PDF table.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Table;

/**
 * Table Cell class
 *
 * Represents a single cell in a table with its content and properties.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type TCellStyle array{
 *     'backgroundColor'?: string,
 *     'borderColor'?: string,
 *     'borderWidth'?: float,
 *     'borderTop'?: bool,
 *     'borderRight'?: bool,
 *     'borderBottom'?: bool,
 *     'borderLeft'?: bool,
 *     'paddingTop'?: float,
 *     'paddingRight'?: float,
 *     'paddingBottom'?: float,
 *     'paddingLeft'?: float,
 *     'textColor'?: string,
 *     'fontFamily'?: string,
 *     'fontSize'?: float,
 *     'fontStyle'?: string,
 * }
 */
class TableCell
{
    /**
     * Cell content (text or markup)
     */
    protected string $content = '';

    /**
     * Number of columns this cell spans
     */
    protected int $colspan = 1;

    /**
     * Number of rows this cell spans
     */
    protected int $rowspan = 1;

    /**
     * Horizontal alignment (L=left, C=center, R=right, J=justify)
     */
    protected string $halign = 'L';

    /**
     * Vertical alignment (T=top, C=center, B=bottom)
     */
    protected string $valign = 'T';

    /**
     * Cell style properties
     *
     * @var TCellStyle
     */
    protected array $style = [];

    /**
     * Calculated cell width in user units
     */
    protected float $width = 0;

    /**
     * Calculated cell height in user units
     */
    protected float $height = 0;

    /**
     * Calculated minimum height based on content
     */
    protected float $minHeight = 0;

    /**
     * Cell X position
     */
    protected float $x = 0;

    /**
     * Cell Y position
     */
    protected float $y = 0;

    /**
     * Whether this cell is a header cell
     */
    protected bool $isHeader = false;

    /**
     * Row index this cell belongs to
     */
    protected int $rowIndex = -1;

    /**
     * Column index this cell starts at
     */
    protected int $colIndex = -1;

    /**
     * Constructor
     *
     * @param string $content Cell content
     * @param int $colspan Number of columns to span
     * @param int $rowspan Number of rows to span
     * @param TCellStyle $style Cell style properties
     */
    public function __construct(
        string $content = '',
        int $colspan = 1,
        int $rowspan = 1,
        array $style = []
    ) {
        $this->content = $content;
        $this->colspan = max(1, $colspan);
        $this->rowspan = max(1, $rowspan);
        $this->style = $style;
    }

    /**
     * Get cell content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set cell content
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get colspan
     */
    public function getColspan(): int
    {
        return $this->colspan;
    }

    /**
     * Set colspan
     */
    public function setColspan(int $colspan): self
    {
        $this->colspan = max(1, $colspan);
        return $this;
    }

    /**
     * Get rowspan
     */
    public function getRowspan(): int
    {
        return $this->rowspan;
    }

    /**
     * Set rowspan
     */
    public function setRowspan(int $rowspan): self
    {
        $this->rowspan = max(1, $rowspan);
        return $this;
    }

    /**
     * Get horizontal alignment
     */
    public function getHAlign(): string
    {
        return $this->halign;
    }

    /**
     * Set horizontal alignment
     */
    public function setHAlign(string $halign): self
    {
        $this->halign = $halign;
        return $this;
    }

    /**
     * Get vertical alignment
     */
    public function getVAlign(): string
    {
        return $this->valign;
    }

    /**
     * Set vertical alignment
     */
    public function setVAlign(string $valign): self
    {
        $this->valign = $valign;
        return $this;
    }

    /**
     * Get cell style
     *
     * @return TCellStyle
     */
    public function getStyle(): array
    {
        return $this->style;
    }

    /**
     * Set cell style
     *
     * @param TCellStyle $style
     */
    public function setStyle(array $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Merge style with existing
     *
     * @param TCellStyle $style
     */
    public function mergeStyle(array $style): self
    {
        $this->style = array_merge($this->style, $style);
        return $this;
    }

    /**
     * Get calculated width
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * Set calculated width
     */
    public function setWidth(float $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Get calculated height
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * Set calculated height
     */
    public function setHeight(float $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Get minimum height
     */
    public function getMinHeight(): float
    {
        return $this->minHeight;
    }

    /**
     * Set minimum height
     */
    public function setMinHeight(float $minHeight): self
    {
        $this->minHeight = $minHeight;
        return $this;
    }

    /**
     * Get X position
     */
    public function getX(): float
    {
        return $this->x;
    }

    /**
     * Set X position
     */
    public function setX(float $x): self
    {
        $this->x = $x;
        return $this;
    }

    /**
     * Get Y position
     */
    public function getY(): float
    {
        return $this->y;
    }

    /**
     * Set Y position
     */
    public function setY(float $y): self
    {
        $this->y = $y;
        return $this;
    }

    /**
     * Check if this is a header cell
     */
    public function isHeader(): bool
    {
        return $this->isHeader;
    }

    /**
     * Set header flag
     */
    public function setIsHeader(bool $isHeader): self
    {
        $this->isHeader = $isHeader;
        return $this;
    }

    /**
     * Get row index
     */
    public function getRowIndex(): int
    {
        return $this->rowIndex;
    }

    /**
     * Set row index
     */
    public function setRowIndex(int $rowIndex): self
    {
        $this->rowIndex = $rowIndex;
        return $this;
    }

    /**
     * Get column index
     */
    public function getColIndex(): int
    {
        return $this->colIndex;
    }

    /**
     * Set column index
     */
    public function setColIndex(int $colIndex): self
    {
        $this->colIndex = $colIndex;
        return $this;
    }

    /**
     * Get padding value
     *
     * @param string $side 'top', 'right', 'bottom', 'left'
     * @param float $default Default value if not set
     */
    public function getPadding(string $side, float $default = 1): float
    {
        $key = 'padding' . ucfirst($side);
        return $this->style[$key] ?? $default;
    }

    /**
     * Get border visibility
     *
     * @param string $side 'top', 'right', 'bottom', 'left'
     */
    public function hasBorder(string $side): bool
    {
        $key = 'border' . ucfirst($side);
        return $this->style[$key] ?? true;
    }

    /**
     * Get border width
     */
    public function getBorderWidth(): float
    {
        return $this->style['borderWidth'] ?? 0.2;
    }

    /**
     * Get border color
     */
    public function getBorderColor(): string
    {
        return $this->style['borderColor'] ?? '#000000';
    }

    /**
     * Get background color
     */
    public function getBackgroundColor(): ?string
    {
        return $this->style['backgroundColor'] ?? null;
    }
}
