<?php

/**
 * DssBuilder.php
 *
 * Document Security Store (DSS) Builder for LTV Signatures
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Signature;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * DSS Builder for Long-Term Validation of PDF Signatures
 *
 * Creates the Document Security Store (DSS) dictionary containing:
 * - Certificates: All certificates in the signing chain
 * - OCSPs: OCSP responses for certificate validation
 * - CRLs: Certificate Revocation Lists
 * - VRI: Validation-Related Information per signature
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type VriData array{
 *     'signatureHash': string,
 *     'certs': array<string>,
 *     'ocsps': array<string>,
 *     'crls': array<string>,
 * }
 */
class DssBuilder
{
    /**
     * OCSP client instance
     */
    protected OcspClient $ocspClient;

    /**
     * CRL fetcher instance
     */
    protected CrlFetcher $crlFetcher;

    /**
     * Collected certificates (DER-encoded)
     *
     * @var array<string>
     */
    protected array $certificates = [];

    /**
     * Collected OCSP responses (DER-encoded)
     *
     * @var array<string>
     */
    protected array $ocsps = [];

    /**
     * Collected CRLs (DER-encoded)
     *
     * @var array<string>
     */
    protected array $crls = [];

    /**
     * VRI entries per signature
     *
     * @var array<string, VriData>
     */
    protected array $vris = [];

    /**
     * Constructor
     *
     * @param int $timeout Request timeout for OCSP/CRL fetching
     */
    public function __construct(int $timeout = 30)
    {
        $this->ocspClient = new OcspClient($timeout);
        $this->crlFetcher = new CrlFetcher($timeout);
    }

    /**
     * Add validation data for a signature
     *
     * @param string $signatureValue The signature bytes (used to create VRI key)
     * @param string $signingCertificate DER-encoded signing certificate
     * @param array<string> $certificateChain Array of DER-encoded certificates in chain
     * @return self
     */
    public function addSignatureValidation(
        string $signatureValue,
        string $signingCertificate,
        array $certificateChain = []
    ): self {
        // Calculate signature hash for VRI key (SHA-1 of signature value)
        $signatureHash = strtoupper(sha1($signatureValue));

        // Collect all certificates
        $allCerts = array_merge([$signingCertificate], $certificateChain);
        $vriCerts = [];
        $vriOcsps = [];
        $vriCrls = [];

        foreach ($allCerts as $index => $cert) {
            // Add certificate to collection
            $certHash = sha1($cert, true);
            if (!isset($this->certificates[$certHash])) {
                $this->certificates[$certHash] = $cert;
            }
            $vriCerts[] = $certHash;

            // Find issuer certificate in chain
            $issuerCert = null;
            if ($index + 1 < count($allCerts)) {
                $issuerCert = $allCerts[$index + 1];
            }

            // Try to get OCSP response
            if ($issuerCert !== null) {
                $ocspResponse = $this->ocspClient->getOcspResponse($cert, $issuerCert);
                if ($ocspResponse !== null) {
                    $ocspHash = sha1($ocspResponse, true);
                    if (!isset($this->ocsps[$ocspHash])) {
                        $this->ocsps[$ocspHash] = $ocspResponse;
                    }
                    $vriOcsps[] = $ocspHash;
                }
            }

            // Try to get CRL
            $crl = $this->crlFetcher->getCrl($cert);
            if ($crl !== null) {
                $crlHash = sha1($crl, true);
                if (!isset($this->crls[$crlHash])) {
                    $this->crls[$crlHash] = $crl;
                }
                $vriCrls[] = $crlHash;
            }
        }

        // Store VRI entry
        $this->vris[$signatureHash] = [
            'signatureHash' => $signatureHash,
            'certs' => $vriCerts,
            'ocsps' => $vriOcsps,
            'crls' => $vriCrls,
        ];

        return $this;
    }

    /**
     * Add a certificate manually
     *
     * @param string $certificate DER-encoded certificate
     * @return self
     */
    public function addCertificate(string $certificate): self
    {
        $hash = sha1($certificate, true);
        if (!isset($this->certificates[$hash])) {
            $this->certificates[$hash] = $certificate;
        }
        return $this;
    }

