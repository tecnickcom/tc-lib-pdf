<?php

/**
 * locale_prepend.php
 *
 * Sets LC_CTYPE to the test locale built by "make locale-fixture".
 * Used as auto_prepend_file by "make test-locale".
 *
 * @since       2026-11-20
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

$locpath = \dirname(__DIR__) . '/target/locale';
\putenv('LOCPATH=' . $locpath);
if (\setlocale(\LC_CTYPE, 'hostile') !== 'hostile') {
    \fwrite(\STDERR, 'locale_prepend.php: cannot select the "hostile" locale from ' . $locpath . \PHP_EOL);
    exit(1);
}
