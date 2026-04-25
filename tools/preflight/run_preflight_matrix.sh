#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
OUT_DIR="${1:-${ROOT_DIR}/target/preflight}"
REPORT_DIR="${OUT_DIR}/report"
GENERATOR="${ROOT_DIR}/tools/preflight/generate_mode_samples.php"

PDFX_MODES=(pdfx pdfx1a pdfx3 pdfx4 pdfx5)
PDFUA_MODES=(pdfua pdfua1 pdfua2)
ALL_MODES=("${PDFX_MODES[@]}" "${PDFUA_MODES[@]}")

VERAPDF_BIN="${VERAPDF_BIN:-$(command -v verapdf || true)}"
QPDF_BIN="${QPDF_BIN:-$(command -v qpdf || true)}"
PDFX_VALIDATOR_CMD="${PDFX_VALIDATOR_CMD:-}"

pdfua_flavour() {
  case "$1" in
    pdfua|pdfua1)
      printf 'ua1'
      ;;
    pdfua2)
      printf 'ua2'
      ;;
    *)
      return 1
      ;;
  esac
}

mkdir -p "${OUT_DIR}" "${REPORT_DIR}"

echo "[preflight] generating conformance samples in ${OUT_DIR}"
php "${GENERATOR}" "${OUT_DIR}" >/dev/null

failures=0
checks=0

if [[ -n "${QPDF_BIN}" ]]; then
  echo "[preflight] running qpdf structural checks"
  for mode in "${ALL_MODES[@]}"; do
    file="${OUT_DIR}/mode-${mode}.pdf"
    report="${REPORT_DIR}/qpdf-${mode}.txt"
    if "${QPDF_BIN}" --check "${file}" >"${report}" 2>&1; then
      echo "[ok] qpdf ${mode}"
      checks=$((checks + 1))
    else
      echo "[fail] qpdf ${mode} (see ${report})"
      failures=$((failures + 1))
    fi
  done
else
  echo "[skip] qpdf not found; install qpdf to enable structural validation"
fi

if [[ -n "${PDFX_VALIDATOR_CMD}" ]]; then
  echo "[preflight] running configured PDF/X validator"
  for mode in "${PDFX_MODES[@]}"; do
    file="${OUT_DIR}/mode-${mode}.pdf"
    report="${REPORT_DIR}/pdfx-validator-${mode}.txt"
    if MODE="${mode}" FILE="${file}" REPORT="${report}" bash -lc "${PDFX_VALIDATOR_CMD}" >"${report}" 2>&1; then
      echo "[ok] pdfx-validator ${mode}"
      checks=$((checks + 1))
    else
      echo "[fail] pdfx-validator ${mode} (see ${report})"
      failures=$((failures + 1))
    fi
  done
else
  echo "[skip] no PDF/X profile validator configured; set PDFX_VALIDATOR_CMD to enable PDF/X authority checks"
fi

if [[ -n "${VERAPDF_BIN}" ]]; then
  echo "[preflight] running veraPDF PDF/UA profile checks"
  for mode in "${PDFUA_MODES[@]}"; do
    file="${OUT_DIR}/mode-${mode}.pdf"
    report="${REPORT_DIR}/verapdf-${mode}.txt"
    flavour="$(pdfua_flavour "${mode}")"
    if "${VERAPDF_BIN}" --format text --flavour "${flavour}" "${file}" >"${report}" 2>&1; then
      echo "[ok] veraPDF ${mode} (${flavour})"
      checks=$((checks + 1))
    else
      echo "[fail] veraPDF ${mode} (${flavour}) (see ${report})"
      failures=$((failures + 1))
    fi
  done
else
  echo "[skip] veraPDF not found; install verapdf to enable explicit PDF/UA profile validation"
fi

echo "[preflight] completed checks=${checks} failures=${failures}"
if [[ ${failures} -gt 0 ]]; then
  exit 1
fi
