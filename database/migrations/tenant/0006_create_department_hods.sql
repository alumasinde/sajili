CREATE TABLE IF NOT EXISTS department_hods (
    department_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (department_id),
    UNIQUE KEY uq_department_hods_user (user_id),
    CONSTRAINT fk_department_hods_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_department_hods_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
