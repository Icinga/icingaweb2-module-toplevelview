-- MySQL dump 10.16  Distrib 10.1.18-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: icinga
-- ------------------------------------------------------
-- Server version	10.1.18-MariaDB-1~jessie

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `icinga_instances`
--

LOCK
TABLES `icinga_instances` WRITE;
/*!40000 ALTER TABLE `icinga_instances` DISABLE KEYS */;
INSERT INTO `icinga_instances`
VALUES (1, 'default', 'default');
/*!40000 ALTER TABLE `icinga_instances` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Dumping data for table `icinga_programstatus`
--

LOCK
TABLES `icinga_programstatus` WRITE;
/*!40000 ALTER TABLE `icinga_programstatus` DISABLE KEYS */;
INSERT INTO `icinga_programstatus`
VALUES (1, 1, '0.0.1', '2017-10-10 10:59:47', '2017-10-10 10:59:47', NULL, NULL, 1, 0, 0, NULL, NULL, 1, NULL, 1, 1, 1,
        1, 1, 1, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL);
/*!40000 ALTER TABLE `icinga_programstatus` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Dumping data for table `icinga_objects`
--

LOCK
TABLES `icinga_objects` WRITE;
/*!40000 ALTER TABLE `icinga_objects` DISABLE KEYS */;
INSERT INTO `icinga_objects`
VALUES (1, 1, 1, 'host_ok', NULL, 1),
       (2, 1, 1, 'host_down', NULL, 1),
       (3, 1, 1, 'host_s_soft', NULL, 1),
       (4, 1, 2, 'host_ok', 's_ok', 1),
       (5, 1, 2, 'host_down', 's_critical', 1),
       (6, 1, 2, 'host_s_soft', 's_critical_soft', 1),
       (7, 1, 1, 'host_s_critical', NULL, 1),
       (8, 1, 1, 'host_s_warning', NULL, 1),
       (9, 1, 2, 'host_s_critical', 's_critical', 1),
       (10, 1, 2, 'host_s_warning', 's_warning', 1),
       (11, 1, 3, 'HG_OK', NULL, 1),
       (12, 1, 3, 'HG_SOFT', NULL, 1),
       (13, 1, 3, 'HG_DOWN', NULL, 1),
       (14, 1, 3, 'HG_CRITICAL', NULL, 1),
       (15, 1, 3, 'HG_WARNING', NULL, 1),
       (16, 1, 1, 'host_s_critical_handled', NULL, 1),
       (17, 1, 1, 'host_s_warning_handled', NULL, 1),
       (18, 1, 2, 'host_s_critical_handled', 's_critical_handled', 1),
       (19, 1, 2, 'host_s_warning_handled', 's_warning_handled', 1),
       (20, 1, 9, '24x7', NULL, 1);
/*!40000 ALTER TABLE `icinga_objects` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Dumping data for table `icinga_hosts`
--

LOCK
TABLES `icinga_hosts` WRITE;
/*!40000 ALTER TABLE `icinga_hosts` DISABLE KEYS */;
INSERT INTO `icinga_hosts`
VALUES (1, 1, 1, 1, 'host_ok', 'host_ok', '127.0.0.1', '', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0,
        0, 0, 0, NULL),
       (2, 1, 1, 2, 'host_down', 'host_down', '127.0.0.1', '', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0,
        0, 0, 0, 0, 0, NULL),
       (3, 1, 1, 3, 'host_s_soft', 'host_s_soft', '127.0.0.1', '', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0,
        0, 0, 0, 0, 0, 0, NULL),
       (4, 1, 1, 7, 'host_s_critical', 'host_s_critical', '127.0.0.1', '', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL,
        NULL, 0, 0, 0, 0, 0, 0, 0, NULL),
       (5, 1, 1, 8, 'host_s_warning', 'host_s_warning', '127.0.0.1', '', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL,
        NULL, 0, 0, 0, 0, 0, 0, 0, NULL),
       (6, 1, 1, 16, 'host_s_critical_handled', 'host_s_critical_handled', '127.0.0.1', '', 0, NULL, 0, NULL, 20, 0, '',
        5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL,
        NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL),
       (7, 1, 1, 17, 'host_s_warning_handled', 'host_s_warning_handled', '127.0.0.1', '', 0, NULL, 0, NULL, 20, 0, '',
        5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL,
        NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL);
