-- Heritage Family Tree Application Database Schema
-- MySQL 5.7+ / 8.0+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- DROP EXISTING TABLES (for clean install)
-- ============================================
DROP TABLE IF EXISTS relationships;
DROP TABLE IF EXISTS person_families;
DROP TABLE IF EXISTS persons;
DROP TABLE IF EXISTS families;
DROP TABLE IF EXISTS users;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE users (
    user_id CHAR(36) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: families
-- ============================================
CREATE TABLE families (
    family_id CHAR(36) PRIMARY KEY,
    family_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_by CHAR(36) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_family_name (family_name),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: persons
-- ============================================
CREATE TABLE persons (
    person_id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NULL,
    fullname VARCHAR(120) NOT NULL,
    gender ENUM('male', 'female', 'other') NULL,
    birthdate DATE NULL,
    deathdate DATE NULL,
    is_alive BOOLEAN DEFAULT TRUE,
    birthplace VARCHAR(200) NULL,
    photo_url VARCHAR(255) NULL,
    notes TEXT NULL,
    created_by CHAR(36) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_fullname (fullname),
    INDEX idx_user_id (user_id),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: person_families (Many-to-Many)
-- ============================================
CREATE TABLE person_families (
    id CHAR(36) PRIMARY KEY,
    person_id CHAR(36) NOT NULL,
    family_id CHAR(36) NOT NULL,
    role_in_family ENUM('bloodline', 'married_in', 'honorary', 'founder') NOT NULL DEFAULT 'bloodline',
    note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(person_id) ON DELETE CASCADE,
    FOREIGN KEY (family_id) REFERENCES families(family_id) ON DELETE CASCADE,
    UNIQUE KEY unique_person_family (person_id, family_id),
    INDEX idx_person_id (person_id),
    INDEX idx_family_id (family_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: relationships
-- ============================================
CREATE TABLE relationships (
    relationship_id CHAR(36) PRIMARY KEY,
    member_id_1 CHAR(36) NOT NULL,
    member_id_2 CHAR(36) NOT NULL,
    relation_type ENUM('parent', 'spouse') NOT NULL,
    started_at DATE NULL,
    ended_at DATE NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id_1) REFERENCES persons(person_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id_2) REFERENCES persons(person_id) ON DELETE CASCADE,
    UNIQUE KEY unique_relationship (member_id_1, member_id_2, relation_type),
    INDEX idx_member_1 (member_id_1),
    INDEX idx_member_2 (member_id_2),
    INDEX idx_relation_type (relation_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;