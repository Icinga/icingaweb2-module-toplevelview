Configuration and Behavior
========

Top Level View (TLV) uses additional status logic for its views.

This does not affect the overall status behavior of Icinga 2 or Icinga Web 2,
but it is important to understand the differences.

## Worst state on top

The main responsibility of TLV is to show you the worst state on top.

Worst status is defined in the following order:

* critical_unhandled
* warning_unhandled
* unknown_unhandled
* critical_handled
* warning_handled
* unknown_handled
* ok
* downtime_handled
* missing

In addition, counter badges will present you in-depth details over the
status below a tile or tree element.

Similar to Icinga Web 2 you can easily see unhandled problems by the color of a tile.

![Unhandled problems](screenshots/colors-unhandled.png)
![Handled problems](screenshots/colors-handled.png)

## TLV only shows Hard States

While the normal monitoring views will always show you all current states,
the **Top Level Views will only show hard states**.

Which means, as long as the object doesn't have reached a hard state, the TLV tree element will be OK.

## Configuration Options

These options can change the behavior of views.
They are placed at the top of a view configuration:

```yaml
name: My View
set_downtime_if_notification_disabled: true
override_host_problem_to_handled: true
children:
- name: Section 1
```

### Option `override_host_problem_to_handled` (bool)

This overrides every host problem to be handled.

* `override_host_problem_to_handled = true`
* `override_host_problem_to_handled = false # (default)`

This helps when alerting is mostly based on service states, and the host
is only a container.

### Option `set_downtime_if_notification_enabled` (bool)

Set the host or service to "active downtime" if notifications are disabled.

* `set_downtime_if_notification_disabled = true`
* `set_downtime_if_notification_disabled = false # (default)`

Since downtime and notification settings are essential for alerting,
Top Level Views tries to integrate these into its status logic.
