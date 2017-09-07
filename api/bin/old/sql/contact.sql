USE `perpcast_api_migrated`;

DELIMITER //

DROP FUNCTION IF EXISTS ConvertStatus //

CREATE FUNCTION ConvertStatus(`status` text)
  RETURNS MEDIUMINT(7)
  BEGIN
    DECLARE result INT;

    IF (status = 'pending') THEN
      SET result = 1;
    ELSEIF (status = 'accepted') THEN
      SET result = 2;
    ELSEIF (status = 'declined') THEN
      SET result = 4;
    END IF;

    RETURN result;
  END //

INSERT INTO
  perpcast_api_migrated.`contact`(
    `id`,
    `user_id`,
    `email`,
    `phone`,
    `phone_alt`,
    `first_name`,
    `last_name`,
    `flags`,
    `relation_type`,
    `is_primary`,
    `created`,
    `modified`,
    `deleted`,
    `secret_key`)
  SELECT
    UUID_TO_BIN(UUID()),
    UUID_TO_BIN(c.`user_uuid`),
    c.`contact_email`,
    c.`phone`,
    c.`phone_alt`,
    c.`first_name`,
    c.`last_name`,
    ConvertStatus(c.`status`),
    c.`relationship`,
    c.`is_primary`,
    c.`created_at`,
    c.`updated_at`,
    null,
    c.`secret_key`
  FROM `perpcast_old`.`cast_contact` c inner join perpcast_api_migrated.user u on uuid_to_bin(c.user_uuid) = u.id
//

DROP FUNCTION IF EXISTS ConvertStatus //

DELIMITER ;