/*!40000 ALTER TABLE `icinga_hosts` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Dumping data for table `icinga_hoststatus`
--

LOCK
TABLES `icinga_hoststatus` WRITE;
/*!40000 ALTER TABLE `icinga_hoststatus` DISABLE KEYS */;
INSERT INTO `icinga_hoststatus`
VALUES (1, 1, 1, '2017-10-10 11:01:51', 'TEST', NULL, NULL, '', 0, 1, 0, 1, 5, '2017-10-10 11:01:51',
        '2017-10-10 11:06:51', 0, '2017-10-10 11:01:51', '2017-10-10 11:01:51', 0, '2017-10-10 11:01:51', NULL, NULL, 1,
        NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
       (2, 1, 2, '2017-10-10 11:01:51', 'TEST', NULL, NULL, '', 1, 1, 0, 1, 5, '2017-10-10 11:01:51',
        '2017-10-10 11:06:51', 0, '2017-10-10 11:01:51', '2017-10-10 11:01:51', 1, '2017-10-10 11:01:51',
        '2017-10-10 11:01:51', NULL, 1, NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL,
        NULL, 0, 0, 0, 0, NULL),
       (3, 1, 3, '2017-10-10 11:01:51', 'TEST', NULL, NULL, '', 0, 1, 0, 1, 5, '2017-10-10 11:01:51',
        '2017-10-10 11:06:51', 0, '2017-10-10 11:01:51', '2017-10-10 11:01:51', 0, '2017-10-10 11:01:51', NULL, NULL, 1,
        NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
       (4, 1, 7, '2017-10-10 11:01:51', 'TEST', NULL, NULL, '', 0, 1, 0, 1, 5, '2017-10-10 11:01:51',
        '2017-10-10 11:06:51', 0, '2017-10-10 11:01:51', '2017-10-10 11:01:51', 0, '2017-10-10 11:01:51', NULL, NULL, 1,
        NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
       (5, 1, 8, '2017-10-10 11:01:51', 'TEST', NULL, NULL, '', 0, 1, 0, 1, 5, '2017-10-10 11:01:51',
        '2017-10-10 11:06:51', 0, '2017-10-10 11:01:51', '2017-10-10 11:01:51', 0, '2017-10-10 11:01:51', NULL, NULL, 1,
        NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
       (6, 1, 16, '2017-10-10 11:01:51', 'TEST', NULL, NULL, '', 0, 1, 0, 1, 5, '2017-10-10 11:01:51',
        '2017-10-10 11:06:51', 0, '2017-10-10 11:01:51', '2017-10-10 11:01:51', 0, '2017-10-10 11:01:51', NULL, NULL, 1,
        NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
       (7, 1, 17, '2017-10-10 11:01:51', 'TEST', NULL, NULL, '', 0, 1, 0, 1, 5, '2017-10-10 11:01:51',
        '2017-10-10 11:06:51', 0, '2017-10-10 11:01:51', '2017-10-10 11:01:51', 0, '2017-10-10 11:01:51', NULL, NULL, 1,
        NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, 0, 0, 0, NULL);
/*!40000 ALTER TABLE `icinga_hoststatus` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Dumping data for table `icinga_services`
--

LOCK
TABLES `icinga_services` WRITE;
/*!40000 ALTER TABLE `icinga_services` DISABLE KEYS */;
INSERT INTO `icinga_services`
VALUES (1, 1, 1, 1, 4, 's_ok', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
       (2, 1, 1, 2, 5, 's_critical', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
       (3, 1, 1, 3, 6, 's_critical_soft', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
       (4, 1, 1, 7, 9, 's_critical', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
       (5, 1, 1, 8, 10, 's_warning', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
       (6, 1, 1, 16, 18, 's_critical_handled', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
       (7, 1, 1, 17, 19, 's_warning_handled', 0, NULL, 0, NULL, 20, 0, '', 5, 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE `icinga_services` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Dumping data for table `icinga_servicestatus`
--

LOCK
TABLES `icinga_servicestatus` WRITE;
/*!40000 ALTER TABLE `icinga_servicestatus` DISABLE KEYS */;
INSERT INTO `icinga_servicestatus`
VALUES (1, 1, 4, '2017-10-10 11:16:38', 'TEST', NULL, NULL, '', 0, 1, 0, 1, 5, '2017-10-10 11:17:01',
        '2017-10-10 11:22:03', 1, '2017-10-10 11:17:11', '2017-10-10 11:17:11', 0, '2017-10-10 11:17:11', NULL, NULL,
        NULL, 1, NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 5, 1, 0, 0, NULL),
       (2, 1, 5, '2017-10-10 11:16:38', 'TEST', NULL, NULL, '', 2, 1, 0, 1, 5, '2017-10-10 11:17:01',
        '2017-10-10 11:22:03', 1, '2017-10-10 11:17:11', '2017-10-10 11:17:11', 1, '2017-10-10 11:17:11', NULL, NULL,
        NULL, 1, NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 5, 1, 0, 0, NULL),
       (3, 1, 6, '2017-10-10 11:16:38', 'TEST', NULL, NULL, '', 2, 1, 0, 1, 5, '2017-10-10 11:17:01',
        '2017-10-10 11:22:03', 1, '2017-10-10 11:17:11', '2017-10-10 11:17:11', 0, '2017-10-10 11:17:11', NULL, NULL,
        NULL, 0, NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 5, 1, 0, 0, NULL),
       (4, 1, 9, '2017-10-10 11:16:38', 'TEST', NULL, NULL, '', 2, 1, 0, 1, 5, '2017-10-10 11:17:01',
        '2017-10-10 11:22:03', 1, '2017-10-10 11:17:11', '2017-10-10 11:17:11', 2, '2017-10-10 11:17:11', NULL, NULL,
        NULL, 1, NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 5, 1, 0, 0, NULL),
       (5, 1, 10, '2017-10-10 11:16:38', 'TEST', NULL, NULL, '', 1, 1, 0, 1, 5, '2017-10-10 11:17:01',
        '2017-10-10 11:22:03', 1, '2017-10-10 11:17:11', '2017-10-10 11:17:11', 1, '2017-10-10 11:17:11', NULL, NULL,
        NULL, 1, NULL, NULL, 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 5, 1, 0, 0, NULL),
       (6, 1, 19, '2017-10-10 11:16:38', 'TEST', NULL, NULL, '', 1, 1, 0, 1, 5, '2017-10-10 11:17:01',
        '2017-10-10 11:22:03', 1, '2017-10-10 11:17:11', '2017-10-10 11:17:11', 1, '2017-10-10 11:17:11', NULL, NULL,
        NULL, 1, NULL, NULL, 0, 1, 1, 3, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 5, 1, 0, 0, NULL),
       (7, 1, 18, '2017-10-10 11:16:38', 'TEST', NULL, NULL, '', 2, 1, 0, 1, 5, '2017-10-10 11:17:01',
        '2017-10-10 11:22:03', 1, '2017-10-10 11:17:11', '2017-10-10 11:17:11', 1, '2017-10-10 11:17:11', NULL, NULL,
        NULL, 1, NULL, NULL, 0, 1, 1, 3, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 5, 1, 0, 0, NULL);
/*!40000 ALTER TABLE `icinga_servicestatus` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Dumping data for table `icinga_hostgroups`
--

LOCK
TABLES `icinga_hostgroups` WRITE;
/*!40000 ALTER TABLE `icinga_hostgroups` DISABLE KEYS */;
INSERT INTO `icinga_hostgroups`
VALUES (1, 1, 1, 11, 'HG_OK', NULL, NULL, NULL, NULL),
       (2, 1, 1, 12, 'HG_SOFT', NULL, NULL, NULL, NULL),
       (3, 1, 1, 13, 'HG_DOWN', NULL, NULL, NULL, NULL),
       (4, 1, 1, 14, 'HG_CRITICAL', NULL, NULL, NULL, NULL),
       (5, 1, 1, 15, 'HG_WARNING', NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE `icinga_hostgroups` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Dumping data for table `icinga_hostgroup_members`
--

LOCK
TABLES `icinga_hostgroup_members` WRITE;
/*!40000 ALTER TABLE `icinga_hostgroup_members` DISABLE KEYS */;
INSERT INTO `icinga_hostgroup_members`
VALUES (1, 1, 1, 1),
       (2, 1, 2, 3),
       (3, 1, 3, 2),
       (4, 1, 4, 7),
       (5, 1, 5, 8);
/*!40000 ALTER TABLE `icinga_hostgroup_members` ENABLE KEYS */;
UNLOCK
TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-10-17 10:05:46
