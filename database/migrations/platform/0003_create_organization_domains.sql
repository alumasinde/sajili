CREATE TABLE IF NOT EXISTS organization_domains (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    domain VARCHAR(253) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_organization_domains_domain (domain),
    KEY idx_organization_domains_org (organization_id),
    KEY idx_organization_domains_status (status),
    CONSTRAINT fk_organization_domains_organization
        FOREIGN KEY (organization_id) REFERENCES organizations(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
