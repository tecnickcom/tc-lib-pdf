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

Requires:  php(language) >= 8.1.0
Requires:  php-date
Requires:  php-pcre
Requires:  php-composer(%{c_vendor}/tc-lib-barcode) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-barcode) >= 2.4.37
Requires:  php-composer(%{c_vendor}/tc-lib-color) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-color) >= 2.5.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-image) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-image) >= 2.2.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-font) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-font) >= 2.7.1
Requires:  php-composer(%{c_vendor}/tc-lib-file) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-file) >= 2.5.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-encrypt) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-encrypt) >= 2.2.0
Requires:  php-composer(%{c_vendor}/tc-lib-unicode-data) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-unicode-data) >= 2.0.51
Requires:  php-composer(%{c_vendor}/tc-lib-unicode) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-unicode) >= 2.1.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-page) < 5.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-page) >= 4.3.21
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-graph) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-graph) >= 2.4.21

Provides:  php-composer(%{c_vendor}/%{gh_project}) = %{version}
Provides:  php-%{gh_project} = %{version}

%description
PHP library to generate PDF documents

%build
#(cd %{_current_directory} && make build)

%install
rm -rf %{buildroot}
(cd %{_current_directory} && make install DESTDIR=%{buildroot})

%files
%attr(-,root,root) %{_libpath}
%attr(-,root,root) %{_docpath}
%docdir %{_docpath}
%config(noreplace) %{_configpath}*

%changelog
* Tue Apr 21 2026 Nicola Asuni <info@tecnick.com> 8.7.0-1
- Update RPM packaging metadata and release mapping.
