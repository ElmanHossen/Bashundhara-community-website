-- ============================================================
-- Migration: Add "likes" table  (CommunityUser - Feature 4: Like Post)
-- Matches UML: Like class (likeId, postId, userId, createdAt)
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing users/categories/posts data.
-- ============================================================

USE webtech_community;

CREATE TABLE IF NOT EXISTS likes (
    like_id    INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- a user can only like a given post once (prevents duplicate likes,
    -- and lets us tell "already liked" apart from "not liked" with one query)
    UNIQUE KEY unique_like (post_id, user_id),

    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
