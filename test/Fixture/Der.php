<?php

/**
 * Der.php
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

/**
 * Protocol message builder for the signature tests.
 *
 * Assembles the RFC 3161, RFC 6960, and RFC 5280 structures the tc-lib-pdf-sign
 * validators read, signed by an Authority key, so that a mocked TSA, OCSP, or
 * CRL transport answers with material the package accepts.
 */
final class Der
{
    private const OID_SIGNED_DATA = '1.2.840.113549.1.7.2';

    private const OID_TST_INFO = '1.2.840.113549.1.9.16.1.4';

    private const OID_OCSP_BASIC = '1.3.6.1.5.5.7.48.1.1';

    private const OID_CONTENT_TYPE = '1.2.840.113549.1.9.3';

    private const OID_MESSAGE_DIGEST = '1.2.840.113549.1.9.4';

    private const OID_SIGNING_CERTIFICATE = '1.2.840.113549.1.9.16.2.12';

    private const OID_SIGNING_CERTIFICATE_V2 = '1.2.840.113549.1.9.16.2.47';

    private const OID_SHA256 = '2.16.840.1.101.3.4.2.1';

    private readonly Asn1 $asn1;

    public function __construct(?Asn1 $asn1 = null)
    {
        $this->asn1 = $asn1 ?? new Asn1();
    }

    /**
     * Build a complete TimeStampResp answering a DER TimeStampReq.
     *
     * The token covers the imprint that was sent, echoes the nonce, and is signed
     * by the TSA key, which is what Timestamp\Client::parseResponse() checks.
     *
     * @param list<string> $extraCertsDer Certificates to embed besides the TSA's own.
     * @param ?int         $genTime       Instant the token attests, or null for now.
     * @param bool         $legacyEssCert Name the TSA certificate with the RFC 2634
     *                                    signing-certificate (v1) attribute, which is
     *                                    SHA-1, as some public TSAs still do.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     * @throws \Random\RandomException
     * @throws \RuntimeException
     */
    public function timestampResponseFor(
        string $request,
        array $extraCertsDer = [],
        ?int $genTime = null,
        bool $legacyEssCert = false,
    ): string {
        [$imprint, $hashOid, $nonce] = $this->requestFields($request);
        $token = $this->timestampToken(
            $this->tstInfo($imprint, $hashOid, $nonce, $genTime ?? \time()),
            $extraCertsDer,
            $legacyEssCert,
        );

        return $this->asn1->encodeSequence($this->asn1->encodeSequence($this->asn1->encodeInteger(0)) . $token);
    }

    /**
     * Build a complete, signed OCSPResponse saying good for a CertID.
     *
     * @param string $certId DER CertID the response quotes back.
     * @param ?int   $now    Instant the response is built for, or null for now.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     * @throws \Random\RandomException
     * @throws \RuntimeException
     */
    public function ocspResponse(string $certId, ?int $now = null): string
    {
        $now ??= \time();
        $single = $this->asn1->encodeSequence(
            $certId . "\x80\x00" . $this->generalizedTime($now - 3600)
                . $this->asn1->encodeContext(0, $this->generalizedTime($now + 86_400)),
        );

        $responseData = $this->asn1->encodeSequence(
            Authority::ca()->responderIdByName($this->asn1) . $this->generalizedTime($now - 3600)
                . $this->asn1->encodeSequence($single),
        );

        $basic = $this->asn1->encodeSequence(
            $responseData . $this->signatureAlgorithm() . $this->bitString(Authority::ca()->sign($responseData)),
        );

        return $this->asn1->encodeSequence(
            "\x0A\x01\x00"
                . $this->asn1->encodeContext(
                    0,
                    $this->asn1->encodeSequence(
                        $this->asn1->encodeObjectIdentifier(self::OID_OCSP_BASIC)
                            . $this->asn1->encodeOctetString($basic),
                    ),
                ),
        );
    }

    /**
     * Build a CertificateList issued and signed by the CA, revoking nothing.
     *
     * @param ?int $now Instant the list is built for, or null for now.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     * @throws \Random\RandomException
     * @throws \RuntimeException
     */
    public function crl(?int $now = null): string
    {
        $now ??= \time();
        $tbs = $this->asn1->encodeSequence(
            $this->asn1->encodeInteger(1)
                . $this->signatureAlgorithm()
                . Authority::ca()->subject($this->asn1)
                . $this->generalizedTime($now - 3600)
                . $this->generalizedTime($now + 86_400),
        );

        return $this->asn1->encodeSequence(
            $tbs . $this->signatureAlgorithm() . $this->bitString(Authority::ca()->sign($tbs)),
        );
    }

