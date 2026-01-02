<?php

/**
 * TimestampClient.php
 *
 * RFC 3161 Time-Stamp Protocol Client
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
 * RFC 3161 Time-Stamp Protocol Client
 *
 * Sends TimeStampReq to a TSA and receives TimeStampResp.
 * The timestamp token can be embedded in PDF signatures for
 * long-term validation (LTV).
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class TimestampClient
{
    /**
     * OID for SHA-256
     */
    protected const OID_SHA256 = '2.16.840.1.101.3.4.2.1';

    /**
     * OID for SHA-384
     */
    protected const OID_SHA384 = '2.16.840.1.101.3.4.2.2';

    /**
     * OID for SHA-512
     */
    protected const OID_SHA512 = '2.16.840.1.101.3.4.2.3';

    /**
     * TSA server URL
     */
    protected string $tsaUrl;

    /**
     * Hash algorithm
     */
    protected string $hashAlgorithm;

    /**
     * TSA username for authentication (optional)
     */
    protected ?string $username = null;

    /**
     * TSA password for authentication (optional)
     */
    protected ?string $password = null;

    /**
     * Request timeout in seconds
     */
    protected int $timeout = 30;

    /**
     * Constructor
     *
     * @param string $tsaUrl TSA server URL
     * @param string $hashAlgorithm Hash algorithm (sha256, sha384, sha512)
     * @param string|null $username TSA username for authentication
     * @param string|null $password TSA password for authentication
     * @param int $timeout Request timeout in seconds
     */
    public function __construct(
        string $tsaUrl,
        string $hashAlgorithm = 'sha256',
        ?string $username = null,
        ?string $password = null,
        int $timeout = 30
    ) {
        $this->tsaUrl = $tsaUrl;
        $this->hashAlgorithm = strtolower($hashAlgorithm);
        $this->username = $username;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    /**
     * Get a timestamp token for the given data
     *
     * @param string $data Binary data to timestamp (typically the signature value)
     * @return string DER-encoded TimeStampToken
     * @throws PdfException on error
     */
    public function getTimestampToken(string $data): string
    {
        // Calculate the hash of the data
        $hash = hash($this->hashAlgorithm, $data, true);

        // Build the TimeStampReq
        $request = $this->buildTimeStampReq($hash);

        // Send request to TSA
        $response = $this->sendRequest($request);

        // Parse the TimeStampResp and extract the token
        return $this->parseTimeStampResp($response);
    }

    /**
     * Build a TimeStampReq structure (RFC 3161)
     *
     * TimeStampReq ::= SEQUENCE {
     *   version          INTEGER { v1(1) },
     *   messageImprint   MessageImprint,
     *   reqPolicy        TSAPolicyId OPTIONAL,
     *   nonce            INTEGER OPTIONAL,
     *   certReq          BOOLEAN DEFAULT FALSE,
     *   extensions       [0] IMPLICIT Extensions OPTIONAL
     * }
     *
     * MessageImprint ::= SEQUENCE {
     *   hashAlgorithm    AlgorithmIdentifier,
     *   hashedMessage    OCTET STRING
     * }
     *
     * @param string $hash Binary hash value
     * @return string DER-encoded TimeStampReq
     */
    protected function buildTimeStampReq(string $hash): string
    {
        // Version (1)
        $version = "\x02\x01\x01";

        // MessageImprint
        $hashOid = $this->getHashAlgorithmOID();
        $algorithmIdentifier = $this->wrapInSequence(
            $this->encodeOID($hashOid) . "\x05\x00" // Algorithm + NULL parameters
        );
        $hashedMessage = $this->encodeOctetString($hash);
        $messageImprint = $this->wrapInSequence($algorithmIdentifier . $hashedMessage);

        // Nonce (random for replay protection)
        $nonce = $this->encodeInteger(random_int(1, PHP_INT_MAX));

        // CertReq = TRUE (request TSA certificate in response)
        $certReq = "\x01\x01\xff"; // BOOLEAN TRUE

        // Build complete TimeStampReq
        $tsReq = $version . $messageImprint . $nonce . $certReq;

        return $this->wrapInSequence($tsReq);
    }

    /**
     * Send the TimeStampReq to the TSA server
     *
     * @param string $request DER-encoded TimeStampReq
     * @return string DER-encoded TimeStampResp
     * @throws PdfException on error
     */
    protected function sendRequest(string $request): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->tsaUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/timestamp-query',
                'Accept: application/timestamp-reply',
            ],
        ]);

        // Add authentication if provided
        if ($this->username !== null && $this->password !== null) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new PdfException('TSA request failed: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new PdfException('TSA returned HTTP error: ' . $httpCode);
        }

        return $response;
    }

    /**
     * Parse the TimeStampResp and extract the TimeStampToken
     *
     * TimeStampResp ::= SEQUENCE {
     *   status          PKIStatusInfo,
     *   timeStampToken  TimeStampToken OPTIONAL
     * }
     *
     * PKIStatusInfo ::= SEQUENCE {
     *   status        PKIStatus,
     *   statusString  PKIFreeText OPTIONAL,
     *   failInfo      PKIFailureInfo OPTIONAL
     * }
     *
     * PKIStatus ::= INTEGER {
     *   granted                (0),
     *   grantedWithMods        (1),
     *   rejection              (2),
     *   waiting                (3),
     *   revocationWarning      (4),
     *   revocationNotification (5)
     * }
     *
     * @param string $response DER-encoded TimeStampResp
     * @return string DER-encoded TimeStampToken
     * @throws PdfException on error
     */
    protected function parseTimeStampResp(string $response): string
    {
        $offset = 0;
        $length = strlen($response);

        // Parse outer SEQUENCE (TimeStampResp)
        if ($offset >= $length || ord($response[$offset]) !== 0x30) {
            throw new PdfException('Invalid TimeStampResp: expected SEQUENCE');
        }
        $offset++;
        $seqLength = $this->parseLength($response, $offset);

        // Parse PKIStatusInfo SEQUENCE
        if ($offset >= $length || ord($response[$offset]) !== 0x30) {
            throw new PdfException('Invalid TimeStampResp: expected PKIStatusInfo SEQUENCE');
        }
        $offset++;
        $statusInfoLength = $this->parseLength($response, $offset);
        $statusInfoEnd = $offset + $statusInfoLength;

        // Parse PKIStatus INTEGER
        if ($offset >= $length || ord($response[$offset]) !== 0x02) {
            throw new PdfException('Invalid TimeStampResp: expected PKIStatus INTEGER');
        }
        $offset++;
        $statusLength = $this->parseLength($response, $offset);
        $status = 0;
        for ($i = 0; $i < $statusLength; $i++) {
            $status = ($status << 8) | ord($response[$offset + $i]);
        }
        $offset += $statusLength;

        // Check status (0 = granted, 1 = grantedWithMods)
        if ($status !== 0 && $status !== 1) {
            throw new PdfException('TSA returned error status: ' . $status);
        }

        // Skip rest of PKIStatusInfo (statusString, failInfo)
        $offset = $statusInfoEnd;

        // Parse TimeStampToken (ContentInfo - SEQUENCE starting with OID)
        if ($offset >= $length) {
            throw new PdfException('TimeStampResp missing TimeStampToken');
        }

        // Return the TimeStampToken (everything from here to end)
        return substr($response, $offset);
    }

    /**
     * Parse ASN.1 length
     *
     * @param string $data Binary data
     * @param int &$offset Current offset (updated)
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
     * Get the OID for the hash algorithm
     *
     * @return string OID string
     */
    protected function getHashAlgorithmOID(): string
    {
        return match ($this->hashAlgorithm) {
            'sha384' => self::OID_SHA384,
            'sha512' => self::OID_SHA512,
            default => self::OID_SHA256,
        };
    }

    // ========== ASN.1 Encoding Helpers ==========

    /**
     * Encode an OID
     *
     * @param string $oid OID string
     * @return string DER-encoded OID
     */
    protected function encodeOID(string $oid): string
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
     * Encode an integer
     *
     * @param int $value Integer value
     * @return string DER-encoded integer
     */
    protected function encodeInteger(int $value): string
    {
        if ($value === 0) {
            return "\x02\x01\x00";
        }

        $bytes = '';
        $temp = $value;
        while ($temp > 0) {
            $bytes = chr($temp & 0xFF) . $bytes;
            $temp >>= 8;
        }

        // Add leading zero if high bit is set
        if (ord($bytes[0]) & 0x80) {
            $bytes = "\x00" . $bytes;
        }

        return "\x02" . $this->encodeLength(strlen($bytes)) . $bytes;
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
