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
    ]);

$pdf->signature()->appearance()->place(posx: 15, posy: 35, width: 90, height: 20, page: -1, name: 'Signature');
$widgetObjId = $pdf->signature()->widgetObjectId();
```

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
]);
```

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
