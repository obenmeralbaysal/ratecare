-- ============================================
-- RateCare API Cache & Statistics System
-- Migration 001: Create Cache Tables
-- Date: 2025-10-23
-- ============================================

-- 1. Settings için cache-time ekleme
-- ============================================
INSERT INTO settings (`key`, `value`, `description`, `created_at`, `updated_at`) 
VALUES ('caching-time', '30', 'API cache süresi (dakika cinsinden)', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = '30', `description` = 'API cache süresi (dakika cinsinden)';

-- 2. API Cache Tablosu
-- ============================================
CREATE TABLE IF NOT EXISTS `api_cache` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cache_key` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Unique cache identifier',
    `widget_code` VARCHAR(50) NOT NULL COMMENT 'Widget kodu',
    `parameters` JSON NOT NULL COMMENT 'Request parametreleri (currency, dates, etc)',
    `response_data` JSON NOT NULL COMMENT 'Cached API response',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL COMMENT 'Cache expiry time',
    INDEX `idx_cache_key` (`cache_key`),
    INDEX `idx_widget_code` (`widget_code`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='API response cache for performance optimization';

-- 3. API Statistics Tablosu
-- ============================================
CREATE TABLE IF NOT EXISTS `api_statistics` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `widget_code` VARCHAR(50) NOT NULL COMMENT 'Widget kodu',
    `request_date` DATE NOT NULL COMMENT 'Request tarihi',
    `request_time` TIME NOT NULL COMMENT 'Request saati',
    `parameters` JSON NOT NULL COMMENT 'Request parametreleri',
    `cache_hit_type` ENUM('full', 'partial', 'miss') NOT NULL DEFAULT 'miss' COMMENT 'Cache durumu: full=tümü cache, partial=bazısı cache, miss=hiç cache yok',
    `cached_platforms` JSON NULL COMMENT 'Cache den okunan platformlar',
    `requested_platforms` JSON NULL COMMENT 'API ye istek atılan platformlar',
    `updated_platforms` JSON NULL COMMENT 'Cache e eklenen/güncellenen platformlar',
    `response_time_ms` INT(11) NULL COMMENT 'Response süresi (milisaniye)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_widget_code` (`widget_code`),
    INDEX `idx_request_date` (`request_date`),
    INDEX `idx_cache_hit_type` (`cache_hit_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='API request statistics with cache performance tracking';

-- 4. API Statistics Summary Tablosu (Daily aggregation)
-- ============================================
CREATE TABLE IF NOT EXISTS `api_statistics_summary` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL UNIQUE COMMENT 'Özet tarihi',
    `total_requests` INT(11) DEFAULT 0 COMMENT 'Toplam istek sayısı',
    `cache_full_hits` INT(11) DEFAULT 0 COMMENT 'Tam cache hit sayısı',
    `cache_partial_hits` INT(11) DEFAULT 0 COMMENT 'Kısmi cache hit sayısı',
    `cache_misses` INT(11) DEFAULT 0 COMMENT 'Cache miss sayısı',
    `total_platforms_requested` INT(11) DEFAULT 0 COMMENT 'Toplam platform request sayısı',
    `total_platforms_from_cache` INT(11) DEFAULT 0 COMMENT 'Cache den okunan toplam platform sayısı',
    `channels_usage` JSON NULL COMMENT 'Platform bazlı kullanım istatistikleri',
    `avg_response_time_ms` INT(11) NULL COMMENT 'Ortalama response süresi',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Daily aggregated API statistics';

-- ============================================
-- Migration Complete
-- ============================================

SELECT 'Migration 001: Cache tables created successfully!' AS Status;
