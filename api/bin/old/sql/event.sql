use `perpcast_api_migrated`;

INSERT INTO
  `event`(
    `id`,
    `user_id`,
    `initial_lat`,
    `initial_long`,
    `processed`,
    `display_name`,
    `name`,
    `created`,
    `modified`,
    `deleted`,
    `log`,
    `attempted`,
    `flags`)
  SELECT
    uuid_to_bin(`event_uuid`),
    uuid_to_bin(`user_uuid`),
    `initial_lat`,
    `initial_long`,
    (CASE `processed`
     WHEN  -1 THEN 0
     ELSE 1
     END),
    `title`,
    `title`,
    `created_at`,
    `updated_at`,
    null,
    '',
    (CASE `processed`
     WHEN  -1 THEN 4
     WHEN  1 THEN NULL
     ELSE NULL
     END)
    ,
    (CASE `processed`
     WHEN  -1 THEN 2
     WHEN  1 THEN 4
     ELSE 0
     END)
  FROM `perpcast_old`.`cast_event` eo
    INNER JOIN perpcast_api_migrated.user u on uuid_to_bin(eo.user_uuid) = u.id
;
