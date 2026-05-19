<?php

/**
 * TestableHTMLBBoxProbe.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

/**
 * @phpstan-type BBoxTraceEntry array{
 *     txt: string, in_x: float, in_y: float,
 *     bbox_x: float, bbox_y: float, bbox_w: float, bbox_h: float,
 *     bbox_end_x: float, font_size: float
 * }
 */
class TestableHTMLBBoxProbe extends TestableHTML
{
    /**
     * @var array<int, BBoxTraceEntry>
     */
    private array $bboxTrace = [];

    /**
     * @return array<int, BBoxTraceEntry>
     */
    public function exposeGetBBoxTrace(): array
    {
        return $this->bboxTrace;
    }

    public function exposeResetBBoxTrace(): void
    {
        $this->bboxTrace = [];
    }

    public function getTextCell(
        string $txt,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        float $offset = 0,
        float $linespace = 0,
        string $valign = 'C',
        string $halign = 'C',
        ?array $cell = null,
        array $styles = [],
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        bool $jlast = true,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
        bool $drawcell = true,
        string $forcedir = '',
        ?array $shadow = null,
        string $fit = '',
    ): string {
        $out = parent::getTextCell(
            $txt,
            $posx,
            $posy,
            $width,
            $height,
            $offset,
            $linespace,
            $valign,
            $halign,
            $cell,
            $styles,
            $strokewidth,
            $wordspacing,
            $leading,
            $rise,
            $jlast,
            $fill,
            $stroke,
            $underline,
            $linethrough,
            $overline,
            $clip,
            $drawcell,
            $forcedir,
            $shadow,
            $fit,
        );

        $bbox = $this->getLastBBox();
        /** @var array<string, mixed> $curfont */
        $curfont = $this->font->getCurrentFont();
        $bboxX = isset($bbox['x']) && \is_numeric($bbox['x']) ? (float) $bbox['x'] : 0.0;
        $bboxY = isset($bbox['y']) && \is_numeric($bbox['y']) ? (float) $bbox['y'] : 0.0;
        $bboxW = isset($bbox['w']) && \is_numeric($bbox['w']) ? (float) $bbox['w'] : 0.0;
        $bboxH = isset($bbox['h']) && \is_numeric($bbox['h']) ? (float) $bbox['h'] : 0.0;
        $fontSize = isset($curfont['size']) && \is_numeric($curfont['size']) ? (float) $curfont['size'] : 0.0;
        $this->bboxTrace[] = [
            'txt' => $txt,
            'in_x' => $posx,
            'in_y' => $posy,
            'bbox_x' => $bboxX,
            'bbox_y' => $bboxY,
            'bbox_w' => $bboxW,
            'bbox_h' => $bboxH,
            'bbox_end_x' => $bboxX + $bboxW,
            'font_size' => $fontSize,
        ];

        return $out;
    }
}
