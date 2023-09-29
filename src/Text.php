<?php
/**
 * Text.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2023 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

use \Com\Tecnick\Unicode\Bidi;

/**
 * Com\Tecnick\Pdf\Text
 *
 * Text PDF data
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2023 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * @SuppressWarnings(PHPMD)
 */
abstract class Text
{
    
    /**
     * Last text bounding box [llx, lly, urx, ury].
     *
     * @var array
     */
    protected $lasttxtbbox = array(
        'llx'=>0,
        'lly'=>0,
        'urx'=>0,
        'ury'=>0
    );

    /**
     * Returns the PDF code to render a line of text.
     *
     * @param string  $txt          Text string to be processed.
     * @param float   $xpos         X position relative to the start of the current line.
     * @param float   $ypos         Y position relative to the start of the current line.
     * @param float   $width        Desired string width to force justification via word spacing (0 = automatic).
     * @param float   $strokewidth  Stroke width.
     * @param float   $wordspacing  Word spacing (use it only when width == 0).
     * @param float   $leading      Leading.
     * @param float   $rise         Text rise.
     * @param boolean $fill         If true fills the text.
     * @param boolean $stroke       If true stroke the text.
     * @param boolean $clip         If true activate clipping mode.
     * @param boolean $forcertl     If true forces the RTL mode in the Unicode BIDI algorithm.
     *
     * @return string
     */
    public function getTextLine(
        $txt,
        $xpos = 0,
        $ypos = 0,
        $width = 0,
        $strokewidth = 0,
        $wordspacing = 0,
        $leading = 0,
        $rise = 0,
        $fill = true,
        $stroke = false,
        $clip = false,
        $forcertl = false
    ) {
        $width = $width>0?$width:0;
        $curfont = $this->font->getCurrentFont();
        $this->lasttxtbbox = array(
            'llx' => $xpos,
            'lly' => ($ypos + $curfont['descent']),
            'urx' => ($xpos + $width),
            'ury' => ($ypos - $curfont['ascent'])
        );
        $out = $this->getJustifiedString($txt, $width, $forcertl);
        $out = $this->getOutTextPosXY($out, $xpos, $ypos, 'Td');
        $trmode = $this->getTextRenderingMode($fill, $stroke, $clip);
        $out = $this->getOutTextStateOperator($out, 'w', $strokewidth);
        $out = $this->getOutTextStateOperator($out, 'Tr', $trmode);
        $out = $this->getOutTextStateOperator($out, 'Tw', $wordspacing);
        $out = $this->getOutTextStateOperator($out, 'Tc', $curfont['spacing']);
        $out = $this->getOutTextStateOperator($out, 'Tz', $curfont['stretching']);
        $out = $this->getOutTextStateOperator($out, 'TL', $leading);
        $out = $this->getOutTextStateOperator($out, 'Ts', $rise);
        return $this->getOutTextObject($out);
    }

    /**
     * Returns the last text bounding box [llx, lly, urx, ury].
     * @return array
     */
    public function getLastTextBBox()
    {
        return $this->lasttxtbbox;
    }

    /**
     * Returns the string to be used as input for getOutTextShowing().
     *
     * @param string  $txt       Text string to be processed.
     * @param float   $width     Desired string width (0 = automatic).
     * @param boolean $forcertl  If true forces the RTL mode in the Unicode BIDI algorithm.
     *
     * @return string
     */
    protected function getJustifiedString($txt, $width = 0, $forcertl = false)
    {
        if (empty($txt)) {
            return '';
        }
        $txt = str_replace("\r", ' ', $txt);
        // replace 'NO-BREAK SPACE' (U+00A0) character with a simple space
        $txt = str_replace($this->uniconv->chr(0x00A0), ' ', $txt);
        // remove 'SHY' (U+00AD) SOFT HYPHEN used for hyphenation
        $txt = str_replace($this->uniconv->chr(0x00AD), '', $txt);
        // converts an UTF-8 string to an array of UTF-8 codepoints (integer values)
        $ordarr = $this->uniconv->strToOrdArr($txt);
        $dim = $this->font->getOrdArrDims($ordarr);
        $spacewidth = (($width - $dim['totwidth'] + $dim['totspacewidth']) / ($dim['spaces']?$dim['spaces']:1));
        if (!$this->isunicode) {
            $txt = $this->encrypt->escapeString($txt);
            $txt = $this->getOutTextShowing($txt, 'Tj');
            if ($width > 0) {
                return $this->getOutTextStateOperator($txt, 'Tw', $spacewidth * $this->kunit);
            }
            $this->lasttxtbbox['urx'] += $dim['totwidth'];
            return $txt;
        }
        if ($this->font->isCurrentByteFont()) {
            $txt = $this->uniconv->latinArrToStr($this->uniconv->uniArrToLatinArr($ordarr));
        } else {
            $bidi = new Bidi($txt, null, $ordarr, $forcertl);
            $unistr = $this->replaceUnicodeChars($bidi->getString());
            $txt = $this->uniconv->toUTF16BE($unistr);
        }
        $txt = $this->encrypt->escapeString($txt);
        if ($width <= 0) {
            $this->lasttxtbbox['urx'] += $dim['totwidth'];
            return $this->getOutTextShowing($txt, 'Tj');
        }
        $fontsize = $this->font->getCurrentFont()['size']?$this->font->getCurrentFont()['size']:1;
        $spacewidth = -1000 * $spacewidth / $fontsize;
        $txt = str_replace(chr(0).chr(32), ') '.sprintf('%F', $spacewidth).' (', $txt);
        return $this->getOutTextShowing($txt, 'TJ');
    }

