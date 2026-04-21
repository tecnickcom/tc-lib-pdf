# Signature Modernization Plan

## Goal

Implement modern digital signature support in `tc-lib-pdf` with a staged design that adds:

- detached CMS signatures for generated PDFs,
- RFC 3161 TSA integration,
- LTV-oriented validation material embedding,
- optional append-mode timestamping for archival workflows,
- tests and internal abstractions that comply with the current `phpcs`, `phpmd`, and `phpstan` rules.

This plan is intentionally designed for `tc-lib-pdf` as it exists today, not as a direct port of legacy TCPDF behavior.

## Current State

The current code already provides the basic seams needed for a modern implementation:

- signature configuration defaults live in `src/Base.php`,
- public signature setters live in `src/Tcpdf.php`,
- signature object emission and final signing live in `src/Output.php`,
- the current signing flow reserves `/Contents`, computes `/ByteRange`, writes a temporary PDF, and signs it with `openssl_pkcs7_sign`,
- TSA support is declared in the public API but not implemented,
- DSS, VRI, OCSP, CRL, and append-mode incremental update support are not implemented.

The most important constraint is architectural: the library currently writes a complete PDF in one pass and patches the detached signature into the final byte stream. That works for basic signing, but full LTV support requires either:

- precomputing validation material before signing and embedding it in the original revision, or
- adding true append-mode incremental update support for DSS and document timestamps.

## Design Principles

The implementation should follow these rules:

1. Keep public APIs narrow and typed.
2. Keep PDF serialization concerns separate from crypto, parsing, and network transport concerns.
3. Prefer several small internal helpers over expanding `Output.php` into a large multi-purpose signature engine.
4. Use explicit array-shape types for all new configuration and runtime state.
5. Make all network-facing behavior injectable and testable without external services.
6. Phase the work so TSA support ships before full LTV support.
7. Do not hand-roll ASN.1 or CMS parsing if a maintained dependency provides that functionality more safely.

## Scope

### In Scope

- modernizing the internal signature pipeline,
- implementing TSA requests and response handling,
- embedding revocation validation material for LTV workflows,
- adding PDF object emitters for DSS-related structures,
- optionally adding append-mode document timestamps,
- extending tests for success and failure paths,
- preserving existing public signature behavior where possible.

### Out of Scope for the First Merge

- upgrading already signed external PDFs,
- broad PDF parser work unrelated to signatures,
- certificate path validation as a full PKI engine,
- online integration tests against live TSA, OCSP, or CRL endpoints,
- supporting every historical TCPDF signature quirk.

## High-Level Delivery Plan

The work should be split into four phases.

### Phase 1: Refactor the Existing Signing Pipeline

Objective: make the current implementation easier to extend without changing behavior.

Work items:

- split `Output::signDocument()` into smaller internal steps,
- isolate byte-range reservation and patching logic,
- isolate CMS creation from PDF serialization,
- isolate temporary-file handling,
- define typed internal state for signing results,
- keep existing public methods in `Tcpdf` unchanged unless a new option is required.

Expected outcome:

- no feature change,
- smaller methods,
- lower PHPMD complexity,
- safer base for TSA and LTV work.

### Phase 2: Implement TSA Support

Objective: replace the placeholder timestamp method with a real RFC 3161 implementation.

Work items:

- extend the existing timestamp configuration with a typed set of options:
  - `enabled`,
  - `host`,
  - `username`,
  - `password`,
  - `cert`,
  - `hash_algorithm`,
  - `policy_oid`,
  - `nonce_enabled`,
  - `timeout`,
  - `verify_peer`,
- add a small internal transport abstraction for HTTP POST requests,
- build RFC 3161 requests against the CMS signature hash,
- validate the timestamp response structure and status,
- extract the timestamp token and embed it into the CMS unsigned attributes,
- surface deterministic exceptions for invalid TSA responses.

Expected outcome:

- current detached signatures gain real TSA support,
- the implementation remains testable with fixtures only,
- no dependence on live network services in CI.

## Phase 3: Add LTV Validation Material Collection

Objective: collect the certificate and revocation material needed for LTV-capable PDFs.

