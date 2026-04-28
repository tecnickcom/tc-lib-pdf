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
.SHELLFLAGS=-o pipefail -c

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
CURRENTDIR=$(dir $(realpath $(firstword $(MAKEFILE_LIST))))

# Target directory
TARGETDIR=$(CURRENTDIR)target

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

# Composer executable (disable APC to as a work-around of a bug)
COMPOSER=$(PHP) -d "apc.enable_cli=0" $(shell which composer)

# phpDocumentor executable file
PHPDOC=$(shell which phpDocumentor)

# phpstan version
PHPSTANVER=2.1.40

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
buildall: deps
	# cd vendor/tecnickcom/tc-lib-pdf-font/ && make deps fonts
	$(MAKE) codefix qa bz2 rpm deb

## Package the library in a compressed bz2 archive
.PHONY: bz2
bz2:
	rm -rf $(PATHBZ2PKG)
	make install DESTDIR=$(PATHBZ2PKG)
	tar -jcvf $(PATHBZ2PKG)/$(PKGNAME)-$(VERSION)-$(RELEASE).tbz2 -C $(PATHBZ2PKG) $(DATADIR)

## Delete the vendor and target directories
.PHONY: clean
clean:
	rm -rf ./vendor $(TARGETDIR)

## Fix code style violations
.PHONY: codefix
codefix:
	./vendor/bin/phpcbf --ignore="\./vendor/" --standard=psr12 src test

## Build a DEB package for Debian-like Linux distributions
.PHONY: deb
deb:
	rm -rf $(PATHDEBPKG)
	$(MAKE) install DESTDIR=$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)
	rm -f $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/$(DOCPATH)LICENSE
	tar -zcvf $(PATHDEBPKG)/$(PKGNAME)_$(VERSION).orig.tar.gz -C $(PATHDEBPKG)/ $(PKGNAME)-$(VERSION)
	cp -rf ./resources/debian $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -name '*.bak' -delete
	chmod 755 $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/rules
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed $(SEDINPLACE) "s/~#DATE#~/`date -R`/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed $(SEDINPLACE) "s/~#VENDOR#~/$(VENDOR)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed $(SEDINPLACE) "s/~#PROJECT#~/$(PROJECT)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed $(SEDINPLACE) "s/~#PKGNAME#~/$(PKGNAME)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed $(SEDINPLACE) "s/~#VERSION#~/$(VERSION)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed $(SEDINPLACE) "s/~#RELEASE#~/$(DEBRELEASE)/" {} \;
	echo $(LIBPATH) > $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs
	echo "$(LIBPATH)* $(LIBPATH)" > $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install
	echo $(DOCPATH) >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs
	echo "$(DOCPATH)* $(DOCPATH)" >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install
ifneq ($(strip $(CONFIGPATH)),)
	echo $(CONFIGPATH) >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs
	echo "$(CONFIGPATH)* $(CONFIGPATH)" >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install
endif
	cd $(PATHDEBPKG)/$(PKGNAME)-$(VERSION) && debuild -us -uc

