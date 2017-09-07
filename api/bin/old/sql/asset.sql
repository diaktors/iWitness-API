use `perpcast_api_migrated`;


INSERT INTO
  `asset`(
    `id`,
    `user_id`,
    `event_id`,
    `filename`,
    `filesize`,
    `media_type`,
    `processed`,
    `lat`,
    `lng`,
    `width`,
    `height`,
    `meta`,
    `flags`,
    `display_name`,
    `name`,
    `created`,
    `modified`,
    `deleted`,
    `attempted`,
    `log`)
  SELECT
    uuid_to_bin(`asset_uuid`),
    uuid_to_bin(`user_uuid`),
    uuid_to_bin(`event_uuid`),
    ca.`filename`,
    ca.`filesize`,
    ca.`media_type`,
    ca.`processed`,
    ca.`lat`,
    ca.`lng`,
    ca.`width`,
    ca.`height`,
    ca.`meta`,
    (CASE ca.`processed`
   	WHEN  0 THEN 2
   	WHEN  1 THEN 4
    END),
    ca.`caption`,
    ca.`caption`,
    ca.`created_at`,
    ca.`insert_time`,
    null,
    (CASE ca.`processed`
   	WHEN  0 THEN 4
   	WHEN  1 THEN NULL
    END),
    ''
  FROM `perpcast_old`.`cast_asset` ca
  inner join event e on uuid_to_bin(ca.event_uuid) = e.id and uuid_to_bin(ca.user_uuid) = e.user_id
;
