# Digital Signatures

Back to root overview: [README.md](../README.md#in-depth-documentation)

`tc-lib-pdf` produces detached CMS (PKCS#7) signatures and **PAdES baseline** signatures
(ETSI EN 319 142-1) with optional RFC 3161 timestamps, LTV (Long-Term Validation)
material, and archive timestamps. The cryptography lives in the companion package
[`tecnickcom/tc-lib-pdf-sign`](https://github.com/tecnickcom/tc-lib-pdf-sign).

## Signature profiles

The `profile` option selects the signature format:

| Profile | /SubFilter | What it adds |
| --- | --- | --- |
| `legacy` (default) | `adbe.pkcs7.detached` | ISO 32000-1 detached CMS with the ESS signing-certificate-v2 attribute. |
| `pades-b-b` | `ETSI.CAdES.detached` | PAdES-BASELINE-B: CAdES-based CMS; the signing time is carried by the `/M` dictionary entry (the CMS signing-time attribute is omitted, as the baseline requires). |
| `pades-b-t` | `ETSI.CAdES.detached` | PAdES-BASELINE-T: B-B plus an RFC 3161 signature timestamp embedded in the CMS. |
| `pades-b-lt` | `ETSI.CAdES.detached` | PAdES-BASELINE-LT: B-T plus a Document Security Store (`/DSS`, `/VRI`) with certificates and, where reachable, OCSP/CRL revocation data. |
| `pades-b-lta` | `ETSI.CAdES.detached` + `ETSI.RFC3161` | PAdES-BASELINE-LTA: B-LT plus a `/Type /DocTimeStamp` archive timestamp over the whole document. |

The default profile stays `legacy`, so existing signing output is unchanged unless a PAdES
profile is requested. `digest_algorithm` accepts `sha256` (default), `sha384`, or `sha512`;
RSA and ECDSA signing keys are both supported.

Signature-focused runnable examples:

- [examples/E007_signature_basic.php](../examples/E007_signature_basic.php): PAdES-BASELINE-B signature via the fluent `signature()` facade.
- [examples/E008_signature_timestamp.php](../examples/E008_signature_timestamp.php): PAdES-BASELINE-T signature with an RFC 3161 TSA timestamp.
- [examples/E009_signature_ltv.php](../examples/E009_signature_ltv.php): PAdES-BASELINE-LT signature with LTV material (`/DSS`, `/VRI`).
- [examples/E081_signature_pades_lta.php](../examples/E081_signature_pades_lta.php): PAdES-BASELINE-LTA signature with a document archive timestamp via `upgradeToLta()`.
- [examples/E075_external_signature_injection.php](../examples/E075_external_signature_injection.php): external/remote signing workflow with ByteRange digest export and later CMS signature injection.
- [examples/E084_signature_two_phase_cms.php](../examples/E084_signature_two_phase_cms.php): two-phase signing where a key service signs the CMS signed attributes and the detached CAdES is assembled here.

## Fluent API: `signature()`

The preferred entry point is the `signature()` facade. Each call is chainable and forwards
to the underlying methods (which remain available as `setSignature()`, `setSignTimeStamp()`,
`setUserRights()`, `setSignatureAppearance()`, and so on).

```php
$pdf->signature()
    ->configure([
        'profile'          => 'pades-b-t',   // legacy | pades-b-b | pades-b-t | pades-b-lt | pades-b-lta
        'digest_algorithm' => 'sha256',      // sha256 | sha384 | sha512
        'signcert'         => 'file:///path/to/cert.pem',
        'privkey'          => 'file:///path/to/key.pem',
        'password'         => '',
        'extracerts'       => 'file:///path/to/chain.pem',   // optional issuer chain
        'cert_type'        => 2,
        'info'             => [
            'Name'        => 'Jane Smith',
            'Location'    => 'London',
            'Reason'      => 'Document approval',
            'ContactInfo' => 'jane@example.com',
        ],
    ])
    ->timestamp([
        'enabled'        => true,
        'host'           => 'https://freetsa.org/tsr',
        'hash_algorithm' => 'sha256',
        'timeout'        => 30,
        'verify_peer'    => true,
        'allow_sha1'     => true,            // this TSA still uses the SHA-1 v1 attribute
    ]);

$pdf->signature()->appearance()->place(posx: 15, posy: 35, width: 90, height: 20, page: -1, name: 'Signature');
$widgetObjId = $pdf->signature()->widgetObjectId();
```

A signature field name cannot contain a period: ISO 32000-1 clause 12.7.3.2 makes it the
separator between the components of a fully qualified field name, so one is rejected.

## Adding a TSA Timestamp (RFC 3161)

For `pades-b-t` and above a timestamp is required. Configure it with
`signature()->timestamp([...])` (or the legacy `setSignTimeStamp([...])`); the RFC 3161
token is embedded in the CMS as the `id-aa-signatureTimeStampToken` unsigned attribute:

```php
$pdf->signature()->timestamp([
    'enabled'        => true,
    'host'           => 'https://freetsa.org/tsr',
    'username'       => '',
    'password'       => '',
    'cert'           => '',
    'hash_algorithm' => 'sha256',   // sha256 | sha384 | sha512
    'policy_oid'     => '',         // optional OID string
    'nonce_enabled'  => true,
    'timeout'        => 30,
    'verify_peer'    => true,
    'allow_sha1'     => false,      // accept a SHA-1 token (see below)
]);
```

The token a TSA returns is verified before it is embedded: its `SignerInfo` signature
must verify against the TSA certificate the token carries, that certificate must hold
`id-kp-timeStamping` as its single critical extended key usage and must have covered the
instant the token attests, and the token must answer the request that was sent (same
message imprint under the same digest algorithm, the nonce echoed unchanged, the policy
that was asked for, and a `genTime` near the moment of the request). A token failing any
of these raises a `\Com\Tecnick\Pdf\Exception` instead of being written to the document.

`allow_sha1` (off by default) relaxes the digest rules for that check: it accepts a token
whose signature, message digest, or ESS certificate hash uses SHA-1. Some public TSAs
name their certificate with the RFC 2634 `signing-certificate` (v1) attribute, which is
SHA-1 by definition, and are refused without it; `freetsa.org`, used by the signature
examples, is one of them. A TSA emitting the `signing-certificate-v2` attribute
(SHA-256) needs no such setting. The relaxation applies only to the timestamp token, not
to the OCSP responses or CRLs collected for the DSS.

## LTV (Long-Term Validation) and archive timestamps

Enable LTV via the `ltv` key inside `configure()`. The library fetches OCSP responses and
CRL payloads from the certificate's AIA and CRL-DP extensions and writes a `/DSS` (with a
`/VRI` map keyed by the uppercase SHA-1 of the signature `/Contents`) in a post-signing
incremental revision:

```php
$pdf->signature()->configure([
    'profile'  => 'pades-b-lt',
    'signcert' => 'file:///path/to/cert.pem',
    'privkey'  => 'file:///path/to/key.pem',
    'password' => '',
    'ltv'      => [
        'enabled'     => true,
        'embed_ocsp'  => true,   // fetch OCSP responses
        'embed_crl'   => true,   // fetch CRL payloads (fallback)
        'embed_certs' => true,   // include certificate DER bytes
        'include_dss' => true,   // emit /DSS in the catalog
        'include_vri' => true,   // emit /VRI map keyed by signature SHA-1
    ],
]);
```

The `extracerts` bundle is read as the chain above the signing certificate, leaf first up
to the root, one certificate per entry; a bundle that repeats the signing certificate (a
`fullchain.pem`) is accepted, the repeat being dropped. The order is verified by
signature, so a chain whose entries do not issue one another is rejected with a
`\Com\Tecnick\Pdf\Exception`.

To reach PAdES-BASELINE-LTA, call `upgradeToLta()` (it selects the `pades-b-lta` profile,
forces the DSS on, and adds a `/Type /DocTimeStamp` archive timestamp over the whole
document in a further incremental revision; a TSA must be configured):

```php
$pdf->signature()->configure([/* pades-b-lt + ltv */])->timestamp([/* TSA */])->upgradeToLta();
```

A validator only reports the LT/LTA level when the DSS actually contains the revocation data
for the chain, so the signing certificate must expose reachable OCSP/CRL responders. A
self-signed certificate embeds only its own bytes, so a validator then reports B-T with a
DSS present.

### What reaches the DSS

Nothing an OCSP responder or a CRL distribution point returns is embedded until it has
been verified, so the document never carries evidence a validator would reject:

- an **OCSP response** must have a successful status, a basic response type, a signature
  that verifies against a responder the issuer authorised (itself, or a delegate holding
  `id-kp-OCSPSigning`), a `CertID` matching the request, a `good` certificate status, and
  a validity interval covering the signing time;
- a **CRL** must be one complete list issued and signed by the certificate whose
  distribution point it came from, covering the signing time, not narrowed by a
  `deltaCRLIndicator` or an `issuingDistributionPoint`, and issued by a CA whose
  `keyUsage` admits `cRLSign`;
- the digests and signatures accepted are SHA-256 and above; SHA-1 is refused, and
  RSASSA-PSS is not supported in either direction.

The signing time (the document modification time, the same instant the `/M` entry
carries) is the moment every response is checked against.

Revocation collection is best-effort: a source that is unreachable, that returns
something unusable, or that reports the certificate revoked is skipped, and the rest of
the material is still embedded. The certificates carried by the signature timestamp token
are collected into the DSS alongside the signer's own chain, and are run through the same
OCSP and CRL lookups, as ETSI EN 319 142-1 requires of a B-LT Document Security Store.

## Signing with a key this process cannot reach

`signature()->external()` reserves the signature field without a key, so the document is
built and hashed here and the signature is made elsewhere. `configure()` takes the same
options as `configure()` on the facade, with `signcert` and `privkey` left empty;
`prepare()` returns the prepared document bytes, the `/ByteRange` tuple, and the digest of
the covered bytes; `apply()` writes the returned CMS into the reserved `/Contents`, which
has to hold it (11742 hexadecimal digits, or 31742 with a timestamp enabled).

```php
$pdf->signature()->external()->configure([/* ... */ 'privkey' => '', 'signcert' => '']);
$prepared = $pdf->signature()->external()->prepare('sha256');
$signedPdf = $pdf->signature()->external()->apply(
    $prepared['prepared_pdf'],
    $prepared['byte_range'],
    $cms,
    'binary',
);
```

What produces `$cms` is what separates the two workflows:

- a **provider that returns a complete CMS** is handed the digest and gives back the whole
  detached signature, which `apply()` only injects. The profile the provider signs under
  has to match the one `configure()` was given, since the PDF `/SubFilter` comes from it.
  This is [examples/E075_external_signature_injection.php](../examples/E075_external_signature_injection.php);
- a **key service that signs bytes**, such as an HSM, a KMS, or a remote signing API, never
  sees the document. `Com\Tecnick\Pdf\Sign\Signer::prepare()` turns the digest and the
  signing certificate into a `SigningRequest`, `signaturePayload()` renders it as the DER
  SET OF signed attributes the key signs, and `buildFromSignature()` rebuilds the same
  attributes, checks the returned signature against them, and emits the detached CAdES. The
  request crosses a queue or a second HTTP request through `toArray()`/`fromArray()`, which
  take an optional key for an HMAC over the exported state. This is
  [examples/E084_signature_two_phase_cms.php](../examples/E084_signature_two_phase_cms.php).

Both halves of a two-phase signature take the same `Com\Tecnick\Pdf\Sign\Config`: it fixes
the digest algorithm and, through the profile, whether the CMS carries the signing-time
attribute. A B-T or higher profile also requires the timestamp client and transport in
`buildFromSignature()`.

## Generating a Self-Signed Test Certificate

The bundled `examples/data/cert/tcpdf.crt` is a self-signed demo certificate (certificate
and RSA private key in one file). Generate your own with:

```bash
openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -sha256 \
    -keyout tcpdf.key -out tcpdf.crt \
    -subj "/CN=tc-lib-pdf test certificate"
# combine into a single file (as the bundled demo does), or reference them separately
cat tcpdf.crt tcpdf.key > tcpdf.pem
# convert to PKCS#12 if needed
openssl pkcs12 -export -in tcpdf.crt -inkey tcpdf.key -out tcpdf.p12
```

For a real PAdES-BASELINE-LT/LTA validation you need a certificate issued by a CA whose
OCSP responder (AIA) and CRL distribution point are reachable at signing time.
