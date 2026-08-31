# External Preflight Matrix

Generates one sample PDF for each supported conformance mode and runs external validators against them.

## Included scripts

- `generate_mode_samples.php` generates sample PDFs for:
  - `pdfa1`, `pdfa1a`, `pdfa1b`, `pdfa2`, `pdfa2a`, `pdfa2b`, `pdfa2u`, `pdfa3`, `pdfa3a`, `pdfa3b`, `pdfa3u`
  - `pdfx`, `pdfx1a`, `pdfx3`, `pdfx4`, `pdfx5`
  - `pdfua`, `pdfua1`, `pdfua2`

  Each sample carries the metadata its mode requires (title, language, and an embedded
  destination profile for PDF/X-4 and PDF/X-5). Any warning raised while building a
  sample fails the generator.
- `run_preflight_matrix.sh` runs:
  - `qpdf --check` when `qpdf` is available
  - `verapdf --format text --flavour 1a|1b|2a|2b|2u|3a|3b|3u` for PDF/A samples when `verapdf` is available
  - `verapdf --format text --flavour ua1|ua2` for PDF/UA samples when `verapdf` is available
  - a custom PDF/X validator command when `PDFX_VALIDATOR_CMD` is set

## Usage

From the repository root:

```bash
make preflight
```

Optional PDF/X validator hook:

```bash
PDFX_VALIDATOR_CMD='my-pdfx-validator --mode "$MODE" "$FILE"' make preflight
```

The script runs the command through `bash -lc` with these environment variables set per file:

- `MODE`: the current conformance mode, for example `pdfx4`
- `FILE`: the generated sample PDF path
- `REPORT`: the report file path under `target/preflight/report/`

Optional custom output directory:

```bash
bash resources/preflight/run_preflight_matrix.sh /tmp/tc-lib-pdf-preflight
```

Reports are written under `target/preflight/report/` (or the custom output path).

## Notes

- veraPDF has no PDF/X profile: PDF/X samples get the structural check and the optional
  `PDFX_VALIDATOR_CMD` hook only.
- Compliance claims require profile-specific preflight policies and manual review with your own validation authority.
