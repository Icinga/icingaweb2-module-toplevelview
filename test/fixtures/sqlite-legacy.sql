-- sqlite3 -bail /tmp/toplevelview.db </usr/share/icingaweb2/modules/toplevelview/test/fixtures/sqlite-legacy.sql
PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

CREATE TABLE toplevelview_adminroles (hierarchy_id INTEGER, role_id INTEGER, PRIMARY KEY(hierarchy_id));
CREATE TABLE toplevelview_cache (hierarchy_id INTEGER, updated datetime, json TEXT, PRIMARY KEY(hierarchy_id));
CREATE TABLE toplevelview_options (name TEXT, value TEXT, PRIMARY KEY(name));

CREATE TABLE toplevelview_host (id INTEGER PRIMARY KEY AUTOINCREMENT, host_object_id INTEGER, view_id INTEGER);
INSERT INTO "toplevelview_host" VALUES(1,991,5);
INSERT INTO "toplevelview_host" VALUES(2,991,6);

CREATE TABLE toplevelview_hostgroup (id INTEGER PRIMARY KEY AUTOINCREMENT, hostgroup_object_id INTEGER, view_id INTEGER);
INSERT INTO "toplevelview_hostgroup" VALUES(1,9911,2);
INSERT INTO "toplevelview_hostgroup" VALUES(2,9911,3);
INSERT INTO "toplevelview_hostgroup" VALUES(3,9911,4);
INSERT INTO "toplevelview_hostgroup" VALUES(4,9911,5);
INSERT INTO "toplevelview_hostgroup" VALUES(5,9911,6);

CREATE TABLE toplevelview_service (id INTEGER PRIMARY KEY AUTOINCREMENT, service_object_id INTEGER, view_id INTEGER);
INSERT INTO "toplevelview_service" VALUES(1,994,5);
INSERT INTO "toplevelview_service" VALUES(2,994,6);

CREATE TABLE toplevelview_view (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL, display_name VARCHAR(255) NOT NULL);
INSERT INTO "toplevelview_view" VALUES(1,'TLV','TLV');
INSERT INTO "toplevelview_view" VALUES(6,'Icinga','Icinga');

CREATE TABLE toplevelview_view_hierarchy (id INTEGER PRIMARY KEY AUTOINCREMENT, view_id INTEGER, root_id INTEGER, lft INTEGER, rgt INTEGER, level INTEGER);
INSERT INTO "toplevelview_view_hierarchy" VALUES(1,1,1,1,12,0);
INSERT INTO "toplevelview_view_hierarchy" VALUES(3,6,1,10,11,1);

DELETE FROM sqlite_sequence;
INSERT INTO "sqlite_sequence" VALUES('toplevelview_view',6);
INSERT INTO "sqlite_sequence" VALUES('toplevelview_view_hierarchy',3);
INSERT INTO "sqlite_sequence" VALUES('toplevelview_hostgroup',5);
INSERT INTO "sqlite_sequence" VALUES('toplevelview_service',2);
INSERT INTO "sqlite_sequence" VALUES('toplevelview_host',2);

CREATE UNIQUE INDEX host_in_view_unique_index_idx ON toplevelview_host (view_id, host_object_id);
CREATE UNIQUE INDEX hostgroup_in_view_unique_index_idx ON toplevelview_hostgroup (view_id, hostgroup_object_id);
CREATE UNIQUE INDEX service_in_view_unique_index_idx ON toplevelview_service (view_id, service_object_id);
CREATE INDEX root_id_idx ON toplevelview_view_hierarchy (root_id);

COMMIT;
