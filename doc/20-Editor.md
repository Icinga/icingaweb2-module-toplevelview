Config Editor
=============

The module ships a very basic configuration editor,
which allows to to edit the bare YAML configuration within the web interface.

Please also see the [chapter on config format](21-Config-Format.md).

## Saving

You have two options on saving:

* Save to session - so you can test and review your edit
* Save to disk - it is stored to disk and visible for everyone

When you have saved changes to your session, but not yet disk, the interface
reminds you of that.

Also you can cancel an edit with a button below the editor, and return to disk state.

## History

Configuration history is saved to disk, so it can be restored manually.
But there is not web interface.

See `/etc/icingaweb2/modules/toplevelview/views/<name>/*.yml`.
