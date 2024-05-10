Behavior
========

Top Level View uses additional status logic for its views.

This does not affect the overall status behavior of Icinga 2 or Icinga Web 2,
but it is important to understand the differences.

## Worst status on top

The main responsibility of TLV is to show you the worst status on top.

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

In addition counter badges will present you indepth details over the
status below a tile or tree element.

Similar to Icinga Web 2 you can easily see unhandled problems by the strength of color.

![Unhandled problems](screenshots/colors-unhandled.png)
![Handled problems](screenshots/colors-handled.png)

## SOFT and HARD states

While the normal monitoring views will always show you all current states,
the **Top Level Views will only show hard states**.

Which means, as long as the object doesn't have reached a hard state, the node will be OK.

## Handled and Unhandled

Icinga Web 2 introduced an handled state to every host and service.

By default handled would be true if:

* Problem has been acknowledged
* Object is in downtime

In Top Level View, a few things are different:

* Downtimes are handled special (see next topic)
* Notification settings can influence a status (see next topic)
* Flapping means the state is handled

## Downtime and Notifications

Since downtime and notification settings are essential for alerting,
Top Level Views tries to integrate these into its status logic.

The following behaviors will trigger the downtime logic:

* Host or Service is in an active downtime
* Notifications are disabled for the host or service

If those conditions are met:

* TLV counts the service as `downtime_active`
* TLV ignored non-OK states and marks them as `downtime_handled`

## Additional Features

Some features can be enabled by view, and control some additional behavior.

### Option `override_host_problem_to_handled` (bool)

Override every host problem to handled.

* `override_host_problem_to_handled = true`
* `override_host_problem_to_handled = false`

This helps when alerting is mostly based on service states, and the host
is only a container.
