<?php

declare(strict_types=1);

/**
 * External.php
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
 * Com\Tecnick\Pdf\Signature\External
 *
 * Fluent sub-facade for the external / remote signing workflow, reached through
 * Tcpdf::signature()->external(). It forwards to the existing external-signing
 * methods on Tcpdf.
 *
 * @phpstan-import-type TSignature from \Com\Tecnick\Pdf\Base
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
final class External
{
    public function __construct(
        private readonly Tcpdf $pdf,
    ) {}

    /**
     * Reserve the signature placeholder for external signing without a local key.
     *
     * @param TSignature $data Signature configuration (signcert/privkey may be empty).
     *
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function configure(array $data): self
    {
        $this->pdf->setSignatureForExternalSigning($data);
        return $this;
    }

    /**
     * Build the document for external signing and compute the ByteRange digest.
     *
     * @param string $algorithm Hash algorithm accepted by hash() (default: sha256).
     *
     * @return array{
     *          algorithm: string,
     *          byte_range: array{int,int,int,int},
     *          prepared_pdf: string,
     *          hash_raw: string,
     *          hash_hex: string,
     *          hash_base64: string,
     *        }
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Throwable
     */
    public function prepare(string $algorithm = 'sha256'): array
    {
        return $this->pdf->getExternalSignaturePreparation($algorithm);
    }

    /**
     * Inject an externally generated CMS/PKCS#7 signature into a prepared document.
     *
     * @param string                 $preparedPdf Prepared PDF returned by prepare().
     * @param array{int,int,int,int} $byteRange   ByteRange returned by prepare().
     * @param string                 $signature   External signature data.
     * @param string|ExternalSignatureEncoding $encoding Signature encoding: binary, base64, hex, or enum case.
     *
     * @return string Fully signed PDF document.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function apply(
        string $preparedPdf,
        array $byteRange,
        string $signature,
        string|ExternalSignatureEncoding $encoding = 'binary',
    ): string {
        return $this->pdf->applyExternalSignature($preparedPdf, $byteRange, $signature, $encoding);
    }
}
