USE perpcast_api_migrated;

INSERT IGNORE INTO `perpcast_api_migrated`.`coupon`
(
  `id`,
  `code`,
  `current_usages`,
  `is_active`,
  `price`,
  `name`,
  `max_redemption`,
  `redemption_start_date`,
  `redemption_end_date`,
  `subscription_length`,
  `code_string`,
  `plan`,
  `recipient_email`,
  `sender_id`,
  `message`,
  `is_deliveved`,
  `delivery_date`,
  `subscription_id`,
  `type`,
  `created`,
  `modified`,
  `deleted`
)
SELECT
  UUID_TO_BIN(UUID()),
  cr.`redeem_code`,
  cr.`is_redeem`,
  '1',
  '29.99',
  cr.`recipient_name`,
  '1',
  NULL, NULL,
  '12', NULL,
  'giftplanyear',
  cr.`recipient_email`,
  u.id,
  cr.`recipient_message`,
  cr.`is_delivered`,
  (SELECT UNIX_TIMESTAMP(STR_TO_DATE(cr.`delivery_date`, '%m-%d-%y'))),
  NULL,
  '1',
  UNIX_TIMESTAMP(),
  UNIX_TIMESTAMP(),
  NULL
FROM `perpcast_old`.`cast_recipient` AS cr
INNER JOIN `perpcast_old`.`cast_sender` AS cs ON cs.`sender_uuid` = cr.`sender_uuid`
INNER JOIN `perpcast_api_migrated`.`user` as u ON u.email = cs.`sender_email`
GROUP BY u.id, cr.recipient_email
;