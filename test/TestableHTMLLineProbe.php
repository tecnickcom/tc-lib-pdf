<?php

/**
 * TestableHTMLLineProbe.php
 *
 * @since     2026-10-10
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

/**
 * Records the position and the width of every text line written.
 *
 * @since     2026-10-10
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class TestableHTMLLineProbe extends \Com\Tecnick\Pdf\Tcpdf
{
    /**
     * Abscissa, width and baseline (user units) of each text line with glyphs.
     *
     * @var list<array{x: float, w: float, y: float}>
     */
    private array $lines = [];

    /**
     * Returns the recorded lines.
     *
     * @return list<array{x: float, w: float, y: float}>
     */
    public function getProbeLines(): array
    {
        return $this->lines;
    }

    /**
     * Clears the recorded lines.
     */
    public function resetProbeLines(): void
    {
        $this->lines = [];
    }

    /**
     * Returns the abscissa and the width of the glyphs of a line, without the spaces
     * at its ends.
     *
     * @param array<int, int> $ordarr Code points of the line in visual order.
     *
     * @return array{x: float, w: float, y: float}
     *
     * @throws \Com\Tecnick\Pdf\Font\Exception
     */
    private function getInkExtent(array $ordarr, float $posx, float $posy, float $width, float $wordspacing): array
    {
        $ordarr = \array_values($ordarr);
        $lead = 0;
        while (($ordarr[$lead] ?? 0) === 32) {
            ++$lead;
        }

        $trimmed = \array_slice($ordarr, $lead);
        while ($trimmed !== [] && \end($trimmed) === 32) {
            \array_pop($trimmed);
        }

        $space = $this->toUnit($this->font->getCharWidth(32));
        if ($width > 0) {
            return ['x' => $posx + ($lead * $space), 'w' => $width - ($lead * $space), 'y' => $posy];
        }

        $dim = $this->font->getOrdArrDims($trimmed);
        $inner = \count(\array_keys($trimmed, 32, true));

        return [
            'x' => $posx + ($lead * ($space + $wordspacing)),
            'w' => $this->toUnit($dim['totwidth']) + ($inner * $wordspacing),
            'y' => $posy,
        ];
    }

    protected function getOutTextLine(
        string $txt,
        array $ordarr,
        array $dim,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
        ?array $shadow = null,
    ): string {
        if (\trim($txt) !== '') {
            $this->lines[] = $this->getInkExtent($ordarr, $posx, $posy, $width, $wordspacing);
        }

        return parent::getOutTextLine(
            $txt,
            $ordarr,
            $dim,
            $posx,
            $posy,
            $width,
            $strokewidth,
            $wordspacing,
            $leading,
            $rise,
            $fill,
            $stroke,
            $underline,
            $linethrough,
            $overline,
            $clip,
            $shadow,
        );
    }
}
