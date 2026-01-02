<?php

/**
 * Table.php
 *
 * Native table layout engine for PDF generation.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Table;

use Com\Tecnick\Pdf\Tcpdf;

/**
 * Table class for PDF tables
 *
 * Provides native table layout with:
 * - Auto column sizing based on content
 * - Cell spanning (colspan, rowspan)
 * - Automatic page breaks
 * - Header row repetition on new pages
 * - Customizable borders and backgrounds
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TCellStyle from TableCell
 *
 * @phpstan-type TTableStyle array{
 *     'borderWidth'?: float,
 *     'borderColor'?: string,
 *     'backgroundColor'?: string,
 *     'headerBackgroundColor'?: string,
 *     'headerTextColor'?: string,
 *     'cellPadding'?: float,
 *     'width'?: float,
 * }
 *
 * @phpstan-type TColumnDef array{
 *     'width'?: float,
 *     'minWidth'?: float,
 *     'maxWidth'?: float,
 *     'halign'?: string,
 *     'valign'?: string,
 * }
 */
class Table
{
    /**
     * PDF instance
     */
    protected Tcpdf $pdf;

    /**
     * Table rows containing cells
     *
     * @var array<int, array<int, TableCell>>
     */
    protected array $rows = [];

    /**
     * Header row indices (repeated on each page)
     *
     * @var array<int>
     */
    protected array $headerRows = [];

    /**
     * Column definitions
     *
     * @var array<int, TColumnDef>
     */
    protected array $columns = [];

    /**
     * Calculated column widths
     *
     * @var array<int, float>
     */
    protected array $columnWidths = [];

    /**
     * Calculated row heights
     *
     * @var array<int, float>
     */
    protected array $rowHeights = [];

    /**
     * Number of columns
     */
    protected int $numColumns = 0;

    /**
     * Table width in user units
     */
    protected float $tableWidth = 0;

    /**
     * Table X position
     */
    protected float $x = 0;

    /**
     * Table Y position
     */
    protected float $y = 0;

    /**
     * Default cell padding
     */
    protected float $cellPadding = 2;

    /**
     * Default border width
     */
    protected float $borderWidth = 0.2;

    /**
     * Default border color
     */
    protected string $borderColor = '#000000';

    /**
     * Auto-size columns based on content
     */
    protected bool $autoSize = true;

    /**
     * Table style
     *
     * @var TTableStyle
     */
    protected array $style = [];

    /**
     * Cell occupancy matrix for tracking spans
     *
     * @var array<int, array<int, bool>>
     */
    protected array $occupancy = [];

    /**
     * Constructor
     *
     * @param Tcpdf $pdf PDF instance
     * @param TTableStyle $style Table style
     */
    public function __construct(Tcpdf $pdf, array $style = [])
    {
        $this->pdf = $pdf;
        $this->style = $style;

        if (isset($style['borderWidth'])) {
            $this->borderWidth = $style['borderWidth'];
        }
        if (isset($style['borderColor'])) {
            $this->borderColor = $style['borderColor'];
        }
        if (isset($style['cellPadding'])) {
            $this->cellPadding = $style['cellPadding'];
        }
    }

    /**
     * Set table position
     *
     * @param float $x X position in user units
     * @param float $y Y position in user units
     */
    public function setPosition(float $x, float $y): self
    {
        $this->x = $x;
        $this->y = $y;
        return $this;
    }

    /**
     * Set table width
     *
     * @param float $width Table width in user units (0 = auto)
     */
    public function setWidth(float $width): self
    {
        $this->tableWidth = $width;
        return $this;
    }

    /**
     * Set column definitions
     *
     * @param array<int, TColumnDef> $columns Column definitions
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;
        $this->numColumns = count($columns);
        return $this;
    }

    /**
     * Add a column definition
     *
     * @param TColumnDef $column Column definition
     */
    public function addColumn(array $column = []): self
    {
        $this->columns[] = $column;
        $this->numColumns++;
        return $this;
    }

    /**
     * Set column widths directly
     *
     * @param array<int, float> $widths Column widths in user units
     */
    public function setColumnWidths(array $widths): self
    {
        $this->columnWidths = $widths;
        $this->autoSize = false;
        return $this;
    }

    /**
     * Enable/disable auto column sizing
     */
    public function setAutoSize(bool $autoSize): self
    {
        $this->autoSize = $autoSize;
        return $this;
    }

