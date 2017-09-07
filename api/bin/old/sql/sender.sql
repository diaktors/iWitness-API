USE perpcast_api_migrated;

INSERT IGNORE INTO `perpcast_api_migrated`.`user`
(
  `id`,
  `first_name`,
  `email`,
  `phone`,
  `type`,
  `created`
)
SELECT
  UUID_TO_BIN(`sender_uuid`),
  `sender_name`,
  `sender_email`,
  `sender_phone`,
  8,
  UNIX_TIMESTAMP()
FROM `perpcast_old`.`cast_sender`
GROUP BY `sender_email`
;