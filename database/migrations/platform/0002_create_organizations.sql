CREATE TABLE IF NOT EXISTS organizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    public_id CHAR(32) NOT NULL,
    slug VARCHAR(80) NOT NULL,
    name VARCHAR(160) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    database_mode VARCHAR(20) NOT NULL DEFAULT 'shared',
    database_config_encrypted LONGTEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_organizations_public_id (public_id),
    UNIQUE KEY uq_organizations_slug (slug),
    KEY idx_organizations_status (status),
    KEY idx_organizations_database_mode (database_mode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
