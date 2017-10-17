Limits of this module
=====================

A few notes about compatibility to Icinga 2.

## Notification Period

Currently Icinga 2 does not publish `notification_period` to the IDO, due
to the new notification logic.

When `notification_period` is `NULL`, TLV will treat the object as **in period**
to avoid problems here.

This feature is disabled by default, see [config format](21-Config-Format.md#options) and
[behavior](02-Behavior.md#enabling-notification-period).

### Period ranges in Icinga 2

The publishing of the individual ranges is buggy in Icinga 2 and can not be used here!

See [Icinga2 #4659](https://github.com/Icinga/icinga2/issues/4659).

### Period ranges in general

Not all types of ranges are published to IDO.

**Only the very basic range type is supported:** Weekday start and end time
