<?php

/**
 * SignatureInterface.php
 *
 * @since     2025-01-01
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Signature;

/**
 * Interface for signature providers
 *
 * @since     2025-01-01
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
interface SignatureInterface
{
    /**
     * Create a PKCS#7 detached signature
     *
     * @param string $data Data to sign (the PDF ByteRange content)
     * @return string Binary PKCS#7 signature
     */
    public function sign(string $data): string;

    /**
     * Get the signing certificate
     *
     * @return string PEM-encoded certificate
     */
    public function getCertificate(): string;

    /**
     * Get certificate chain (optional)
     *
     * @return array<string> Array of PEM-encoded certificates
     */
    public function getCertificateChain(): array;
}
