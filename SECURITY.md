# Security Policy

Security policy for **tc-lib-pdf**, a pure-PHP library for generating PDF documents.

---

## Supported Versions

Security fixes are applied only to the **latest stable release** on the `main` branch.

---

## Reporting a Vulnerability

**Do not open a public GitHub issue for security vulnerabilities.**

1. **Email** the maintainer at **[info@tecnick.com](mailto:info@tecnick.com)** with the subject line:  
   `[SECURITY] tc-lib-pdf - <brief description>`
2. Include the details listed under [What to Include](#what-to-include).
3. An acknowledgement follows, then a fix or mitigation.

If you receive no response, reply to the same email thread.

---

## What to Include

- **Description**: summary of the vulnerability and its impact.
- **Affected component**: the class, method, or feature involved (for example `HTML::render()`, font loading, image processing).
- **Steps to reproduce**: a minimal, self-contained PHP script or unit test.
- **Expected vs. actual behaviour**.
- **Environment**: PHP version, OS, library version (output of `composer show tecnickcom/tc-lib-pdf`).
- **CVE / CWE reference** (optional).
- **Suggested fix** (optional): a patch or proposed mitigation.

---

## Security Best Practices for Integrators

`tc-lib-pdf` processes HTML, CSS, SVG, fonts, and images that may originate from untrusted sources. Sanitise input **before** passing it to the library:

- **Validate and sanitise all user-supplied HTML/CSS** before rendering. Use a dedicated HTML sanitiser (for example [HTML Purifier](http://htmlpurifier.org/)) for content from end users.
- **Restrict external asset loading.** Use `fileOptions['allowedHosts']` to allow only trusted remote domains, and set `fileOptions['allowedPaths']` when you need to narrow local file reads to specific asset directories. If you override `allowedPaths`, include every required local root because it replaces the defaults.
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
