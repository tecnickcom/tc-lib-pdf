# tc-lib-pdf Examples

This directory contains example files demonstrating the features of the tc-lib-pdf library.

## Prerequisites

Before running the examples, make sure to install dependencies:

```bash
make deps fonts
```

This will install Composer dependencies and generate the required fonts.

## How to Run Examples

Run any example from the project root directory:

```bash
php examples/<example_file>.php
```

Output files are generated in the `target/` directory.

---

## Example Files

### Basic Examples

#### `index.php`
**Description:** Comprehensive example demonstrating core PDF generation features including text, graphics, images, fonts, and page layout.

```bash
php examples/index.php
```

**Output:** `target/index.pdf`

---

#### `invoice.php`
**Description:** Creates a professional invoice document with tables, formatting, and business layout.

```bash
php examples/invoice.php
```

**Output:** `target/invoice.pdf`

---

#### `table.php`
**Description:** Demonstrates table creation and formatting in PDF documents.

```bash
php examples/table.php
```

**Output:** `target/table.pdf`

---

### PDF Manipulation Examples

#### `pdf_merge.php`
**Description:** Demonstrates merging multiple PDF documents into a single file.

```bash
php examples/pdf_merge.php
```

**Output:**
- `target/merge_source1.pdf` - First source document
- `target/merge_source2.pdf` - Second source document
- `target/merge_result.pdf` - Merged result

---

#### `pdf_split.php`
**Description:** Demonstrates splitting a PDF into individual pages or ranges.

```bash
php examples/pdf_split.php
```

**Output:**
- `target/split_source.pdf` - Source document
- `target/split_page_*.pdf` - Individual pages
- `target/split_range_*.pdf` - Page ranges

---

#### `pdf_stamp.php`
**Description:** Demonstrates adding stamps, watermarks, headers, footers, and page numbers to PDFs.

```bash
php examples/pdf_stamp.php
```

**Output:**
- `target/stamp_source.pdf` - Source document
- `target/stamp_approved.pdf` - With "APPROVED" stamp
- `target/stamp_watermark.pdf` - With "CONFIDENTIAL" watermark
- `target/stamp_draft.pdf` - With "DRAFT" watermark
- `target/stamp_page_numbers.pdf` - With page numbers
- `target/stamp_header_footer.pdf` - With header and footer
- `target/stamp_date.pdf` - With date stamp
- `target/stamp_multiple.pdf` - With multiple stamps

---

#### `pdf_metadata.php`
**Description:** Demonstrates reading and editing PDF metadata (title, author, subject, keywords).

```bash
php examples/pdf_metadata.php
```

**Output:**
- `target/metadata_source.pdf` - Source document
- `target/metadata_modified.pdf` - With updated metadata

---

#### `pdf_bookmarks.php`
**Description:** Demonstrates adding and managing PDF bookmarks/outlines.

```bash
php examples/pdf_bookmarks.php
```

**Output:**
- `target/bookmarks_source.pdf` - Source document
- `target/bookmarks_added.pdf` - With bookmarks added

---

#### `pdf_barcode.php`
**Description:** Demonstrates adding barcodes and QR codes to PDF documents.

```bash
php examples/pdf_barcode.php
```

**Output:**
- `target/barcode_source.pdf` - Source document
- `target/barcode_qr.pdf` - With QR code
- `target/barcode_code128.pdf` - With Code 128 barcode

---

#### `pdf_rotate.php`
**Description:** Demonstrates rotating PDF pages (90, 180, 270 degrees).

```bash
php examples/pdf_rotate.php
```

**Output:**
- `target/rotate_source.pdf` - Source document
- `target/rotate_90.pdf` - Rotated 90 degrees
- `target/rotate_180.pdf` - Rotated 180 degrees
- `target/rotate_270.pdf` - Rotated 270 degrees

---

#### `pdf_crop_resize.php`
**Description:** Demonstrates cropping and resizing PDF pages using MediaBox, CropBox, etc.

```bash
php examples/pdf_crop_resize.php
```

**Output:**
- `target/crop_source.pdf` - Source document
- `target/crop_*.pdf` - Various cropped/resized versions

---

#### `pdf_form_fill.php`
**Description:** Demonstrates filling PDF form fields programmatically.

```bash
php examples/pdf_form_fill.php
```

**Output:**
- `target/form_source.pdf` - Source form
- `target/form_filled.pdf` - Filled form

---

#### `pdf_encrypt.php`
**Description:** Demonstrates PDF encryption with passwords and permission controls.

```bash
php examples/pdf_encrypt.php
```

