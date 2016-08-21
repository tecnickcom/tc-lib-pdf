# Makefile
#
# @since       2015-02-21
# @category    Library
# @package     Pdf
# @author      Nicola Asuni <info@tecnick.com>
# @copyright   2015-2015 Nicola Asuni - Tecnick.com LTD
# @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
# @link        https://github.com/tecnickcom/tc-lib-pdf
#
# This file is part of tc-lib-pdf software library.
# ----------------------------------------------------------------------------------------------------------------------

# List special make targets that are not associated with files
.PHONY: help all test docs phpcs phpcs_test phpcbf phpcbf_test phpmd phpmd_test phpcpd phploc phpdep phpcmpinfo report qa qa_test qa_all clean build build_dev update server install uninstall rpm deb bz2 bintray

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
CURRENTDIR=$(shell pwd)

# RPM Packaging path (where RPMs will be stored)
PATHRPMPKG=$(CURRENTDIR)/target/RPM

# DEB Packaging path (where DEBs will be stored)
PATHDEBPKG=$(CURRENTDIR)/target/DEB

# BZ2 Packaging path (where BZ2s will be stored)
PATHBZ2PKG=$(CURRENTDIR)/target/BZ2

# Default port number for the example server
PORT?=8000

# Composer executable (disable APC to as a work-around of a bug)
COMPOSER=$(shell which php) -d "apc.enable_cli=0" $(shell which composer)

# --- MAKE TARGETS ---

# Display general help about this command
help:
	@echo ""
	@echo "${PROJECT} Make."
	@echo "The following commands are available:"
	@echo ""
	@echo "    make qa          : Run the targets: test, phpcs, phpmd and phpcpd"
	@echo "    make qa_test     : Run the targets: phpcs_test and phpmd_test"
	@echo "    make qa_all      : Run the targets: qa and qa_all"
	@echo ""
	@echo "    make test        : Run the PHPUnit tests"
	@echo ""
	@echo "    make phpcs       : Run PHPCS on the source code and show any style violations"
	@echo "    make phpcs_test  : Run PHPCS on the test code and show any style violations"
	@echo ""
	@echo "    make phpcbf      : Run PHPCBF on the source code to fix style violations"
	@echo "    make phpcbf_test : Run PHPCBF on the test code to fix style violations"
	@echo ""
	@echo "    make phpmd       : Run PHP Mess Detector on the source code"
	@echo "    make phpmd_test  : Run PHP Mess Detector on the test code"
	@echo ""
	@echo "    make phpcpd      : Run PHP Copy/Paste Detector"
	@echo "    make phploc      : Run PHPLOC to analyze the structure of the project"
	@echo "    make phpdep      : Run JDepend static analysis and generate graphs"
	@echo "    make phpcmpinfo  : Find out the minimum version and extensions required"
	@echo "    make report      : Generates various static-analisys reports"
	@echo ""
	@echo "    make docs        : Generate source code documentation"
	@echo ""
	@echo "    make clean       : Delete the vendor and target directory"
	@echo "    make build       : Clean and download the composer dependencies"
	@echo "    make build_dev   : Clean and download the composer dependencies including dev ones"
	@echo "    make update      : Update composer dependencies"
	@echo ""
	@echo "    make server      : Run the example server at http://localhost:"$(PORT)
	@echo ""
	@echo "    make install     : Install this library"
	@echo "    make uninstall   : Remove all installed files"
	@echo ""
	@echo "    make rpm         : Build an RPM package"
	@echo "    make deb         : Build a DEB package"
	@echo "    make bz2         : Build a tar bz2 (tbz2) compressed archive"
	@echo ""

# alias for help target
all: help

# run the PHPUnit tests
test:
	./vendor/bin/phpunit test

# generate docs using phpDocumentor
docs:
	@rm -rf target/phpdocs && ./vendor/apigen/apigen/bin/apigen generate --source="src/" --destination="target/phpdocs/" --exclude="vendor" --access-levels="public,protected,private" --charset="UTF-8" --title="${PROJECT}"

# run PHPCS on the source code and show any style violations
phpcs:
	@./vendor/bin/phpcs --ignore="./vendor/" --standard=psr2 src

# run PHPCS on the test code and show any style violations
phpcs_test:
	@./vendor/bin/phpcs --standard=psr2 test

# run PHPCBF on the source code and show any style violations
phpcbf:
	@./vendor/bin/phpcbf --ignore="./vendor/" --standard=psr2 src

# run PHPCBF on the test code and show any style violations
phpcbf_test:
	@./vendor/bin/phpcbf --standard=psr2 test

# Run PHP Mess Detector on the source code
phpmd:
	@./vendor/bin/phpmd src text codesize,unusedcode,naming,design --exclude vendor

# run PHP Mess Detector on the test code
phpmd_test:
	@./vendor/bin/phpmd test text unusedcode,naming,design

# run PHP Copy/Paste Detector
phpcpd:
	@mkdir -p ./target/report/
	@./vendor/bin/phpcpd src --exclude vendor > ./target/report/phpcpd.txt

# run PHPLOC to analyze the structure of the project
phploc:
	@mkdir -p ./target/report/
	@./vendor/bin/phploc src --exclude vendor > ./target/report/phploc.txt

