<?php

declare(strict_types=1);

/**
 * Facade.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Signature;

use Com\Tecnick\Pdf\Tcpdf;

/**
 * Com\Tecnick\Pdf\Signature\Facade
 *
 * Fluent entry point for the signature subsystem, returned by Tcpdf::signature().
 * It groups the signature methods (configuration, timestamp, user rights,
 * appearance, empty fields, external signing, and the widget object id) behind
 * one discoverable object, forwarding to the underlying Tcpdf methods so the
 * behaviour is identical. Those methods remain fully supported; this facade is
 * a convenience wrapper, not a replacement.
 *
 * @phpstan-import-type TSignature from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TSignTimeStamp from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TUserRights from \Com\Tecnick\Pdf\Base
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
final class Facade
{
    public function __construct(
        private readonly Tcpdf $pdf,
    ) {}

    /**
     * Configure the signature (certificate, key, profile, digest, LTV, info).
     *
     * @param TSignature $data Signature configuration.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function configure(array $data): self
    {
        $this->pdf->setSignature($data);
        return $this;
    }

    /**
     * Configure the RFC 3161 signature timestamp (TSA).
     *
     * @param TSignTimeStamp $data Timestamp configuration.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function timestamp(array $data): self
    {
        $this->pdf->setSignTimeStamp($data);
        return $this;
    }

    /**
     * Set the document usage rights.
     *
     * @param TUserRights $rights User rights.
     */
    public function userRights(array $rights): self
    {
        $this->pdf->setUserRights($rights);
        return $this;
    }

    /**
     * Request a PAdES B-LTA archive (adds the DSS and a /Type /DocTimeStamp archive
     * timestamp on top of the signature). Requires a TSA configured via timestamp().
     */
    public function upgradeToLta(): self
    {
        $this->pdf->upgradeSignatureToLta();
        return $this;
    }

    /**
     * Access the visible-appearance sub-facade.
     */
    public function appearance(): Appearance
    {
        return new Appearance($this->pdf);
    }

    /**
     * Add an empty signature field (a clickable rectangle to be signed later).
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
    public function emptyField(
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        int $page = -1,
        string $name = '',
    ): self {
        $this->pdf->addEmptySignatureAppearance($posx, $posy, $width, $height, $page, $name);
        return $this;
    }

    /**
     * Access the external / remote signing sub-facade.
     */
    public function external(): External
    {
        return new External($this->pdf);
    }

    /**
     * Return the signature widget annotation object id (0 if not initialized).
     */
    public function widgetObjectId(): int
    {
        return $this->pdf->getSignatureObjectID();
    }
}
