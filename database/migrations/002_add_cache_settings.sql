-- Add Cache and Circuit Breaker Settings
-- These settings control cache behavior and circuit breaker pattern

-- Cache Settings
INSERT INTO settings (`key`, `value`, `description`) VALUES
('cache-statistics-retention-days', '30', 'İstatistik detaylarını kaç gün sakla (30 gün önerili)')
ON DUPLICATE KEY UPDATE 
    `description` = 'İstatistik detaylarını kaç gün sakla (30 gün önerili)';

INSERT INTO settings (`key`, `value`, `description`) VALUES
('cache-cleanup-enabled', '1', 'Otomatik cache temizleme aktif mi? (1=aktif, 0=pasif)')
ON DUPLICATE KEY UPDATE 
    `description` = 'Otomatik cache temizleme aktif mi? (1=aktif, 0=pasif)';

INSERT INTO settings (`key`, `value`, `description`) VALUES
('cache-warming-enabled', '0', 'Cache warming aktif mi? (1=aktif, 0=pasif)')
ON DUPLICATE KEY UPDATE 
    `description` = 'Cache warming aktif mi? (1=aktif, 0=pasif)';

INSERT INTO settings (`key`, `value`, `description`) VALUES
('cache-warming-widget-count', '10', 'Cache warming için kaç widget yüklensin?')
ON DUPLICATE KEY UPDATE 
    `description` = 'Cache warming için kaç widget yüklensin?';

-- Circuit Breaker Settings
INSERT INTO settings (`key`, `value`, `description`) VALUES
('circuit-breaker-enabled', '1', 'Circuit breaker pattern aktif mi? (1=aktif, 0=pasif)')
ON DUPLICATE KEY UPDATE 
    `description` = 'Circuit breaker pattern aktif mi? (1=aktif, 0=pasif)';

INSERT INTO settings (`key`, `value`, `description`) VALUES
('circuit-breaker-failure-threshold', '5', 'Kaç başarısız denemeden sonra circuit açılsın?')
ON DUPLICATE KEY UPDATE 
    `description` = 'Kaç başarısız denemeden sonra circuit açılsın?';

INSERT INTO settings (`key`, `value`, `description`) VALUES
('circuit-breaker-timeout-seconds', '600', 'Circuit açıkken kaç saniye beklensin? (600=10 dakika)')
ON DUPLICATE KEY UPDATE 
    `description` = 'Circuit açıkken kaç saniye beklensin? (600=10 dakika)';

INSERT INTO settings (`key`, `value`, `description`) VALUES
('circuit-breaker-half-open-requests', '3', 'Half-open durumunda kaç test isteği atılsın?')
ON DUPLICATE KEY UPDATE 
    `description` = 'Half-open durumunda kaç test isteği atılsın?';

-- Platform-specific circuit breaker settings
INSERT INTO settings (`key`, `value`, `description`) VALUES
('circuit-breaker-platforms', 'booking,hotels,etstur,tatilsepeti,otelz,sabeeapp,odamax', 'Circuit breaker hangi platformlarda aktif? (virgülle ayrılmış)')
ON DUPLICATE KEY UPDATE 
    `description` = 'Circuit breaker hangi platformlarda aktif? (virgülle ayrılmış)';

-- Circuit Breaker State Table
CREATE TABLE IF NOT EXISTS circuit_breaker_state (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(50) NOT NULL UNIQUE,
    state ENUM('closed', 'open', 'half_open') DEFAULT 'closed',
    failure_count INT DEFAULT 0,
    last_failure_time TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    last_success_time TIMESTAMP NULL,
    half_open_attempts INT DEFAULT 0,
    total_requests INT DEFAULT 0,
    total_failures INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_platform (platform),
    INDEX idx_state (state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial states for all platforms
INSERT INTO circuit_breaker_state (platform, state) VALUES
('booking', 'closed'),
('hotels', 'closed'),
('etstur', 'closed'),
('tatilsepeti', 'closed'),
('otelz', 'closed'),
('sabeeapp', 'closed'),
('odamax', 'closed')
ON DUPLICATE KEY UPDATE state = state;