# PHP static analysis
phpdep:
	@mkdir -p ./target/report/
	@./vendor/bin/pdepend --jdepend-xml=./target/report/dependencies.xml --summary-xml=./target/report/metrics.xml --jdepend-chart=./target/report/dependecies.svg --overview-pyramid=./target/report/overview-pyramid.svg --ignore=vendor ./src

# parse any data source to find out the minimum version and extensions required for it to run
phpcmpinfo:
	@./vendor/bartlett/php-compatinfo/bin/phpcompatinfo --no-ansi analyser:run src/ > ./target/report/phpcompatinfo.txt

# generate various reports
report: phploc phpdep phpcmpinfo

# alias to run targets: test, phpcs, phpmd and phpcpd
qa: test phpcs phpmd phpcpd

# alias to run targets: phpcs_test and phpmd_test
qa_test: phpcs_test phpmd_test

# alias to run targets: qa and qa_test
qa_all: qa qa_test

# delete the vendor and target directory
clean:
	rm -rf ./vendor/

# clean and download the composer dependencies
build:
	rm -rf ./vendor/ && ($(COMPOSER) install --no-dev --no-interaction)

# clean and download the composer dependencies including dev ones
build_dev:
	rm -rf ./vendor/ && ($(COMPOSER) install --no-interaction)

# update composer dependencies
update:
	($(COMPOSER) update --no-interaction)

# Run the development server
server:
	php -t example -S localhost:$(PORT)

# Install this application
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
	cp -ru ./resources/${CONFIGPATH}* $(PATHINSTCFG)
	find $(PATHINSTCFG) -type d -exec chmod 755 {} \;
	find $(PATHINSTCFG) -type f -exec chmod 644 {} \;
endif

# Remove all installed files
uninstall:
	rm -rf $(PATHINSTBIN)
	rm -rf $(PATHINSTDOC)

# --- PACKAGING ---

# Build the RPM package for RedHat-like Linux distributions
rpm:
	rm -rf $(PATHRPMPKG)
	rpmbuild --define "_topdir $(PATHRPMPKG)" --define "_vendor $(VENDOR)" --define "_owner $(OWNER)" --define "_project $(PROJECT)" --define "_package $(PKGNAME)" --define "_version $(VERSION)" --define "_release $(RELEASE)" --define "_current_directory $(CURRENTDIR)" --define "_libpath /$(LIBPATH)" --define "_docpath /$(DOCPATH)" --define "_configpath /$(CONFIGPATH)" -bb resources/rpm/rpm.spec

# Build the DEB package for Debian-like Linux distributions
deb: build
	rm -rf $(PATHDEBPKG)
	make install DESTDIR=$(PATHDEBPKG)/$(PKGNAME)-$(VERSION)
	rm -f $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/$(DOCPATH)LICENSE
	tar -zcvf $(PATHDEBPKG)/$(PKGNAME)_$(VERSION).orig.tar.gz -C $(PATHDEBPKG)/ $(PKGNAME)-$(VERSION)
	cp -rf ./resources/debian $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#DATE#~/`date -R`/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#VENDOR#~/$(VENDOR)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#PROJECT#~/$(PROJECT)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#PKGNAME#~/$(PKGNAME)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#VERSION#~/$(VERSION)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#RELEASE#~/$(RELEASE)/" {} \;
	echo $(LIBPATH) > $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs
	echo "$(LIBPATH)* $(LIBPATH)" > $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install
	echo $(DOCPATH) >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs
	echo "$(DOCPATH)* $(DOCPATH)" >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install
ifneq ($(strip $(CONFIGPATH)),)
	echo $(CONFIGPATH) >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs
	echo "$(CONFIGPATH)* $(CONFIGPATH)" >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install
endif
	echo "new-package-should-close-itp-bug" > $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).lintian-overrides
	cd $(PATHDEBPKG)/$(PKGNAME)-$(VERSION) && debuild -us -uc

# build a compressed bz2 archive
bz2: build
	rm -rf $(PATHBZ2PKG)
	make install DESTDIR=$(PATHBZ2PKG)
	tar -jcvf $(PATHBZ2PKG)/$(PKGNAME)-$(VERSION)-$(RELEASE).tbz2 -C $(PATHBZ2PKG) $(DATADIR)

# upload linux packages to bintray
bintray:
	@curl -T target/RPM/RPMS/noarch/php-tecnickcom-${PROJECT}-${VERSION}-${RELEASE}.noarch.rpm -u${APIUSER}:${APIKEY} -H "X-Bintray-Package:${PROJECT}" -H "X-Bintray-Version:${VERSION}" -H "X-Bintray-Publish:1" -H "X-Bintray-Override:1" https://api.bintray.com/content/tecnickcom/rpm/php-tecnickcom-${PROJECT}-${VERSION}-${RELEASE}.noarch.rpm
	@curl -T target/DEB/php-tecnickcom-${PROJECT}_${VERSION}-${RELEASE}_all.deb -u${APIUSER}:${APIKEY} -H "X-Bintray-Package:${PROJECT}" -H "X-Bintray-Version:${VERSION}" -H "X-Bintray-Debian-Distribution:all" -H "X-Bintray-Debian-Component:main" -H "X-Bintray-Debian-Architecture:all" -H "X-Bintray-Publish:1" -H "X-Bintray-Override:1" https://api.bintray.com/content/tecnickcom/deb/php-tecnickcom-${PROJECT}_${VERSION}-${RELEASE}_all.deb
