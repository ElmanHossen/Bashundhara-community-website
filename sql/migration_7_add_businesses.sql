

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

INSERT INTO businesses (name, category, address, opening_hours, status, verified) VALUES
    ('Greenleaf Cafe', 'Cafe', '12 Oak Avenue, Green Park', 'Mon - Sun: 7:00 AM - 5:00 PM', 'approved', 1),
    ('Caring Hands Pharmacy', 'Pharmacy', '8 Sunrise Parade, Sunrise Park', 'Mon - Sat: 8:00 AM - 8:00 PM', 'approved', 1),
    ('FitWell Gym', 'Fitness Center', '3 Power Street, Sunrise Park', 'Mon - Sun: 5:00 AM - 10:00 PM', 'approved', 1),
    ('Sunrise Fresh Mart', 'Grocery Store', '15 Market Square, Green Park', 'Mon - Sun: 7:00 AM - 9:00 PM', 'approved', 1),
    ('Tech Zone Electronics', 'Electronics', 'Unit 6, City Center Mall, Green Park', 'Mon - Sat: 9:00 AM - 7:00 PM', 'approved', 1),
    ('Green Park Family Clinic', 'Clinic', '22 Health Way, Green Park', 'Mon - Fri: 8:30 AM - 6:00 PM', 'approved', 1);
