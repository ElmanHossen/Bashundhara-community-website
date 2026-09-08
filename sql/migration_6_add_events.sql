-- ============================================================
-- Migration: Add "events" table  (CommunityUser - Feature 8: Browse Events)
-- Matches UML: Event class (eventId, createdBy, title, description,
--              date, time, location)
--
-- NOTE: Creating/publishing events is an Admin capability
-- (Admin.manageEvent(), Event.createEvent()/publishEvent()) and is
-- intentionally NOT built here - out of scope for CommunityUser.
-- This migration seeds sample events so CommunityUser.browseEvents()
-- has real data to display.
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing data.
-- ============================================================

USE webtech_community;

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

-- Sample seed data (created_by left NULL - no Admin module exists yet)
INSERT INTO events (title, description, event_date, event_time, location) VALUES
    ('Community Tree Plantation Drive',
     'Join us this Sunday for a community tree plantation drive. Bring your friends and let''s grow together!',
     '2026-09-20', '07:00:00', 'Sunrise Park'),

    ('NeighbourNet Food Fiesta',
     'A celebration of local food from businesses across the neighbourhood. Come hungry!',
     '2026-09-27', '18:00:00', 'Community Ground'),

    ('Blood Donation Camp',
     'Donate blood and help save lives. Organized in partnership with City Health Center.',
     '2026-09-14', '09:00:00', 'City Health Center'),

    ('Yoga in the Park',
     'A relaxing morning yoga session open to all residents, beginners welcome.',
     '2026-09-13', '07:00:00', 'Green Park'),

    ('Local Cultural Evening',
     'An evening of local music, dance, and food celebrating our diverse community.',
     '2026-10-04', '18:30:00', 'Community Hall');
