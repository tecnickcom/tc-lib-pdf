<?php

/**
 * TestablMetaInfo.php
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

class TestablMetaInfo extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeGetFormattedDate(int $time): string
    {
        return $this->getFormattedDate($time);
    }

    public function exposeGetXMPFormattedDate(int $time): string
    {
        return $this->getXMPFormattedDate($time);
    }

    public function exposeGetOutDateTimeString(int $time, int $oid): string
    {
        return $this->getOutDateTimeString($time, $oid);
    }

    public function exposeGetOutMetaInfo(): string
    {
        return $this->getOutMetaInfo();
    }

    public function exposeGetEscapedXML(string $str): string
    {
        return $this->getEscapedXML($str);
    }

    public function exposeGetOutXMP(): string
    {
        return $this->getOutXMP();
    }

    public function exposeGetOutViewerPref(): string
    {
        return $this->getOutViewerPref();
    }

    public function exposeGetPageBoxName(string $name): string
    {
        return $this->getPageBoxName($name);
    }

    public function exposeGetPagePrintScaling(): string
    {
        return $this->getPagePrintScaling();
    }

    public function exposeGetDuplexMode(): string
    {
        return $this->getDuplexMode();
    }

    public function exposeGetBooleanMode(string $name): string
    {
        return $this->getBooleanMode($name);
    }

    public function exposeGetProducer(): string
    {
        return $this->getProducer();
    }
}
