<?php

/**
 * CmsBuilder.php
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
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\File\X509;
use phpseclib3\File\ASN1;
use phpseclib3\File\ASN1\Maps\Name;

/**
 * CMS/PKCS#7 SignedData Builder using phpseclib
 *
 * Builds RFC 5652 CMS SignedData structures for PDF digital signatures.
 *
 * @since     2025-01-01
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class CmsBuilder
{
    /**
     * OID for PKCS#7 SignedData
     */
    protected const OID_SIGNED_DATA = '1.2.840.113549.1.7.2';

    /**
     * OID for PKCS#7 Data
     */
    protected const OID_DATA = '1.2.840.113549.1.7.1';

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
     * OID for RSA encryption
     */
    protected const OID_RSA = '1.2.840.113549.1.1.1';

    /**
     * OID for RSA with SHA-256
     */
    protected const OID_RSA_SHA256 = '1.2.840.113549.1.1.11';

    /**
     * OID for RSA with SHA-384
     */
    protected const OID_RSA_SHA384 = '1.2.840.113549.1.1.12';

    /**
     * OID for RSA with SHA-512
     */
    protected const OID_RSA_SHA512 = '1.2.840.113549.1.1.13';

    /**
     * OID for content type attribute
     */
    protected const OID_CONTENT_TYPE = '1.2.840.113549.1.9.3';

    /**
     * OID for message digest attribute
     */
    protected const OID_MESSAGE_DIGEST = '1.2.840.113549.1.9.4';

    /**
     * OID for signing time attribute
     */
    protected const OID_SIGNING_TIME = '1.2.840.113549.1.9.5';

    /**
     * OID for signature timestamp attribute (RFC 3161)
     */
    protected const OID_SIGNATURE_TIMESTAMP = '1.2.840.113549.1.9.16.2.14';

    /**
     * Hash algorithm to use
     */
    protected string $hashAlgorithm = 'sha256';

    /**
     * Timestamp client for RFC 3161 timestamps
     */
    protected ?TimestampClient $timestampClient = null;

    /**
     * Private key for signing
     *
     * @var RSA|EC|null
     */
    protected $privateKey = null;

    /**
     * X.509 certificate
     */
    protected ?X509 $x509 = null;

    /**
     * Certificate chain
     *
     * @var array<X509>
     */
    protected array $certChain = [];

    /**
     * Raw certificate data
     */
    protected string $certificateDer = '';

    /**
     * Constructor
     *
     * @param string $certificate PEM or DER encoded certificate
     * @param string $privateKey PEM or DER encoded private key
     * @param string $password Private key password (optional)
     * @param string $hashAlgorithm Hash algorithm (sha256, sha384, sha512)
     */
    public function __construct(
        string $certificate,
        string $privateKey,
        string $password = '',
        string $hashAlgorithm = 'sha256'
    ) {
        $this->hashAlgorithm = $hashAlgorithm;
        $this->loadCertificate($certificate);
        $this->loadPrivateKey($privateKey, $password);
    }

    /**
     * Load the signing certificate
     *
     * @param string $certificate PEM or DER encoded certificate
     */
    protected function loadCertificate(string $certificate): void
    {
        // Handle file:// prefix
        if (str_starts_with($certificate, 'file://')) {
            $path = substr($certificate, 7);
            $certificate = file_get_contents($path);
            if ($certificate === false) {
                throw new PdfException('Unable to read certificate file: ' . $path);
            }
        }

        $this->x509 = new X509();
        $cert = $this->x509->loadX509($certificate);
        if ($cert === false) {
            throw new PdfException('Unable to load certificate');
        }

        // Store DER-encoded certificate
        $this->certificateDer = $this->x509->saveX509($cert, X509::FORMAT_DER);
    }

    /**
     * Load the private key
     *
     * @param string $privateKey PEM or DER encoded private key
     * @param string $password Private key password
     */
    protected function loadPrivateKey(string $privateKey, string $password = ''): void
    {
        // Handle file:// prefix
        if (str_starts_with($privateKey, 'file://')) {
            $path = substr($privateKey, 7);
            $privateKey = file_get_contents($path);
            if ($privateKey === false) {
                throw new PdfException('Unable to read private key file: ' . $path);
            }
        }

        try {
            $this->privateKey = PublicKeyLoader::load($privateKey, $password);
        } catch (\Exception $e) {
            throw new PdfException('Unable to load private key: ' . $e->getMessage());
        }
    }

    /**
     * Set the timestamp client for RFC 3161 timestamps
     *
     * @param TimestampClient $client Timestamp client instance
     */
    public function setTimestampClient(TimestampClient $client): void
    {
        $this->timestampClient = $client;
    }

    /**
     * Create a timestamp client and set it
     *
     * @param string $tsaUrl TSA server URL
     * @param string|null $username TSA username for authentication
     * @param string|null $password TSA password for authentication
     * @param int $timeout Request timeout in seconds
     */
    public function enableTimestamp(
        string $tsaUrl,
        ?string $username = null,
        ?string $password = null,
        int $timeout = 30
    ): void {
        $this->timestampClient = new TimestampClient(
            $tsaUrl,
            $this->hashAlgorithm,
            $username,
            $password,
            $timeout
        );
    }

    /**
     * Add a certificate to the chain
     *
     * @param string $certificate PEM or DER encoded certificate
     */
    public function addCertificateToChain(string $certificate): void
    {
        if (str_starts_with($certificate, 'file://')) {
            $path = substr($certificate, 7);
            $certificate = file_get_contents($path);
            if ($certificate === false) {
                throw new PdfException('Unable to read certificate file: ' . $path);
            }
        }

        $x509 = new X509();
        $cert = $x509->loadX509($certificate);
        if ($cert !== false) {
            $this->certChain[] = $x509;
        }
    }

    /**
     * Create a PKCS#7/CMS SignedData structure
     *
     * @param string $data Data to sign (PDF ByteRange content)
     * @param int|null $signingTime Unix timestamp for signing time (null for current time)
     * @return string DER-encoded PKCS#7 SignedData
     */
    public function createSignedData(string $data, ?int $signingTime = null): string
    {
        if ($this->privateKey === null || $this->x509 === null) {
            throw new PdfException('Certificate and private key must be loaded');
        }

        $signingTime = $signingTime ?? time();

        // Calculate message digest
        $messageDigest = hash($this->hashAlgorithm, $data, true);

        // Build signed attributes
        $signedAttrs = $this->buildSignedAttributes($messageDigest, $signingTime);

        // Sign the attributes
        $signedAttrsForSigning = $this->encodeSignedAttributesForSigning($signedAttrs);
        $signature = $this->createSignature($signedAttrsForSigning);

        // Build the complete SignedData structure
        return $this->buildSignedDataStructure($signedAttrs, $signature);
    }

    /**
     * Build signed attributes
     *
     * @param string $messageDigest The message digest
     * @param int $signingTime Unix timestamp
     * @return array<array<string, mixed>> Signed attributes array
     */
    protected function buildSignedAttributes(string $messageDigest, int $signingTime): array
    {
        return [
            // Content type attribute
            [
                'type' => self::OID_CONTENT_TYPE,
                'values' => [
                    ['objectIdentifier' => self::OID_DATA]
                ]
            ],
            // Signing time attribute
            [
                'type' => self::OID_SIGNING_TIME,
                'values' => [
                    ['utcTime' => gmdate('ymdHis', $signingTime) . 'Z']
                ]
            ],
            // Message digest attribute
            [
                'type' => self::OID_MESSAGE_DIGEST,
                'values' => [
                    ['octetString' => $messageDigest]
                ]
            ],
        ];
    }

    /**
     * Encode signed attributes for signing (SET OF, implicit tag)
     *
     * @param array<array<string, mixed>> $signedAttrs Signed attributes
     * @return string DER-encoded attributes
     */
    protected function encodeSignedAttributesForSigning(array $signedAttrs): string
    {
        $encoded = $this->encodeAttributes($signedAttrs);
        // Replace implicit [0] tag with SET tag for signing
        $encoded[0] = "\x31";
        return $encoded;
    }

    /**
     * Encode attributes as ASN.1
     *
     * @param array<array<string, mixed>> $attrs Attributes
     * @return string DER-encoded attributes
     */
    protected function encodeAttributes(array $attrs): string
    {
        $encodedAttrs = '';

        foreach ($attrs as $attr) {
            $attrSequence = $this->encodeOID($attr['type']);

            $valuesSet = '';
            foreach ($attr['values'] as $value) {
                if (isset($value['objectIdentifier'])) {
                    $valuesSet .= $this->encodeOID($value['objectIdentifier']);
                } elseif (isset($value['utcTime'])) {
                    $valuesSet .= $this->encodeUTCTime($value['utcTime']);
                } elseif (isset($value['octetString'])) {
                    $valuesSet .= $this->encodeOctetString($value['octetString']);
                }
            }

            $attrSequence .= $this->wrapInSet($valuesSet);
            $encodedAttrs .= $this->wrapInSequence($attrSequence);
        }

        // Wrap in implicit [0] context tag
        return $this->wrapInImplicitTag($encodedAttrs, 0);
    }

    /**
     * Create the actual signature
     *
     * @param string $data Data to sign
     * @return string Binary signature
     */
    protected function createSignature(string $data): string
    {
        if ($this->privateKey instanceof RSA) {
            $this->privateKey = $this->privateKey
                ->withHash($this->hashAlgorithm)
                ->withPadding(RSA::SIGNATURE_PKCS1);
        }

        return $this->privateKey->sign($data);
    }

    /**
     * Build the complete SignedData structure
     *
     * @param array<array<string, mixed>> $signedAttrs Signed attributes
     * @param string $signature Binary signature
     * @return string DER-encoded SignedData
     */
    protected function buildSignedDataStructure(array $signedAttrs, string $signature): string
    {
        // Version
        $version = $this->encodeInteger(1);

        // DigestAlgorithms SET
        $digestAlgOid = $this->getDigestAlgorithmOID();
        $digestAlgorithms = $this->wrapInSet(
            $this->wrapInSequence($this->encodeOID($digestAlgOid))
        );

        // EncapsulatedContentInfo (empty for detached signature)
        $encapContentInfo = $this->wrapInSequence(
            $this->encodeOID(self::OID_DATA)
        );

        // Certificates [0] IMPLICIT
        $certificates = $this->wrapInImplicitTag($this->certificateDer, 0);

        // Add certificate chain
        foreach ($this->certChain as $chainCert) {
            $chainDer = $chainCert->saveX509($chainCert->getCurrentCert(), X509::FORMAT_DER);
            $certificates .= $chainDer;
        }

        // SignerInfos SET
        $signerInfo = $this->buildSignerInfo($signedAttrs, $signature);
        $signerInfos = $this->wrapInSet($signerInfo);

        // Build SignedData SEQUENCE
        $signedData = $version
            . $digestAlgorithms
            . $encapContentInfo
            . $certificates
            . $signerInfos;

        $signedDataSeq = $this->wrapInSequence($signedData);

        // Wrap in ContentInfo
        $contentInfo = $this->encodeOID(self::OID_SIGNED_DATA)
            . $this->wrapInExplicitTag($signedDataSeq, 0);

        return $this->wrapInSequence($contentInfo);
    }

    /**
     * Build SignerInfo structure
     *
     * @param array<array<string, mixed>> $signedAttrs Signed attributes
     * @param string $signature Binary signature
     * @return string DER-encoded SignerInfo
     */
    protected function buildSignerInfo(array $signedAttrs, string $signature): string
    {
        // Version
        $version = $this->encodeInteger(1);

        // IssuerAndSerialNumber
        $issuerAndSerial = $this->buildIssuerAndSerialNumber();

        // DigestAlgorithm
        $digestAlgOid = $this->getDigestAlgorithmOID();
        $digestAlgorithm = $this->wrapInSequence(
            $this->encodeOID($digestAlgOid) . "\x05\x00" // NULL parameters
        );

        // SignedAttrs [0] IMPLICIT
        $encodedAttrs = $this->encodeAttributes($signedAttrs);

        // SignatureAlgorithm
        $sigAlgOid = $this->getSignatureAlgorithmOID();
        $signatureAlgorithm = $this->wrapInSequence(
            $this->encodeOID($sigAlgOid) . "\x05\x00" // NULL parameters
        );

        // Signature value
        $signatureValue = $this->encodeOctetString($signature);

        // Build unsigned attributes (timestamp if configured)
        $unsignedAttrs = $this->buildUnsignedAttributes($signature);

        return $this->wrapInSequence(
            $version
            . $issuerAndSerial
            . $digestAlgorithm
            . $encodedAttrs
            . $signatureAlgorithm
            . $signatureValue
            . $unsignedAttrs
        );
    }

    /**
     * Build unsigned attributes (includes timestamp token if configured)
     *
     * @param string $signature Binary signature value
     * @return string DER-encoded unsigned attributes or empty string
     */
    protected function buildUnsignedAttributes(string $signature): string
    {
        if ($this->timestampClient === null) {
            return '';
        }

        try {
            // Get timestamp token for the signature value
            $timestampToken = $this->timestampClient->getTimestampToken($signature);

            // Build unsigned attribute with timestamp token
            $attrSequence = $this->encodeOID(self::OID_SIGNATURE_TIMESTAMP);
            $attrSequence .= $this->wrapInSet($timestampToken);
            $encodedAttr = $this->wrapInSequence($attrSequence);

            // Wrap in implicit [1] context tag for unsigned attributes
            return $this->wrapInImplicitTag($encodedAttr, 1);
        } catch (\Exception $e) {
            // If timestamp fails, continue without it (log warning in production)
            return '';
        }
    }

    /**
     * Build IssuerAndSerialNumber
     *
     * @return string DER-encoded IssuerAndSerialNumber
     */
    protected function buildIssuerAndSerialNumber(): string
    {
        $cert = $this->x509->getCurrentCert();

        // Get issuer DN from certificate and encode using ASN1
        $issuerDN = $cert['tbsCertificate']['issuer'];
        $issuerEncoded = ASN1::encodeDER($issuerDN, Name::MAP);

        // Get serial number
        $serialNumber = $cert['tbsCertificate']['serialNumber'];
        $serialEncoded = $this->encodeBigInteger($serialNumber);

        return $this->wrapInSequence($issuerEncoded . $serialEncoded);
    }

    /**
     * Encode a BigInteger as DER integer
     *
     * @param \phpseclib3\Math\BigInteger $bigInt BigInteger value
     * @return string DER-encoded integer
     */
    protected function encodeBigInteger(\phpseclib3\Math\BigInteger $bigInt): string
    {
        $bytes = $bigInt->toBytes();

        // Add leading zero if high bit is set (to indicate positive number)
        if (strlen($bytes) > 0 && (ord($bytes[0]) & 0x80)) {
            $bytes = "\x00" . $bytes;
        }

        // Handle zero case
        if ($bytes === '') {
            $bytes = "\x00";
        }

        return "\x02" . $this->encodeLength(strlen($bytes)) . $bytes;
    }

    /**
     * Get the OID for the digest algorithm
     *
     * @return string OID string
     */
    protected function getDigestAlgorithmOID(): string
    {
        return match ($this->hashAlgorithm) {
            'sha384' => self::OID_SHA384,
            'sha512' => self::OID_SHA512,
            default => self::OID_SHA256,
        };
    }

    /**
     * Get the OID for the signature algorithm
     *
     * @return string OID string
     */
    protected function getSignatureAlgorithmOID(): string
    {
        return match ($this->hashAlgorithm) {
            'sha384' => self::OID_RSA_SHA384,
            'sha512' => self::OID_RSA_SHA512,
            default => self::OID_RSA_SHA256,
        };
    }

    // ========== ASN.1 Encoding Helpers ==========

    /**
     * Encode an OID
     *
     * @param string $oid OID string (e.g., "1.2.840.113549.1.7.2")
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
     * @param int|string $value Integer value
     * @return string DER-encoded integer
     */
    protected function encodeInteger(int|string $value): string
    {
        if (is_string($value)) {
            $value = intval($value);
        }

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
     * Encode a UTC time
     *
     * @param string $time Time string (YYMMDDHHmmSSZ)
     * @return string DER-encoded UTC time
     */
    protected function encodeUTCTime(string $time): string
    {
        return "\x17" . $this->encodeLength(strlen($time)) . $time;
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

    /**
     * Wrap data in a SET
     *
     * @param string $data Data to wrap
     * @return string DER-encoded SET
     */
    protected function wrapInSet(string $data): string
    {
        return "\x31" . $this->encodeLength(strlen($data)) . $data;
    }

    /**
     * Wrap data in an explicit context tag
     *
     * @param string $data Data to wrap
     * @param int $tag Tag number
     * @return string DER-encoded tagged data
     */
    protected function wrapInExplicitTag(string $data, int $tag): string
    {
        return chr(0xA0 | $tag) . $this->encodeLength(strlen($data)) . $data;
    }

    /**
     * Wrap data in an implicit context tag
     *
     * @param string $data Data to wrap
     * @param int $tag Tag number
     * @return string DER-encoded tagged data
     */
    protected function wrapInImplicitTag(string $data, int $tag): string
    {
        return chr(0xA0 | $tag) . $this->encodeLength(strlen($data)) . $data;
    }
}