    /**
     * Wrap a TSTInfo as a timestamp token, a CMS SignedData signed by the TSA.
     *
     * @param list<string> $extraCertsDer Certificates to embed besides the TSA's own.
     * @param bool         $legacyEssCert Emit the v1 signing-certificate attribute.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     * @throws \Random\RandomException
     * @throws \RuntimeException
     */
    private function timestampToken(string $tstInfo, array $extraCertsDer, bool $legacyEssCert): string
    {
        $tsa = Authority::tsa();
        $digestAlgorithm = $this->asn1->encodeSequence($this->asn1->encodeObjectIdentifier(self::OID_SHA256));

        // RFC 3161 section 2.4.2 requires content-type and message-digest, and the
        // ESS signing-certificate-v2 names the certificate that signs.
        $signedAttrs =
            $this->attribute(self::OID_CONTENT_TYPE, $this->asn1->encodeObjectIdentifier(self::OID_TST_INFO))
            . $this->attribute(
                self::OID_MESSAGE_DIGEST,
                $this->asn1->encodeOctetString(\hash('sha256', $tstInfo, true)),
            )
            . $this->signingCertificate($tsa->certDer, $legacyEssCert);

        $fields = (new Certificate($this->asn1))->fields($tsa->certDer);
        $signerInfo = $this->asn1->encodeSequence(
            $this->asn1->encodeInteger(1)
                . $this->asn1->encodeSequence($fields['issuer'] . $fields['serial'])
                . $digestAlgorithm
                . $this->asn1->encodeContext(0, $signedAttrs)
                . $this->signatureAlgorithm()
                . $this->asn1->encodeOctetString($tsa->sign($this->asn1->encodeSet($signedAttrs))),
        );

        $encap = $this->asn1->encodeSequence(
            $this->asn1->encodeObjectIdentifier(self::OID_TST_INFO)
                . $this->asn1->encodeContext(0, $this->asn1->encodeOctetString($tstInfo)),
        );

        $signedData = $this->asn1->encodeSequence(
            $this->asn1->encodeInteger(3)
                . $this->asn1->encodeSet($digestAlgorithm)
                . $encap
                . $this->asn1->encodeContext(0, $tsa->certDer . \implode('', $extraCertsDer))
                . $this->asn1->encodeSet($signerInfo),
        );

        return $this->asn1->encodeSequence(
            $this->asn1->encodeObjectIdentifier(self::OID_SIGNED_DATA) . $this->asn1->encodeContext(0, $signedData),
        );
    }

    /**
     * Build a TSTInfo body over the imprint the request carried.
     *
     * @param string $nonce DER INTEGER of the nonce, or '' when none was sent.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     */
    private function tstInfo(string $imprint, string $hashOid, string $nonce, int $genTime): string
    {
        $messageImprint = $this->asn1->encodeSequence(
            $this->asn1->encodeSequence($this->asn1->encodeObjectIdentifier($hashOid))
                . $this->asn1->encodeOctetString($imprint),
        );

        return $this->asn1->encodeSequence(
            $this->asn1->encodeInteger(1)
            . $this->asn1->encodeObjectIdentifier('1.2.3.4.1')
            . $messageImprint
            . $this->asn1->encodeInteger(42)
            . $this->generalizedTime($genTime)
            . $nonce,
        );
    }

    /**
     * Recover the imprint, the digest OID, and the nonce of a DER TimeStampReq.
     *
     * @return array{string, string, string}
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     */
    private function requestFields(string $request): array
    {
        $offset = 0;
        $root = $this->asn1->readTlv($request, $offset);

        $inner = 0;
        $this->asn1->readTlv($root['value'], $inner); // version
        $imprint = $this->asn1->readTlv($root['value'], $inner);

        $imprintOffset = 0;
        $algorithm = $this->asn1->readTlv($imprint['value'], $imprintOffset);
        $hashed = $this->asn1->readTlv($imprint['value'], $imprintOffset);

        $algorithmOffset = 0;
        $oid = $this->asn1->readTlv($algorithm['value'], $algorithmOffset);

        // reqPolicy, nonce, and certReq follow the imprint; only the nonce is an
        // INTEGER, and it comes back unchanged in the token.
        $nonce = '';
        while ($inner < \strlen($root['value'])) {
            $field = $this->asn1->readTlv($root['value'], $inner);
            if ($field['tag'] === 0x02) {
                $nonce = $field['raw'];
            }
        }

        return [$hashed['value'], $this->asn1->decodeObjectIdentifier($oid['value']), $nonce];
    }

    /**
     * Encode an ESS signing-certificate attribute over a certificate.
     *
     * ESSCertIDv2 defaults to SHA-256 and so omits the algorithm; the v1 attribute
     * has no algorithm field at all and is SHA-1 by definition.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     */
    private function signingCertificate(string $certDer, bool $legacy): string
    {
        return $this->attribute(
            $legacy ? self::OID_SIGNING_CERTIFICATE : self::OID_SIGNING_CERTIFICATE_V2,
            $this->asn1->encodeSequence($this->asn1->encodeSequence($this->asn1->encodeSequence($this->asn1->encodeOctetString(\hash(
                $legacy ? 'sha1' : 'sha256',
                $certDer,
                true,
            ))))),
        );
    }

    /**
     * Encode a single CMS Attribute (type plus a one-element value SET).
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     */
    private function attribute(string $oid, string $value): string
    {
        return $this->asn1->encodeSequence($this->asn1->encodeObjectIdentifier($oid) . $this->asn1->encodeSet($value));
    }

    /**
     * The sha256WithRSAEncryption AlgorithmIdentifier every fixture signature uses.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     */
    private function signatureAlgorithm(): string
    {
        return $this->asn1->encodeSequence(
            $this->asn1->encodeObjectIdentifier(Authority::SIGNATURE_OID) . $this->asn1->encodeNull(),
        );
    }

    /**
     * Encode a DER GeneralizedTime.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     */
    private function generalizedTime(int $time): string
    {
        $value = \gmdate('YmdHis', $time) . 'Z';

        return "\x18" . $this->asn1->encodeLength(\strlen($value)) . $value;
    }

    /**
     * Encode a DER BIT STRING with no unused bits.
     *
     * @throws \Com\Tecnick\Pdf\Sign\Exception
     */
    private function bitString(string $bytes): string
    {
        $value = "\x00" . $bytes;

        return "\x03" . $this->asn1->encodeLength(\strlen($value)) . $value;
    }
}
