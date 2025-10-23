-- Add missing columns to settings table
ALTER TABLE `settings` 
ADD COLUMN `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
ADD COLUMN `group` varchar(50) DEFAULT 'general' AFTER `key`,
ADD COLUMN `type` varchar(20) DEFAULT 'string' AFTER `value`,
ADD COLUMN `description` text DEFAULT NULL AFTER `type`,
ADD COLUMN `is_public` tinyint(1) DEFAULT 0 AFTER `description`,
ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `is_public`,
ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Update existing records to have a group
UPDATE `settings` SET `group` = 'general' WHERE `group` IS NULL;

-- Add index for better performance
ALTER TABLE `settings` ADD INDEX `idx_group` (`group`);
ALTER TABLE `settings` ADD INDEX `idx_key` (`key`);
