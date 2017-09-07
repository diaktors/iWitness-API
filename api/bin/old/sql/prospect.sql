USE perpcast_api_migrated;

INSERT INTO `perpcast_api_migrated`.`user`
(
  `id`,
  `email`,
  `first_name`,
  `platform`,
  `ip_address`,
  `user_agent`,
  `created`,
  `type`
)
SELECT
  UUID_TO_BIN(`prospect_uuid`),
  `email`,
  `name`,
  `platform`,
  `ip_address`,
  `user_agent`,
  `created_at`,
  4
FROM `perpcast_old`.`cast_prospect`
GROUP BY
  `email`,
  `platform`
;
