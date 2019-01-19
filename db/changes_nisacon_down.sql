ALTER TABLE `users_contact`
  DROP COLUMN `hometown`,
  DROP COLUMN `street`,
  DROP COLUMN `zip_code`,
  DROP COLUMN `emergency_contact`,
  DROP COLUMN `emergency_contact_phone`;

ALTER TABLE `users_personal_data`
  DROP COLUMN `date_of_birth`,
  DROP COLUMN `allergies`,
  DROP COLUMN `medicines`;