# SPEC file

%global c_vendor    %{_vendor}
%global gh_owner    %{_owner}
%global gh_project  %{_project}

Name:      %{_package}
Version:   %{_version}
Release:   %{_release}%{?dist}
Summary:   PHP library to generate PDF documents

Group:     Development/Libraries
License:   LGPLv3+
URL:       https://github.com/%{gh_owner}/%{gh_project}

BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-%(%{__id_u} -n)
BuildArch: noarch

Requires:  php(language) >= 5.4.0
Requires:  php-date
Requires:  php-pcre
Requires:  php-composer(%{c_vendor}/tc-lib-barcode) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-barcode >= 1.15.12
Requires:  php-composer(%{c_vendor}/tc-lib-color) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-color) >= 1.12.12
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-image) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-image >= 1.2.8
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-font) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-font >= 1.8.3
Requires:  php-composer(%{c_vendor}/tc-lib-file) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-file >= 1.6.8
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-encrypt) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-encrypt >= 1.5.7
Requires:  php-composer(%{c_vendor}/tc-lib-unicode-data) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-unicode-data >= 1.6.8
Requires:  php-composer(%{c_vendor}/tc-lib-unicode) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-unicode >= 1.3.8
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-page) < 3.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-page >= 2.4.7
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-graph) < 2.0.0
Requires:  php-composer(%{c_vendor}/tc-lib-pdf-graph >= 1.4.8

Provides:  php-composer(%{c_vendor}/%{gh_project}) = %{version}
Provides:  php-%{gh_project} = %{version}

%description
PHP library to generate PDF documents

%build
(cd %{_current_directory} && make build)

%install
rm -rf $RPM_BUILD_ROOT
(cd %{_current_directory} && make install DESTDIR=$RPM_BUILD_ROOT)

%clean
rm -rf $RPM_BUILD_ROOT
(cd %{_current_directory} && make clean)

%files
%attr(-,root,root) %{_libpath}
%attr(-,root,root) %{_docpath}
%docdir %{_docpath}
%config(noreplace) %{_configpath}*

%changelog
* Fri Jun 10 2016 Nicola Asuni <info@tecnick.com> 8.0.0-1
- Initial commit
