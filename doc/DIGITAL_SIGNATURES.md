# Digital Signatures

Back to root overview: [README.md](../README.md#in-depth-documentation)

`tc-lib-pdf` supports detached CMS (PKCS#7) signatures with optional RFC 3161 timestamps and LTV (Long-Term Validation) material, all embedded in a single PDF revision.

Signature-focused runnable examples:

- [examples/E007_signature_basic.php](../examples/E007_signature_basic.php): basic detached CMS signature with visible signature fields.
- [examples/E008_signature_timestamp.php](../examples/E008_signature_timestamp.php): detached CMS signature with RFC 3161 TSA timestamp configuration.
- [examples/E009_signature_ltv.php](../examples/E009_signature_ltv.php): detached CMS signature with LTV validation material embedding.
- [examples/E075_external_signature_injection.php](../examples/E075_external_signature_injection.php): external/remote signing workflow with ByteRange digest export and later CMS (PKCS#7) signature injection.

## Basic Signature

```php
$pdf->setSignature([
    'signcert'  => 'file:///path/to/cert.pem',
    'privkey'   => 'file:///path/to/key.pem',
    'password'  => '',
    'cert_type' => 2,
    'info'      => [
        'Name'        => 'Jane Smith',
        'Location'    => 'London',
        'Reason'      => 'Document approval',
        'ContactInfo' => 'jane@example.com',
    ],
]);
```

## Adding a TSA Timestamp (RFC 3161)

Call `setSignTimeStamp()` after `setSignature()` to request a timestamp token from a trusted TSA and embed it in the CMS unsigned attributes:

```php
$pdf->setSignTimeStamp([
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

## LTV (Long-Term Validation)

Enable LTV via the `ltv` key inside `setSignature()`. The library fetches OCSP responses and CRL payloads from the certificate's AIA and CDP extensions and writes them into the same PDF revision as the signature:

```php
$pdf->setSignature([
    'signcert' => 'file:///path/to/cert.pem',
    'privkey'  => 'file:///path/to/key.pem',
    'password' => '',
    'ltv'      => [
        'enabled'     => true,
        'embed_ocsp'  => true,   // fetch OCSP responses
        'embed_crl'   => true,   // fetch CRL payloads (fallback)
        'embed_certs' => true,   // include certificate DER bytes
        'include_dss' => true,   // emit /DSS in catalog
        'include_vri' => true,   // emit /VRI map keyed by cert SHA-1
    ],
]);
```

When LTV is enabled the output PDF contains `/DSS`, `/VRI`, `/OCSPs`, `/CRLs`, and `/Certs` objects, making Adobe-style long-term signature validation feasible without any external retrieval at verification time.

## Generating a Self-Signed Test Certificate

```bash
openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
    -keyout tcpdf.key -out tcpdf.crt
# convert to PKCS#12 if needed
openssl pkcs12 -export -in tcpdf.crt -inkey tcpdf.key -out tcpdf.p12
```