## Clean all artifacts and download all dependencies
.PHONY: deps
deps: ensuretarget
	rm -rf ./vendor/*
	($(COMPOSER) install -vvv --no-interaction)
	curl --silent --show-error --fail --location --output ./vendor/phpstan.phar https://github.com/phpstan/phpstan/releases/download/${PHPSTANVER}/phpstan.phar \
	&& chmod +x ./vendor/phpstan.phar

## Generate source code documentation
.PHONY: doc
doc: ensuretarget
	rm -rf $(TARGETDIR)/doc
	$(PHPDOC) -d ./src -t $(TARGETDIR)/doc/

## Create missing target directories for test and build artifacts
.PHONY: ensuretarget
ensuretarget:
	mkdir -p $(TARGETDIR)/test
	mkdir -p $(TARGETDIR)/report
	mkdir -p $(TARGETDIR)/doc

## Build default tc-font-mirror fonts via tc-lib-pdf-font
.PHONY: fonts
fonts:
	cd vendor/tecnickcom/tc-lib-pdf-font/ && make deps fonts

## Install this application
.PHONY: install
install: uninstall
	mkdir -p $(PATHINSTBIN)
	cp -rf ./src/* $(PATHINSTBIN)
	cp -f ./resources/autoload.php $(PATHINSTBIN)
	find $(PATHINSTBIN) -type d -exec chmod 755 {} \;
	find $(PATHINSTBIN) -type f -exec chmod 644 {} \;
	mkdir -p $(PATHINSTDOC)
	cp -f ./LICENSE $(PATHINSTDOC)
	cp -f ./README.md $(PATHINSTDOC)
	cp -f ./VERSION $(PATHINSTDOC)
	cp -f ./RELEASE $(PATHINSTDOC)
	chmod -R 644 $(PATHINSTDOC)*
ifneq ($(strip $(CONFIGPATH)),)
	mkdir -p $(PATHINSTCFG)
	touch -c $(PATHINSTCFG)*
	cp -r ./resources/${CONFIGPATH}* $(PATHINSTCFG)
	find $(PATHINSTCFG) -type d -exec chmod 755 {} \;
	find $(PATHINSTCFG) -type f -exec chmod 644 {} \;
endif

## Test source code for coding standard violations
.PHONY: lint
lint:
	#./vendor/bin/phpcbf --config-set ignore_non_auto_fixable_on_exit 1
	./vendor/bin/phpcs --standard=phpcs.xml
	./vendor/bin/phpmd src text unusedcode,naming,design --exclude vendor
	./vendor/bin/phpmd test text unusedcode,naming,design --exclude */vendor/*
	php -r 'exit((int)version_compare(PHP_MAJOR_VERSION, "7", ">"));' || ./vendor/phpstan.phar analyse

## Run all tests and reports
.PHONY: qa
qa: version ensuretarget lint test report

## Generate various reports
.PHONY: report
report: ensuretarget
	./vendor/bin/pdepend --jdepend-xml=$(TARGETDIR)/report/dependencies.xml --summary-xml=$(TARGETDIR)/report/metrics.xml --jdepend-chart=$(TARGETDIR)/report/dependecies.svg --overview-pyramid=$(TARGETDIR)/report/overview-pyramid.svg --ignore=vendor ./src
	#./vendor/bartlett/php-compatinfo/bin/phpcompatinfo --no-ansi analyser:run src/ > $(TARGETDIR)/report/phpcompatinfo.txt

## Generate mode samples and run external preflight validators (if installed)
.PHONY: preflight
preflight: ensuretarget
	bash ./resources/preflight/run_preflight_matrix.sh

## Build the RPM package for RedHat-like Linux distributions
.PHONY: rpm
rpm:
	rm -rf $(PATHRPMPKG)
	mkdir -p $(RPMDBPATH) $(PATHRPMPKG)/tmp
	rpmbuild \
	--define "_topdir $(PATHRPMPKG)" \
	--define "_dbpath $(RPMDBPATH)" \
	--define "_tmppath $(PATHRPMPKG)/tmp" \
	--define "_vendor $(VENDOR)" \
	--define "_owner $(OWNER)" \
	--define "_project $(PROJECT)" \
	--define "_package $(PKGNAME)" \
	--define "_version $(VERSION)" \
	--define "_release $(RPMRELEASE)" \
	--define "_current_directory $(CURRENTDIR)" \
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
.PHONY: test
test:
	cp phpunit.xml.dist phpunit.xml
	#./vendor/bin/phpunit --migrate-configuration || true
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --stderr test

## Remove all installed files
.PHONY: uninstall
uninstall:
	rm -rf $(PATHINSTBIN)
	rm -rf $(PATHINSTDOC)

## Set the version from the VERSION file
.PHONY: version
version:
	sed $(SEDINPLACE) -e "s/protected string \$$version = '.*';/protected string \$$version = '${VERSION}';/g" src/Base.php

## Increase the version patch number
.PHONY: versionup
versionup:
	echo ${VERSION} | gawk -F. '{printf("%d.%d.%d\n",$$1,$$2,(($$3+1)));}' > VERSION
	$(MAKE) version
