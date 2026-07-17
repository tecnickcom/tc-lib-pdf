<?php

declare(strict_types=1);

/**
 * Appearance.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Signature;

use Com\Tecnick\Pdf\Tcpdf;

/**
 * Com\Tecnick\Pdf\Signature\Appearance
 *
 * Fluent sub-facade for the signature widget appearance, reached through
 * Tcpdf::signature()->appearance(). It forwards to the existing appearance
 * methods on Tcpdf while returning $this for chaining.
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
final class Appearance
{
    public function __construct(
        private readonly Tcpdf $pdf,
    ) {}

    /**
     * Place the visible signature appearance rectangle.
     *
     * @param float  $posx   Abscissa of the upper-left corner.
     * @param float  $posy   Ordinate of the upper-left corner.
     * @param float  $width  Width of the signature area.
     * @param float  $height Height of the signature area.
     * @param int    $page   Page number (if < 0 the current page is used).
     * @param string $name   Name of the signature field.
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function place(
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        int $page = -1,
        string $name = '',
    ): self {
        $this->pdf->setSignatureAppearance($posx, $posy, $width, $height, $page, $name);
        return $this;
    }

    /**
     * Set a custom appearance stream for the signature widget annotation.
     *
     * @param string $stream Appearance stream content.
     * @param string $mode   Appearance mode: N (normal), R (rollover), D (down).
     * @param string $state  Optional appearance state name.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function stream(string $stream, string $mode = 'N', string $state = ''): self
    {
        $this->pdf->setSignatureAppearanceStream($stream, $mode, $state);
        return $this;
    }

    /**
     * Use a Form XObject as the signature widget appearance source.
     *
     * @param string $xobjid XObject resource name (for example "IMP1").
     *
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function xobject(string $xobjid): self
    {
        $this->pdf->setSignatureAppearanceXObject($xobjid);
        return $this;
    }
}