    /**
     * Add a header row
     *
     * @param array<int, string|TableCell> $cells Array of cell contents or TableCell objects
     * @param TCellStyle $style Default style for cells in this row
     */
    public function addHeaderRow(array $cells, array $style = []): self
    {
        $rowIndex = count($this->rows);
        $this->headerRows[] = $rowIndex;

        $defaultStyle = array_merge([
            'backgroundColor' => $this->style['headerBackgroundColor'] ?? '#e0e0e0',
            'textColor' => $this->style['headerTextColor'] ?? '#000000',
            'borderWidth' => $this->borderWidth,
            'borderColor' => $this->borderColor,
            'paddingTop' => $this->cellPadding,
            'paddingRight' => $this->cellPadding,
            'paddingBottom' => $this->cellPadding,
            'paddingLeft' => $this->cellPadding,
        ], $style);

        return $this->addRow($cells, $defaultStyle, true);
    }

    /**
     * Add a data row
     *
     * @param array<int, string|TableCell> $cells Array of cell contents or TableCell objects
     * @param TCellStyle $style Default style for cells in this row
     * @param bool $isHeader Whether this is a header row
     */
    public function addRow(array $cells, array $style = [], bool $isHeader = false): self
    {
        $rowIndex = count($this->rows);
        $row = [];
        $colIndex = 0;

        // Apply default style
        $defaultStyle = array_merge([
            'borderWidth' => $this->borderWidth,
            'borderColor' => $this->borderColor,
            'paddingTop' => $this->cellPadding,
            'paddingRight' => $this->cellPadding,
            'paddingBottom' => $this->cellPadding,
            'paddingLeft' => $this->cellPadding,
        ], $style);

        foreach ($cells as $cellData) {
            // Skip occupied cells due to rowspan from previous rows
            while ($this->isCellOccupied($rowIndex, $colIndex)) {
                $colIndex++;
            }

            $cell = $this->createCell($cellData, $defaultStyle);
            $cell->setRowIndex($rowIndex);
            $cell->setColIndex($colIndex);
            $cell->setIsHeader($isHeader);

            // Mark cells as occupied for colspan/rowspan
            $this->markCellOccupied($rowIndex, $colIndex, $cell->getRowspan(), $cell->getColspan());

            $row[$colIndex] = $cell;
            $colIndex += $cell->getColspan();
        }

        // Update number of columns if needed
        $this->numColumns = max($this->numColumns, $colIndex);

        $this->rows[$rowIndex] = $row;
        return $this;
    }

    /**
     * Create a table cell from various input types
     *
     * @param string|TableCell $data Cell data
     * @param TCellStyle $defaultStyle Default style
     */
    protected function createCell(string|TableCell $data, array $defaultStyle): TableCell
    {
        if ($data instanceof TableCell) {
            // Merge default style with cell's style
            $data->mergeStyle($defaultStyle);
            return $data;
        }

        return new TableCell($data, 1, 1, $defaultStyle);
    }

    /**
     * Check if a cell position is occupied by a span
     */
    protected function isCellOccupied(int $row, int $col): bool
    {
        return $this->occupancy[$row][$col] ?? false;
    }

    /**
     * Mark cells as occupied due to rowspan/colspan
     */
    protected function markCellOccupied(int $row, int $col, int $rowspan, int $colspan): void
    {
        for ($r = $row; $r < $row + $rowspan; $r++) {
            for ($c = $col; $c < $col + $colspan; $c++) {
                if ($r !== $row || $c !== $col) {
                    $this->occupancy[$r][$c] = true;
                }
            }
        }
    }

    /**
     * Calculate table layout (column widths and row heights)
     */
    public function calculate(): self
    {
        if ($this->autoSize && empty($this->columnWidths)) {
            $this->calculateColumnWidths();
        }

        $this->calculateRowHeights();
        return $this;
    }

