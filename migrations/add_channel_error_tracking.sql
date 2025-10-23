-- Add channel and error tracking to api_statistics table
-- This enables real error rate monitoring per platform

ALTER TABLE `api_statistics` 
ADD COLUMN `channel` VARCHAR(50) NULL COMMENT 'Platform/Channel name (booking, hotels, sabee, etc.)' AFTER `widget_code`,
ADD COLUMN `has_error` TINYINT(1) DEFAULT 0 COMMENT 'Whether the request had any API errors' AFTER `response_time_ms`,
ADD COLUMN `error_platforms` JSON NULL COMMENT 'Platforms that returned errors' AFTER `has_error`,
ADD COLUMN `error_message` TEXT NULL COMMENT 'Error details if any' AFTER `error_platforms`;

-- Add index for channel queries
ALTER TABLE `api_statistics` ADD INDEX `idx_channel` (`channel`);
ALTER TABLE `api_statistics` ADD INDEX `idx_has_error` (`has_error`);
ALTER TABLE `api_statistics` ADD INDEX `idx_channel_error` (`channel`, `has_error`);

-- Update existing records to extract channel from parameters if possible
-- This is optional and depends on your data structure
UPDATE `api_statistics` 
SET `channel` = 
    CASE 
        WHEN JSON_EXTRACT(parameters, '$.channel') IS NOT NULL 
        THEN JSON_UNQUOTE(JSON_EXTRACT(parameters, '$.channel'))
        ELSE NULL
    END
WHERE `channel` IS NULL;

-- Create a view for easy error rate queries
CREATE OR REPLACE VIEW `channel_error_statistics` AS
SELECT 
    channel,
    DATE(created_at) as date,
    COUNT(*) as total_requests,
    SUM(has_error) as error_count,
    SUM(CASE WHEN has_error = 0 THEN 1 ELSE 0 END) as success_count,
    ROUND((SUM(has_error) / COUNT(*)) * 100, 2) as error_rate,
    ROUND((SUM(CASE WHEN has_error = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate,
    AVG(response_time_ms) as avg_response_time
FROM api_statistics
WHERE channel IS NOT NULL AND channel != ''
GROUP BY channel, DATE(created_at)
ORDER BY date DESC, channel ASC;
