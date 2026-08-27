# Makefile
#
# @since       2015-02-21
# @category    Library
# @package     Pdf
# @author      Nicola Asuni <info@tecnick.com>
# @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
# @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
# @link        https://github.com/tecnickcom/tc-lib-pdf
#
# This file is part of tc-lib-pdf software library.
# ----------------------------------------------------------------------------------------------------------------------

SHELL=/bin/bash
.SHELLFLAGS=-e -o pipefail -c

# Project owner
OWNER=tecnickcom

# Project vendor
VENDOR=${OWNER}

# Project name
PROJECT=tc-lib-pdf

# Project version
VERSION=$(shell cat VERSION)

# Project release number (packaging build number)
RELEASE=$(shell cat RELEASE)

# Debian revision cannot be zero; map 0 to 1 for Debian packaging only.
DEBRELEASE=$(if $(filter 0,$(RELEASE)),1,$(RELEASE))

# RPM release is conventionally >= 1; map 0 to 1 for RPM packaging only.
RPMRELEASE=$(if $(filter 0,$(RELEASE)),1,$(RELEASE))

# Name of RPM or DEB package
PKGNAME=php-${OWNER}-${PROJECT}

# Data dir
DATADIR=usr/share

# PHP home folder
PHPHOME=${DATADIR}/php/Com/Tecnick

# Default installation path for code
LIBPATH=${PHPHOME}/Pdf/

# Path for configuration files (etc/$(PKGNAME)/)
CONFIGPATH=etc/$(PKGNAME)/

# Default installation path for documentation
DOCPATH=${DATADIR}/doc/$(PKGNAME)/

# Installation path for the code
PATHINSTBIN=$(DESTDIR)/$(LIBPATH)

# Installation path for the configuration files
PATHINSTCFG=$(DESTDIR)/$(CONFIGPATH)

# Installation path for documentation
PATHINSTDOC=$(DESTDIR)/$(DOCPATH)

# Current directory
CURRENTDIR=$(CURDIR)/

# Target directory
TARGETDIR=target

# RPM Packaging path (where RPMs will be stored)
PATHRPMPKG=$(TARGETDIR)/RPM

# RPM local database path (avoid host rpmdb permission issues)
RPMDBPATH=$(PATHRPMPKG)/.rpmdb

# DEB Packaging path (where DEBs will be stored)
PATHDEBPKG=$(TARGETDIR)/DEB

# BZ2 Packaging path (where BZ2s will be stored)
PATHBZ2PKG=$(TARGETDIR)/BZ2

# sed argument for in-place substitutions
SEDINPLACE=-i
ifeq ($(shell uname -s),Darwin)
	SEDINPLACE=-i ''
endif

# Default port number for the example server
PORT?=8971

# PHP binary
PHP=$(shell which php)

# Composer executable (APC disabled to work around a bug)
COMPOSER=$(PHP) -d "apc.enable_cli=0" $(shell which composer)

# phpDocumentor executable file
PHPDOC=$(shell which phpDocumentor)

# veraPDF binary path (can be overridden, e.g. make verapdf-ua VERAPDF_BIN=/opt/verapdf/bin/verapdf)
VERAPDF_BIN?=$(shell command -v verapdf 2>/dev/null)

# --- MAKE TARGETS ---

# Display general help about this command
.PHONY: help
help:
	@echo ""
	@echo "$(PROJECT) Makefile."
	@echo "The following commands are available:"
	@echo ""
	@awk '/^## /{desc=substr($$0,4)} /^\.PHONY:/{if(NF>1) {target=$$2; if(desc) printf "  make %-15s: %s\n",target,desc; desc=""}}' Makefile
	@echo ""
	@echo "To test and build everything from scratch, use the shortcut:"
	@echo "    make x"
	@echo ""

# alias for help target
.PHONY: all
all: help

# Test and build everything from scratch
.PHONY: x
x: buildall

## Test and build everything from scratch
.PHONY: buildall
buildall:
	$(MAKE) deps qa report bz2 rpm deb

## Package the library in a compressed bz2 archive
.PHONY: bz2
bz2:
	rm -rf "$(PATHBZ2PKG)"
	$(MAKE) install DESTDIR="$(PATHBZ2PKG)"
	tar -jcvf "$(PATHBZ2PKG)/$(PKGNAME)-$(VERSION)-$(RELEASE).tbz2" -C "$(PATHBZ2PKG)" "$(DATADIR)"

## Delete the vendor and target directories
.PHONY: clean
clean:
	rm -rf ./vendor "$(TARGETDIR)"