    /**
     * Calculate optimal column widths
     */
    protected function calculateColumnWidths(): void
    {
        // Initialize column widths
        $this->columnWidths = array_fill(0, $this->numColumns, 0);
        $contentWidths = array_fill(0, $this->numColumns, 0);

        // Calculate minimum width needed for each column based on content
        foreach ($this->rows as $row) {
            foreach ($row as $cell) {
                if ($cell->getColspan() === 1) {
                    $colIndex = $cell->getColIndex();
                    $textWidth = $this->measureCellWidth($cell);
                    $contentWidths[$colIndex] = max($contentWidths[$colIndex], $textWidth);
                }
            }
        }

        // Handle spanned cells (distribute width proportionally)
        foreach ($this->rows as $row) {
            foreach ($row as $cell) {
                if ($cell->getColspan() > 1) {
                    $startCol = $cell->getColIndex();
                    $endCol = $startCol + $cell->getColspan();
                    $textWidth = $this->measureCellWidth($cell);
                    $currentWidth = 0;

                    for ($c = $startCol; $c < $endCol; $c++) {
                        $currentWidth += $contentWidths[$c];
                    }

                    if ($textWidth > $currentWidth) {
                        $extra = ($textWidth - $currentWidth) / $cell->getColspan();
                        for ($c = $startCol; $c < $endCol; $c++) {
                            $contentWidths[$c] += $extra;
                        }
                    }
                }
            }
        }

        // Apply column constraints from definitions
        foreach ($this->columns as $index => $colDef) {
            if (isset($colDef['width'])) {
                $contentWidths[$index] = $colDef['width'];
            } elseif (isset($colDef['minWidth'])) {
                $contentWidths[$index] = max($contentWidths[$index], $colDef['minWidth']);
            }

            if (isset($colDef['maxWidth'])) {
                $contentWidths[$index] = min($contentWidths[$index], $colDef['maxWidth']);
            }
        }

        // Calculate total content width
        $totalContentWidth = array_sum($contentWidths);

        // Determine table width
        if ($this->tableWidth <= 0) {
            $region = $this->pdf->page->getRegion();
            $this->tableWidth = $region['RW'] - $this->x;
        }

        // Scale columns to fit table width
        if ($totalContentWidth > 0) {
            $scale = $this->tableWidth / $totalContentWidth;
            for ($c = 0; $c < $this->numColumns; $c++) {
                $this->columnWidths[$c] = $contentWidths[$c] * $scale;
            }
        } else {
            // Equal distribution
            $colWidth = $this->tableWidth / max(1, $this->numColumns);
            $this->columnWidths = array_fill(0, $this->numColumns, $colWidth);
        }
    }

    /**
     * Measure the width needed for a cell's content
     */
    protected function measureCellWidth(TableCell $cell): float
    {
        $content = $cell->getContent();
        if ($content === '') {
            return $this->cellPadding * 2;
        }

        // Get text dimensions from font stack
        $ordarr = $this->pdf->uniconv->strToOrdArr($content);
        $dim = $this->pdf->font->getOrdArrDims($ordarr);
        $textWidth = $this->pdf->toUnit($dim['totwidth']);

        // Add padding
        return $textWidth + $cell->getPadding('left') + $cell->getPadding('right');
    }

    /**
     * Calculate row heights
     */
    protected function calculateRowHeights(): void
    {
        $this->rowHeights = array_fill(0, count($this->rows), 0);

        // First pass: calculate minimum heights for non-spanned cells
        foreach ($this->rows as $rowIndex => $row) {
            foreach ($row as $cell) {
                if ($cell->getRowspan() === 1) {
                    $cellHeight = $this->measureCellHeight($cell);
                    $this->rowHeights[$rowIndex] = max(
                        $this->rowHeights[$rowIndex],
                        $cellHeight
                    );
                }
            }
        }

        // Ensure minimum height
        foreach ($this->rowHeights as $index => $height) {
            if ($height <= 0) {
                $this->rowHeights[$index] = $this->pdf->toUnit(
                    $this->pdf->font->getCurrentFont()['height']
                ) + $this->cellPadding * 2;
            }
        }

        // Second pass: handle row-spanning cells
        foreach ($this->rows as $rowIndex => $row) {
            foreach ($row as $cell) {
                if ($cell->getRowspan() > 1) {
                    $startRow = $cell->getRowIndex();
                    $endRow = $startRow + $cell->getRowspan();
                    $cellHeight = $this->measureCellHeight($cell);

                    // Calculate current combined height
                    $currentHeight = 0;
                    for ($r = $startRow; $r < $endRow; $r++) {
                        $currentHeight += $this->rowHeights[$r];
                    }

                    // Distribute extra height if needed
                    if ($cellHeight > $currentHeight) {
                        $extra = ($cellHeight - $currentHeight) / $cell->getRowspan();
                        for ($r = $startRow; $r < $endRow; $r++) {
                            $this->rowHeights[$r] += $extra;
                        }
                    }
                }
            }
        }
    }