**Output:** See [Encrypted PDFs](#encrypted-pdfs) section below for files and passwords.

---

#### `pdf_optimize.php`
**Description:** Demonstrates PDF compression and optimization to reduce file size.

```bash
php examples/pdf_optimize.php
```

**Output:**
- `target/optimize_source.pdf` - Source document
- `target/optimize_result.pdf` - Optimized document

---

#### `pdf_linearize.php`
**Description:** Demonstrates PDF linearization for fast web viewing.

```bash
php examples/pdf_linearize.php
```

**Output:**
- `target/linearize_source.pdf` - Source document
- `target/linearize_result.pdf` - Linearized document

---

#### `pdf_to_image.php`
**Description:** Demonstrates converting PDF pages to images (requires Imagick or Ghostscript).

```bash
php examples/pdf_to_image.php
```

**Output:**
- `target/toimage_source.pdf` - Source document
- `target/toimage_page_*.png` - Converted images

**Requirements:** Imagick PHP extension or Ghostscript installed.

---

### HTML & Advanced Features

#### `html_to_pdf.php`
**Description:** Demonstrates converting HTML content to PDF with CSS styling support.

```bash
php examples/html_to_pdf.php
```

**Output:** `target/html_to_pdf.pdf`

---

#### `richtext_annotation.php`
**Description:** Demonstrates adding rich text annotations to PDF documents.

```bash
php examples/richtext_annotation.php
```

**Output:** `target/richtext_annotation.pdf`

---

### Forms & AcroForms

#### `advanced_acroforms.php`
**Description:** Demonstrates creating interactive PDF forms with various field types.

```bash
php examples/advanced_acroforms.php
```

**Output:** `target/advanced_acroforms.pdf`

---

### PDF/A Compliance

#### `pdfa.php`
**Description:** Creates PDF/A compliant documents for long-term archiving.

```bash
php examples/pdfa.php
```

**Output:** `target/pdfa.pdf`

---

#### `pdfa3_embedded.php`
**Description:** Creates PDF/A-3 documents with embedded files.

```bash
php examples/pdfa3_embedded.php
```

**Output:** `target/pdfa3_embedded.pdf`

---

### Digital Signatures

#### `multiple_signatures.php`
**Description:** Demonstrates adding multiple digital signatures to a PDF.

```bash
php examples/multiple_signatures.php
```

**Output:** `target/multiple_signatures.pdf`

**Requirements:** OpenSSL, certificate files.

---

#### `timestamp_signature.php`
**Description:** Demonstrates adding timestamped digital signatures.

```bash
php examples/timestamp_signature.php
```

**Output:** `target/timestamp_signature.pdf`

**Requirements:** OpenSSL, certificate files, timestamp server access.

---

#### `ltv_signature.php`
**Description:** Demonstrates Long-Term Validation (LTV) signatures.

```bash
php examples/ltv_signature.php
```

**Output:** `target/ltv_signature.pdf`

**Requirements:** OpenSSL, certificate files.

---

### Fonts

#### `variable_font.php`
**Description:** Demonstrates using variable fonts in PDF documents.

```bash
php examples/variable_font.php
```

**Output:** `target/variable_font.pdf`

---

## Encrypted PDFs

The `pdf_encrypt.php` example creates multiple encrypted PDF files. Here are the passwords:

| File | User Password | Owner Password | Description |
|------|---------------|----------------|-------------|
| `encrypt_source.pdf` | - | - | Unencrypted source |
| `encrypt_basic.pdf` | `secret123` | - | Basic encryption (AES-128) |
| `encrypt_dual_password.pdf` | `user_pass` | `owner_pass` | Dual password (AES-256) |
| `encrypt_no_copy.pdf` | `nocopy` | - | Copy disabled |
| `encrypt_print_only.pdf` | `printonly` | - | Print only allowed |
| `encrypt_locked.pdf` | `locked` | `masterkey` | Maximum restriction |
| `encrypt_rc4_40.pdf` | `mode_test` | - | RC4 40-bit encryption |
| `encrypt_rc4_128.pdf` | `mode_test` | - | RC4 128-bit encryption |
| `encrypt_aes_128.pdf` | `mode_test` | - | AES 128-bit encryption |
| `encrypt_aes_256.pdf` | `mode_test` | - | AES 256-bit encryption |
| `encrypt_form_fill.pdf` | `formfill` | - | Form filling allowed |
| `encrypt_convenience.pdf` | `easypass` | `ownerpass` | Using convenience method |

### Password Types

- **User Password:** Required to open and view the document
- **Owner Password:** Provides full access to the document, including changing permissions

---

## Running All Examples

To run all examples at once:

```bash
for f in examples/*.php; do echo "Running $f..."; php "$f"; done
```

Or run specific categories:

```bash
# PDF manipulation examples
php examples/pdf_merge.php
php examples/pdf_split.php
php examples/pdf_stamp.php
php examples/pdf_rotate.php
php examples/pdf_encrypt.php

# Form examples
php examples/advanced_acroforms.php
php examples/pdf_form_fill.php
```

---

## Output Directory

All generated PDF files are saved to the `target/` directory. This directory is created automatically when running examples.

To clean up generated files:

```bash
rm -rf target/*.pdf
```

---

## Troubleshooting

### Missing Fonts
If you see font-related errors, run:
```bash
make fonts
```

### Missing Dependencies
If you see autoload errors, run:
```bash
composer install
```

### PDF/A Validation
To validate PDF/A compliance, use tools like:
- veraPDF (https://verapdf.org/)
- Adobe Acrobat Pro

### Digital Signature Issues
For signature examples, ensure you have:
- OpenSSL installed
- Valid certificate files (`.crt`, `.key`)
- Proper file permissions
