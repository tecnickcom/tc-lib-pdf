# SPEC file

%global c_vendor    %{_vendor}
%global gh_owner    %{_owner}
%global gh_project  %{_project}

Name:      %{_package}
Version:   %{_version}
Release:   %{_release}%{?dist}
Summary:   PHP library to generate PDF documents

License:   LGPLv3+
URL:       https://github.com/%{gh_owner}/%{gh_project}

BuildArch: noarch

Requires:  php(language) >= 8.2.0
Requires:  php-ctype
Requires:  php-date
Requires:  php-filter
Requires:  php-hash
Requires:  php-json
Requires:  php-mbstring
Requires:  php-openssl
Requires:  php-pcre
Requires:  php-xml
Requires:  php-zlib
Requires:  php-composer(%{c_vendor}/tc-lib-barcode) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-barcode) >= 2.13.3
Requires:  php-composer(%{c_vendor}/tc-lib-color) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-color) >= 2.13.4
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-image) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-image) >= 3.12.4
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-font) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-font) >= 4.2.1
Requires:  php-composer(%{c_vendor}/tc-lib-file) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-file) >= 3.7.4
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-encrypt) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-encrypt) >= 2.10.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-sign) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-sign) >= 1.1.4
Requires:  php-composer(%{c_vendor}/tc-lib-unicode-data) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-unicode-data) >= 3.0.3
Requires:  php-composer(%{c_vendor}/tc-lib-unicode) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-unicode) >= 3.0.3
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-page) < 5.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-page) >= 4.14.4
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-graph) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-graph) >= 2.15.4
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-parser) < 4.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-parser) >= 3.14.3

Recommends: php-curl
Recommends: php-intl

Provides:  php-composer(%{c_vendor}/%{gh_project}) = %{version}
Provides:  php-%{gh_project} = %{version}

%description
PHP library to generate PDF documents

%build
#(cd %{_current_directory} && make build)

%install
rm -rf "%{buildroot}"
(cd "%{_current_directory}" && make install DESTDIR="%{buildroot}")

%files
%attr(-,root,root) %{_libpath}
%attr(-,root,root) %{_docpath}
%docdir %{_docpath}
%config(noreplace) %{_configpath}*

%changelog
* Tue Apr 21 2026 Nicola Asuni <info@tecnick.com> 8.7.0-1
- Update RPM packaging metadata and release mapping.
