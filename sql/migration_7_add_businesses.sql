-- ============================================================
-- Migration: Add "businesses" table  (CommunityUser - Search Businesses)
-- Matches UML: Business class (businessId, userId, name, logo, category,
--              address, openingHours, status, verified)
--
-- NOTE: Creating/managing a business profile is a BusinessPerson
-- capability (manageBusinessProfile(), submitForApproval()) and
-- approving it is an Admin capability (approveBusiness()). Both are
-- intentionally NOT built here - out of scope for CommunityUser.
-- This migration seeds a few already-approved sample businesses so
-- CommunityUser can search a real business directory.
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing data.
-- ============================================================

USE webtech_community;

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

-- Sample seed data - all pre-approved so they're visible in search
-- (user_id left NULL - no BusinessPerson module exists yet to own these)
INSERT INTO businesses (name, category, address, opening_hours, status, verified) VALUES
    ('Greenleaf Cafe', 'Cafe', '12 Oak Avenue, Green Park', 'Mon - Sun: 7:00 AM - 5:00 PM', 'approved', 1),
    ('Caring Hands Pharmacy', 'Pharmacy', '8 Sunrise Parade, Sunrise Park', 'Mon - Sat: 8:00 AM - 8:00 PM', 'approved', 1),
    ('FitWell Gym', 'Fitness Center', '3 Power Street, Sunrise Park', 'Mon - Sun: 5:00 AM - 10:00 PM', 'approved', 1),
    ('Sunrise Fresh Mart', 'Grocery Store', '15 Market Square, Green Park', 'Mon - Sun: 7:00 AM - 9:00 PM', 'approved', 1),
    ('Tech Zone Electronics', 'Electronics', 'Unit 6, City Center Mall, Green Park', 'Mon - Sat: 9:00 AM - 7:00 PM', 'approved', 1),
    ('Green Park Family Clinic', 'Clinic', '22 Health Way, Green Park', 'Mon - Fri: 8:30 AM - 6:00 PM', 'approved', 1);
