

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

    UNIQUE KEY unique_post_report (user_id, post_id),
    UNIQUE KEY unique_comment_report (user_id, comment_id),

    CONSTRAINT chk_report_target CHECK (
        (post_id IS NOT NULL AND comment_id IS NULL) OR
        (post_id IS NULL AND comment_id IS NOT NULL)
    )
) ENGINE=InnoDB;
