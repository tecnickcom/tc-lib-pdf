# Signature Execution Plan A

## Goal

Implement modern signature support in `tc-lib-pdf` using Strategy A only:

- detached CMS signatures,
- RFC 3161 TSA integration,
- single-revision LTV support,
- no append-mode incremental update in this execution plan.

This file is the execution-oriented version of the broader signature plan. It assumes that validation material is collected before final signing and embedded in the same PDF revision.

## Chosen Strategy

Strategy A means:

- collect certificates and revocation material before final signature assembly,
- emit `/DSS`, `/VRI`, `/OCSPs`, `/CRLs`, and `/Certs` in the same output revision,
- sign the final byte stream once, using the existing `/ByteRange` reservation flow.

This is the best fit for the current `tc-lib-pdf` architecture because the library already writes a full PDF in one pass and injects the detached CMS signature afterward.

## Non-Goals

This plan explicitly excludes:

- append-mode incremental update,
- `/Type /DocTimeStamp`,
- post-signature enhancement of existing PDFs,
- archival timestamp chaining,
- general-purpose signed PDF rewriting.

Those can be handled later in a separate Strategy B plan if needed.

## Why Strategy A First

Strategy A minimizes risk while delivering the core modern features:

- it fits the current writer model,
- it avoids introducing a second PDF writing mode,
- it keeps the first implementation reviewable,
- it allows TSA and LTV support without reworking xref and trailer generation,
- it is easier to validate under the current test suite and static-analysis rules.

## Current Constraints

The current implementation already provides these important seams:

- signature defaults in `src/Base.php`,
- public setters in `src/Tcpdf.php`,
- signature serialization and signing in `src/Output.php`,
- detached signing via `openssl_pkcs7_sign`,
- a placeholder TSA hook in `applySignatureTimestamp()`.

The major missing pieces are:

- a real TSA implementation,
- typed runtime state for LTV artifacts,
- collection of cert and revocation material,
- PDF serialization for DSS-related structures.

## Execution Phases

### Phase 1: Refactor the Existing Signature Flow

Objective:

- make the current signing pipeline extensible without changing behavior.

Tasks:

- split `Output::signDocument()` into smaller helpers,
- isolate:
  - reserved-contents sizing,
  - byte-range computation,
  - temporary-file creation,
  - CMS generation,
  - final hex injection,
- define typed internal results for the detached signing step,
- keep the current public API stable.

Definition of done:

- existing signature-related tests still pass,
- no behavior change,
- complexity in `Output.php` is reduced enough to support the next phase cleanly.

### Phase 2: Implement TSA Support

Objective:

- replace the timestamp placeholder with a real RFC 3161 path.

Tasks:

- extend the timestamp configuration shape with:
  - `hash_algorithm`,
  - `policy_oid`,
  - `nonce_enabled`,
  - `timeout`,
  - `verify_peer`,
- add a small HTTP transport abstraction,
- build the timestamp request from the detached CMS signature hash,
- post to the TSA endpoint,
- validate the returned structure and failure status,
- extract the timestamp token,
- embed the timestamp token in the CMS unsigned attributes,
- keep all external calls mockable in tests.

Definition of done:

- `setSignTimeStamp()` accepts and validates the extended options,
- `applySignatureTimestamp()` no longer returns the input unchanged when enabled,
- fixture-based tests cover success, invalid response, and configuration failure paths.

### Phase 3: Add Validation Material Collection

Objective:

- collect everything needed for LTV before final signature injection.

Tasks:

- add typed structures for:
  - signing cert chain,
  - embedded cert payloads,
  - OCSP responses,
  - CRLs,
  - VRI lookup values,
- implement a validation-material collector that:
  - loads the signing cert and any extra certs,
  - extracts AIA and CRL Distribution Point URLs,
  - fetches OCSP responses when available,
  - falls back to CRL when needed,
  - deduplicates identical binary payloads,
- keep the collector separate from PDF serialization,
- gate behavior behind explicit LTV configuration flags.

Definition of done:

- the collector returns deterministic, typed output,
- tests can run without live OCSP or CRL endpoints,
- error handling is explicit when configured resources cannot be obtained.

### Phase 4: Emit DSS and VRI Objects

Objective:

- serialize validation material into the same PDF revision.

Tasks:

- extend the catalog serializer to optionally include `/DSS`,
- add output helpers for:
  - `/DSS`,
  - `/VRI`,
  - `/OCSPs`,
  - `/CRLs`,
  - `/Certs`,
- reserve object IDs before final PDF assembly,
- wire the VRI entry to the signature key expected by the chosen validation profile,
- ensure xref generation includes every new object.

Definition of done:

- generated PDFs can include all LTV material in the original signed revision,
- output tests assert the presence and structure of the new objects,
- standard output generation remains stable for unsigned PDFs.

### Phase 5: Integrate Strategy A End-to-End

Objective:

- ensure the full single-revision path works as one coherent flow.

Tasks:

