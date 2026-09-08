-- ============================================================
-- Bashundhara Community (NeighbourNet) - Database Schema
-- Covers: users, categories, posts  (CommunityUser - Feature 1: Create Post)
-- Import this once via phpMyAdmin -> Import, or run in phpMyAdmin's SQL tab.
-- ============================================================

CREATE DATABASE IF NOT EXISTS webtech_community
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE webtech_community;

-- ------------------------------------------------------------
-- USERS  (matches UML: User class)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin', 'community_user', 'business_person') NOT NULL DEFAULT 'community_user',
    status     ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CATEGORIES  (matches UML: Category class + PostCategoryType enum)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL,
    type        VARCHAR(50) NOT NULL DEFAULT 'PostCategoryType'
) ENGINE=InnoDB;

INSERT INTO categories (name, type) VALUES
    ('Local Discussion', 'PostCategoryType'),
    ('Emergency',         'PostCategoryType'),
    ('Lost & Found',      'PostCategoryType'),
    ('Road & Traffic',    'PostCategoryType'),
    ('Suggestions',       'PostCategoryType'),
    ('Local News',        'PostCategoryType'),
    ('Recommendations',   'PostCategoryType');

-- ------------------------------------------------------------
-- POSTS  (matches UML: Post class)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS posts (
    post_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    category_id INT NOT NULL,
    title       VARCHAR(150) NOT NULL,
    content     TEXT NOT NULL,
    image       VARCHAR(255) DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- LIKES  (matches UML: Like class)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS likes (
    like_id    INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- COMMENTS  (matches UML: Comment class)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    content    TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- REPORTS  (matches UML: Report class, {xor} post/comment target)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reports (
    report_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    post_id    INT NULL,
    comment_id INT NULL,
    reason     VARCHAR(255) NOT NULL,
    status     ENUM('pending', 'reviewed', 'resolved') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_report (user_id, post_id),
    UNIQUE KEY unique_comment_report (user_id, comment_id),
    CONSTRAINT chk_report_target CHECK (
        (post_id IS NOT NULL AND comment_id IS NULL) OR
        (post_id IS NULL AND comment_id IS NOT NULL)
    )
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- NOTICES  (matches UML: Notice class - CommunityUser browses only,
--           Admin.publishNotice() is out of scope here)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notices (
    notice_id   INT AUTO_INCREMENT PRIMARY KEY,
    created_by  INT NULL,
    title       VARCHAR(150) NOT NULL,
    content     TEXT NOT NULL,
    priority    ENUM('General', 'Important', 'Critical') NOT NULL DEFAULT 'General',
    location    VARCHAR(150) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- EVENTS  (matches UML: Event class - CommunityUser browses only)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
    event_id    INT AUTO_INCREMENT PRIMARY KEY,
    created_by  INT NULL,
    title       VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    event_date  DATE NOT NULL,
    event_time  TIME NOT NULL,
    location    VARCHAR(150) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- BUSINESSES  (matches UML: Business class - CommunityUser searches
--              only; management/approval are BusinessPerson/Admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS businesses (
    business_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NULL,
    name          VARCHAR(150) NOT NULL,
    category      VARCHAR(100) NOT NULL,
    address       VARCHAR(255) NOT NULL,
    opening_hours VARCHAR(150) NULL,
    status        ENUM('pending', 'approved', 'rejected', 'suspended') NOT NULL DEFAULT 'approved',
    verified      TINYINT(1) NOT NULL DEFAULT 1,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PASSWORD RESETS  (matches UML: User.resetPassword())
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    reset_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used       TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