## Build a DEB package for Debian-like Linux distributions
.PHONY: deb
deb:
	rm -rf "$(PATHDEBPKG)"
	$(MAKE) install DESTDIR="$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)"
	rm -f "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/$(DOCPATH)LICENSE"
	tar -zcvf "$(PATHDEBPKG)/$(PKGNAME)_$(VERSION).orig.tar.gz" -C "$(PATHDEBPKG)/" "$(PKGNAME)-$(VERSION)"
	cp -rf ./resources/debian "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian"
	find "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/" -type f -name '*.bak' -delete
	chmod 755 "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/rules"
	find "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/" -type f -exec sed $(SEDINPLACE) "s/~#DATE#~/`date -R`/" {} \;
	find "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/" -type f -exec sed $(SEDINPLACE) "s/~#VENDOR#~/$(VENDOR)/" {} \;
	find "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/" -type f -exec sed $(SEDINPLACE) "s/~#PROJECT#~/$(PROJECT)/" {} \;
	find "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/" -type f -exec sed $(SEDINPLACE) "s/~#PKGNAME#~/$(PKGNAME)/" {} \;
	find "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/" -type f -exec sed $(SEDINPLACE) "s/~#VERSION#~/$(VERSION)/" {} \;
	find "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/" -type f -exec sed $(SEDINPLACE) "s/~#RELEASE#~/$(DEBRELEASE)/" {} \;
	echo "$(LIBPATH)" > "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs"
	echo "$(LIBPATH)* $(LIBPATH)" > "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install"
	echo "$(DOCPATH)" >> "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs"
	echo "$(DOCPATH)* $(DOCPATH)" >> "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install"
ifneq ($(strip $(CONFIGPATH)),)
	echo "$(CONFIGPATH)" >> "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs"
	echo "$(CONFIGPATH)* $(CONFIGPATH)" >> "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install"
endif
	cd "$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)" && debuild -us -uc

## Clean all artifacts and download all dependencies
.PHONY: deps
deps: ensuretarget
	rm -rf ./vendor
	($(COMPOSER) install -vvv --no-interaction)

## Generate source code documentation
.PHONY: doc
doc: ensuretarget
	rm -rf "$(TARGETDIR)/doc"
	$(PHPDOC) -d ./src -t "$(TARGETDIR)/doc/"

## Create missing target directories for test and build artifacts
.PHONY: ensuretarget
ensuretarget:
	mkdir -p "$(TARGETDIR)/logs"
	mkdir -p "$(TARGETDIR)/report"
	mkdir -p "$(TARGETDIR)/doc"

## Build default tc-font-mirror fonts via tc-lib-pdf-font
.PHONY: fonts
fonts:
	$(COMPOSER) run-script fonts

