USE perpcast_api_migrated;

INSERT INTO `perpcast_api_migrated`.`coupon`(
	`id`,
  `code`,
  `is_active` ,
  `max_redemption`,
  `current_usages`,
  `price` ,
  `name` ,
  `redemption_start_date`,
  `redemption_end_date` ,
  `subscription_length`,
  `code_string`,
  `type` ,
  `created` ,
  `modified`
)
SELECT
	`UUID_TO_BIN`( UUID()),
	`code`,
	`is_active`,
	`max_usages` ,
	`current_usages` ,
	`prize`,
  `promotion_name`,
  `redemption_start_date`,
  `redemption_end_date`,
  `subscription_length`,
  `code_string`,
  1, -- coupon
  UNIX_TIMESTAMP(),
  UNIX_TIMESTAMP()
FROM `perpcast_old`.`cast_coupon`
WHERE `code` is NOT NULL
;


UPDATE `perpcast_api_migrated`.`coupon` SET `price` = 0 WHERE `code` IN (SELECT  `code` FROM perpcast_old.cast_coupon WHERE free_account = 1);