Work items:

- define typed runtime structures for:
  - signer certificates,
  - intermediate certificates,
  - OCSP responses,
  - CRLs,
  - VRI keys,
- add a validation-material collector that:
  - reads the signing certificate chain,
  - extracts AIA and CRL Distribution Point URLs,
  - fetches OCSP responses where possible,
  - falls back to CRLs when needed,
  - deduplicates binary payloads by fingerprint,
- keep this collector independent from PDF object emission,
- allow feature flags so callers can enable or disable OCSP, CRL, or cert embedding.

Expected outcome:

- the library can produce a complete set of validation artifacts for the signature it is generating,
- the collector can be tested in isolation.

### Phase 4: Emit DSS and VRI Structures

Objective: serialize collected validation material into PDF objects.

Work items:

- extend the catalog output to optionally include `/DSS`,
- add PDF emitters for:
  - `/DSS`,
  - `/VRI`,
  - `/OCSPs`,
  - `/CRLs`,
  - `/Certs`,
- allocate object IDs deterministically before final output,
- bind the VRI entry to the signature contents digest or another stable key required by the chosen profile,
- verify that catalog and xref generation continue to work with the new objects.

Expected outcome:

- generated PDFs can embed revocation evidence and certificate material in the same revision,
- Adobe-style LTV validation becomes feasible for documents signed by `tc-lib-pdf`.

### Phase 5: Optional Append-Mode Incremental Update

Objective: support workflows that require post-signature append operations, especially document timestamps.

This phase should be treated as optional for the first feature branch because it is materially larger than TSA support.

Work items:

- add append-mode PDF writing that preserves the signed original revision,
- write new objects after the original `startxref`,
- emit a new xref and trailer with `/Prev`,
- support `/Type /DocTimeStamp` objects,
- optionally append DSS or archival timestamps in a new revision,
- ensure the original `/ByteRange` remains valid.

Expected outcome:

- support for archival timestamping and future-proof signature workflows,
- a reusable append-mode mechanism for other PDF incremental update features.

## Recommended Internal Architecture

The implementation should avoid placing every signature concern in `Output.php`.

Recommended internal components:

- `SignatureManager` or similar:
  - coordinates the signing process,
  - owns the high-level workflow,
- `SignatureCmsBuilder`:
  - creates or enriches CMS signature payloads,
- `TimestampClient`:
  - builds TSA requests,
  - performs transport,
  - validates TSA responses,
- `ValidationMaterialCollector`:
  - collects certificates, OCSP, and CRL payloads,
- `SignaturePdfSerializer` or `Output` helper methods:
  - emit signature, DSS, and VRI objects.

These do not need to be public API classes. They can be internal classes under `src` or protected helpers if the maintainer prefers to minimize the class count. The key requirement is separation of concerns.

## Dependency Strategy

The current package does not include a CMS or ASN.1 helper dependency. That should be resolved early.

Preferred approach:

- add a maintained Composer dependency that can safely handle CMS and ASN.1 operations needed for TSA attribute insertion and related parsing,
- wrap that dependency behind a small internal adapter so the rest of the code remains library-agnostic.

Fallback approach:

- use OpenSSL only for detached signing,
- implement limited binary handling locally,
- accept that deeper CMS manipulation will be more brittle and harder to maintain.

Recommendation:

- do not hand-roll DER parsing unless the project explicitly requires zero new runtime dependencies.

## Public API Plan

The current API should remain recognizable. Existing methods should continue to exist:

- `setSignature(array $data): void`
- `setSignTimeStamp(array $data): void`

The array shapes should be extended rather than replaced. New keys should be validated in `Tcpdf` before they reach `Output`.

Recommended additions to signature configuration:

- `ltv`:
  - `enabled`,
  - `embed_ocsp`,
  - `embed_crl`,
  - `embed_certs`,
  - `include_dss`,
  - `include_vri`,
  - `append_document_timestamp`,
- `digest_algorithm`,
- `estimated_contents_length` for future-proof sizing.

Recommended additions to timestamp configuration:

