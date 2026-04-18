# Security Policy

This document describes the security policy for **tc-lib-pdf** (the modern successor to [TCPDF](https://tcpdf.org)), a pure-PHP library for programmatically generating PDF documents.

---

## Supported Versions

Security fixes are applied only to the **latest stable release** on the `main` branch.

We strongly recommend always running the latest release.

---

## Reporting a Vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

If you discover a security vulnerability — or suspect one — follow responsible disclosure:

1. **Email** the maintainer directly at **[info@tecnick.com](mailto:info@tecnick.com)** with the subject line:  
   `[SECURITY] tc-lib-pdf – <brief description>`
2. Include as much detail as possible (see [What to include](#what-to-include) below).
3. You will receive an acknowledgement as soon as possible.
4. We will work on a fix or mitigation as promptly as the complexity of the issue allows.

If you do not receive a timely response, please follow up by replying to the same email thread.

---

## What to Include

A high-quality report helps us triage and fix issues faster. Please provide:

- **Description** — a clear summary of the vulnerability and its potential impact.
- **Affected component** — which class, method, or feature is involved (e.g., `HTML::render()`, font loading, image processing).
- **Steps to reproduce** — a minimal, self-contained PHP script or unit test that demonstrates the issue.
- **Expected vs. actual behaviour** — what you expected to happen and what actually happened.
- **Environment** — PHP version, OS, library version (output of `composer show tecnickcom/tc-lib-pdf`).
- **CVE / CWE reference** (optional) — if you have already identified a relevant classification.
- **Suggested fix** (optional) — a patch or proposed mitigation if you have one.

---

## Security Best Practices for Integrators

`tc-lib-pdf` processes rich content (HTML, CSS, SVG, fonts, images) which may originate from untrusted sources. Integrators are responsible for sanitising input **before** passing it to the library. We recommend:

- **Validate and sanitise all user-supplied HTML/CSS** before rendering. Use a dedicated HTML sanitiser (e.g., [HTML Purifier](http://htmlpurifier.org/)) when accepting content from end users.
- **Restrict remote resource loading.** If the library is configured to fetch remote images or fonts, apply an allowlist of trusted domains or disable remote fetching entirely in untrusted environments.
- **Limit file-system access.** Run the PDF-generation process with the minimum required filesystem permissions. Never pass raw user input as a file path.
- **Keep dependencies up to date.** Run `composer update` regularly and monitor advisories via [Packagist Security Advisories](https://packagist.org/packages/tecnickcom/tc-lib-pdf) or tools such as `composer audit`.
- **Pin versions in production.** Use `composer.lock` and review changes on every update.

---

## Contact

| Channel | Details |
|---------|---------|
| Security email | [info@tecnick.com](mailto:info@tecnick.com) |
| Project website | <https://tcpdf.org> |
| GitHub repository | <https://github.com/tecnickcom/tc-lib-pdf> |
| Packagist | <https://packagist.org/packages/tecnickcom/tc-lib-pdf> |
