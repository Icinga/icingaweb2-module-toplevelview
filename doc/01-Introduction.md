Introduction
============

Top Level View is a hierarchy based status view for Icinga Web 2.

You can define a hierarchical structure containing hosts, services and hostgroups.
This view presents you an overview of the overall status of the sub-hierarchies.

Example:

```yaml
name: My View
children:
- name: Section 1
  children:
  - name: Tile 1
    children:
    - host: localhost
    - host: localhost
      service: disk
    - host: anotherhost
      service: ssh
  - name: Tile 2
    - hostgroup: linux-servers
  - name: Tile 3
- name: Section 2
  - name: Tile 1
  - name: Tile 2
```

With a caching layer, this view can aggregate thousands of status objects and make
them easily available for overview and drill down.

**Hint:** Top Level View uses additional status logic for its views, see later chapters on details.

## Installation

Top Level View is a module for Icinga Web 2, and can be installed via git or a tarball.

Only other requirement is PHP YAML (which is needed for the configuration format), make
sure to reload your web server after installing the module.

    # on RHEL and compatible
    yum install php-pecl-yaml
    systemctl reload httpd.service

    # on Debian / Ubuntu
    apt-get install php-yaml
    systemctl reload apache2.service

You should download the latest released tarball from [GitHub](https://github.com/Icinga/icingaweb2-module-toplevelview/releases).

    tar xf icingaweb2-module-toplevelview-0.x.x.tar.gz
    mv icingaweb2-module-toplevelview-0.x.x/ /usr/share/icingaweb2/modules/toplevelview

Or if you prefer use git.

    git clone https://github.com/Icinga/icingaweb2-module-toplevelview.git \
      /usr/share/icingaweb2/modules/toplevelview
    git checkout v0.x.x

Enable the module in the web interface, or via CLI:

    icingacli module enable toplevelview
