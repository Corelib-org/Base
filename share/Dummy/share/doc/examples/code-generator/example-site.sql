CREATE TABLE `tbl_animal_species` (
	`pk_animal_species` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`common_name` VARCHAR( 50 ) NOT NULL ,
	`latin_name` VARCHAR( 50 ) NOT NULL ,
	`create_timestamp` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE = InnoDB COMMENT = 'Revision: 1';


CREATE TABLE `tbl_animal_breeds` (
	`pk_animal_breeds` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`fk_animal_species` SMALLINT UNSIGNED NOT NULL ,
	`name` VARCHAR( 50 ) NOT NULL ,
	`create_timestamp` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	UNIQUE (`fk_animal_species`),
	FOREIGN KEY ( `fk_animal_species` ) REFERENCES `tbl_animal_species` (`pk_animal_species`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB COMMENT = 'Revision: 1';