    /**
     * Measure the height needed for a cell's content
     */
    protected function measureCellHeight(TableCell $cell): float
    {
        $content = $cell->getContent();
        if ($content === '') {
            $fontHeight = $this->pdf->toUnit($this->pdf->font->getCurrentFont()['height']);
            return $fontHeight + $cell->getPadding('top') + $cell->getPadding('bottom');
        }

        // Calculate width available for text
        $startCol = $cell->getColIndex();
        $cellWidth = 0;
        for ($c = $startCol; $c < $startCol + $cell->getColspan(); $c++) {
            $cellWidth += $this->columnWidths[$c] ?? 0;
        }

        $textWidth = $cellWidth - $cell->getPadding('left') - $cell->getPadding('right');
        if ($textWidth <= 0) {
            $textWidth = 10; // Minimum
        }

        // Split text into lines and calculate height
        $ordarr = $this->pdf->uniconv->strToOrdArr($content);
        $dim = $this->pdf->font->getOrdArrDims($ordarr);
        $lines = $this->pdf->splitLines($ordarr, $dim, $this->pdf->toPoints($textWidth));

        $numLines = max(1, count($lines));
        $fontHeight = $this->pdf->toUnit($this->pdf->font->getCurrentFont()['height']);
        $textHeight = $fontHeight * $numLines;

        return $textHeight + $cell->getPadding('top') + $cell->getPadding('bottom');
    }

    /**
     * Render the table to the PDF
     *
     * @param int $pid Page ID (-1 for current page)
     * @return self
     */
    public function render(int $pid = -1): self
    {
        if ($pid < 0) {
            $pid = $this->pdf->page->getPageId();
        }

        // Ensure calculations are done
        $this->calculate();

        // Get starting position
        $region = $this->pdf->page->getRegion($pid);
        $startX = $this->x + $region['RX'];
        $startY = $this->y + $region['RY'];
        $availableHeight = $region['RH'] - $this->y;

        $currentY = $startY;
        $rowIndex = 0;
        $pageStartRow = 0;
        $onNewPage = false;

        while ($rowIndex < count($this->rows)) {
            $rowHeight = $this->rowHeights[$rowIndex];

            // Check if we need a page break
            if ($currentY + $rowHeight > $startY + $availableHeight) {
                // Move to next page
                $this->pdf->page->getNextRegion($pid);
                $newPid = $this->pdf->page->getPageId();

                if ($newPid > $pid) {
                    $pid = $newPid;
                    $this->pdf->setPageContext($pid);
                }

                $region = $this->pdf->page->getRegion($pid);
                $currentY = $region['RY'];
                $availableHeight = $region['RH'];
                $pageStartRow = $rowIndex;
                $onNewPage = true;

                // Render header rows on new page
                if (!empty($this->headerRows)) {
                    foreach ($this->headerRows as $headerRowIndex) {
                        $this->renderRow($headerRowIndex, $startX, $currentY, $pid);
                        $currentY += $this->rowHeights[$headerRowIndex];
                    }
                }

                // Skip if current row is a header row (already rendered)
                if (in_array($rowIndex, $this->headerRows)) {
                    $rowIndex++;
                    continue;
                }
            }

            $this->renderRow($rowIndex, $startX, $currentY, $pid);
            $currentY += $rowHeight;
            $rowIndex++;
        }

        return $this;
    }

    /**
     * Render a single row
     *
     * @param int $rowIndex Row index
     * @param float $startX Starting X position
     * @param float $startY Starting Y position
     * @param int $pid Page ID
     */
    protected function renderRow(int $rowIndex, float $startX, float $startY, int $pid): void
    {
        if (!isset($this->rows[$rowIndex])) {
            return;
        }

        $row = $this->rows[$rowIndex];
        $rowHeight = $this->rowHeights[$rowIndex];
        $currentX = $startX;

        // Process each column position
        for ($colIndex = 0; $colIndex < $this->numColumns; $colIndex++) {
            $colWidth = $this->columnWidths[$colIndex];

            if (isset($row[$colIndex])) {
                $cell = $row[$colIndex];

                // Calculate cell dimensions including spans
                $cellWidth = 0;
                for ($c = $colIndex; $c < $colIndex + $cell->getColspan(); $c++) {
                    $cellWidth += $this->columnWidths[$c] ?? 0;
                }

                $cellHeight = 0;
                for ($r = $rowIndex; $r < $rowIndex + $cell->getRowspan(); $r++) {
                    $cellHeight += $this->rowHeights[$r] ?? 0;
                }

                // Only render if this is the origin cell (not spanned)
                if ($cell->getRowIndex() === $rowIndex && $cell->getColIndex() === $colIndex) {
                    $this->renderCell($cell, $currentX, $startY, $cellWidth, $cellHeight, $pid);
                }
            }

            $currentX += $colWidth;
        }
    }

