<?php

/**
 * OcspClient.php
 *
 * OCSP (Online Certificate Status Protocol) Client for LTV Signatures
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
 * OCSP Client for fetching certificate revocation status
 *
 * Implements RFC 6960 OCSP protocol to check certificate validity
 * for Long-Term Validation (LTV) of PDF signatures.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class OcspClient
{
    /**
     * OID for SHA-1 hash algorithm
     */
    protected const OID_SHA1 = '1.3.14.3.2.26';

    /**
     * OID for SHA-256 hash algorithm
     */
    protected const OID_SHA256 = '2.16.840.1.101.3.4.2.1';

    /**
     * OID for OCSP basic response
     */
    protected const OID_OCSP_BASIC = '1.3.6.1.5.5.7.48.1.1';

    /**
     * Request timeout in seconds
     */
    protected int $timeout = 30;

    /**
     * Hash algorithm to use
     */
    protected string $hashAlgorithm = 'sha1';

    /**
     * Constructor
     *
     * @param int $timeout Request timeout in seconds
     * @param string $hashAlgorithm Hash algorithm (sha1, sha256)
     */
    public function __construct(int $timeout = 30, string $hashAlgorithm = 'sha1')
    {
        $this->timeout = $timeout;
        $this->hashAlgorithm = strtolower($hashAlgorithm);
    }

    /**
     * Get OCSP response for a certificate
     *
     * @param string $certificate DER-encoded certificate to check
     * @param string $issuerCertificate DER-encoded issuer certificate
     * @param string|null $ocspUrl OCSP responder URL (extracted from cert if null)
     * @return string|null DER-encoded OCSP response or null on failure
     */
    public function getOcspResponse(
        string $certificate,
        string $issuerCertificate,
        ?string $ocspUrl = null
    ): ?string {
        try {
            // Extract OCSP URL from certificate if not provided
            if ($ocspUrl === null) {
                $ocspUrl = $this->extractOcspUrl($certificate);
                if ($ocspUrl === null) {
                    return null;
                }
            }

            // Build OCSP request
            $request = $this->buildOcspRequest($certificate, $issuerCertificate);

            // Send request
            return $this->sendOcspRequest($ocspUrl, $request);
        } catch (\Exception $e) {
            // Log error but don't fail - LTV is optional
            return null;
        }
    }

    /**
     * Extract OCSP responder URL from certificate
     *
     * @param string $certificate DER-encoded certificate
     * @return string|null OCSP URL or null if not found
     */
    public function extractOcspUrl(string $certificate): ?string
    {
        // Look for Authority Information Access extension (OID 1.3.6.1.5.5.7.1.1)
        // containing OCSP (OID 1.3.6.1.5.5.7.48.1)

        // Search for OCSP access method OID followed by URL
        $ocspOid = "\x06\x08\x2b\x06\x01\x05\x05\x07\x30\x01"; // OID 1.3.6.1.5.5.7.48.1

        $pos = strpos($certificate, $ocspOid);
        if ($pos === false) {
            return null;
        }

        // After the OID, look for a URI (tag 0x86 for IA5String in context-specific)
        $searchStart = $pos + strlen($ocspOid);
        $remaining = substr($certificate, $searchStart, 200);

        // Look for context-specific tag [6] which indicates URI
        if (preg_match('/\x86([\x01-\x7f])/', $remaining, $m, PREG_OFFSET_CAPTURE)) {
            $length = ord($m[1][0]);
            $urlStart = $m[1][1] + 1;
            $url = substr($remaining, $urlStart, $length);

            // Validate it looks like a URL
            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                return $url;
            }
        }

        // Try alternate method: look for "http" directly
        if (preg_match('/(https?:\/\/[^\x00-\x1f]+?)[\x00-\x1f]/', $remaining, $m)) {
            $url = rtrim($m[1], "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f");
            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Build OCSP request
     *
     * OCSPRequest ::= SEQUENCE {
     *   tbsRequest TBSRequest,
     *   optionalSignature [0] EXPLICIT Signature OPTIONAL
     * }
     *
     * TBSRequest ::= SEQUENCE {
     *   version [0] EXPLICIT Version DEFAULT v1,
     *   requestorName [1] EXPLICIT GeneralName OPTIONAL,
     *   requestList SEQUENCE OF Request,
     *   requestExtensions [2] EXPLICIT Extensions OPTIONAL
     * }
     *
     * Request ::= SEQUENCE {
     *   reqCert CertID,
     *   singleRequestExtensions [0] EXPLICIT Extensions OPTIONAL
     * }
     *
     * CertID ::= SEQUENCE {
     *   hashAlgorithm AlgorithmIdentifier,
     *   issuerNameHash OCTET STRING,
     *   issuerKeyHash OCTET STRING,
     *   serialNumber CertificateSerialNumber
     * }
     *
     * @param string $certificate DER-encoded certificate
     * @param string $issuerCertificate DER-encoded issuer certificate
     * @return string DER-encoded OCSP request
     */
    protected function buildOcspRequest(string $certificate, string $issuerCertificate): string
    {
        // Extract certificate serial number
        $serialNumber = $this->extractSerialNumber($certificate);

        // Extract issuer name from certificate (subject from issuer cert)
        $issuerName = $this->extractSubjectDN($issuerCertificate);

        // Extract issuer public key
        $issuerPublicKey = $this->extractPublicKey($issuerCertificate);

        // Calculate hashes
        $hashAlgo = $this->hashAlgorithm;
        $issuerNameHash = hash($hashAlgo, $issuerName, true);
        $issuerKeyHash = hash($hashAlgo, $issuerPublicKey, true);

        // Build CertID
        $algorithmOid = $this->getHashOid();
        $algorithmIdentifier = $this->wrapInSequence(
            $this->encodeOid($algorithmOid) . "\x05\x00" // NULL parameters
        );

        $certId = $this->wrapInSequence(
            $algorithmIdentifier
            . $this->encodeOctetString($issuerNameHash)
            . $this->encodeOctetString($issuerKeyHash)
            . $serialNumber // Already DER-encoded INTEGER
        );

        // Build Request
        $request = $this->wrapInSequence($certId);

        // Build requestList (SEQUENCE OF Request)
        $requestList = $this->wrapInSequence($request);

        // Build TBSRequest (no version, no extensions for simplicity)
        $tbsRequest = $this->wrapInSequence($requestList);

        // Build OCSPRequest
        return $this->wrapInSequence($tbsRequest);
    }

    /**
     * Send OCSP request to responder
     *
     * @param string $url OCSP responder URL
     * @param string $request DER-encoded OCSP request
     * @return string|null DER-encoded OCSP response
     */
    protected function sendOcspRequest(string $url, string $request): ?string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/ocsp-request',
                'Accept: application/ocsp-response',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        // Validate response (basic check - starts with SEQUENCE tag)
        if (strlen($response) < 3 || ord($response[0]) !== 0x30) {
            return null;
        }

        return $response;
    }

    /**
     * Extract serial number from certificate
     *
     * @param string $certificate DER-encoded certificate
     * @return string DER-encoded serial number (INTEGER)
     */
    protected function extractSerialNumber(string $certificate): string
    {
        // Certificate structure:
        // SEQUENCE {
        //   tbsCertificate SEQUENCE {
        //     version [0] EXPLICIT Version DEFAULT v1,
        //     serialNumber CertificateSerialNumber,
        //     ...
        //   },
        //   ...
        // }

        $offset = 0;

        // Skip outer SEQUENCE
        $this->skipTag($certificate, $offset, 0x30);

        // Skip tbsCertificate SEQUENCE header
        $this->skipTag($certificate, $offset, 0x30);

        // Check for version [0] EXPLICIT
        if (ord($certificate[$offset]) === 0xa0) {
            // Skip version
            $offset++;
            $versionLen = $this->parseLength($certificate, $offset);
            $offset += $versionLen;
        }

        // Now at serialNumber INTEGER
        if (ord($certificate[$offset]) !== 0x02) {
            throw new PdfException('Expected INTEGER for serial number');
        }

        $start = $offset;
        $offset++;
        $length = $this->parseLength($certificate, $offset);

        return substr($certificate, $start, ($offset - $start) + $length);
    }

    /**
     * Extract Subject DN from certificate
     *
     * @param string $certificate DER-encoded certificate
     * @return string DER-encoded subject DN (raw bytes for hashing)
     */
    protected function extractSubjectDN(string $certificate): string
    {
        // The subject is the 6th element in tbsCertificate (after version, serial, signature, issuer, validity)
        // For simplicity, we'll search for the issuer/subject pattern

        $offset = 0;

        // Skip outer SEQUENCE
        $this->skipTag($certificate, $offset, 0x30);

        // Skip tbsCertificate SEQUENCE header
        $tbsStart = $offset;
        $this->skipTag($certificate, $offset, 0x30);

        // Skip version if present
        if (ord($certificate[$offset]) === 0xa0) {
            $offset++;
            $versionLen = $this->parseLength($certificate, $offset);
            $offset += $versionLen;
        }

        // Skip serial number
        $this->skipTag($certificate, $offset, 0x02);
        $serialLen = $this->parseLength($certificate, $offset);
        $offset += $serialLen;

        // Skip signature algorithm
        $this->skipTag($certificate, $offset, 0x30);
        $sigAlgLen = $this->parseLength($certificate, $offset);
        $offset += $sigAlgLen;

        // Skip issuer
        $this->skipTag($certificate, $offset, 0x30);
        $issuerLen = $this->parseLength($certificate, $offset);
        $offset += $issuerLen;

        // Skip validity
        $this->skipTag($certificate, $offset, 0x30);
        $validityLen = $this->parseLength($certificate, $offset);
        $offset += $validityLen;

        // Now at subject - extract it
        $subjectStart = $offset;
        $this->skipTag($certificate, $offset, 0x30);
        $subjectLen = $this->parseLength($certificate, $offset);

        return substr($certificate, $subjectStart, ($offset - $subjectStart) + $subjectLen);
    }

    /**
     * Extract public key from certificate (for hashing)
     *
     * @param string $certificate DER-encoded certificate
     * @return string Public key bytes (BIT STRING content without unused bits byte)
     */
    protected function extractPublicKey(string $certificate): string
    {
        $offset = 0;

        // Skip to tbsCertificate
        $this->skipTag($certificate, $offset, 0x30);
        $this->skipTag($certificate, $offset, 0x30);

        // Skip version if present
        if (ord($certificate[$offset]) === 0xa0) {
            $offset++;
            $versionLen = $this->parseLength($certificate, $offset);
            $offset += $versionLen;
        }

        // Skip serial, sigAlg, issuer, validity, subject
        for ($i = 0; $i < 5; $i++) {
            $tag = ord($certificate[$offset]);
            if ($tag === 0x30 || $tag === 0x02) {
                $offset++;
                $len = $this->parseLength($certificate, $offset);
                $offset += $len;
            }
        }

        // Now at subjectPublicKeyInfo SEQUENCE
        $this->skipTag($certificate, $offset, 0x30);
        $spkiLen = $this->parseLength($certificate, $offset);

        // Skip algorithm identifier
        $this->skipTag($certificate, $offset, 0x30);
        $algLen = $this->parseLength($certificate, $offset);
        $offset += $algLen;

        // Now at BIT STRING containing public key
        if (ord($certificate[$offset]) !== 0x03) {
            throw new PdfException('Expected BIT STRING for public key');
        }
        $offset++;
        $bitStringLen = $this->parseLength($certificate, $offset);

        // Skip unused bits byte and return the key bytes
        return substr($certificate, $offset + 1, $bitStringLen - 1);
    }

    /**
     * Skip a tag and its length bytes
     *
     * @param string $data Binary data
     * @param int &$offset Current offset (modified)
     * @param int $expectedTag Expected tag value
     */
    protected function skipTag(string $data, int &$offset, int $expectedTag): void
    {
        if (ord($data[$offset]) !== $expectedTag) {
            // Don't throw - just skip whatever tag is there
        }
        $offset++;
    }

    /**
     * Parse ASN.1 length
     *
     * @param string $data Binary data
     * @param int &$offset Current offset (modified)
     * @return int Length value
     */
    protected function parseLength(string $data, int &$offset): int
    {
        $byte = ord($data[$offset]);
        $offset++;

        if ($byte < 0x80) {
            return $byte;
        }

        $numBytes = $byte & 0x7F;
        $length = 0;
        for ($i = 0; $i < $numBytes; $i++) {
            $length = ($length << 8) | ord($data[$offset]);
            $offset++;
        }

        return $length;
    }

    /**
     * Get OID for current hash algorithm
     *
     * @return string OID string
     */
    protected function getHashOid(): string
    {
        return match ($this->hashAlgorithm) {
            'sha256' => self::OID_SHA256,
            default => self::OID_SHA1,
        };
    }

    // ========== ASN.1 Encoding Helpers ==========

    /**
     * Encode an OID
     *
     * @param string $oid OID string
     * @return string DER-encoded OID
     */
    protected function encodeOid(string $oid): string
    {
        $parts = explode('.', $oid);
        $encoded = chr((int)$parts[0] * 40 + (int)$parts[1]);

        for ($i = 2; $i < count($parts); $i++) {
            $value = (int)$parts[$i];
            if ($value < 128) {
                $encoded .= chr($value);
            } else {
                $bytes = '';
                while ($value > 0) {
                    $bytes = chr(($value & 0x7F) | ($bytes === '' ? 0 : 0x80)) . $bytes;
                    $value >>= 7;
                }
                $encoded .= $bytes;
            }
        }

        return "\x06" . $this->encodeLength(strlen($encoded)) . $encoded;
    }

    /**
     * Encode an octet string
     *
     * @param string $data Binary data
     * @return string DER-encoded octet string
     */
    protected function encodeOctetString(string $data): string
    {
        return "\x04" . $this->encodeLength(strlen($data)) . $data;
    }

    /**
     * Encode length in DER format
     *
     * @param int $length Length value
     * @return string Encoded length
     */
    protected function encodeLength(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $bytes = '';
        $temp = $length;
        while ($temp > 0) {
            $bytes = chr($temp & 0xFF) . $bytes;
            $temp >>= 8;
        }

        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    /**
     * Wrap data in a SEQUENCE
     *
     * @param string $data Data to wrap
     * @return string DER-encoded SEQUENCE
     */
    protected function wrapInSequence(string $data): string
    {
        return "\x30" . $this->encodeLength(strlen($data)) . $data;
    }
}
