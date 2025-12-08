<?php

/**
 * JavaScript.php
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

/**
 * Com\Tecnick\Pdf\JavaScript
 *
 * JavaScript PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class JavaScript extends \Com\Tecnick\Pdf\CSS
{
    //@TODO

    /**
     * Fonts used in annotations.
     *
     * @var array<string, int>
     */
    protected array $annotation_fonts = [];

    /**
     * Destinations.
     *
     * @var array<string, array{
     *          'p': int,
     *          'x': float,
     *          'y': float,
     *      }>
     */
    protected array $dests = [];

    /**
     * Links.
     *
     * @var array<string, array{
     *          'p': int,
     *          'y': float,
     *      }>
     */
    protected array $links = [];

    /**
     * Radio Button Groups.
     *
     * @var array<string, array{
     *         'n': int,
     *         '#readonly#': bool,
     *         'kid'?: int,
     *         'def': string,
     *      }>
     */
    protected array $radiobuttonGroups = [];

    /**
     * Javascript block to add.
     */
    protected string $javascript = '';

    /**
     * Javascript objects.
     *
     * @var array<int, array{
     *          'n': int,
     *          'js': string,
     *          'onload': bool,
     *      }>
     */
    protected array $jsobjects = [];

    /**
     * Append raw javascript string to the global one.
     *
     * @param string $script Raw Javascript string.
     *
     * @return void
     */
    public function appendRawJavaScript(string $script): void
    {
        $this->javascript .= $script;
    }

    /**
     * Add a raw javascript string as new object.
     *
     * @param string $script Raw Javascript string.
     * @param bool $onload Set to true to execute the script when opening the document.
     *
     * @return int PDF object ID or -1 in case of error.
     */
    public function addRawJavaScriptObj(string $script, bool $onload = false): int
    {
        if ($this->pdfa > 0) {
            return -1;
        }
        $oid = ++$this->pon;
        $this->jsobjects[] = [
            'n' => $oid,
            'js' => $script,
            'onload' => $onload,
        ];
        return $oid;
    }

    /**
     * Adds a JavaScript field.
     *
     * @param string $type field type.
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param array<string, string> $prop javascript field properties (see: Javascript for Acrobat API reference).
     */
    protected function addJavaScriptField(
        string $type,
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        array $prop,
    ): void {
        $page = $this->page->getPage();
        $curfont = $this->font->getCurrentFont();
        // avoid fields duplication after saving the document
        $this->javascript .= "if (getField('tcpdfdocsaved').value != 'saved') {"
        . sprintf(
            "f" . $name . "=this.addField('%s','%s',%u,[%F,%F,%F,%F]);",
            $name,
            $type,
            $page['num'] - 1,
            $this->toPoints($posx),
            $this->toYPoints($posy, $page['pheight']) + 1.0,
            $this->toPoints($posx + $width),
            $this->toYPoints($posy + $height, $page['pheight']) + 1.0,
        ) . "\n"
        . 'f' . $name . '.textSize=' . $curfont['size'] . ";\n";
        foreach ($prop as $key => $val) {
            if (strcmp(substr($key, -5), 'Color') == 0) {
                $color = $this->color->getColorObj($val);
                $val = ($color === null) ? '' : $color->getJsPdfColor();
            } else {
                $val = "'" . $val . "'";
            }
            $this->javascript .= 'f' . $name . '.' . $key . '=' . $val . ";\n";
        }
        $this->javascript .= '}';
    }
}
