TopLevelView contrib files
==========================

## php-pecl-yaml

No official package is available for RedHat SCL packages. You can use the SPEC file provided here, but make sure to install requirements and enable the SCL repositories.

```
$ sudo yum install -y centos-release-scl
or
$ sudo subscription-manager repos --enable rhel-7-server-optional-rpms --enable rhel-server-rhscl-7-rpms

$ sudo yum install -y rpm-build rpmdevtools scl-utils scl-utils-build

$ rpmbuild -ba SPECS/php-pecl-yaml.spec --define 'scl rh-php71'
```
