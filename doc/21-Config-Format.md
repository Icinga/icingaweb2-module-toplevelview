Configuration Format
====================

The configuration format is based on YAML.

    YAML is a human friendly data serialization standard for all programming languages.

It is an easy way to define data in a hierarchy, and still being able to view and modify it as a human.

For details on the format see [yaml.org](http://yaml.org/).

The configuration is structured in a hierarchical object structure like:

    root object -> children (array) -> objects -> more children 

Every node is unique in the tree, but names can be repeated. An Icinga objects can be inserted multiple times.

## Example

Best to understand it is to start with an example.

```yaml
name: View Name
children:
- name: Section 1
  children:
  - name: Tile 1
    children:
    - host: localhost
    - host: localhost
      service: disk
    - host: localhost
      service: ssh
  - name: Tile 2
    - hostgroup: linux-servers
  - name: Tile 3
- name: Section 2
  - name: Tile 1
  - name: Tile 2
  - name: 'Tile 3: The return of long names' # some values should be quoted
```

## Layers

The first three layers have a special meaning for the view.

1. Root Node - defines the name of the view
2. Sections - defines the sections in the tile view
3. Tiles - builds tiles in the tile view

Everything below is only visible via the tree view, and every Icinga node should be below the 3rd level.

## Nodes

Every node is an object in YAML, while the object attribute `children` is an array of all children objects
for that node.

Indention does matter as far as it defines the levels and structure of the objects. (Please use no soft tabs!)

Every node can have multiple attributes, they are partially validated, and unknown keys just get ignored.

### Name nodes

Default node type is a simple named node that gets status from deeper nodes.

The root node itself has also only a name, but can contain options mentioned below.

Attributes:
* `name: Test` user readable name of the object

### Icinga Host

Brings in the host state of an individual Icinga host.

Attributes:
* `host: localhost` hostname in Icinga
* `type: host` (optional - detected by key attribute)

### Icinga Service

Brings in the service state of an individual Icinga service.

Attributes:
* `host: localhost` hostname in Icinga
* `service: servicename` servicename in Icinga
* `type: service` (optional - detected by key attribute)

### Icinga Hostgroup

Brings in the hostgroup summary state.

Attributes:
* `hostgroup: linux-servers` hostname in Icinga
* `type: hostgroup` (optional - detected by key attribute)

## Options

Additional options are available to control status behavior of
the respective view.

* `host_never_unhandled` (boolean) Controls the host not being displayed as an unhandled problem.
* `notification_periods` (boolean) TODO [#18](https://github.com/Icinga/icingaweb2-module-toplevelview/issues/18)

These options are just set on the root node:

```yaml
name: Test Config with Options
host_never_unhandled: true
children:
- name: Section 1
```

## Examples

Here is a longer example from a testing configuration

```yaml
name: Test
children:
- name: Single Objects
  children:
  - name: OK
    children:
    - host: host_ok
    - host: host_ok
      service: s_ok
    - host: host_s_soft
    - host: host_s_soft
      service: s_critical_soft
  - name: DOWN
    children:
    - host: host_down
    - host: host_down
      service: s_critical
  - name: CRITICAL
    children:
    - host: host_s_critical
    - host: host_s_critical
      service: s_critical
  - name: WARNING
    children:
    - host: host_s_warning
    - host: host_s_warning
      service: s_warning
- name: Single Objects Handled
  children:
  - name: CRITICAL handled
    children:
    - host: host_s_critical_handled
    - host: host_s_critical_handled
      service: s_critical_handled
  - name: WARNING handled
    children:
    - host: host_s_warning_handled
    - host: host_s_warning_handled
      service: s_warning_handled
- name: Hostgroups
  children:
  - name: OK
    children:
    - hostgroup: HG_OK
    - hostgroup: HG_SOFT
  - name: DOWN
    children:
    - hostgroup: HG_DOWN
  - name: CRITICAL
    children:
    - hostgroup: HG_CRITICAL
  - name: WARNING
    children:
    - hostgroup: HG_WARNING
- name: TLV Missing
  children:
  - name: Partially missing
    children:
    - host: host_ok
    - name: missing
  - name: Missing with problems
    children:
    - host: host_down
    - name: missing
  - name: Missing with handled
    children:
    - host: host_down_handled
    - name: missing
  - name: Empty
    children:
    - name: nothing here
  - name: Notexisting Object
    children:
    - host: notexisting
```