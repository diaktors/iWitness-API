USE perpcast_api_migrated;

INSERT INTO `perpcast_api_migrated`.`subscription`(
	`id`,
  `user_id`,
  `original_phone`,
  `original_phone_model`,
  `customer_ip`,
  `coupon_id`,
  `arb_billing_id`,
  `plan`,
  `start_at`,
  `expire_at`,
  `suspended`,
  `is_active`,
  `receipt_id`,
  `created`,
  `modified`
)
SELECT
	`UUID_TO_BIN`(`user_uuid`) ,
	u.id ,
  `original_phone`,
  `original_phone_model`,
  `customer_ip`,
   cop.id,
  `arb_id`,
   sub.`plan` ,
  `start_at` ,
  `expire_at`,
  `suspended` ,
   1,
  `purchase_token`,
   UNIX_TIMESTAMP(),
   UNIX_TIMESTAMP()
FROM `perpcast_old`.`cast_subscription` sub
LEFT JOIN `perpcast_api_migrated`.`coupon` AS cop  ON sub.promo_code = cop.code
LEFT JOIN `perpcast_api_migrated`.`user` AS u  ON (u.id = `UUID_TO_BIN`(sub.user_uuid) OR u.phone = sub.original_phone)
;

update perpcast_api_migrated.user u
  join perpcast_api_migrated.subscription s on u.id = s.user_id
  set u.subscription_start_at = s.start_at,
      u.subscription_expire_at = s.expire_at
;