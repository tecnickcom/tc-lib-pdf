<?php

/**
 * CrlFetcher.php
 *
 * CRL (Certificate Revocation List) Fetcher for LTV Signatures
 *
 * @since     2025-01-02
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
 * CRL Fetcher for downloading Certificate Revocation Lists
 *
 * Fetches CRLs from CRL Distribution Points in certificates
 * for Long-Term Validation (LTV) of PDF signatures.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class CrlFetcher
{
    /**
     * Request timeout in seconds
     */
    protected int $timeout = 30;

    /**
     * Maximum CRL size to download (10 MB default)
     */
    protected int $maxSize = 10485760;

    /**
     * Constructor
     *
     * @param int $timeout Request timeout in seconds
     * @param int $maxSize Maximum CRL size to download
     */
    public function __construct(int $timeout = 30, int $maxSize = 10485760)
    {
        $this->timeout = $timeout;
        $this->maxSize = $maxSize;
    }

    /**
     * Get CRL for a certificate
     *
     * @param string $certificate DER-encoded certificate
     * @param string|null $crlUrl CRL distribution point URL (extracted from cert if null)
     * @return string|null DER-encoded CRL or null on failure
     */
    public function getCrl(string $certificate, ?string $crlUrl = null): ?string
    {
        try {
            // Extract CRL URL from certificate if not provided
            if ($crlUrl === null) {
                $urls = $this->extractCrlUrls($certificate);
                if (empty($urls)) {
                    return null;
                }
                $crlUrl = $urls[0];
            }

            return $this->downloadCrl($crlUrl);
        } catch (\Exception $e) {
            // Log error but don't fail - LTV is optional
            return null;
        }
    }

    /**
     * Get all CRLs for a certificate (from all distribution points)
     *
     * @param string $certificate DER-encoded certificate
     * @return array<string> Array of DER-encoded CRLs
     */
    public function getAllCrls(string $certificate): array
    {
        $crls = [];
        $urls = $this->extractCrlUrls($certificate);

        foreach ($urls as $url) {
            $crl = $this->downloadCrl($url);
            if ($crl !== null) {
                $crls[] = $crl;
            }
        }

        return $crls;
    }

    /**
     * Extract CRL Distribution Point URLs from certificate
     *
     * @param string $certificate DER-encoded certificate
     * @return array<string> Array of CRL URLs
     */
    public function extractCrlUrls(string $certificate): array
    {
        $urls = [];

        // Look for CRL Distribution Points extension (OID 2.5.29.31)
        // The OID is encoded as: 06 03 55 1d 1f
        $crlDpOid = "\x06\x03\x55\x1d\x1f";

        $pos = 0;
        while (($pos = strpos($certificate, $crlDpOid, $pos)) !== false) {
            // Search for URLs after this OID
            $searchStart = $pos + strlen($crlDpOid);
            $remaining = substr($certificate, $searchStart, 500);

            // Look for URIs (tag 0x86 for IA5String in context-specific)
            $offset = 0;
            while ($offset < strlen($remaining) - 2) {
                if (ord($remaining[$offset]) === 0x86) {
                    $length = ord($remaining[$offset + 1]);
                    if ($length > 0 && $length < 200) {
                        $url = substr($remaining, $offset + 2, $length);
                        if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                            if (!in_array($url, $urls)) {
                                $urls[] = $url;
                            }
                        }
                    }
                }
                $offset++;
            }

            $pos++;
        }

        // Also try direct pattern matching for http(s) URLs
        if (empty($urls)) {
            if (preg_match_all('/(https?:\/\/[^\x00-\x1f\s]+\.crl)/i', $certificate, $matches)) {
                foreach ($matches[1] as $url) {
                    if (!in_array($url, $urls)) {
                        $urls[] = $url;
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * Download CRL from URL
     *
     * @param string $url CRL URL
     * @return string|null DER-encoded CRL or null on failure
     */
    protected function downloadCrl(string $url): ?string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_HTTPHEADER => [
                'Accept: application/pkix-crl, application/x-pkcs7-crl',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        // Check size
        if ($contentLength > $this->maxSize || strlen($response) > $this->maxSize) {
            return null;
        }

        // Validate response looks like a CRL (starts with SEQUENCE tag)
        if (strlen($response) < 3) {
            return null;
        }

        // Check if it's DER-encoded (starts with 0x30)
        if (ord($response[0]) === 0x30) {
            return $response;
        }

        // Try to decode if it's PEM-encoded
        if (strpos($response, '-----BEGIN X509 CRL-----') !== false) {
            return $this->pemToDer($response, 'X509 CRL');
        }

        return null;
    }

    /**
     * Convert PEM-encoded data to DER
     *
     * @param string $pem PEM-encoded data
     * @param string $type Certificate type (e.g., 'X509 CRL')
     * @return string|null DER-encoded data or null on failure
     */
    protected function pemToDer(string $pem, string $type): ?string
    {
        $pattern = '/-----BEGIN ' . preg_quote($type, '/') . '-----\s*([A-Za-z0-9+\/=\s]+)\s*-----END ' . preg_quote($type, '/') . '-----/';

        if (!preg_match($pattern, $pem, $matches)) {
            return null;
        }

        $base64 = preg_replace('/\s+/', '', $matches[1]);
        $der = base64_decode($base64, true);

        if ($der === false) {
            return null;
        }

        return $der;
    }
}
