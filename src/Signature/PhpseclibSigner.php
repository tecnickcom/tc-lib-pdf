<?php

/**
 * PhpseclibSigner.php
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

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Phpseclib-based Signature Provider
 *
 * Implements SignatureInterface using phpseclib for cryptographic operations.
 *
 * @since     2025-01-01
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class PhpseclibSigner implements SignatureInterface
{
    /**
     * CMS builder instance
     */
    protected CmsBuilder $cmsBuilder;

    /**
     * Certificate content (PEM)
     */
    protected string $certificate;

    /**
     * Certificate chain
     *
     * @var array<string>
     */
    protected array $certificateChain = [];

    /**
     * Constructor
     *
     * @param string $certificate PEM or DER certificate (or file path with 'file://' prefix)
     * @param string $privateKey PEM or DER private key (or file path with 'file://' prefix)
     * @param string $password Private key password
     * @param string $hashAlgorithm Hash algorithm (sha256, sha384, sha512)
     */
    public function __construct(
        string $certificate,
        string $privateKey,
        string $password = '',
        string $hashAlgorithm = 'sha256'
    ) {
        $this->certificate = $this->loadContent($certificate);
        $this->cmsBuilder = new CmsBuilder(
            $certificate,
            $privateKey,
            $password,
            $hashAlgorithm
        );
    }

    /**
     * Load content from string or file
     *
     * @param string $content Content or file path
     * @return string Content
     */
    protected function loadContent(string $content): string
    {
        if (str_starts_with($content, 'file://')) {
            $path = substr($content, 7);
            $data = file_get_contents($path);
            if ($data === false) {
                throw new PdfException('Unable to read file: ' . $path);
            }
            return $data;
        }

        return $content;
    }

    /**
     * Add certificate to the chain
     *
     * @param string $certificate PEM certificate
     */
    public function addChainCertificate(string $certificate): void
    {
        $this->certificateChain[] = $this->loadContent($certificate);
        $this->cmsBuilder->addCertificateToChain($certificate);
    }

    /**
     * Create a PKCS#7 detached signature
     *
     * @param string $data Data to sign (the PDF ByteRange content)
     * @return string Binary PKCS#7 signature
     */
    public function sign(string $data): string
    {
        return $this->cmsBuilder->createSignedData($data);
    }

    /**
     * Get the signing certificate
     *
     * @return string PEM-encoded certificate
     */
    public function getCertificate(): string
    {
        return $this->certificate;
    }

    /**
     * Get certificate chain
     *
     * @return array<string> Array of PEM-encoded certificates
     */
    public function getCertificateChain(): array
    {
        return $this->certificateChain;
    }
}