    /**
     * Render a single cell
     *
     * @param TableCell $cell Cell to render
     * @param float $x X position
     * @param float $y Y position
     * @param float $width Cell width
     * @param float $height Cell height
     * @param int $pid Page ID
     */
    protected function renderCell(
        TableCell $cell,
        float $x,
        float $y,
        float $width,
        float $height,
        int $pid
    ): void {
        // Build cell border styles
        $styles = $this->buildCellStyles($cell);

        // Build cell definition
        $cellDef = [
            'margin' => ['T' => 0, 'R' => 0, 'B' => 0, 'L' => 0],
            'padding' => [
                'T' => $this->pdf->toPoints($cell->getPadding('top')),
                'R' => $this->pdf->toPoints($cell->getPadding('right')),
                'B' => $this->pdf->toPoints($cell->getPadding('bottom')),
                'L' => $this->pdf->toPoints($cell->getPadding('left')),
            ],
            'borderpos' => 0.5,
        ];

        // Draw cell background and borders
        $out = $this->pdf->drawCell(
            $this->pdf->toPoints($x),
            $this->pdf->toYPoints($y),
            $this->pdf->toPoints($width),
            $this->pdf->toPoints($height),
            $styles,
            $cellDef
        );

        // Add text content
        $content = $cell->getContent();
        if ($content !== '') {
            $textX = $x + $cell->getPadding('left');
            $textY = $y + $cell->getPadding('top');
            $textWidth = $width - $cell->getPadding('left') - $cell->getPadding('right');
            $textHeight = $height - $cell->getPadding('top') - $cell->getPadding('bottom');

            $out .= $this->pdf->getTextCell(
                $content,
                $textX,
                $textY,
                $textWidth,
                $textHeight,
                0, // offset
                0, // linespace
                $cell->getVAlign(),
                $cell->getHAlign(),
                null, // cell def
                [], // no additional border styles (already drawn)
                0, // stroke width
                0, // word spacing
                0, // leading
                0, // rise
                true, // jlast
                true, // fill
                false, // stroke
                false, // underline
                false, // linethrough
                false, // overline
                false, // clip
                false, // drawcell
                '', // forcedir
                null // shadow
            );
        }

        $this->pdf->page->addContent($out, $pid);
    }

    /**
     * Build cell border and fill styles
     *
     * @param TableCell $cell Cell to style
     * @return array<int|string, array<string, mixed>>
     */
    protected function buildCellStyles(TableCell $cell): array
    {
        $borderWidth = $cell->getBorderWidth();
        $borderColor = $cell->getBorderColor();
        $bgColor = $cell->getBackgroundColor();

        // Convert colors
        $lineColor = '';
        if ($borderColor) {
            $lineColor = $this->pdf->color->getColorObject($borderColor)->getPdfColor(false);
        }

        $fillColor = '';
        if ($bgColor) {
            $fillColor = $this->pdf->color->getColorObject($bgColor)->getPdfColor(true);
        }

        $baseStyle = [
            'lineWidth' => $borderWidth,
            'lineColor' => $lineColor,
            'fillColor' => $fillColor,
        ];

        // Per-side borders (0=top, 1=right, 2=bottom, 3=left)
        $styles = [];

        // Top border
        $styles[0] = $baseStyle;
        if (!$cell->hasBorder('top')) {
            $styles[0]['lineWidth'] = 0;
        }

        // Right border
        $styles[1] = $baseStyle;
        if (!$cell->hasBorder('right')) {
            $styles[1]['lineWidth'] = 0;
        }

        // Bottom border
        $styles[2] = $baseStyle;
        if (!$cell->hasBorder('bottom')) {
            $styles[2]['lineWidth'] = 0;
        }

        // Left border
        $styles[3] = $baseStyle;
        if (!$cell->hasBorder('left')) {
            $styles[3]['lineWidth'] = 0;
        }

        return $styles;
    }

    /**
     * Get the total calculated height of the table
     */
    public function getTotalHeight(): float
    {
        return array_sum($this->rowHeights);
    }

    /**
     * Get calculated column widths
     *
     * @return array<int, float>
     */
    public function getColumnWidths(): array
    {
        return $this->columnWidths;
    }

    /**
     * Get calculated row heights
     *
     * @return array<int, float>
     */
    public function getRowHeights(): array
    {
        return $this->rowHeights;
    }

    /**
     * Get number of rows
     */
    public function getRowCount(): int
    {
        return count($this->rows);
    }

    /**
     * Get number of columns
     */
    public function getColumnCount(): int
    {
        return $this->numColumns;
    }

    /**
     * Clear all table data
     */
    public function clear(): self
    {
        $this->rows = [];
        $this->headerRows = [];
        $this->occupancy = [];
        $this->columnWidths = [];
        $this->rowHeights = [];
        return $this;
    }
}