    /**
     * Add an OCSP response manually
     *
     * @param string $ocspResponse DER-encoded OCSP response
     * @return self
     */
    public function addOcspResponse(string $ocspResponse): self
    {
        $hash = sha1($ocspResponse, true);
        if (!isset($this->ocsps[$hash])) {
            $this->ocsps[$hash] = $ocspResponse;
        }
        return $this;
    }

    /**
     * Add a CRL manually
     *
     * @param string $crl DER-encoded CRL
     * @return self
     */
    public function addCrl(string $crl): self
    {
        $hash = sha1($crl, true);
        if (!isset($this->crls[$hash])) {
            $this->crls[$hash] = $crl;
        }
        return $this;
    }

    /**
     * Check if there's any validation data to embed
     *
     * @return bool True if there's data to embed
     */
    public function hasValidationData(): bool
    {
        return !empty($this->certificates)
            || !empty($this->ocsps)
            || !empty($this->crls);
    }

    /**
     * Build DSS PDF objects
     *
     * @param int $startObjNum Starting object number
     * @return array{objects: string, dssObjNum: int, nextObjNum: int}
     */
    public function buildDssObjects(int $startObjNum): array
    {
        $objNum = $startObjNum;
        $objects = '';

        // Object number mappings
        $certObjNums = [];
        $ocspObjNums = [];
        $crlObjNums = [];

        // Create certificate stream objects
        foreach ($this->certificates as $hash => $cert) {
            $objNum++;
            $certObjNums[$hash] = $objNum;
            $objects .= $this->buildStreamObject($objNum, $cert);
        }

        // Create OCSP stream objects
        foreach ($this->ocsps as $hash => $ocsp) {
            $objNum++;
            $ocspObjNums[$hash] = $objNum;
            $objects .= $this->buildStreamObject($objNum, $ocsp);
        }

        // Create CRL stream objects
        foreach ($this->crls as $hash => $crl) {
            $objNum++;
            $crlObjNums[$hash] = $objNum;
            $objects .= $this->buildStreamObject($objNum, $crl);
        }

        // Build VRI dictionary objects
        $vriObjNums = [];
        foreach ($this->vris as $sigHash => $vri) {
            $objNum++;
            $vriObjNums[$sigHash] = $objNum;
            $objects .= $this->buildVriObject(
                $objNum,
                $vri,
                $certObjNums,
                $ocspObjNums,
                $crlObjNums
            );
        }

        // Build main DSS dictionary object
        $objNum++;
        $dssObjNum = $objNum;
        $objects .= $this->buildDssDictionary(
            $dssObjNum,
            $certObjNums,
            $ocspObjNums,
            $crlObjNums,
            $vriObjNums
        );

        return [
            'objects' => $objects,
            'dssObjNum' => $dssObjNum,
            'nextObjNum' => $objNum,
        ];
    }

    /**
     * Build a stream object for certificate/OCSP/CRL data
     *
     * @param int $objNum Object number
     * @param string $data Binary data
     * @return string PDF object string
     */
    protected function buildStreamObject(int $objNum, string $data): string
    {
        return $objNum . " 0 obj\n"
            . "<<\n"
            . "/Length " . strlen($data) . "\n"
            . ">>\n"
            . "stream\n"
            . $data . "\n"
            . "endstream\n"
            . "endobj\n";
    }

