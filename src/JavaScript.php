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
}
