<?php

/**
 * Text.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Unicode\Bidi;
use Com\Tecnick\Unicode\Data\Type as UnicodeType;

/**
 * Com\Tecnick\Pdf\Text
 *
 * Text PDF data
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TTextDims from \Com\Tecnick\Pdf\Font\Stack
 * @phpstan-import-type StyleDataOpt from \Com\Tecnick\Pdf\Cell
 * @phpstan-import-type TCellDef from \Com\Tecnick\Pdf\Cell
 * @phpstan-import-type PageInputData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type PageData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type TFontMetric from \Com\Tecnick\Pdf\Font\Stack
 * @phpstan-import-type TBBox from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TStackBBox from \Com\Tecnick\Pdf\Base
 *
 * @phpstan-type TextShadow array{
 *          'xoffset': float,
 *          'yoffset': float,
 *          'opacity': float,
 *          'mode': string,
 *          'color': string,
 *      }
 *
 * @phpstan-type TextLinePos array{
 *          'pos': int,
 *          'chars': int,
 *          'spaces': int,
 *          'septype': string,
 *          'totwidth': float,
 *          'totspacewidth': float,
 *          'words': int,
 *      }
 */
abstract class Text extends \Com\Tecnick\Pdf\Cell
{
    /**
     * The Unicode character used for hyphenation.
     * (45) '-'
     *  Type: 'ES' (European Number Separator)
     */
    protected const ORD_HYPHEN = 0x002D;

    /*
    * The Unicode character used for non-breaking space.
    * (160) 'NO-BREAK SPACE'
    * Type: 'CS' (Common Separator)
    */
    protected const ORD_NO_BREAK_SPACE = 0x00A0;

    /*
    * The Unicode character used for soft hyphen.
    * (173) 'SHY' (SOFT HYPHEN)
    * Type: 'BN' (Boundary Neutral)
    */
    protected const ORD_SOFT_HYPHEN = 0x00AD;

    /*
    * The Unicode character used for zero width space.
    * (8203) 'ZERO WIDTH SPACE'
    * Type: 'BN' (Boundary Neutral)
    */
    protected const ORD_ZERO_WIDTH_SPACE = 0x200B;

    /**
     * The array of hyphenation patterns used for text processing.
     *
     * @var array<string, string> Array of hyphenation patterns.
     */
    protected $hyphen_patterns = [];

    /**
     * Dafault value for $dim array.
     *
     * @var TTextDims
     */
    protected const DIM_DEFAULT = [
        'chars' => 0,
        'spaces' => 0,
        'words' => 0,
        'totwidth' => 0.0,
        'totspacewidth' => 0.0,
        'split' => [],
    ];



    /**
     * If true, ZERO-WIDTH-SPACE characters are automatically added
     * to the text to allow line breaking after some non-letter characters.
     *
     * @var bool
     */
    protected $autozerowidthbreaks = false;

