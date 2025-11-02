ALTER TABLE `user_cred`
  ADD COLUMN `is_host` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `user_cred`
  ADD COLUMN `host_status` ENUM('pending','approved','rejected') NULL DEFAULT NULL;

ALTER TABLE `rooms`
  ADD COLUMN `host_id` INT NULL DEFAULT NULL;

ALTER TABLE `rooms`
  ADD CONSTRAINT `fk_rooms_host`
    FOREIGN KEY (`host_id`) REFERENCES `user_cred`(`id`)
    ON DELETE SET NULL;
