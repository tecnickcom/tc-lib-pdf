<?php

/**
 * Authority.php
 *
 * @since       2026-08-25
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test\Fixture;

use Com\Tecnick\Pdf\Sign\Cms\Asn1;
use Com\Tecnick\Pdf\Sign\Cms\Certificate;
use OpenSSLAsymmetricKey;
use RuntimeException;

/**
 * Signing authority backed by a certificate generated for the test run.
 *
 * The tc-lib-pdf-sign validators verify the signature over every timestamp
 * token, OCSP response, and CRL before it becomes DSS material, so a fixture has
 * to be genuinely signed by the key of the certificate it claims. The chain is
 * one CA that issued a TSA certificate carrying a critical id-kp-timeStamping
 * and a leaf carrying the AIA and CRL distribution points the URL extraction
 * reads. It is built once per process and kept valid from the moment it is
 * built, so no fixture expires.
 */
final class Authority
{
    /**
     * sha256WithRSAEncryption, the algorithm every fixture key uses.
     */
    public const SIGNATURE_OID = '1.2.840.113549.1.1.11';

    /**
     * The generated chain, or null before the first use.
     *
     * @var ?array{ca: self, tsa: self, leaf: self}
     */
    private static ?array $chain = null;

    private function __construct(
        public readonly string $certPem,
        public readonly string $certDer,
        private readonly OpenSSLAsymmetricKey $key,
    ) {}

    /**
     * The self-signed CA that issued the other two, and the issuer whose key
     * signs the OCSP responses and the CRLs.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     * @throws \Random\RandomException
     * @throws RuntimeException
     */
    public static function ca(): self
    {
        return self::chain()['ca'];
    }

    /**
     * The TSA certificate of the CA, carrying a critical id-kp-timeStamping.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     * @throws \Random\RandomException
     * @throws RuntimeException
     */
    public static function tsa(): self
    {
        return self::chain()['tsa'];
    }

    /**
     * The leaf of the CA, the certificate carrying an OCSP responder URL and two
     * CRL distribution points.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     * @throws \Random\RandomException
     * @throws RuntimeException
     */
    public static function leaf(): self
    {
        return self::chain()['leaf'];
    }

    /**
     * Sign bytes with this authority's key, as sha256WithRSAEncryption.
     *
     * @throws RuntimeException
     */
    public function sign(string $data): string
    {
        $signature = '';
        if (!\openssl_sign($data, $signature, $this->key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign the fixture');
        }

        return $signature;
    }

    /**
     * The subject Name of this authority, as the certificate encodes it.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     */
    public function subject(Asn1 $asn1): string
    {
        return (new Certificate($asn1))->fields($this->certDer)['subject'];
    }

    /**
     * ResponderID ::= byName [1] EXPLICIT Name.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     */
    public function responderIdByName(Asn1 $asn1): string
    {
        return $asn1->encodeContext(1, $this->subject($asn1));
    }

    /**
     * Generate the chain, once per process.
     *
     * @return array{ca: self, tsa: self, leaf: self}
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     * @throws \Random\RandomException
     * @throws RuntimeException
     */
    private static function chain(): array
    {
        if (self::$chain !== null) {
            return self::$chain;
        }

        $ca = self::issue('tc-lib-pdf test CA', 'ca_ext', null);
        self::$chain = [
            'ca' => $ca,
            'tsa' => self::issue('tc-lib-pdf test TSA', 'tsa_ext', $ca),
            'leaf' => self::issue('tc-lib-pdf test leaf', 'leaf_ext', $ca),
        ];

        return self::$chain;
    }

    /**
     * Generate a key pair and the certificate that carries it.
     *
     * @param string $extensions Section of the fixture OpenSSL configuration
     *                           holding the extensions of this certificate.
     * @param ?self  $issuer     Issuing authority, or null for a self-signed one.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     * @throws \Random\RandomException
     * @throws RuntimeException
     */
    private static function issue(string $commonName, string $extensions, ?self $issuer): self
    {
        $config = [
            'config' => __DIR__ . '/../fixtures/sign_openssl.cnf',
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $key = \openssl_pkey_new($config);
        if (!$key instanceof OpenSSLAsymmetricKey) {
            throw new RuntimeException('Unable to generate the fixture key');
        }

        $subject = ['countryName' => 'IT', 'organizationName' => 'Tecnick.com', 'commonName' => $commonName];
        $csr = \openssl_csr_new($subject, $key, $config);
        if (!$csr instanceof \OpenSSLCertificateSigningRequest) {
            throw new RuntimeException('Unable to generate the fixture certificate request');
        }

        $cert = \openssl_csr_sign(
            $csr,
            $issuer instanceof self ? $issuer->certPem : null,
            $issuer instanceof self ? $issuer->key : $key,
            7300,
            $config + ['x509_extensions' => $extensions],
            \random_int(1, PHP_INT_MAX),
        );
        if (!$cert instanceof \OpenSSLCertificate) {
            throw new RuntimeException('Unable to sign the fixture certificate');
        }

        $certPem = '';
        \openssl_x509_export($cert, $certPem);

        return new self($certPem, Certificate::pemToDer($certPem), $key);
    }
}
