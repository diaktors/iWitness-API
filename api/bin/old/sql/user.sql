USE perpcast_api_migrated;

INSERT IGNORE INTO `perpcast_api_migrated`.`user`(
	`id` ,
  `phone` ,
  `phone_alt` ,
  `first_name`,
  `last_name` ,
  `address_1` ,
  `address_2` ,
  `city` ,
  `state` ,
  `zip` ,
  `email` ,
  `birth_date` ,
  `height_feet` ,
  `height_inches` ,
  `weight` ,
  `eye_color` ,
  `hair_color` ,
  `ethnicity` ,
  `dist_feature` ,
  `photo` ,
  `timezone` ,
  `password` ,
  `type` ,
  `subscription_id`,
  `subscription_start_at` ,
  `subscription_expire_at` ,
  `flags`,
  `secret_key`,
  `created` ,
  `modified` ,
  `deleted`,
  `gender`
)
SELECT
	UUID_TO_BIN(u.`user_uuid`) ,
  u.`phone` ,
  u.`phone_alt` ,
  u.`first_name`,
  u.`last_name` ,
  u.`address_1` ,
  u.`address_2` ,
  u.`city` ,
  u.`st` ,
  u.`zip` ,
  u.`email`,
  u.`birth_date` ,
  u.`height_feet` ,
  u.`height_inches` ,
  u.`weight` ,
  u.`eye_color` ,
  u.`hair_color` ,
  u.`ethnicity` ,
  u.`dist_feature` ,
  u.`photo` ,
  u.`timezone` ,
  u.`password` ,
   1,	 -- type = 1 is user
  UUID_TO_BIN(u.`user_uuid`),
  u.`subscription_start_at` ,
  u.`subscription_expire_at` ,
   0, -- flags is status
  u.`secret_key`,
  u.`created_at` ,
  u.`updated_at` ,
   NULL, -- not deleted,
   CASE u.`gender`
   	   WHEN  2 THEN 0
   	   WHEN  1 THEN 1
   	   ELSE NULL
   END
FROM `perpcast_old`.`cast_user` u
  inner join `perpcast_old`.`cast_subscription` s on (u.user_uuid = s.user_uuid OR u.phone = s.original_phone)
where u.phone is not null
	and u.user_uuid <> '676add1d-e93d-41ef-a442-f4f2ccf24c58'
group by u.user_uuid
;
