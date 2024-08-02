Top Level View for Icinga Web 2
===============================

![Top Level View screenshot](doc/screenshots/tiles.png)

Top Level View is a hierarchy based status view for Icinga Web 2.

You can define a hierarchical structure containing hosts, services and hostgroups.
And the view presents you an overview of the overall status of the sub-hierarchies.

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
    hostgroup: linux-servers
  - servicegroup: http
```

With a caching layer, this view can aggregate thousands of status objects and make
them easily available for overview and drill down.

## Requirements

* Icinga Web 2 >= 2.5.0
* Icinga DB Web >= 1.0.0
* php-yaml

Also see [Introduction in docs](doc/01-Introduction.md).

## Documentation

All documentation can be found inside the [doc](doc/) directory.

## License

Icinga Web TopLevelView is licensed under the terms of the [GNU General Public License Version 2](COPYING).