    /**
     * Build VRI (Validation-Related Information) object
     *
     * @param int $objNum Object number
     * @param VriData $vri VRI data
     * @param array<string, int> $certObjNums Certificate hash to object number mapping
     * @param array<string, int> $ocspObjNums OCSP hash to object number mapping
     * @param array<string, int> $crlObjNums CRL hash to object number mapping
     * @return string PDF object string
     */
    protected function buildVriObject(
        int $objNum,
        array $vri,
        array $certObjNums,
        array $ocspObjNums,
        array $crlObjNums
    ): string {
        $obj = $objNum . " 0 obj\n<<\n";

        // Cert references
        if (!empty($vri['certs'])) {
            $certRefs = [];
            foreach ($vri['certs'] as $hash) {
                if (isset($certObjNums[$hash])) {
                    $certRefs[] = $certObjNums[$hash] . ' 0 R';
                }
            }
            if (!empty($certRefs)) {
                $obj .= "/Cert [" . implode(' ', $certRefs) . "]\n";
            }
        }

        // OCSP references
        if (!empty($vri['ocsps'])) {
            $ocspRefs = [];
            foreach ($vri['ocsps'] as $hash) {
                if (isset($ocspObjNums[$hash])) {
                    $ocspRefs[] = $ocspObjNums[$hash] . ' 0 R';
                }
            }
            if (!empty($ocspRefs)) {
                $obj .= "/OCSP [" . implode(' ', $ocspRefs) . "]\n";
            }
        }

        // CRL references
        if (!empty($vri['crls'])) {
            $crlRefs = [];
            foreach ($vri['crls'] as $hash) {
                if (isset($crlObjNums[$hash])) {
                    $crlRefs[] = $crlObjNums[$hash] . ' 0 R';
                }
            }
            if (!empty($crlRefs)) {
                $obj .= "/CRL [" . implode(' ', $crlRefs) . "]\n";
            }
        }

        // Add timestamp
        $obj .= "/TU (D:" . gmdate('YmdHis') . "Z)\n";

        $obj .= ">>\nendobj\n";

        return $obj;
    }

    /**
     * Build main DSS dictionary object
     *
     * DSS dictionary structure:
     * <<
     *   /Type /DSS
     *   /Certs [ references to certificate streams ]
     *   /OCSPs [ references to OCSP response streams ]
     *   /CRLs [ references to CRL streams ]
     *   /VRI << signature-hash-based dictionary >>
     * >>
     *
     * @param int $objNum Object number
     * @param array<string, int> $certObjNums Certificate object numbers
     * @param array<string, int> $ocspObjNums OCSP object numbers
     * @param array<string, int> $crlObjNums CRL object numbers
     * @param array<string, int> $vriObjNums VRI object numbers
     * @return string PDF object string
     */
    protected function buildDssDictionary(
        int $objNum,
        array $certObjNums,
        array $ocspObjNums,
        array $crlObjNums,
        array $vriObjNums
    ): string {
        $obj = $objNum . " 0 obj\n<<\n";
        $obj .= "/Type /DSS\n";

        // Certs array
        if (!empty($certObjNums)) {
            $refs = array_map(fn($n) => $n . ' 0 R', array_values($certObjNums));
            $obj .= "/Certs [" . implode(' ', $refs) . "]\n";
        }

        // OCSPs array
        if (!empty($ocspObjNums)) {
            $refs = array_map(fn($n) => $n . ' 0 R', array_values($ocspObjNums));
            $obj .= "/OCSPs [" . implode(' ', $refs) . "]\n";
        }

        // CRLs array
        if (!empty($crlObjNums)) {
            $refs = array_map(fn($n) => $n . ' 0 R', array_values($crlObjNums));
            $obj .= "/CRLs [" . implode(' ', $refs) . "]\n";
        }

        // VRI dictionary
        if (!empty($vriObjNums)) {
            $obj .= "/VRI <<\n";
            foreach ($vriObjNums as $sigHash => $vriObjNum) {
                $obj .= "  /" . $sigHash . " " . $vriObjNum . " 0 R\n";
            }
            $obj .= ">>\n";
        }

        $obj .= ">>\nendobj\n";

        return $obj;
    }

    /**
     * Get collected certificates
     *
     * @return array<string> DER-encoded certificates
     */
    public function getCertificates(): array
    {
        return array_values($this->certificates);
    }

    /**
     * Get collected OCSP responses
     *
     * @return array<string> DER-encoded OCSP responses
     */
    public function getOcspResponses(): array
    {
        return array_values($this->ocsps);
    }

    /**
     * Get collected CRLs
     *
     * @return array<string> DER-encoded CRLs
     */
    public function getCrls(): array
    {
        return array_values($this->crls);
    }

    /**
     * Reset all collected data
     *
     * @return self
     */
    public function reset(): self
    {
        $this->certificates = [];
        $this->ocsps = [];
        $this->crls = [];
        $this->vris = [];
        return $this;
    }
}
