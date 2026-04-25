<?php

/**
 * TestableText.php
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
 * @phpstan-import-type TTextDims from \Com\Tecnick\Pdf\Font\Stack
 * @phpstan-import-type TTMatrix from \Com\Tecnick\Pdf\Graph\Base
 * @phpstan-import-type TextShadow from \Com\Tecnick\Pdf\Text
 * @phpstan-import-type TextLinePos from \Com\Tecnick\Pdf\Text
 */
class TestableText extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeGetOutUTOLine(float $pntx, float $pnty, float $pwidth, float $psize): string
    {
        return $this->getOutUTOLine($pntx, $pnty, $pwidth, $psize);
    }

    public function exposeCleanupText(string $txt): string
    {
        return $this->cleanupText($txt);
    }

    public function exposeGetOutTextPosXY(string $raw, float $posx = 0, float $posy = 0, string $mode = 'Td'): string
    {
        return $this->getOutTextPosXY($raw, $posx, $posy, $mode);
    }

    public function exposeGetTextRenderingMode(bool $fill = true, bool $stroke = false, bool $clip = false): int
    {
        return $this->getTextRenderingMode($fill, $stroke, $clip);
    }

    public function exposeGetOutTextStateOperatorTc(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTc($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTw(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTw($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTz(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTz($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTL(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTL($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTr(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTr($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTs(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTs($raw, $value);
    }

    public function exposeGetOutTextStateOperatorw(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorw($raw, $value);
    }

    /** @phpstan-param array<int, int|float> $matrix */
    public function exposeGetOutTextPosMatrix(string $raw, array $matrix = [1, 0, 0, 1, 0, 0]): string
    {
        if (\count($matrix) !== 6) {
            return '';
        }
        /** @var TTMatrix $textMatrix */
        $textMatrix = [
            (float) $matrix[0],
            (float) $matrix[1],
            (float) $matrix[2],
            (float) $matrix[3],
            (float) $matrix[4],
            (float) $matrix[5],
        ];
        return $this->getOutTextPosMatrix($raw, $textMatrix);
    }

    /** @phpstan-param array<int, int|float> $matrix */
    public function exposeRawGetOutTextPosMatrix(string $raw, array $matrix): string
    {
        /** @var array{float, float, float, float, float, float}|array<int, int|float> $matrix */
        // @phpstan-ignore argument.type
        return $this->getOutTextPosMatrix($raw, $matrix);
    }

    public function exposeGetOutTextShowing(string $str, string $mode = 'Tj'): string
    {
        return $this->getOutTextShowing($str, $mode);
    }

    public function exposeGetOutTextObject(string $raw = ''): string
    {
        return $this->getOutTextObject($raw);
    }

    /**
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-return array<int, int>
     */
    public function exposeReplaceUnicodeChars(array $ordarr): array
    {
        return $this->replaceUnicodeChars($ordarr);
    }

    /**
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-return array<int, int>
     */
    public function exposeRemoveOrdArrSoftHyphens(array $ordarr): array
    {
        return $this->removeOrdArrSoftHyphens($ordarr);
    }

    /**
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-return array<int, int>
     */
    public function exposeAddOrdArrBreakPoints(array $ordarr): array
    {
        return $this->addOrdArrBreakPoints($ordarr);
    }

    public function exposeSetPageContext(int $pid = -1): void
    {
        $this->setPageContext($pid);
    }

    public function exposeEscapePerc(string $str): string
    {
        return $this->escapePerc($str);
    }

    public function exposeGetStringWidth(string $str): float
    {
        return $this->getStringWidth($str);
    }

    /** @phpstan-return array{0: string, 1: array<int, int>, 2: TTextDims} */
    public function exposePrepareText(string $txt, string $forcedir = ''): array
    {
        $ordarr = [];
        $dim = self::DIM_DEFAULT;
        $this->prepareText($txt, $ordarr, $dim, $forcedir);
        return [$txt, $ordarr, $dim];
    }

    /**
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-param TTextDims $dim
     * @phpstan-return array<int, TextLinePos>
     */
    public function exposeSplitLines(array $ordarr, array $dim, float $pwidth, float $poffset = 0): array
    {
        return $this->splitLines($ordarr, $dim, $pwidth, $poffset);
    }

    /**
      * @phpstan-param array<int, int> $ordarr
      * @phpstan-param TTextDims|array{} $dim
     * @phpstan-param TextShadow|null $shadow
     */
    public function exposeGetOutTextLine(
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
        if (($txt === '') || ($dim === [])) {
            return '';
        }
        /** @var TTextDims $lineDim */
        $lineDim = \array_replace(self::DIM_DEFAULT, $dim);
        return $this->getOutTextLine(
            $txt,
            $ordarr,
            $lineDim,
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

    /**
      * @phpstan-param array<int, int> $ordarr
      * @phpstan-param TTextDims|array{} $dim
     * @phpstan-param TextShadow|null $shadow
     */
    public function exposeRawGetOutTextLine(
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
        if (($txt === '') || ($dim === [])) {
            return '';
        }
        /** @var TTextDims $lineDim */
        $lineDim = \array_replace(self::DIM_DEFAULT, $dim);
        return $this->getOutTextLine(
            $txt,
            $ordarr,
            $lineDim,
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
            $shadow
        );
    }

    /**
      * @phpstan-param array<int, int> $ordarr
      * @phpstan-param TTextDims|array{} $dim
     */
    public function exposeOutTextLine(
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
    ): string {
        if (($txt === '') || ($dim === [])) {
            return '';
        }
        /** @var TTextDims $lineDim */
        $lineDim = \array_replace(self::DIM_DEFAULT, $dim);
        return $this->outTextLine(
            $txt,
            $ordarr,
            $lineDim,
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
        );
    }

    /**
      * @phpstan-param array<int, int> $ordarr
      * @phpstan-param TTextDims|array{} $dim
     */
    public function exposeRawOutTextLine(
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
    ): string {
        if (($txt === '') || ($dim === [])) {
            return '';
        }
        /** @var TTextDims $lineDim */
        $lineDim = \array_replace(self::DIM_DEFAULT, $dim);
        return $this->outTextLine(
            $txt,
            $ordarr,
            $lineDim,
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
            $clip
        );
    }

    /**
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-param array<int, TextLinePos> $lines
     * @phpstan-param TextShadow|null $shadow
     */
    public function exposeOutTextLines(
        array $ordarr,
        array $lines,
        float $posx,
        float $posy,
        float $width,
        float $offset,
        float $fontascent,
        float $linespace = 0,
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        string $halign = '',
        bool $jlast = true,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
        ?array $shadow = null,
    ): string {
        if (($ordarr === []) || ($lines === [])) {
            return '';
        }
        return $this->outTextLines(
            $ordarr,
            $lines,
            $posx,
            $posy,
            $width,
            $offset,
            $fontascent,
            $linespace,
            $strokewidth,
            $wordspacing,
            $leading,
            $rise,
            $halign,
            $jlast,
            $fill,
            $stroke,
            $underline,
            $linethrough,
            $overline,
            $clip,
            $shadow,
        );
    }

    /**
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-param array<int, TextLinePos> $lines
     * @phpstan-param TextShadow|null $shadow
     */
    public function exposeRawOutTextLines(
        array $ordarr,
        array $lines,
        float $posx,
        float $posy,
        float $width,
        float $offset,
        float $fontascent,
        float $linespace = 0,
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        string $halign = '',
        bool $jlast = true,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
        ?array $shadow = null,
    ): string {
        return $this->outTextLines(
            $ordarr,
            $lines,
            $posx,
            $posy,
            $width,
            $offset,
            $fontascent,
            $linespace,
            $strokewidth,
            $wordspacing,
            $leading,
            $rise,
            $halign,
            $jlast,
            $fill,
            $stroke,
            $underline,
            $linethrough,
            $overline,
            $clip,
            $shadow,
        );
    }

    /**
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-param TTextDims $dim
     */
    public function exposeGetJustifiedString(string $txt, array $ordarr, array $dim, float $width = 0): string
    {
        return $this->getJustifiedString($txt, $ordarr, $dim, $width);
    }

    /**
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-return TTextDims
     */
    public function exposeGetOrdArrDims(array $ordarr): array
    {
        return $this->font->getOrdArrDims($ordarr); // @phpstan-ignore argument.type
    }

    /**
     * @phpstan-param array<string, string> $phyphens
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-return array<int, int>
     */
    public function exposeHyphenateTextOrdArr(array $phyphens, array $ordarr): array
    {
        return $this->hyphenateTextOrdArr($phyphens, $ordarr);
    }

    /**
     * @phpstan-param array<string, string> $phyphens
     * @phpstan-param array<int, int> $ordarr
     * @phpstan-return array<int, int>
     */
    public function exposeHyphenateWordOrdArr(
        array $phyphens,
        array $ordarr,
        int $leftmin = 1,
        int $rightmin = 2,
        int $charmin = 1,
        int $charmax = 8,
    ): array {
        return $this->hyphenateWordOrdArr($phyphens, $ordarr, $leftmin, $rightmin, $charmin, $charmax);
    }

    /** @phpstan-return array<int, int> */
    public function exposeStrToOrdArr(string $txt): array
    {
        $ords = [];
        foreach ($this->uniconv->strToOrdArr($txt) as $key => $ord) {
            $ords[$key] = (int) $ord;
        }

        /** @var array<int, int> $ords */
        return $ords;
    }

    /** @phpstan-param array<int, int> $ordarr */
    public function exposeGetActualTextForOrdarr(array $ordarr): string
    {
        return $this->getActualTextForOrdarr($ordarr);
    }

    public function exposeFormatPdfUaActualText(string $txt): string
    {
        return $this->formatPdfUaActualText($txt);
    }

    public function exposeTagPdfUaTextContent(string $content, int $pid, string $actualText = ''): string
    {
        return $this->tagPdfUaTextContent($content, $pid, $actualText);
    }
}
