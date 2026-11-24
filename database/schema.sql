CREATE DATABASE IF NOT EXISTS stevig_klus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stevig_klus;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS quote_request_status_log, quote_request_notes, quote_request_photos, quote_requests,
    reviews, project_photos, projects, service_areas, services, throttle_events, admin_users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE throttle_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(30) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_throttle (action, ip_hash, created_at)
) ENGINE=InnoDB;

CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    icon VARCHAR(20) NOT NULL DEFAULT 'tools',
    intro VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE service_areas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    town VARCHAR(80) NOT NULL UNIQUE,
    distance_km TINYINT UNSIGNED NOT NULL DEFAULT 0,
    sort_order SMALLINT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(90) NOT NULL UNIQUE,
    title VARCHAR(120) NOT NULL,
    town VARCHAR(80) NOT NULL,
    service_id INT UNSIGNED NULL,
    summary VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    duration VARCHAR(40) NULL,
    completed_on DATE NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL,
    INDEX idx_projects_published (is_published, completed_on)
) ENGINE=InnoDB;

CREATE TABLE project_photos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    kind ENUM('before', 'after') NOT NULL,
    file VARCHAR(64) NOT NULL UNIQUE,
    width SMALLINT UNSIGNED NOT NULL,
    height SMALLINT UNSIGNED NOT NULL,
    alt VARCHAR(150) NOT NULL DEFAULT '',
    sort_order SMALLINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_photos_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
    INDEX idx_photos_project (project_id, kind, sort_order)
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(80) NOT NULL,
    town VARCHAR(80) NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    service_id INT UNSIGNED NULL,
    project_id INT UNSIGNED NULL,
    reviewed_on DATE NOT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_reviews_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL,
    CONSTRAINT fk_reviews_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE SET NULL,
    INDEX idx_reviews_published (is_published, reviewed_on)
) ENGINE=InnoDB;

CREATE TABLE quote_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(20) NULL UNIQUE,
    status ENUM('new', 'contacted', 'quote_sent', 'accepted', 'rejected', 'done') NOT NULL DEFAULT 'new',
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    street VARCHAR(120) NOT NULL,
    house_number VARCHAR(10) NOT NULL,
    postcode VARCHAR(7) NOT NULL,
    town VARCHAR(80) NOT NULL,
    service_id INT UNSIGNED NULL,
    service_name VARCHAR(80) NOT NULL,
    description TEXT NOT NULL,
    preferred_period ENUM('asap', 'within_1_month', '1_3_months', '3_6_months', 'flexible') NOT NULL,
    privacy_consent_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_quotes_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL,
    INDEX idx_quotes_status (status, created_at)
) ENGINE=InnoDB;

CREATE TABLE quote_request_photos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_request_id INT UNSIGNED NOT NULL,
    file VARCHAR(64) NOT NULL UNIQUE,
    mime VARCHAR(20) NOT NULL,
    bytes INT UNSIGNED NOT NULL,
    width SMALLINT UNSIGNED NOT NULL,
    height SMALLINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_qphotos_quote FOREIGN KEY (quote_request_id) REFERENCES quote_requests (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quote_request_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_request_id INT UNSIGNED NOT NULL,
    admin_user_id INT UNSIGNED NULL,
    body TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notes_quote FOREIGN KEY (quote_request_id) REFERENCES quote_requests (id) ON DELETE CASCADE,
    CONSTRAINT fk_notes_admin FOREIGN KEY (admin_user_id) REFERENCES admin_users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE quote_request_status_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_request_id INT UNSIGNED NOT NULL,
    admin_user_id INT UNSIGNED NULL,
    old_status VARCHAR(20) NULL,
    new_status VARCHAR(20) NOT NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_quote FOREIGN KEY (quote_request_id) REFERENCES quote_requests (id) ON DELETE CASCADE,
    CONSTRAINT fk_log_admin FOREIGN KEY (admin_user_id) REFERENCES admin_users (id) ON DELETE SET NULL
) ENGINE=InnoDB;
