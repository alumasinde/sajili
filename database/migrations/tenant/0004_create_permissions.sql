CREATE TABLE IF NOT EXISTS permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_permissions_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO permissions (name, description) VALUES
('dashboard.view', 'View the administration dashboard'),
('users.view', 'View users'),
('users.create', 'Create users'),
('users.update', 'Update users'),
('users.deactivate', 'Deactivate users'),
('roles.view', 'View roles'),
('roles.manage', 'Create and update roles'),
('departments.view', 'View departments'),
('departments.manage', 'Create and update departments'),
('departments.assign_hod', 'Assign a department HOD'),
('employees.view', 'View employees'),
('employees.create', 'Create employees'),
('employees.update', 'Update employees'),
('employees.import', 'Import employees'),
('onboarding.view', 'View onboarding cases'),
('onboarding.create', 'Create onboarding cases'),
('onboarding.sign', 'Sign own onboarding documents'),
('onboarding.approve', 'Approve onboarding cases'),
('assets.view', 'View assets'),
('assets.create', 'Create assets'),
('assets.assign', 'Assign assets'),
('assets.return', 'Return assets');
