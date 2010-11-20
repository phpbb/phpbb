USE PlaceHolderForDb$$

DROP PROCEDURE IF EXISTS add_user $$

CREATE PROCEDURE add_user()
BEGIN
DECLARE EXIT HANDLER FOR 1044 BEGIN END;
GRANT ALL PRIVILEGES ON PlaceHolderForDb.* to 'PlaceHolderForUser'@'PlaceHolderForServer' IDENTIFIED BY 'PlaceHolderForPassword';
FLUSH PRIVILEGES;
END
$$

CALL add_user() $$

DROP PROCEDURE IF EXISTS add_user $$
