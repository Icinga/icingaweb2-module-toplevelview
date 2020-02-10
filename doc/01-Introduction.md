Introduction
============

Top Level View is a hierarchy based status view for Icinga Web 2.

You can define a hierarchical structure containing hosts, services and hostgroups.
And the view presents you an overview of the overall status of the sub-hierarchies.

With a caching layer, this view can aggregate thousands of status objects and make
them easily available for overview and drill down.

This view extends the status logic and behavior of Icinga Web 2 a bit,
please see later chapters on details.

## Requirements

* Icinga Web 2 >= 2.5.0
  * and its monitoring module

## Installation

The view is a simple module for Icinga Web 2, and can be installed via git or a tarball.

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

Enable the module in the web interface, or via CLI:

    icingacli module enable toplevelview

## Permissions

TODO