    /**
     * Get the PDF code for the specified Text Positioning Operator mode.
     *
     * @param string $raw   Raw PDf data to be wrapped by this command.
     * @param float  $xpos  X position relative to the start of the current line.
     * @param float  $ypos  Y position relative to the start of the current line.
     * @param string $mode  Text state parameter to apply (one of: Td, TD, T*).
     *
     * @return string
     */
    protected function getOutTextPosXY($raw, $xpos = 0, $ypos = 0, $mode = 'Td')
    {
        switch ($mode) {
            case 'Td': // Move to the start of the next line, offset from the start of the current line by (xpos, ypos).
                return sprintf('%F %F Td '.$raw, $xpos, $ypos);
            case 'TD': // Same as: -xpos TL xpos ypos Td
                return sprintf('%F %F TD '.$raw, $xpos, $ypos);
            case 'T*': // Move to the start of the next line.
                return sprintf('T* '.$raw);
        }
        return '';
    }

    /**
     * Get the text rendering mode.
     *
     * @param boolean $fill   If true fills the text.
     * @param boolean $stroke If true stroke the text.
     * @param boolean $clip   If true activate clipping mode.
     *
     * @return int Text rendering mode as in PDF 32000-1:2008 - 9.3.6 Text Rendering Mode.
     */
    protected function getTextRenderingMode($fill = true, $stroke = false, $clip = false)
    {

        $mode = ((int)$clip << 2) + ((int)$stroke << 1) + ((int)$fill);
        switch ($mode) {
            case 0: // 000 = Neither fill nor stroke text (invisible).
                return 3;
            case 4: // 100 = Add text to path for clipping.
                return 7;
        }
        // 001 = Fill text.
        // 010 = Stroke text.
        // 011 = Fill, then stroke text.
        // 101 = Fill text and add to path for clipping.
        // 110 = Stroke text and add to path for clipping.
        // 111 = Fill, then stroke text and add to path for clipping.
        return ($mode -1);
    }

    /**
     * Get the PDF code for the specified Text State Operator.
     *
     * @param string     $raw    Raw PDf data to be wrapped by this command.
     * @param string     $param  Text state parameter to apply (one of: Tc, Tw, Tz, TL, Tf, Tr, Ts, w)
     * @param int|float  $value  Value to apply.
     *
     * @return string
     */
    protected function getOutTextStateOperator($raw, $param, $value = 0)
    {
        switch ($param) {
            case 'Tc': // character spacing
                if ($value == 0) {
                    break;
                }
                return sprintf('%F Tc '.$raw.' 0 Tc', ($value * $this->kunit));
            case 'Tw': // word spacing
                if ($value == 0) {
                    break;
                }
                return sprintf('%F Tw '.$raw.' 0 Tw', ($value * $this->kunit));
            case 'Tz': // horizontal scaling
                if ($value == 1) {
                    break;
                }
                return sprintf('%F Tz '.$raw.' 100 Tz', ($value * 100));
            case 'TL': // text leading
                if ($value == 0) {
                    break;
                }
                return sprintf('%F TL '.$raw.' 0 TL', $value);
            case 'Tr': // text rendering
                if (($value < 0) || ($value > 7)) {
                    break;
                }
                return sprintf('%d Tr '.$raw, $value);
            case 'Ts': // text rise
                if ($value == 0) {
                    break;
                }
                return sprintf('%F Ts '.$raw.' 0 Ts', $value);
            case 'w': // stroke width
                return sprintf('%F w '.$raw, ($value > 0 ? ($value * $this->kunit) : 0));
        }
        return $raw;
    }

    /**
     * Get the PDF code for the Text Positioning Operator Matrix.
     *
     * @param string $raw    Raw PDf data to be wrapped by this command.
     * @param array  $matrix Positioning matrix. Values (a,b,c,d,e,f) where the matrix is: [[a b 0][c d 0][e f 1]].
     *
     * @return string
     */
    protected function getOutTextPosMatrix($raw, $matrix = array(1,0,0,1,0,0))
    {
        if (count($matrix) != 6) {
            return '';
        }
        return sprintf(
            '%F %F %F %F %F %F Tm '.$raw,
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
     *
     * @return string
     */
    protected function getOutTextShowing($str, $mode = 'Tj')
    {
        switch ($mode) {
            case 'Tj': // Show a text string.
                return '('.$str.') Tj';
            case 'TJ': // Show one or more text strings, allowing individual glyph positioning.
                return '[('.$str.')] TJ';
            case '\'': // Move to the next line and show a text string. Same as: T* $str Tj
                return '('.$str.') \'';
        }
        return '';
    }

    /**
     * Returns a text oject by wrapping the $raw input.
     *
     * @param string $raw  Raw PDf data to be wrapped by this command.
     *
     * @return string
     */
    protected function getOutTextObject($raw = '')
    {
        return 'BT '.$raw.' BE'."\r";
    }

    /**
     * Replace characters for langiages like Thai.
     *
     * @param string $str  String to process.
     *
     * @return string
     */
    protected function replaceUnicodeChars($str)
    {
        // @TODO
        return $str;
    }
}
