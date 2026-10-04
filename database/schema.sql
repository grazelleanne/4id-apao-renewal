CREATE DATABASE IF NOT EXISTS apao_vanilla CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE apao_vanilla;

CREATE TABLE IF NOT EXISTS users (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL,
 email VARCHAR(255) NOT NULL UNIQUE, contact_number VARCHAR(30) NULL,
 password VARCHAR(255) NOT NULL, role VARCHAR(32) NOT NULL DEFAULT 'viewer',
 is_active TINYINT(1) NOT NULL DEFAULT 1, session_version INT UNSIGNED NOT NULL DEFAULT 1,
 last_login_at TIMESTAMP NULL, remember_token VARCHAR(100) NULL,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, KEY users_role_active(role,is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS personnel (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, item_number INT NOT NULL UNIQUE,
 date_of_validity DATE NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL,
 middle_name VARCHAR(255) NULL, `rank` VARCHAR(255) NULL, afp_serial_number VARCHAR(255) NULL UNIQUE,
 afos_mos VARCHAR(255) NULL, branch VARCHAR(255) NULL, email VARCHAR(255) NULL UNIQUE,
 contact_number VARCHAR(20) NULL UNIQUE, issued_by VARCHAR(255) NULL, date_of_birth DATE NULL,
 citizenship VARCHAR(100) NOT NULL DEFAULT 'Filipino', civil_status VARCHAR(30) NULL,
 pistol_nomenclature VARCHAR(255) NULL, pistol_serial_number VARCHAR(255) NULL UNIQUE,
 pistol_type VARCHAR(255) NULL, par_number VARCHAR(255) NULL, last_renewed_at DATE NULL,
 qty_ammo INT NOT NULL DEFAULT 0, unit VARCHAR(255) NULL,
 approved_status VARCHAR(20) NOT NULL DEFAULT 'pending', status VARCHAR(32) NOT NULL DEFAULT 'active',
 ics_status VARCHAR(32) NOT NULL DEFAULT 'inspection', date_approved DATE NULL,
 is_archived TINYINT(1) NOT NULL DEFAULT 0, archived_at DATE NULL, photo LONGTEXT NULL,
 signature LONGTEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
 KEY personnel_archive_status(archived_at,approved_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspections (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 personnel_id BIGINT UNSIGNED NOT NULL, item_number INT NOT NULL,
 afp_serial_number VARCHAR(255) NULL, pistol_type VARCHAR(255) NULL, date_registered DATE NULL,
 status VARCHAR(32) NOT NULL DEFAULT 'pending', barrel VARCHAR(255) NULL, slide VARCHAR(255) NULL,
 recoil_spring_assembly VARCHAR(255) NULL, firing_pin VARCHAR(255) NULL, spacer_sleeve VARCHAR(255) NULL,
 firing_pin_spring VARCHAR(255) NULL, spring_cups VARCHAR(255) NULL, firing_pin_safety VARCHAR(255) NULL,
 firing_pin_safety_spring VARCHAR(255) NULL, extractor VARCHAR(255) NULL,
 extractor_depressor_plunger VARCHAR(255) NULL, extractor_depressor_plunger_spring VARCHAR(255) NULL,
 trigger_loaded_bearing VARCHAR(255) NULL, rear_sight VARCHAR(255) NULL, front_sight VARCHAR(255) NULL,
 front_sight_screw VARCHAR(255) NULL, frame VARCHAR(255) NULL, magazine VARCHAR(255) NULL,
 magazine_catch_spring VARCHAR(255) NULL, magazine_catch VARCHAR(255) NULL,
 slide_lock VARCHAR(255) NULL, slide_lock_spring VARCHAR(255) NULL, slide_cover_plate VARCHAR(255) NULL,
 connector VARCHAR(255) NULL, trigger_mechanism_housing VARCHAR(255) NULL, `trigger` VARCHAR(255) NULL,
 trigger_spring VARCHAR(255) NULL, trigger_bar VARCHAR(255) NULL, trigger_with_trigger_bar VARCHAR(255) NULL,
 slide_stop_lever VARCHAR(255) NULL, trigger_pin VARCHAR(255) NULL, trigger_housing_pin VARCHAR(255) NULL,
 locking_block VARCHAR(255) NULL, locking_block_pin VARCHAR(255) NULL, guide_rod VARCHAR(255) NULL,
 remarks TEXT NULL, next_renewal_date DATE NULL,
 inspected_by_name VARCHAR(255) NULL, inspected_by_rank VARCHAR(255) NULL,
 inspected_by_position VARCHAR(255) NULL, inspected_by_sig LONGTEXT NULL,
 witnessed_by_name VARCHAR(255) NULL, witnessed_by_rank VARCHAR(255) NULL,
 witnessed_by_position VARCHAR(255) NULL, witnessed_by_sig LONGTEXT NULL,
 approved_by_name VARCHAR(255) NULL, approved_by_rank VARCHAR(255) NULL,
 approved_by_position VARCHAR(255) NULL, approved_by_sig LONGTEXT NULL,
 noted_by_name VARCHAR(255) NULL, noted_by_rank VARCHAR(255) NULL,
 noted_by_position VARCHAR(255) NULL, noted_by_sig LONGTEXT NULL,
 inspected_by_user_id BIGINT UNSIGNED NULL, inspected_at TIMESTAMP NULL,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
 KEY inspections_item_number_id(item_number,id),
 CONSTRAINT inspections_personnel_fk FOREIGN KEY(personnel_id) REFERENCES personnel(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS renewal_history (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, item_number INT NOT NULL,
 action VARCHAR(255) NOT NULL DEFAULT 'renewed', date_of_validity DATE NULL,
 previous_validity DATE NULL, inspected_by VARCHAR(255) NULL, remarks TEXT NULL,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, KEY renewal_history_item(item_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS renewal_transactions (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, source_history_id BIGINT UNSIGNED NULL UNIQUE,
 personnel_id BIGINT UNSIGNED NOT NULL, item_number INT NOT NULL, par_number VARCHAR(255) NULL,
 renewal_date DATE NOT NULL, new_validity_date DATE NOT NULL, old_validity_date DATE NULL,
 status VARCHAR(32) NOT NULL DEFAULT 'submitted', processed_by VARCHAR(255) NULL,
 processed_by_user_id BIGINT UNSIGNED NULL, remarks TEXT NULL, created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL, KEY renewal_transactions_personnel(personnel_id,status),
 CONSTRAINT renewal_transactions_personnel_fk FOREIGN KEY(personnel_id) REFERENCES personnel(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_acknowledgement_receipts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, par_number VARCHAR(255) NOT NULL UNIQUE,
 personnel_id BIGINT UNSIGNED NOT NULL, previous_par_id BIGINT UNSIGNED NULL, unit VARCHAR(255) NULL,
 firearm VARCHAR(255) NOT NULL, firearm_serial_number VARCHAR(255) NULL,
 firearm_quantity INT UNSIGNED NOT NULL DEFAULT 1, firearm_unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
 ammunition_quantity INT UNSIGNED NOT NULL DEFAULT 0, ammunition_unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
 equipment_items JSON NULL, status VARCHAR(32) NOT NULL DEFAULT 'Active',
 issued_date DATE NOT NULL, valid_until DATE NULL, issued_by VARCHAR(255) NULL,
 issued_by_personnel_id BIGINT UNSIGNED NULL, approved_by VARCHAR(255) NULL,
 approved_by_personnel_id BIGINT UNSIGNED NULL, remarks TEXT NULL, receiver_signature LONGTEXT NULL,
 issued_by_signature LONGTEXT NULL, approved_by_signature LONGTEXT NULL, replacement_reason TEXT NULL,
 replaced_at TIMESTAMP NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
 KEY par_personnel_status(personnel_id,status),
 CONSTRAINT par_personnel_fk FOREIGN KEY(personnel_id) REFERENCES personnel(id) ON DELETE RESTRICT,
 CONSTRAINT par_previous_fk FOREIGN KEY(previous_par_id) REFERENCES property_acknowledgement_receipts(id) ON DELETE SET NULL,
 CONSTRAINT par_issuer_fk FOREIGN KEY(issued_by_personnel_id) REFERENCES personnel(id) ON DELETE SET NULL,
 CONSTRAINT par_approver_fk FOREIGN KEY(approved_by_personnel_id) REFERENCES personnel(id) ON DELETE SET NULL,
 CONSTRAINT par_creator_fk FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
 CONSTRAINT par_updater_fk FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, type VARCHAR(255) NOT NULL,
 title VARCHAR(255) NOT NULL, message TEXT NOT NULL, personnel_name VARCHAR(255) NULL,
 personnel_id INT NULL, read_by_admin TINYINT(1) NOT NULL DEFAULT 0,
 read_by_staff TINYINT(1) NOT NULL DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets_otp (
 email VARCHAR(255) NOT NULL PRIMARY KEY,
 otp VARCHAR(255) NOT NULL COMMENT 'password_hash output; never store plaintext OTP',
 expires_at TIMESTAMP NOT NULL, created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL,
 user_name VARCHAR(255) NULL, user_role VARCHAR(255) NULL, action VARCHAR(255) NULL,
 target VARCHAR(255) NULL, model_type VARCHAR(255) NULL, model_id VARCHAR(255) NULL,
 subject VARCHAR(255) NULL, old_values JSON NULL, new_values JSON NULL, description TEXT NULL,
 ip_address VARCHAR(45) NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL, KEY audit_user_created(user_id,created_at), KEY audit_action_created(action,created_at),
 CONSTRAINT audit_user_fk FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ics_settings (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, office_name VARCHAR(255) NULL,
 agency_name VARCHAR(255) NULL, unit_address VARCHAR(255) NULL, unit_code VARCHAR(255) NULL,
 chief_officer_name VARCHAR(255) NULL, chief_officer_position VARCHAR(255) NULL,
 issued_by_name VARCHAR(255) NULL, issued_by_position VARCHAR(255) NULL,
 pistol_unit_cost VARCHAR(255) NULL, ammo_unit_cost VARCHAR(255) NULL,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS personnel_data_conflicts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, personnel_id BIGINT UNSIGNED NOT NULL,
 item_number INT UNSIGNED NULL, field VARCHAR(64) NOT NULL, conflicting_value VARCHAR(255) NOT NULL,
 resolution_status VARCHAR(20) NOT NULL DEFAULT 'pending', created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
 UNIQUE KEY personnel_conflict_field(personnel_id,field), KEY conflict_status(field,resolution_status),
 CONSTRAINT personnel_conflict_fk FOREIGN KEY(personnel_id) REFERENCES personnel(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