    /**
     * Returns the PDF code to render a text block inside a rectangular cell.
     *
     * @param string      $txt         Text string to be processed.
     * @param float       $posx        Abscissa of upper-left corner.
     * @param float       $posy        Ordinate of upper-left corner.
     * @param float       $width       Width.
     * @param float       $height      Height.
     * @param float       $offset      Horizontal offset to apply to the line start.
     * @param float       $linespace   Additional space to add between lines.
     * @param string      $valign      Text vertical alignment inside the cell: T=top; C=center; B=bottom.
     * @param string      $halign      Text horizontal alignment inside the cell: L=left; C=center; R=right; J=justify.
     * @param ?TCellDef   $cell        Optional to overwrite cell parameters for padding, margin etc.
     * @param array<int, StyleDataOpt> $styles Cell border styles (see: getCurrentStyleArray).
     * @param float       $strokewidth Stroke width.
     * @param float       $wordspacing Word spacing (use it only when justify == false).
     * @param float       $leading     Leading.
     * @param float       $rise        Text rise.
     * @param bool        $jlast       If true does not justify the last line when $halign == J.
     * @param bool        $fill        If true fills the text.
     * @param bool        $stroke      If true stroke the text.
     * @param bool        $underline   If true underline the text.
     * @param bool        $linethrough If true line through the text.
     * @param bool        $overline    If true overline the text.
     * @param bool        $clip        If true activate clipping mode.
     * @param bool        $drawcell    If true draw the cell border.
     * @param string      $forcedir    If 'R' forces RTL, if 'L' forces LTR.
     * @param ?TextShadow $shadow      Text shadow parameters.
     */
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
    ): string {
        if ($txt === '') {
            return '';
        }

        $ordarr = [];
        $dim = self::DIM_DEFAULT;
        $this->prepareText($txt, $ordarr, $dim, $forcedir);
        $txt_pwidth = $dim['totwidth'];

        $cell = $this->adjustMinCellPadding($styles, $cell);

        $pntx = $this->toPoints($posx);

        $cell_pwidth = $this->toPoints($width);
        if ($width <= 0) {
            $cell_pwidth = min(
                $this->cellMaxWidth($pntx, $cell),
                $this->cellMinWidth(
                    $txt_pwidth,
                    $halign,
                    $cell
                ),
            );
        }

        $txt_pwidth = $this->textMaxWidth($cell_pwidth, $cell);
        $line_width = $this->toUnit($txt_pwidth);

        $curfont = $this->font->getCurrentFont();
        $fontascent = $this->toUnit($curfont['ascent']);

        $lines = $this->splitLines(
            $ordarr,
            $dim,
            $txt_pwidth,
            $this->toPoints($offset)
        );
        $numlines = count($lines);
        $txt_pheight = (($numlines * $curfont['height']) + (($numlines - 1) * $this->toPoints($linespace)));

        $cell_pheight = $this->toPoints($height);
        if ($height <= 0) {
            $cell_pheight = $this->cellMinHeight($txt_pheight, $valign, $cell);
        }

        $pnty = $this->toYPoints($posy);
        $cell_pnty = ($pnty - $cell['margin']['T']);

        $txt_pnty = $this->textVPosFromCell(
            $cell_pnty,
            $cell_pheight,
            $txt_pheight,
            $valign,
            $cell
        );


        $cell_pntx = $this->cellHPos($pntx, $cell_pwidth, 'L', $cell);
        $txt_pntx = $this->textHPosFromCell(
            $cell_pntx,
            $cell_pwidth,
            $txt_pwidth,
            $halign,
            $cell
        );

        $txt_out = $this->outTextLines(
            $ordarr,
            $lines,
            $this->toUnit($txt_pntx),
            $this->toYUnit($txt_pnty),
            $line_width,
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

        if (!$drawcell) {
            return $txt_out;
        }

        $cell_out = $this->drawCell(
            $cell_pntx,
            $cell_pnty,
            $cell_pwidth,
            $cell_pheight,
            $styles,
            $cell,
        );

        return $cell_out . $txt_out;
    }

    /**
     * Adds a text block inside a rectangular cell.
     * Accounts for automatic line, page and region breaks.
     *
     * @param string      $txt         Text string to be processed.
     * @param int         $pid         Page index. Omit or set it to -1 for the current page ID.
     * @param float       $posx        Abscissa of upper-left corner relative to the region origin X coordinate.
     * @param float       $posy        Ordinate of upper-left corner relative to the region origin Y coordinate.
     * @param float       $width       Width.
     * @param float       $height      Height.
     * @param float       $offset      Horizontal offset to apply to the line start.
     * @param float       $linespace   Additional space to add between lines.
     * @param string      $valign      Text vertical alignment inside the cell: T=top; C=center; B=bottom.
     * @param string      $halign      Text horizontal alignment inside the cell: L=left; C=center; R=right; J=justify.
     * @param ?TCellDef   $cell        Optional to overwrite cell parameters for padding, margin etc.
     * @param array<int, StyleDataOpt> $styles Cell border styles (see: getCurrentStyleArray).
     * @param float       $strokewidth Stroke width.
     * @param float       $wordspacing Word spacing (use it only when justify == false).
     * @param float       $leading     Leading.
     * @param float       $rise        Text rise.
     * @param bool        $jlast       If true does not justify the last line when $halign == J.
     * @param bool        $fill        If true fills the text.
     * @param bool        $stroke      If true stroke the text.
     * @param bool        $underline   If true underline the text.
     * @param bool        $linethrough If true line through the text.
     * @param bool        $overline    If true overline the text.
     * @param bool        $clip        If true activate clipping mode.
     * @param bool        $drawcell    If true draw the cell border.
     * @param string      $forcedir    If 'R' forces RTL, if 'L' forces LTR.
     * @param ?TextShadow $shadow      Text shadow parameters.
     */
    public function addTextCell(
        string $txt,
        int $pid = -1,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        float $offset = 0,
        float $linespace = 0,
        string $valign = 'T',
        string $halign = '',
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
    ): void {
        if ($txt === '') {
            return;
        }

        if ($pid < 0) {
            $pid = $this->page->getPageId();
        }

        if ($halign == '') {
            $halign = $this->rtl ? 'R' : 'L';
        }

        $cstyles = $styles;
        if ($cstyles === []) {
            $cstyles = ['all' => $this->graph->getCurrentStyleArray()];
        }
        if ($drawcell && (count($cstyles) == 1) && (!empty($cstyles['all']))) {
            $cstyles[0] = $cstyles['all'];
            $cstyles[1] = $cstyles['all'];
            $cstyles[2] = $cstyles['all'];
            $cstyles[3] = $cstyles['all'];
        }

        $ordarr = [];
        $dim = self::DIM_DEFAULT;
        $this->prepareText($txt, $ordarr, $dim, $forcedir);
        $txt_pwidth = $dim['totwidth'];

        $curfont = $this->font->getCurrentFont();
        $fontascent = $this->toUnit($curfont['ascent']);
        $fontheight = $this->toUnit($curfont['height']);

        $ocell = $this->adjustMinCellPadding($cstyles, $cell);
        $cell = $ocell;

        $cell_pntw = $this->toPoints($width);
        $cell_pnth = $this->toPoints($height);

        $region_max_lines  = 1;
        $num_blocks = 0;

        // loop through the regions to fit all available text
        while ($region_max_lines > 0) {
            $region = $this->page->getRegion($pid);
            $rposy = ($posy + $region['RY']);
            $cell_pnty = ($this->toYPoints($rposy) - $cell['margin']['T']);
            $cell_posy = $this->toYUnit($cell_pnty);

            $rposx = ($posx + $region['RX']);
            $rpntx = $this->toPoints($rposx);

            $cell_pwidth = $cell_pntw;
            if ($width <= 0) {
                $cell_pwidth = min(
                    $this->cellMaxWidth($rpntx, $cell),
                    $this->cellMinWidth(
                        $txt_pwidth,
                        $halign,
                        $cell
                    ),
                );
            }

            $txt_pwidth = $this->textMaxWidth($cell_pwidth, $cell);
            $line_width = $this->toUnit($txt_pwidth);

            $cell_pntx = ($rpntx + $cell['margin']['L']);
            $txt_pntx = $this->textHPosFromCell(
                $cell_pntx,
                $cell_pwidth,
                $txt_pwidth,
                $halign,
                $cell
            );
            $line_posx = $this->toUnit($txt_pntx);

            $lines = $this->splitLines(
                $ordarr,
                $dim,
                $txt_pwidth,
                $this->toPoints($offset)
            );
            $numlines = count($lines);

            $vspace = $this->textMaxHeight($region['RH'] + $cell['margin']['B'] + $cell['padding']['B'] - $cell_posy);
            $region_max_lines = (int)(($vspace + $linespace) / ($fontheight + $linespace));
            $lastblock = ($numlines <= $region_max_lines);

            $rlines = $lines;
            if ($numlines > $region_max_lines) {
                $rlines = array_slice($lines, 0, $region_max_lines);
            }

            $txt_pheight = (($numlines * $curfont['height']) + (($numlines - 1) * $this->toPoints($linespace)));

            $cell_pheight = $cell_pnth;
            if ($height <= 0) {
                $cell_pheight = $this->cellMinHeight($txt_pheight, $valign, $cell);
            }

            $txt_pnty = $this->textVPosFromCell(
                $cell_pnty,
                $cell_pheight,
                $txt_pheight,
                $valign,
                $cell
            );
            $line_posy = $this->toYUnit($txt_pnty);

            $out = $this->outTextLines(
                $ordarr,
                $rlines,
                $line_posx,
                $line_posy,
                $line_width,
                $offset,
                $fontascent,
                $linespace,
                $strokewidth,
                $wordspacing,
                $leading,
                $rise,
                $halign,
                ($lastblock and $jlast),
                $fill,
                $stroke,
                $underline,
                $linethrough,
                $overline,
                $clip,
                $shadow,
            );

            if ($drawcell) {
                $styles = $cstyles;
                if ($num_blocks > 0) {
                    $styles[0]['lineWidth'] = 0;
                    empty($styles[0]['fillColor']) ? null : ($styles[0]['lineColor'] = $styles[0]['fillColor']);
                    if (!$lastblock) {
                        $styles[2]['lineWidth'] = 0;
                        empty($styles[2]['fillColor']) ? null : ($styles[2]['lineColor'] = $styles[2]['fillColor']);
                    }
                }

                $out = $this->drawCell(
                    $cell_pntx,
                    $cell_pnty,
                    $cell_pwidth,
                    $cell_pheight,
                    $styles,
                    $cell,
                ) . $out;
            }

            $this->page->addContent($out, $pid);

            if ($lastblock) {
                return;
            }

            $ordarr = array_slice($ordarr, $lines[$region_max_lines]['pos']);
            $dim = $this->font->getOrdArrDims($ordarr); // @phpstan-ignore argument.type
            $posy = 0;
            $offset = 0;
            $num_blocks++;

            $cell = $ocell;
            $cell['margin']['T'] = 0;
            $cell['margin']['B'] = 0;

            $this->page->getNextRegion($pid);
            $curpid = $this->page->getPageId();
            if ($curpid > $pid) {
                $pid = $curpid;
                $this->setPageContext($pid);
            }
        }
    }

    /**
     * Returns the PDF code to render a contiguous text block with automatic line breaks.
     *
     * @param array<int, int> $ordarr  Array of UTF-8 codepoints (integer values).
     * @param array<int, TextLinePos> $lines    Array of lines metrics.
     * @param float       $posx        Abscissa of upper-left corner.
     * @param float       $posy        Ordinate of upper-left corner.
     * @param float       $width       Width.
     * @param float       $offset      Horizontal offset to apply to the line start.
     * @param float       $fontascent  Font ascent in user units.
     * @param float       $linespace   Additional space to add between lines.
     * @param float       $strokewidth Stroke width.
     * @param float       $wordspacing Word spacing (use it only when justify == false).
     * @param float       $leading     Leading.
     * @param float       $rise        Text rise.
     * @param string      $halign      Text horizontal alignment inside the cell: L=left; C=center; R=right; J=justify.
     * @param bool        $jlast       If true does not justify the last line when $halign == J.
     * @param bool        $fill        If true fills the text.
     * @param bool        $stroke      If true stroke the text.
     * @param bool        $underline   If true underline the text.
     * @param bool        $linethrough If true line through the text.
     * @param bool        $overline    If true overline the text.
     * @param bool        $clip        If true activate clipping mode.
     * @param ?TextShadow $shadow      Text shadow parameters.
     *
     * @return string PDF code to render the text.
     */
    protected function outTextLines(
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
        if ($ordarr === [] || $lines === []) {
            return '';
        }

        if ($halign == '') {
            $halign = $this->rtl ? 'R' : 'L';
        }

        $num_lines = count($lines);
        $lastline = ($num_lines - 1);

        $line_posx = $posx + $offset;
        $line_posy = $posy + $fontascent;

        $out = '';
        foreach ($lines as $i => $data) {
            $line_ordarr = array_slice($ordarr, $data['pos'], $data['chars']);
            $line_ordarr = $this->removeOrdArrSoftHyphens($line_ordarr);
            $line_txt = implode('', $this->uniconv->ordArrToChrArr($line_ordarr));
            $line_dim = [
                'chars' => $data['chars'],
                'spaces' => $data['spaces'],
                'totwidth' => $data['totwidth'],
                'totspacewidth' => $data['totspacewidth'],
                'words' => $data['words'],
                'split' => [],
            ];

            $cell_width = ($width - $offset);
            $txt_posx = $this->toUnit(
                $this->textHPosFromCell(
                    $this->toPoints($line_posx),
                    $this->toPoints($cell_width),
                    $line_dim['totwidth'],
                    $halign,
                    static::ZEROCELL, // @phpstan-ignore argument.type
                )
            );

            $jwidth = 0;
            if (($halign == 'J') && ($data['septype'] != 'B') && (($i < $lastline) || !$jlast)) {
                $jwidth = $cell_width;
            }

            $out .= $this->getOutTextLine(
                $line_txt,
                $line_ordarr,
                $line_dim,
                $txt_posx,
                $line_posy,
                $jwidth,
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

            $offset = 0;
            $line_posx = $posx;
            $bbox = $this->getLastBBox();
            $line_posy = ($bbox['y'] + $bbox['h'] + $fontascent + $linespace);
        }

        return $out;
    }

    /**
     * Returns the PDF code to render a single line of text.
     *
     * @param string      $txt         Text string to be processed.
     * @param float       $posx        X position relative to the start of the current line.
     * @param float       $posy        Y position relative to the start of the current line (font baseline).
     * @param float       $width       Desired string width to force justification via word spacing (0 = automatic).
     * @param float       $strokewidth Stroke width.
     * @param float       $wordspacing Word spacing (use it only when width == 0).
     * @param float       $leading     Leading.
     * @param float       $rise        Text rise.
     * @param bool        $fill        If true fills the text.
     * @param bool        $stroke      If true stroke the text.
     * @param bool        $underline   If true underline the text.
     * @param bool        $linethrough If true line through the text.
     * @param bool        $overline    If true overline the text.
     * @param bool        $clip        If true activate clipping mode.
     * @param string      $forcedir    If 'R' forces RTL, if 'L' forces LTR.
     * @param string      $txtanchor   Text anchor position: 'S'=start (default), 'M'=middle, 'E'=end.
     * @param ?TextShadow $shadow      Text shadow parameters.
     */
    public function getTextLine(
        string $txt,
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
        string $forcedir = '',
        string $txtanchor = '',
        ?array $shadow = null,
    ): string {
        if ($txt === '') {
            return '';
        }

        $ordarr = [];
        $dim = self::DIM_DEFAULT;
        $this->prepareText($txt, $ordarr, $dim, $forcedir);

        switch ($txtanchor) {
            case 'M':
                if ($this->rtl || ($forcedir === 'R')) {
                    $posx += ($dim['totwidth'] / 2);
                    break;
                }
                $posx -= ($dim['totwidth'] / 2);
                break;
            case 'E':
                if ($this->rtl || ($forcedir === 'R')) {
                    $posx += $dim['totwidth'];
                    break;
                }
                $posx -= $dim['totwidth'];
                break;
            default:
                // do nothing
                break;
        }

        return $this->getOutTextLine(
            $txt,
            $ordarr,
            $dim, // @phpstan-ignore argument.type
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
     * Returns the PDF code to render a single line of text.
     *
     * @param string      $txt         Text string to be processed.
     * @param array<int, int> $ordarr  Array of UTF-8 codepoints (integer values).
     * @param TTextDims   $dim         Array of dimensions
     * @param float       $posx        X position relative to the start of the current line.
     * @param float       $posy        Y position relative to the start of the current line (font baseline).
     * @param float       $width       Desired string width to force justification via word spacing (0 = automatic).
     * @param float       $strokewidth Stroke width.
     * @param float       $wordspacing Word spacing (use it only when width == 0).
     * @param float       $leading     Leading.
     * @param float       $rise        Text rise.
     * @param bool        $fill        If true fills the text.
     * @param bool        $stroke      If true stroke the text.
     * @param bool        $underline   If true underline the text.
     * @param bool        $linethrough If true line through the text.
     * @param bool        $overline    If true overline the text.
     * @param bool        $clip        If true activate clipping mode.
     * @param ?TextShadow $shadow      Text shadow parameters.
     */
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
        if ($txt === '' || $ordarr === [] || $dim === []) {
            return '';
        }

        $out = '';

        if (!empty($shadow)) {
            if ($shadow['xoffset'] < 0) {
                $posx += $shadow['xoffset'];
            }

            if ($shadow['yoffset'] < 0) {
                $posy += $shadow['yoffset'];
            }

            $out .= $this->graph->getStartTransform();
            $out .= $this->color->getPdfColor($shadow['color'], false);
            $out .= $this->graph->getAlpha($shadow['opacity'], $shadow['mode']);
            $out .= $this->outTextLine(
                $txt,
                $ordarr,
                $dim,
                $posx + $shadow['xoffset'],
                $posy + $shadow['yoffset'],
                $width,
                0,
                $wordspacing,
                $leading,
                $rise,
                true,
                false,
                false,
                false,
                false,
                false,
            );
            $out .= $this->graph->getStopTransform();
        }

        return $out . $this->outTextLine(
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
        );
    }

    /**
     * Cleanup the input text, convert it to UTF-8 array and get the dimensions.
     *
     * @param string          $txt      Clean text string to be processed.
     * @param array<int, int> $ordarr   Array of UTF-8 codepoints (integer values).
     * @param TTextDims $dim Array of dimensions
     * @param string          $forcedir If 'R' forces RTL, if 'L' forces LTR.
     */
    protected function prepareText(
        string &$txt,
        array &$ordarr,
        array &$dim,
        string $forcedir = '',
    ): void {
        if ($txt === '') {
            return;
        }

        $txt = $this->cleanupText($txt);
        $ordarr = $this->uniconv->strToOrdArr($txt); // @phpstan-ignore parameterByRef.type

        if ($this->isunicode && !$this->font->isCurrentByteFont()) {
            $bidi = new Bidi($txt, null, $ordarr, $forcedir);
            $ordarr = $this->replaceUnicodeChars($bidi->getOrdArray()); // @phpstan-ignore argument.type
        }

        if (!empty($this->hyphen_patterns)) {
            $ordarr = $this->hyphenateTextOrdArr(
                $this->hyphen_patterns,
                $ordarr, // @phpstan-ignore argument.type
            );
        }

        if ($this->autozerowidthbreaks) {
            $ordarr = $this->addOrdArrBreakPoints($ordarr); // @phpstan-ignore argument.type
        }

        $dim = $this->font->getOrdArrDims($ordarr); // @phpstan-ignore argument.type
    }

    /**
     * Split the text into lines to fit the specified width.
     *
     * @param array<int, int> $ordarr   Array of UTF-8 codepoints (integer values).
     * @param TTextDims       $dim      Array of dimensions.
     * @param float           $pwidth   Max line width in internal points.
     * @param float           $poffset  Horizontal offset to apply to the line start in internal points.
     *
     * @return array<int, TextLinePos> Array of lines metrics.
     */
    protected function splitLines(
        array $ordarr,
        array $dim,
        float $pwidth,
        float $poffset = 0,
    ): array {
        if (empty($ordarr)) {
            // no lines
            return [];
        }

        $line_width = ($pwidth - $poffset);

        if ($dim['totwidth'] <= $line_width) {
            // the input text fits in a single line
            return [[
                'pos' => 0,
                'chars' => $dim['chars'],
                'spaces' => $dim['spaces'],
                'septype' => 'BN',
                'totwidth' => $dim['totwidth'],
                'totspacewidth' => $dim['totspacewidth'],
                'words' => $dim['words'],
            ]];
        }

        $lines = [];
        $posstart = 0;
        $posend = 0;
        $prev_spaces = 0;
        $prev_totwidth = 0;
        $prev_totspacewidth = 0;
        $prev_words = 0;
        $num_words = count($dim['split']);
        $soft_hyphen_width = $this->font->getCharWidth(static::ORD_HYPHEN); // @phpstan-ignore argument.type

        for ($word = 0; $word < $num_words; $word++) {
            $data = $dim['split'][$word]; // current word data
            $curwidth = ($data['totwidth'] - $prev_totwidth);
            $overline = ($curwidth > $line_width);

            if (($data['septype'] == 'B') || $overline) {
                // the current word is a line break or does not fit in the current line
                if ($overline && ($word > 0)) {
                    // the current word does not fit in the current line
                    $data = $dim['split'][($word - 1)];
                    --$word;
                }

                $posend = $data['pos'];
                $totwidth = $data['totwidth'];
                $totspacewidth = $data['totspacewidth'];
                $spaces = $data['spaces'];
                $septype = $data['septype'];

                $sepend = 0;
                $sepwidth = 0;
                if ($data['ord'] == static::ORD_SOFT_HYPHEN) {
                    $sepend = 1;
                    $sepwidth = $soft_hyphen_width;
                }

                $lines[] = [
                    'pos' => $posstart,
                    'chars' => ($posend - $posstart) + $sepend,
                    'spaces' => ($spaces - $prev_spaces),
                    'septype' => $septype,
                    'totwidth' => ($totwidth - $prev_totwidth) + $sepwidth,
                    'totspacewidth' => ($totspacewidth - $prev_totspacewidth),
                    'words' => ($word - $prev_words),
                ];

                $chrwidth = $this->font->getCharWidth($data['ord']);
                $prev_totwidth = $totwidth + $chrwidth;
                $prev_totspacewidth = $totspacewidth;
                $prev_spaces = $spaces;
                if ($septype == 'WS') {
                    ++$prev_spaces;
                    $prev_totspacewidth += $chrwidth;
                }
                $prev_words = $word;
                $line_width = $pwidth;
                $posstart = $posend + 1; // skip word separator
            }
        }

        if ($posstart < $dim['chars']) {
            $last = $dim['split'][$dim['words'] - 1];
            $lines[] = [
                'pos' => $posstart,
                'chars' => ($dim['chars'] - $posstart),
                'spaces' => ($last['spaces'] - $prev_spaces),
                'septype' => $last['septype'],
                'totwidth' => ($last['totwidth'] - $prev_totwidth),
                'totspacewidth' => ($last['totspacewidth'] - $prev_totspacewidth),
                'words' => ($dim['words'] - $prev_words),
            ];
        }

        return $lines;
    }

    /**
     * Returns the PDF code to render a line of text.
     *
     * @param string          $txt         Clean text string to be processed.
     * @param array<int, int> $ordarr      Array of UTF-8 codepoints (integer values).
     * @param TTextDims       $dim         Array of dimensions.
     * @param float           $posx        X position relative to the start of the current line.
     * @param float           $posy        Y position relative to the start of the current line (font baseline).
     * @param float           $width       Desired string width to force justification via word spacing (0 = automatic).
     * @param float           $strokewidth Stroke width.
     * @param float           $wordspacing Word spacing (use it only when width == 0).
     * @param float           $leading     Leading.
     * @param float           $rise        Text rise.
     * @param bool            $fill        If true fills the text.
     * @param bool            $stroke      If true stroke the text.
     * @param bool            $underline   If true underline the text.
     * @param bool            $linethrough If true line through the text.
     * @param bool            $overline    If true overline the text.
     * @param bool            $clip        If true activate clipping mode.
     */
    protected function outTextLine(
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
        if ($txt === '' || $ordarr === [] || $dim === []) {
            return '';
        }

        $width = $width > 0 ? $width : 0;
        $curfont = $this->font->getCurrentFont();
        $this->bbox[] = [
            'x' => $posx,
            'y' => ($posy - $this->toUnit($curfont['ascent'])),
            'w' => $width,
            'h' => $this->toUnit($curfont['height']),
        ];

        $out = $this->getJustifiedString($txt, $ordarr, $dim, $width);

        $out = $this->getOutTextPosXY($out, $posx, $posy, 'Td');

        $trmode = $this->getTextRenderingMode($fill, $stroke, $clip);
        $out = $this->getOutTextStateOperatorw($out, $this->toPoints($strokewidth));
        $out = $this->getOutTextStateOperatorTr($out, $trmode);
        $out = $this->getOutTextStateOperatorTw($out, $this->toPoints($wordspacing));
        $out = $this->getOutTextStateOperatorTc($out, $curfont['spacing']);
        $out = $this->getOutTextStateOperatorTz($out, $curfont['stretching']);
        $out = $this->getOutTextStateOperatorTL($out, $this->toPoints($leading));
        $out = $this->getOutTextStateOperatorTs($out, $this->toPoints($rise));
        $out = $this->getOutTextObject($out);

        $bbox = $this->getLastBBox();
        if ($underline) {
            $out .= $this->getOutUTOLine(
                $this->toPoints($bbox['x']),
                $this->toYPoints($bbox['y'] + $bbox['h']),
                $this->toPoints($bbox['w']),
                $curfont['ut'],
            );
        }

        if ($linethrough) {
            $out .= $this->getOutUTOLine(
                $this->toPoints($bbox['x']),
                $this->toYPoints($bbox['y'] + ($bbox['h'] / 2)),
                $this->toPoints($bbox['w']),
                $curfont['ut'],
            );
        }

        if ($overline) {
            $out .= $this->getOutUTOLine(
                $this->toPoints($bbox['x']),
                $this->toYPoints($bbox['y']),
                $this->toPoints($bbox['w']),
                $curfont['ut'],
            );
        }

        return $out;
    }

    /**
     * Return the raw PDF command to print a graphic line.
     * This is used for text underline, overline and line-through.
     *
     * @param float $pntx X position in internal points.
     * @param float $pnty Y position in internal points.
     * @param float $pwidth Line width in internal points.
     * @param float $psize Line tickness in internal points.
     *
     * @return string Raw PDF data.
     */
    protected function getOutUTOLine(
        float $pntx,
        float $pnty,
        float $pwidth,
        float $psize,
    ): string {
        return sprintf('%F %F %F %F re f' . "\n", $pntx, $pnty, $pwidth, $psize);
    }

    /**
     * Returns the last text bounding box [llx, lly, urx, ury].
     *
     * @return TBBox  Array of bounding box values.
     */
    public function getLastBBox(): array
    {
        return $this->bbox[array_key_last($this->bbox)];
    }

    /**
     * Remove special chacters from the text string:
     *     - 'CARRIAGE RETURN' (U+000D)
     *     - 'NO-BREAK SPACE' (U+00A0)
     *     - 'SHY' (U+00AD) SOFT HYPHEN
     *
     * @param string $txt Text string to be processed.
     */
    protected function cleanupText(string $txt): string
    {
        $txt = str_replace("\r", ' ', $txt);
        $txt = str_replace($this->uniconv->chr(self::ORD_NO_BREAK_SPACE), ' ', $txt);
        $txt = str_replace($this->uniconv->chr(self::ORD_SOFT_HYPHEN), '', $txt);
        return $txt;
    }

    /**
     * Returns the string to be used as input for getOutTextShowing().
     *
     * @param string          $txt      Clean text string to be processed.
     * @param array<int, int> $ordarr   Array of UTF-8 codepoints (integer values).
     * @param TTextDims       $dim      Array of dimensions
     * @param float           $width    Desired string width in points (0 = automatic).
     */
    protected function getJustifiedString(
        string $txt,
        array $ordarr,
        array $dim,
        float $width = 0,
    ): string {
        $pwidth = $this->toPoints($width);

        $this->bbox[] = $this->getLastBBox();
        $bboxid = array_key_last($this->bbox);

        if ((!$this->isunicode) || $this->font->isCurrentByteFont()) {
            if ($this->isunicode) {
                $txt = $this->uniconv->latinArrToStr($this->uniconv->uniArrToLatinArr($ordarr));
            }
            $txt = $this->encrypt->escapeString($txt);
            $txt = $this->getOutTextShowing($txt, 'Tj');
            if ($pwidth > 0) {
                $spacewidth = (($pwidth - $dim['totwidth']) / ($dim['spaces'] ?: 1));
                return $this->getOutTextStateOperatorTw($txt, $spacewidth);
            }
            $this->bbox[$bboxid]['w'] = $this->toUnit($dim['totwidth']);
            return $txt;
        }

        $unistr = implode('', $this->uniconv->ordArrToChrArr($ordarr));
        $txt = $this->uniconv->toUTF16BE($unistr);
        $txt = $this->encrypt->escapeString($txt);

        if ($pwidth <= 0) {
            $this->bbox[$bboxid]['w'] = $this->toUnit($dim['totwidth']);
            return $this->getOutTextShowing($txt, 'Tj');
        }

        $fontsize = $this->font->getCurrentFont()['size'] ?: 1;
        $spacewidth = (($pwidth - $dim['totwidth'] + $dim['totspacewidth']) / ($dim['spaces'] ?: 1));
        $spacewidth = -1000 * $spacewidth / $fontsize;
        $txt = str_replace(chr(0) . chr(32), ') ' . sprintf('%F', $spacewidth) . ' (', $txt);

        return $this->getOutTextShowing($txt, 'TJ');
    }

    /**
     * Get the PDF code for the specified Text Positioning Operator mode.
     *
     * @param string $raw  Raw PDf data to be wrapped by this command.
     * @param float  $posx X position relative to the start of the current line.
     * @param float  $posy Y position relative to the start of the current line.
     * @param string $mode Text state parameter to apply (one of: Td, TD, T*).
     */
    protected function getOutTextPosXY(
        string $raw,
        float $posx = 0,
        float $posy = 0,
        string $mode = 'Td'
    ): string {
        $pntx = $this->toPoints($posx);
        $pnty = $this->toYPoints($posy);
        return match ($mode) {
            'Td' => sprintf('%F %F Td ' . $this->escapePerc($raw), $pntx, $pnty),
            'TD' => sprintf('%F %F TD ' . $this->escapePerc($raw), $pntx, $pnty),
            'T*' => 'T* ' . $raw,
            default => '',
        };
    }

    /**
     * Get the text rendering mode.
     *
     * @param bool $fill   If true fills the text.
     * @param bool $stroke If true stroke the text.
     * @param bool $clip   If true activate clipping mode.
     *
     * @return int Text rendering mode as in PDF 32000-1:2008 - 9.3.6 Text Rendering Mode.
     */
    protected function getTextRenderingMode(
        bool $fill = true,
        bool $stroke = false,
        bool $clip = false
    ): int {
        $mode = ((int) $clip << 2) + ((int) $stroke << 1) + ((int) $fill);
        return match ($mode) {
            0 => 3,
            4 => 7,
            default => $mode - 1,
        };
    }

    /**
     * Get the PDF code for the Tc (character spacing) Text State Operator.
     *
     * @param string    $raw   Raw PDf data to be wrapped by this command.
     * @param int|float $value Raw value to apply in internal units.
     */
    protected function getOutTextStateOperatorTc(
        string $raw,
        int|float $value = 0
    ): string {
        if ($value == 0) {
            return $raw;
        }

        return sprintf('%F Tc ' . $this->escapePerc($raw) . ' 0 Tc', $value);
    }

    /**
     * Get the PDF code for the Tw (word spacing) Text State Operator.
     *
     * @param string    $raw   Raw PDf data to be wrapped by this command.
     * @param int|float $value Raw value to apply in internal units.
     */
    protected function getOutTextStateOperatorTw(
        string $raw,
        int|float $value = 0
    ): string {
        if ($value == 0) {
            return $raw;
        }

        return sprintf('%F Tw ' . $this->escapePerc($raw) . ' 0 Tw', $value);
    }

    /**
     * Get the PDF code for the Tz (horizontal scaling) Text State Operator.
     *
     * @param string    $raw   Raw PDf data to be wrapped by this command.
     * @param int|float $value Raw value to apply in internal units.
     */
    protected function getOutTextStateOperatorTz(
        string $raw,
        int|float $value = 0
    ): string {
        if ($value == 1) {
            return $raw;
        }

        return sprintf('%F Tz ' . $this->escapePerc($raw) . ' 100 Tz', $value);
    }

    /**
     * Get the PDF code for the TL (text leading) Text State Operator.
     *
     * @param string    $raw   Raw PDf data to be wrapped by this command.
     * @param int|float $value Raw value to apply in internal units.
     */
    protected function getOutTextStateOperatorTL(
        string $raw,
        int|float $value = 0
    ): string {
        if ($value == 0) {
            return $raw;
        }

        return sprintf('%F TL ' . $this->escapePerc($raw) . ' 0 TL', $value);
    }

    /**
     * Get the PDF code for the Tr (text rendering) Text State Operator.
     *
     * @param string    $raw   Raw PDf data to be wrapped by this command.
     * @param int|float $value Raw value to apply in internal units.
     */
    protected function getOutTextStateOperatorTr(
        string $raw,
        int|float $value = 0
    ): string {
        if (($value < 0) || ($value > 7)) {
            return $raw;
        }

        return sprintf('%d Tr ' . $this->escapePerc($raw), $value);
    }

    /**
     * Get the PDF code for the Ts (text rise) Text State Operator.
     *
     * @param string    $raw   Raw PDf data to be wrapped by this command.
     * @param int|float $value Raw value to apply in internal units.
     */
    protected function getOutTextStateOperatorTs(
        string $raw,
        int|float $value = 0
    ): string {
        if ($value == 0) {
            return $raw;
        }

        return sprintf('%F Ts ' . $this->escapePerc($raw) . ' 0 Ts', $value);
    }

    /**
     * Get the PDF code for the w (stroke width) Text State Operator.
     *
     * @param string    $raw   Raw PDf data to be wrapped by this command.
     * @param int|float $value Raw value to apply in internal units.
     */
    protected function getOutTextStateOperatorw(
        string $raw,
        int|float $value = 0
    ): string {
        return sprintf('%F w ' . $this->escapePerc($raw), ($value > 0 ? $value : 0));
    }

    /**
     * Get the PDF code for the Text Positioning Operator Matrix.
     *
     * @param string $raw    Raw PDf data to be wrapped by this command.
     * @param array{float, float, float, float, float, float} $matrix Text Positioning Operator Matrix.
     */
    protected function getOutTextPosMatrix(
        string $raw,
        array $matrix = [1, 0, 0, 1, 0, 0]
    ): string {
        if (count($matrix) != 6) {
            return '';
        }

        return sprintf(
            '%F %F %F %F %F %F Tm ' . $this->escapePerc($raw),
            $matrix[0],
            $matrix[1],
            $matrix[2],
            $matrix[3],
            $matrix[4],
            $matrix[5]
        );
    }

    /**
     * Get the PDF code for showing a string.
     *
     * @param string $str  String to show.
     * @param string $mode Text-showing operator to apply (one of: Tj, TJ, ').
     */
    protected function getOutTextShowing(string $str, string $mode = 'Tj'): string
    {
        return match ($mode) {
            'Tj' => '(' . $str . ') Tj',
            'TJ' => '[(' . $str . ')] TJ',
            "'" => '(' . $str . ") '",
            default => '',
        };
    }

    /**
     * Returns a text oject by wrapping the $raw input.
     *
     * @param string $raw Raw PDf data to be wrapped by this command.
     */
    protected function getOutTextObject(string $raw = ''): string
    {
        return 'BT ' . $raw . ' ET' . "\n";
    }

    /**
     * Replace characters for languages like Thai.
     *
     * @param array<int, int> $ordarr Array of UTF-8 codepoints (integer values).
     *
     * @return array<int, int> Array of UTF-8 codepoints (integer values).
     */
    protected function replaceUnicodeChars(array $ordarr): array
    {
        // @TODO
        return $ordarr;
    }

    // ===| HYPENATION |====================================================

    /**
     * Returns an array of hyphenation patterns.
     *
     * @param string $file TEX file containing hypenation patterns.
     *                     TEX patterns can be downloaded from
     *                     https://www.ctan.org/tex-archive/language/hyph-utf8/tex/generic/hyph-utf8/patterns/tex
     *                     See https://www.ctan.org/tex-archive/language/hyph-utf8/ for more information.
     *
     * @return array<string, string> Array of hyphenation patterns.
     */
    public function loadTexHyphenPatterns(string $file): array
    {
        $pattern = [];
        $data = $this->file->fileGetContents($file);
        // remove comments
        $data = preg_replace('/\%[^\n]*+/', '', $data);
        if ($data === null) {
            throw new PdfException('Unable to load hyphenation patterns from file: ' . $file);
        }

        // extract the patterns part
        if (preg_match('/\\\\patterns\{([^\}]*+)\}/i', $data, $matches) !== 1) {
            throw new PdfException('Invalid hyphenation pattern section from file: ' . $file);
        }

        $data = trim(substr($matches[0], 10, -1));
        // extract each pattern
        $list = preg_split('/[\s]+/', $data);
        if ($list === false) {
            throw new PdfException('Invalid hyphenation patterns from file: ' . $file);
        }

        // map patterns
        $pattern = [];
        foreach ($list as $val) {
            if ($val === '') {
                continue;
            }

            $val = str_replace("'", '\\\'', trim($val));
            $key = preg_replace('/\d+/', '', $val);
            $pattern[$key] = $val;
        }

        return $pattern;
    }

    /**
     * Sets the hyphen patterns for text.
     *
     * @param array<string, string> $patterns Array of hyphenation patterns.
     *
     * @return void
     *
     * @see loadTexHyphenPatterns()
     */
    public function setTexHyphenPatterns(array $patterns): void
    {
        $this->hyphen_patterns = $patterns;
    }

    /**
     * Removes soft hyphens from an array of Unicode code points.
     *
     * @param array<int, int> $ordarr The array of Unicode code points.
     *
     * @return array<int, int> The filtered array with soft hyphens removed.
     */
    protected function removeOrdArrSoftHyphens(array $ordarr): array
    {
        $keeplast = ((count($ordarr) > 0) && ($ordarr[(count($ordarr) - 1)] == self::ORD_SOFT_HYPHEN));
        $retarr = array_filter(
            $ordarr,
            fn($ord) => (
                ($ord != self::ORD_SOFT_HYPHEN)
                && ($ord != self::ORD_ZERO_WIDTH_SPACE)
            )
        );
        if ($keeplast) {
            $retarr[] = self::ORD_SOFT_HYPHEN;
        }
        return $retarr;
    }

    /**
     * Hyphenate a text array of UTF-8 codepoints by adding SOFT-HYPHEN (U+00AD) characters.
     *
     * @param array<string, string> $phyphens An array of hyphenation patterns.
     * @param array<int, int> $ordarr  Array of UTF-8 codepoints (integer values).
     *
     * @return array<int, int> The modified array with SOFT-HYPHEN (U+00AD) characters.
     */
    protected function hyphenateTextOrdArr(array $phyphens, array $ordarr): array
    {
        $txtarr = [];
        $word = [];

        foreach ($ordarr as $ord) {
            $unitype = UnicodeType::UNI[$ord];
            switch ($unitype) {
                case 'L':
                    $word[] = $ord;
                    break;
                default:
                    if (count($word) > 0) {
                        $txtarr = array_merge($txtarr, $this->hyphenateWordOrdArr($phyphens, $word));
                        $word = [];
                    }
                    $txtarr[] = $ord;
                    break;
            }
        }

        return $txtarr;
    }

    /**
    * Enable or disable automatic line breaking points after some non-letter character types.
    *
    * @param bool $enabled
    */
    public function enableZeroWidthBreakPoints(bool $enabled): void
    {
        $this->autozerowidthbreaks = $enabled;
    }

    /**
     * Add artificial line breaking points to an array of UTF-8 codepoints.
     * This method adds ZERO-WIDTH-SPACE (U+200B) characters after certain Unicode types.
     *
     * @param array<int, int> $ordarr  Array of UTF-8 codepoints (integer values).
     *
     * @return array<int, int> The modified array with SOFT-HYPHEN (U+00AD) characters.
     */
    protected function addOrdArrBreakPoints(array $ordarr): array
    {
        $txtarr = [];
        foreach ($ordarr as $ord) {
            switch (UnicodeType::UNI[$ord]) {
                case 'ES':
                case 'ET':
                case 'CS':
                case 'BN':
                case 'ON':
                    $txtarr[] = $ord;
                    $txtarr[] = self::ORD_ZERO_WIDTH_SPACE;
                    break;
                default:
                    $txtarr[] = $ord;
                    break;
            }
        }

        return $txtarr;
    }


    /**
     * Hyphenate a word array of UTF-8 codepoints by adding SOFT-HYPHEN (U+00AD) characters.
     *
     * @param array<string, string> $phyphens An array of hyphenation patterns.
     * @param array<int, int> $ordarr  Array of UTF-8 codepoints (integer values).
     * @param int $leftmin  Minimum number of characters before the hyphen.
     * @param int $rightmin Minimum number of characters after the hyphen.
     * @param int $charmin  Minimum number of characters to consider for hyphenation.
     * @param int $charmax  Maximum number of characters to consider for hyphenation.
     *
     * @return array<int, int> The modified array with SOFT-HYPHEN (U+00AD) characters.
     */
    protected function hyphenateWordOrdArr(
        array $phyphens,
        array $ordarr,
        $leftmin = 1,
        $rightmin = 2,
        $charmin = 1,
        $charmax = 8,
    ): array {
        $numchars = count($ordarr);
        if (empty($phyphens) || ($numchars < $charmin)) {
            return $ordarr;
        }

        $hyphenpos = []; // hyphens positions

        $pad = array(46); // 46 = Period, dot or full stop
        $tmpword = array_merge($pad, $ordarr, $pad);
        $tmpnumchars = $numchars + 2;
        $maxpos = $tmpnumchars - 1;

        for ($pos = 0; $pos < $maxpos; ++$pos) {
            $imax = min(($tmpnumchars - $pos), $charmax);
            for ($i = 1; $i <= $imax; ++$i) {
                $subword = mb_strtolower(
                    $this->uniconv->getSubUniArrStr(
                        $this->uniconv->ordArrToChrArr($tmpword),
                        $pos,
                        ($pos + $i)
                    )
                );
                if (isset($phyphens[$subword])) {
                    $pattern = $this->uniconv->strToOrdArr($phyphens[$subword]);
                    $pattern_length = count($pattern);
                    $digits = 1;
                    for ($j = 0; $j < $pattern_length; ++$j) {
                        // check if $pattern[$j] is a number = hyphenation level
                        // (only numbers from 1 to 5 are valid)
                        if (($pattern[$j] >= 48) and ($pattern[$j] <= 57)) {
                            $zero = ($j == 0) ? ($pos - 1) : ($pos + $j - $digits);
                            // get hyphenation level
                            $level = ($pattern[$j] - 48);
                            // if two levels from two different patterns match at the same point,
                            // the higher one is selected.
                            if (!isset($hyphenpos[$zero]) or ($hyphenpos[$zero] < $level)) {
                                $hyphenpos[$zero] = $level;
                            }
                            ++$digits;
                        }
                    }
                }
            }
        }

        $inserted = 0;
        $maxpos = $numchars - $rightmin;
        for ($i = $leftmin; $i <= $maxpos; ++$i) {
            // only odd levels indicate allowed hyphenation points
            if (isset($hyphenpos[$i]) && (($hyphenpos[$i] % 2) != 0)) {
                array_splice($ordarr, $i + $inserted, 0, self::ORD_SOFT_HYPHEN);
                ++$inserted;
            }
        }

        return $ordarr;
    }

    // ===| PAGE |==========================================================

    /**
     * Add a new page (wrapper function for $this->page->add()).
     *
     * @param PageInputData $data Page data.
     * @return PageData Page data with additional Page ID property 'pid'.
     */
    public function addPage(array $data = []): array
    {
        $ret = $this->page->add($data);
        $this->setPageContext($ret['pid']);
        return $ret;
    }

    /**
     * Sets the page context by adding the previous page font and graphic settings.
     *
     * @param int  $pid Page index. Omit or set it to -1 for the current page ID.
     *
     * @return void
     */
    protected function setPageContext(int $pid = -1): void
    {
        $this->page->addContent($this->font->getOutCurrentFont(), $pid);
        if ($this->defPageContentEnabled) {
            $this->page->addContent($this->defaultPageContent($pid), $pid);
        }
    }

    /**
     * Sets the page common content like Header and Footer.
     * Override this method to add custom content to all pages.
     *
     * @param int $pid Page index. Omit or set it to -1 for the current page ID.
     *
     * @return string PDF output code.
     */
    public function defaultPageContent(int $pid = -1): string
    {
        if ($pid < 0) {
            $pid = $this->page->getPageId();
        }

        if ($this->defaultfont === null) {
            $this->defaultfont = $this->font->insert($this->pon, 'helvetica', '', 10);
        }

        $page = $this->page->getPage($pid);

        // print page number in the footer
        $out = $this->graph->getStartTransform();
        $out .= $this->defaultfont['out'];
        $out .= $this->color->getPdfColor('black');
        $prevcell = $this->defcell;
        $this->defcell = $this::ZEROCELL; // @phpstan-ignore assign.propertyType

        $out .= $this->getTextCell(
            (string) ($pid + 1),
            $this->toUnit($this->defaultfont['dw']),
            $page['height'] - (2 * $this->toUnit($this->defaultfont['height'])),
            $page['width'] - (4 * $this->toUnit($this->defaultfont['dw'])),
            0,
            0,
            0,
            'T',
            ($this->rtl ? 'L' : 'R'),
        );
        $out .= $this->graph->getStopTransform();
        $this->defcell = $prevcell;
        return $out;
    }

    /**
     * Escape percent signs in a string for use with sprintf.
     *
     * @param string $str The input string to escape.
     *
     * @return string The escaped string with percent signs replaced by double percent signs.
     */
    protected function escapePerc(string $str): string
    {
        return str_replace('%', '%%', $str);
    }
}