- `hash_algorithm`,
- `policy_oid`,
- `nonce_enabled`,
- `timeout`,
- `verify_peer`,
- `headers` only if the maintainer explicitly wants customizable transport.

## PDF Generation Strategy

There are two viable strategies.

### Strategy A: Single-Revision LTV

Use this first.

- collect cert and revocation material before final signing,
- emit `/DSS` and related objects in the same PDF revision,
- compute the final signature over the completed document with reserved signature contents.

Advantages:

- fits the current one-pass writer more naturally,
- smaller implementation surface,
- fewer moving parts.

Limitations:

- does not support adding LTV information later to already signed PDFs,
- does not by itself provide append-mode document timestamps.

### Strategy B: Incremental Update

Use this second.

- sign the original revision,
- append DSS or `/DocTimeStamp` in a later revision,
- preserve the original signed bytes.

Advantages:

- closer to advanced archival workflows,
- supports post-signature enhancement.

Limitations:

- requires new writer logic,
- higher complexity and higher regression risk.

Recommendation:

- implement Strategy A first,
- defer Strategy B to a dedicated follow-up branch unless document timestamping is a hard requirement for the initial release.

## Testing Plan

Testing should be added in three layers.

### Unit Tests

Add focused tests for:

- new setter validation in `test/TcpdfTest.php`,
- TSA request building,
- TSA response validation,
- validation material collection,
- object-ID reservation logic,
- DSS and VRI serialization helpers.

### Output Tests

Extend `test/OutputTest.php` to assert the emitted PDF fragments for:

- `/Type /Sig`,
- `/SubFilter`,
- timestamp-related content where visible,
- `/DSS`,
- `/VRI`,
- `/OCSPs`,
- `/CRLs`,
- `/Certs`,
- `/Type /DocTimeStamp` if append-mode is added.

### Integration Tests

Use fixture-based tests only:

- local certificates,
- local OCSP or CRL binary fixtures,
- fake TSA responses,
- transport mocks or injectable test doubles.

Do not require live external services in CI.

## Static Analysis and Style Constraints

To keep the implementation compatible with the existing project standards:

- define all new nested arrays with `@phpstan-type` aliases,
- avoid undocumented `mixed` state,
- keep methods short enough to satisfy PHPMD complexity thresholds,
- prefer early validation in public setters,
- keep transport and crypto helpers side-effect-light,
- make fixture-driven tests deterministic,
- avoid inline suppression comments unless no cleaner alternative exists.

## Risks

The main risks are:

1. CMS manipulation complexity if no ASN.1 helper library is introduced.
2. Incorrect TSA response handling leading to signatures that appear valid structurally but fail verification.
3. Incorrect DSS or VRI wiring that passes string-level tests but fails in Acrobat or other validators.
4. Append-mode incremental update work expanding beyond the original feature budget.
5. Signature size estimation becoming too small for CMS plus timestamp plus embedded validation material.

## Mitigations

- choose the dependency strategy first,
- ship TSA before full LTV,
- implement single-revision DSS before append-mode support,
- use fixture-based regression tests for all binary inputs,
- make the reserved contents length configurable or dynamically overprovisioned,
- validate generated PDFs with external tooling during development even if those checks are not part of CI.

## Recommended Execution Order

1. Refactor the current signing code without changing behavior.
2. Introduce typed internal signature state and expanded configuration validation.
3. Add the transport abstraction and implement TSA support.
4. Add unit and output tests for TSA.
5. Add the validation material collector.
6. Add DSS and VRI object emission in the original PDF revision.
7. Add tests for DSS and VRI serialization.
8. Decide whether append-mode document timestamps are required for the initial release.
9. If required, implement incremental update support in a dedicated follow-up slice.

## Definition of Done

The signature modernization work can be considered complete when:

- detached signatures still work with the current API,
- TSA support is implemented and covered by tests,
- LTV material can be embedded in generated PDFs,
- the new code passes `phpcs`, `phpmd`, and `phpstan`,
- existing signature tests still pass,
- new fixture-based tests cover success and failure paths,
- the implementation is split cleanly enough that future signature work does not have to accumulate in `Output.php`.