- add orchestration from the public setter state through:
  - CMS creation,
  - TSA enrichment,
  - validation material collection,
  - DSS emission,
  - final signature injection,
- confirm the final document still uses the current one-pass output model,
- validate the interaction between:
  - certification signatures,
  - approval signatures,
  - TSA,
  - LTV embedding,
  - user-rights signatures.

Definition of done:

- one generated PDF can include detached signature, TSA token, and DSS material in the same revision,
- the generated output passes the project test suite and local validator checks used during development.

## Recommended Internal Architecture

The implementation should remain close to current project conventions, but split responsibilities enough to keep complexity under control.

Recommended internal pieces:

- `SignatureManager`:
  - coordinates the full Strategy A flow,
- `SignatureCmsBuilder`:
  - handles detached CMS assembly and timestamp enrichment,
- `TimestampClient`:
  - owns RFC 3161 request and response handling,
- `ValidationMaterialCollector`:
  - collects certs, OCSP, and CRL material,
- `Output` helper methods:
  - serialize `/Sig`, `/DSS`, `/VRI`, `/OCSPs`, `/CRLs`, and `/Certs`.

These can be implemented as internal classes under `src` or as tightly scoped helper classes, depending on how aggressively the maintainer wants to modularize the library.

## Data Model Changes

The public API should remain array-based, but the array shapes must be extended and documented.

Recommended additions to signature state:

- `digest_algorithm`,
- `estimated_contents_length`,
- `ltv`:
  - `enabled`,
  - `embed_ocsp`,
  - `embed_crl`,
  - `embed_certs`,
  - `include_dss`,
  - `include_vri`.

Recommended additions to timestamp state:

- `hash_algorithm`,
- `policy_oid`,
- `nonce_enabled`,
- `timeout`,
- `verify_peer`.

Recommended runtime-only structures:

- detached CMS result,
- timestamp token result,
- collected validation material,
- DSS serialization plan,
- reserved object IDs for signature-related PDF objects.

All of these should be represented using explicit `@phpstan-type` aliases.

## Dependency Recommendation

The safest implementation path is to introduce a maintained dependency for CMS and ASN.1 handling, then wrap it behind a small adapter.

Reason:

- TSA support requires structured CMS manipulation,
- LTV support becomes much safer when certificate and response parsing are not hand-written,
- a dependency wrapper keeps the rest of the codebase decoupled from the underlying implementation choice.

If no dependency is allowed, the scope should be reduced and the risk called out clearly before implementation begins.

## Testing Plan

### Unit Tests

Add tests for:

- new setter validation in `test/TcpdfTest.php`,
- byte-range helper behavior,
- timestamp request building,
- timestamp response parsing,
- validation material collection,
- DSS and VRI helper serialization.

### Output Tests

Extend `test/OutputTest.php` to assert:

- `/Type /Sig`,
- `/SubFilter`,
- timestamp-related output where visible,
- `/DSS`,
- `/VRI`,
- `/OCSPs`,
- `/CRLs`,
- `/Certs`.

### Integration Tests

Use local fixtures only:

- test certificates,
- OCSP binaries,
- CRL binaries,
- TSA response fixtures,
- fake transport implementations.

No live network services should be required.

## Static Analysis and Style Requirements

The implementation must satisfy the existing project rules.

That means:

- keep methods small enough for PHPMD,
- avoid undocumented nested arrays,
- validate options before they reach deep output code,
- keep network and crypto helpers isolated from PDF string building,
- avoid broad inline suppressions for PHPStan,
- write deterministic tests that do not depend on environment timing or remote services.

## Main Risks

1. CMS timestamp insertion is the highest technical-risk area.
2. Incorrect VRI keying can produce structurally valid PDFs that validators still reject.
3. The existing fixed signature reservation length may be too small once timestamp and LTV payloads grow.
4. LTV collection can become unreliable if network concerns are not isolated and configurable.

## Risk Mitigations

- choose the CMS/ASN.1 dependency strategy first,
- make the reserved contents length configurable or safely overprovisioned,
- keep the collector test-driven with binary fixtures,
- validate generated documents with external PDF signature validators during development,
- ship TSA first, then enable DSS only after the collector is stable.

## Concrete Execution Order

1. Refactor the current signing code path without changing behavior.
2. Extend type definitions and defaults for timestamp and LTV settings.
3. Implement the TSA transport and timestamp enrichment path.
4. Add unit and output tests for TSA.
5. Implement validation material collection.
6. Add DSS and VRI serialization in the original revision.
7. Add output and integration tests for Strategy A end-to-end behavior.
8. Run the full project quality gates.

## Done Criteria

This Strategy A execution plan is complete when:

- detached signatures still work,
- TSA support is real and tested,
- LTV material can be embedded in the original signed revision,
- no append-mode incremental update support is required for the shipped feature,
- the code passes `phpcs`, `phpmd`, and `phpstan`,
- tests cover the new success and failure paths,
- the implementation remains maintainable under the current project conventions.