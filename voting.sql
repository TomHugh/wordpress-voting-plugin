CREATE TABLE `u194943_zloty_was`.`voting_codes` (
  `id` INT(6) NOT NULL AUTO_INCREMENT , 
  `timestamp` TIMESTAMP NOT NULL , 
  `email` VARCHAR(50) NOT NULL , 
  `code` INT(4) NOT NULL , 
  PRIMARY KEY (`id`)) ENGINE = InnoDB;

CREATE TABLE `u194943_zloty_was`.`votes` (
  `id` INT(6) NOT NULL AUTO_INCREMENT , 
  `timestamp` TIMESTAMP NOT NULL , 
  `vc_id` INT(6) NOT NULL , 
  `vote` INT(1) NOT NULL , 
  PRIMARY KEY (`id`)) ENGINE = InnoDB;

CREATE TABLE `u194943_zloty_was`.`finalists` (
  `id` INT(1) NOT NULL AUTO_INCREMENT , 
  `name` VARCHAR(50) NOT NULL , 
  `photo` VARCHAR(100) NOT NULL , 
  PRIMARY KEY (`id`)) ENGINE = InnoDB;