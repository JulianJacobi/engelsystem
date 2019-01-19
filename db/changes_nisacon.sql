ALTER TABLE `users_contact` ADD COLUMN `hometown` VARCHAR(255) NOT NULL;
ALTER TABLE `users_contact` ADD COLUMN `street` VARCHAR(255) NOT NULL;
ALTER TABLE `users_contact` ADD COLUMN `zip_code` VARCHAR(255) NOT NULL;
ALTER TABLE `users_contact` ADD COLUMN `emergency_contact` VARCHAR(255) NOT NULL;
ALTER TABLE `users_contact` ADD COLUMN `emergency_contact_phone` VARCHAR(255) NOT NULL;

ALTER TABLE `users_personal_data` ADD COLUMN `date_of_birth` DATE NOT NULL;
ALTER TABLE `users_personal_data` ADD COLUMN `allergies` VARCHAR(511) NOT NULL;
ALTER TABLE `users_personal_data` ADD COLUMN `medicines` VARCHAR(511) NOT NULL;