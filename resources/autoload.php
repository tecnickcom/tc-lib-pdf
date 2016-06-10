<?php
/**
 * autoload.php
 *
 * Autoloader for Tecnick.com libraries
 *
 * @since       2015-03-04
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2016 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */
spl_autoload_register(
    function ($class) {
        $prefix = 'Com\\Tecnick\\';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relative_class = substr($class, $len);
        $file = dirname(__DIR__).'/'.str_replace('\\', '/', $relative_class).'.php';
        if (file_exists($file)) {
            require $file;
        }
    }
);
