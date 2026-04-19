<?php

/**
 * TestableObjPageForMetaInfo.php
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

class TestableObjPageForMetaInfo extends \Com\Tecnick\Pdf\Page\Page
{
    /**
     * @var array<string, string>
     */
    public array $CropBox = [
        'MediaBox' => 'MediaBox',
    ];

    public function __construct()
    {
    }
}
