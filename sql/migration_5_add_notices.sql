-- ============================================================
-- Migration: Add "notices" table  (CommunityUser - Feature 7: Browse Notices)
-- Matches UML: Notice class (noticeId, createdBy, title, content,
--              priority, location, createdAt, expiryDate)
--
-- NOTE: Creating/publishing notices is an Admin capability
-- (Admin.publishNotice()) and is intentionally NOT built here -
-- that is out of scope for the CommunityUser module. This
-- migration seeds a few sample notices so there is something
-- for CommunityUser.browseNotices() to actually display.
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing users/categories/posts/likes/comments/reports data.
-- ============================================================

USE webtech_community;

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

-- Sample seed data (created_by left NULL - no Admin module exists yet
-- to actually publish these; this is just demo content to browse)
INSERT INTO notices (title, content, priority, location, expiry_date) VALUES
    ('Road Closure Notice',
     'MG Road will be closed from 17 May to 19 May for utility works. Please use alternate routes.',
     'Critical', 'MG Road, City Center', '2026-09-19'),

    ('Emergency Alert',
     'Heavy rain forecast for the next 24 hours. Avoid low-lying areas and stay safe.',
     'Critical', 'Neighbourhood Wide', '2026-09-10'),

    ('Elevator Maintenance',
     'Elevator maintenance at Oakview Residency on 18 May, 10 AM - 2 PM.',
     'Important', 'Oakview Residency', '2026-09-12'),

    ('Community Meeting',
     'Monthly community meeting on 25 May at 6 PM at Community Hall. All residents welcome.',
     'General', 'Community Hall', '2026-09-25'),

    ('Water Supply Notice',
     'Intermittent water supply on 20 May due to pipeline maintenance.',
     'General', 'Sector 14 & 15', '2026-09-20');
