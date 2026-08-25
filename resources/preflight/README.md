# External Preflight Matrix

Generates one sample PDF for each supported conformance mode and runs external validators against them.

## Included scripts

- `generate_mode_samples.php` generates sample PDFs for:
  - `pdfx`, `pdfx1a`, `pdfx3`, `pdfx4`, `pdfx5`
  - `pdfua`, `pdfua1`, `pdfua2`
- `run_preflight_matrix.sh` runs:
  - `qpdf --check` when `qpdf` is available
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

- veraPDF is invoked as a PDF/UA validator only, not as a PDF/X validator.
- Compliance claims require profile-specific preflight policies and manual review with your own validation authority.