## Install this application
.PHONY: install
install: uninstall
	mkdir -p "$(PATHINSTBIN)"
	cp -rf ./src/* "$(PATHINSTBIN)"
	cp -f ./resources/autoload.php "$(PATHINSTBIN)"
	find "$(PATHINSTBIN)" -type d -exec chmod 755 {} \;
	find "$(PATHINSTBIN)" -type f -exec chmod 644 {} \;
	mkdir -p "$(PATHINSTDOC)"
	cp -f ./LICENSE "$(PATHINSTDOC)"
	cp -f ./README.md "$(PATHINSTDOC)"
	cp -f ./VERSION "$(PATHINSTDOC)"
	cp -f ./RELEASE "$(PATHINSTDOC)"
	find "$(PATHINSTDOC)" -type d -exec chmod 755 {} \;
	find "$(PATHINSTDOC)" -type f -exec chmod 644 {} \;
ifneq ($(strip $(CONFIGPATH)),)
	mkdir -p "$(PATHINSTCFG)"
	touch -c "$(PATHINSTCFG)"*
	cp -r "./resources/${CONFIGPATH}"* "$(PATHINSTCFG)"
	find "$(PATHINSTCFG)" -type d -exec chmod 755 {} \;
	find "$(PATHINSTCFG)" -type f -exec chmod 644 {} \;
endif

## Format the source code
.PHONY: format
format:
	$(COMPOSER) run-script cs-fix

## Check that the source code is formatted
.PHONY: formatcheck
formatcheck:
	$(COMPOSER) run-script fmt-check

## Analyze and Lint the source code
.PHONY: lint
lint:
	$(COMPOSER) run-script cs-check
	$(COMPOSER) run-script analyse

## Validate composer.json and check the dependencies for known advisories
# Both are enforced by CI on every matrix job, so "qa" runs them too: otherwise a
# packaging mistake or a new advisory is only reported after the PR is opened.
.PHONY: check-deps
check-deps:
	$(COMPOSER) run-script check-deps

## Check dependencies, formatting, lint, analyse and run all tests
.PHONY: qa
qa: check-deps version ensuretarget formatcheck lint test

## Run all checks and produce the coverage report
.PHONY: qa-coverage
qa-coverage: check-deps version ensuretarget formatcheck lint test-coverage

## Generate various reports
# Not part of "qa": pdepend is a metrics tool, not a correctness gate, and its
# 2.x line predates the newest PHP releases in the CI matrix.
.PHONY: report
report: ensuretarget
	$(COMPOSER) run-script report

## Generate mode samples and run external preflight validators (if installed)
.PHONY: preflight
preflight: ensuretarget
	bash ./resources/preflight/run_preflight_matrix.sh

## Generate renderability score and trend reports for the real-page corpus
.PHONY: renderability
renderability: ensuretarget
	$(PHP) resources/css/renderability_score.php \
		--corpus=test/fixtures/html/real_pages/corpus.json \
		--json=$(TARGETDIR)/report/renderability-score.json \
		--markdown=$(TARGETDIR)/report/renderability-score.md
	$(PHP) resources/css/renderability_trend.php \
		--score=$(TARGETDIR)/report/renderability-score.json \
		--history=$(TARGETDIR)/report/renderability-trend.json \
		--markdown=$(TARGETDIR)/report/renderability-trend.md

## Generate PDF/UA examples and validate with veraPDF
.PHONY: verapdf-ua
verapdf-ua: ensuretarget
	@if [[ -z "$(VERAPDF_BIN)" ]]; then \
		echo "veraPDF CLI not found. Set VERAPDF_BIN=/path/to/verapdf or add verapdf to PATH."; \
		exit 2; \
	fi
	$(PHP) examples/E015_pdfua.php > "$(TARGETDIR)/report/E015_pdfua.pdf"
	$(PHP) examples/E016_pdfua1.php > "$(TARGETDIR)/report/E016_pdfua1.pdf"
	$(PHP) examples/E017_pdfua2.php > "$(TARGETDIR)/report/E017_pdfua2.pdf"
	@fail=0; \
	for spec in "E015_pdfua.pdf:ua1" "E016_pdfua1.pdf:ua1" "E017_pdfua2.pdf:ua2"; do \
		file="$${spec%%:*}"; \
		flavour="$${spec##*:}"; \
		input="$(TARGETDIR)/report/$${file}"; \
		report="$(TARGETDIR)/report/$${file%.pdf}.verapdf.txt"; \
		if "$(VERAPDF_BIN)" --format text --verbose --flavour "$${flavour}" "$${input}" > "$${report}" 2>&1; then \
			echo "[ok] $${file} ($${flavour})"; \
		else \
			echo "[fail] $${file} ($${flavour}) - see $${report}"; \
			fail=1; \
		fi; \
	done; \
	echo "veraPDF reports written to $(TARGETDIR)/report/*.verapdf.txt"; \
	exit $$fail

## Build the RPM package for RedHat-like Linux distributions
.PHONY: rpm
rpm:
	@test $(words $(CURDIR)) -eq 1 || { echo "ERROR: rpmbuild does not support spaces in the project path: $(CURDIR)"; exit 1; }
	rm -rf "$(PATHRPMPKG)"
	mkdir -p "$(RPMDBPATH)" "$(PATHRPMPKG)/tmp"
	rpmbuild \
	--define "_topdir $(CURRENTDIR)$(PATHRPMPKG)" \
	--define "_dbpath $(CURRENTDIR)$(RPMDBPATH)" \
	--define "_tmppath $(CURRENTDIR)$(PATHRPMPKG)/tmp" \
	--define "_vendor $(VENDOR)" \
	--define "_owner $(OWNER)" \
	--define "_project $(PROJECT)" \
	--define "_package $(PKGNAME)" \
	--define "_version $(VERSION)" \
	--define "_release $(RPMRELEASE)" \
	--define "_current_directory $(CURRENTDIR)" \
	--define "_builddate $(shell LC_ALL=C date '+%a %b %d %Y')" \
	--define "_libpath /$(LIBPATH)" \
	--define "_docpath /$(DOCPATH)" \
	--define "_configpath /$(CONFIGPATH)" \
	-bb resources/rpm/rpm.spec

## Start the development server
.PHONY: server
server:
	$(PHP) -t examples -S localhost:$(PORT)

## Tag this GIT version
.PHONY: tag
tag:
	git checkout main && \
	git tag -a ${VERSION} -m "Release ${VERSION}" && \
	git push origin --tags && \
	git pull

## Run unit tests
# PHPUnit resolves its own configuration: phpunit.xml when present, else phpunit.xml.dist.
.PHONY: test
test: ensuretarget
	$(COMPOSER) run-script test

## Run unit tests and write the coverage report (requires Xdebug or pcov)
.PHONY: test-coverage
test-coverage: ensuretarget
	$(COMPOSER) run-script test:coverage

## Remove all installed files
.PHONY: uninstall
uninstall:
	rm -rf "$(PATHINSTBIN)"
	rm -rf "$(PATHINSTDOC)"

## Set the version from the VERSION file
.PHONY: version
version:
	sed $(SEDINPLACE) -e "s/protected string \$$version = '.*';/protected string \$$version = '${VERSION}';/g" src/Base.php

## Increase the version patch number
.PHONY: versionup
versionup:
	echo ${VERSION} | gawk -F. '{printf("%d.%d.%d\n",$$1,$$2,(($$3+1)));}' > VERSION
	$(MAKE) version
