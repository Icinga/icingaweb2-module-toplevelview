Configuration Editor
=============

The module ships a very basic configuration editor,
which allows to to edit the bare YAML configuration within the web interface.

Please also see the [chapter on config format](21-Config-Format.md).

## Saving

You have two options on saving:

* Save to session - so you can test and review your edit
* Save to disk - it is stored to disk and visible for everyone with access

When you have saved changes to your session, but not yet disk, the interface
reminds you of that.

Also you can cancel an edit with a button below the editor, and return to disk state.

## History

When updating a view with the Editor the module creates a backup of the previous version.

These previous states are saved to disk, so it can be restored.

```bash
# /etc/icingaweb2/modules/toplevelview/views/<name of the view>/*.yml

ls -l /etc/icingaweb2/modules/toplevelview/views/myview

1722014991.yml
1721917992.yml
1721915691.yml
```

**Hint:** There is no web interface to manage these files.
However, the `icingacli` offers a subcommand to clean these up.

```
# Removes all but one backups for all views
icingacli toplevelview

# Removes all but one backups for a specific view
icingacli toplevelview --view myview

# Removes all but two backups for a specific view
icingacli toplevelview --view myview --keep 2
```
