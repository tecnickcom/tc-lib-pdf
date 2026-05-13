# Contributing to tc-lib-pdf

Thank you for your interest in contributing to **tc-lib-pdf** — the modern evolution of [TCPDF](https://tcpdf.org). Contributions of all kinds are welcome: bug reports, bug fixes, documentation improvements, new features, and refactors.

Please take a moment to read this guide before opening an issue or pull request.

---

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Getting Started](#getting-started)
- [Reporting a Bug](#reporting-a-bug)
- [Submitting a Bug Fix](#submitting-a-bug-fix)
- [Proposing a New Feature](#proposing-a-new-feature)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Pull Request Guidelines](#pull-request-guidelines)
- [Commit Message Guidelines](#commit-message-guidelines)

---

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating you agree to abide by its terms. Please report unacceptable behaviour to [info@tecnick.com](mailto:info@tecnick.com).

---

## Security Vulnerabilities

**Do not open a public GitHub issue for security vulnerabilities.**  
Please follow the [Security Policy](SECURITY.md) and report them privately.

---

## Getting Started

### Requirements

- PHP **≥ 8.2**
- [Composer](https://getcomposer.org/) v2
- `make`, `git`
- Optional: `rpmbuild` (RPM packaging), `dpkg-buildpackage` (DEB packaging)

### Local setup

```bash
git clone https://github.com/tecnickcom/tc-lib-pdf.git
cd tc-lib-pdf
make buildall
```

To verify everything is working after a change:

```bash
make qa
```

This runs linting, static analysis, and the full unit-test suite with coverage.

---

## Reporting a Bug

Before opening an issue:

1. **Check the [Security Policy](SECURITY.md)** — if the bug is a security vulnerability, do not file a public issue.
2. **Search [existing issues](https://github.com/tecnickcom/tc-lib-pdf/issues)** to avoid duplicates.

If no existing issue matches, [open a new one](https://github.com/tecnickcom/tc-lib-pdf/issues/new) and include:

- A **clear title and description** of the problem.
- The **library version** (`composer show tecnickcom/tc-lib-pdf`) and PHP version.
- A **minimal, self-contained reproduction** — a short PHP script or a failing PHPUnit test case is ideal.
- **Expected vs. actual behaviour** — what you expected to happen and what actually happened.
- Any relevant **stack trace or error output**.

The more precise and reproducible the report, the faster it can be triaged and fixed.

---

## Submitting a Bug Fix

1. [Fork the repository](https://github.com/tecnickcom/tc-lib-pdf/fork) and create a branch from `main`:
   ```bash
   git checkout -b fix/short-description-of-bug
   ```
2. Make your changes, following the [Coding Standards](#coding-standards) below.
3. Add or update unit tests to cover the changes.
4. Run the full quality-assurance suite locally and ensure it passes:
   ```bash
   make qa
   ```
5. Commit your changes (see [Commit Message Guidelines](#commit-message-guidelines)).
6. Open a pull request against `main` and fill in the PR template:
   - Describe the problem and your solution.
   - Reference the related issue number (e.g. `Fixes #123`).

---

## Proposing a New Feature

Before writing any code:

1. **Open a Feature Request** on [GitHub Issues](https://github.com/tecnickcom/tc-lib-pdf/issues/new) describing the use case and proposed API.
2. Wait for feedback from the maintainer. This avoids investing time in a direction that may not be accepted.

Once the feature is agreed upon, follow the same branch → code → test → PR workflow as for bug fixes, using a branch named `feature/short-description`.

---

## Development Workflow

The `Makefile` exposes all common development tasks:

| Command | Description |
|---------|-------------|
| `make qa` | Run linting, static analysis, tests, and reports |
| `make test` | Run PHPUnit with code coverage |
| `make lint` | Check coding standards |
| `make format` | Auto-format the code |
| `make buildall` | Install dependencies, fix style, run QA, and build packages |
| `make clean` | Remove `vendor/` and `target/` directories |
| `make server` | Start the built-in PHP development server for the examples |

Run `make help` to see the full list of available targets.

---

## Coding Standards

- The codebase follows **PSR-12** for formatting.
- Run `make format` to auto-format the code.
- Run `make lint` to catch remaining issues.
- All source files live under `src/`, all tests under `test/`.
- Use strict types and explicit visibility on all class members.
- Avoid introducing new external dependencies without prior discussion.

---

## Testing

Tests are written with [PHPUnit](https://phpunit.de/) and live in `test/`.

```bash
# Run the full test suite with coverage
make test

# Run a specific test file
XDEBUG_MODE=coverage ./vendor/bin/phpunit test/HTMLTest.php
```

Requirements for contributions:

- Every bug fix must be accompanied by a regression test that fails before the fix and passes after.
- Every new feature must be accompanied by tests that cover both the happy path and edge cases.

Coverage reports are generated in `target/coverage/`.

---

## Pull Request Guidelines

- Target the `main` branch.
- Keep PRs focused — one fix or feature per PR.
- Ensure `make qa` passes locally before opening the PR.
- Do not bump the version number in your PR; that is handled by the maintainer at release time.
- Be responsive to review feedback; stale PRs may be closed after an extended period of inactivity.

---

## Commit Message Guidelines

Use concise, imperative-mood commit messages:

```
fix: correct path traversal in font loader
feat: add support for CSS grid layout
test: add regression test for #123
docs: update CONTRIBUTING workflow
refactor: extract text measurement into helper
```

Prefix tags: `fix`, `feat`, `test`, `docs`, `refactor`, `chore`, `ci`.  
Reference issues where relevant: `fix: correct X (closes #42)`.

---

## Questions?

If you have a question that is not covered here, feel free to open a [GitHub Discussion](https://github.com/tecnickcom/tc-lib-pdf/discussions) or contact the maintainer at [info@tecnick.com](mailto:info@tecnick.com).
