
LOCK TABLES `oauth_clients` WRITE;
/*!40000 ALTER TABLE `oauth_clients` DISABLE KEYS */;

INSERT INTO `oauth_clients` (`client_id`, `client_secret`, `redirect_uri`, `grant_types`, `scope`, `user_id`)
VALUES
	('47b34ee7-f5a1-11e3-bc94-000c29c9a052','','','password refresh_token',NULL,NULL),
	('ba5659b4-f5a1-11e3-bc94-000c29c9a052','','','password refresh_token',NULL,NULL),
	('e114cbaa-f5a1-11e3-bc94-000c29c9a052','','','password refresh_token',NULL,NULL);
/*!40000 ALTER TABLE `oauth_clients` ENABLE KEYS */;
UNLOCK TABLES;