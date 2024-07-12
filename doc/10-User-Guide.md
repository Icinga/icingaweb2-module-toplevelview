User Guide
==========

This guide wants to help you understand the basic usage of the module.
And how to access its data quickly.

To understand the status behavior, see the [chapter about behavior](02-Behavior.md).

## Finding problems

Unhandled problems can be identified based on color saturation of the tiles.

In addition counters show you how many states of what kind lay below.

![Unhandled colors](screenshots/colors-unhandled.png)

![Handled colors](screenshots/colors-handled.png)

## Drilling down

When you click on tiles in the topmost tile view, you drill down into a
tree view of that tile.

The tree view is collapsing everything that is in an OK state. So you the
problems first.

![](screenshots/tiles-tree-problems.png)

In this view everything can be expanded via the arrow handle or a click
on the title area.

Deeper branches can be opened in a new view by clicking on the title text, but
this is only needed to filter the shown data.

Every Icinga status object, a host, service or hostgroup is a single tile
here.

Counters are meant to give you an indication about how many problems, or
even objects are there.

## Viewing Icinga details

By clicking on an Icinga tile you get dropped into the Monitoring module,
which allows you to see all details and interact with the object (e.g. for
acknowledging the problem).

Clicking on a service ![tile](screenshots/tile-service.png) will bring you
service detail view, same goes for hosts.

![](screenshots/tree-service.png)

The hostgroup ![tile](screenshots/tile-hostgroup.png) gives 2 options,
default will take you to a service overview for that hostgroup, sorted by problems.

While clicking on the `X hosts` link in the tile brings you an overview
over hosts in that group.

![](screenshots/tree-hostgroup.png)

## Note about caching

Please be aware that the data displayed in the Top Level View is cached for 60
seconds by default. This can be adjusted using the URL parameter `cache`.
For example: `toplevelview/show/tree?name=example-view&cache=30`

Latest state changes will only be reflected after that caching time.
