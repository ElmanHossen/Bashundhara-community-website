-- ============================================================
-- Migration: Add "reports" table  (CommunityUser - Feature 6: Report Content)
-- Matches UML: Report class (reportId, userId, postId[0..1], commentId[0..1],
--              reason, status: ReportStatus, createdAt)
--
-- {xor} constraint from the UML: a report targets exactly ONE of
-- a Post OR a Comment - enforced below with a CHECK constraint,
-- and double-checked in PHP (community/ajax/report_content.php)
-- in case your MySQL/MariaDB version ignores CHECK constraints.
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing users/categories/posts/likes/comments data.
-- ============================================================

USE webtech_community;

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

    -- a user can't submit two reports for the exact same post/comment
    UNIQUE KEY unique_post_report (user_id, post_id),
    UNIQUE KEY unique_comment_report (user_id, comment_id),

    -- {xor} from the UML: exactly one of post_id / comment_id must be set
    CONSTRAINT chk_report_target CHECK (
        (post_id IS NOT NULL AND comment_id IS NULL) OR
        (post_id IS NULL AND comment_id IS NOT NULL)
    )
) ENGINE=InnoDB;
