CREATE DATABASE IF NOT EXISTS community_portal;
USE community_portal;

CREATE TABLE IF NOT EXISTS users (
    userId VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address VARCHAR(255),
    pass VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS post_categories (
    categoryId INT AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS posts (
    postId INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(50) NOT NULL,
    categoryId INT,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId),
    FOREIGN KEY (categoryId) REFERENCES post_categories(categoryId)
);

CREATE TABLE IF NOT EXISTS comments (
    commentId INT AUTO_INCREMENT PRIMARY KEY,
    postId INT NOT NULL,
    userId VARCHAR(50) NOT NULL,
    comment TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (postId) REFERENCES posts(postId),
    FOREIGN KEY (userId) REFERENCES users(userId)
);

CREATE TABLE IF NOT EXISTS likes (
    likeId INT AUTO_INCREMENT PRIMARY KEY,
    postId INT NOT NULL,
    userId VARCHAR(50) NOT NULL,
    FOREIGN KEY (postId) REFERENCES posts(postId),
    FOREIGN KEY (userId) REFERENCES users(userId)
);

CREATE TABLE IF NOT EXISTS reports (
    reportId INT AUTO_INCREMENT PRIMARY KEY,
    reporterId VARCHAR(50) NOT NULL,
    postId INT,
    reason VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporterId) REFERENCES users(userId),
    FOREIGN KEY (postId) REFERENCES posts(postId)
);

CREATE TABLE IF NOT EXISTS businesses (
    businessId INT AUTO_INCREMENT PRIMARY KEY,
    ownerId VARCHAR(50) NOT NULL,
    businessName VARCHAR(150) NOT NULL,
    description TEXT,
    address VARCHAR(255),
    phone VARCHAR(20),
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ownerId) REFERENCES users(userId)
);

CREATE TABLE IF NOT EXISTS product_categories (
    pCategoryId INT AUTO_INCREMENT PRIMARY KEY,
    pCategoryName VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    productId INT AUTO_INCREMENT PRIMARY KEY,
    businessId INT NOT NULL,
    pCategoryId INT,
    productName VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (businessId) REFERENCES businesses(businessId),
    FOREIGN KEY (pCategoryId) REFERENCES product_categories(pCategoryId)
);

CREATE TABLE IF NOT EXISTS cart (
    cartId INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(50) NOT NULL,
    productId INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (userId) REFERENCES users(userId),
    FOREIGN KEY (productId) REFERENCES products(productId)
);

CREATE TABLE IF NOT EXISTS orders (
    orderId INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(50) NOT NULL,
    totalAmount DECIMAL(10,2) NOT NULL,
    shippingAddress VARCHAR(255),
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    orderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId)
);

CREATE TABLE IF NOT EXISTS order_items (
    orderItemId INT AUTO_INCREMENT PRIMARY KEY,
    orderId INT NOT NULL,
    productId INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (orderId) REFERENCES orders(orderId),
    FOREIGN KEY (productId) REFERENCES products(productId)
);

CREATE TABLE IF NOT EXISTS notices (
    noticeId INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'notice',
    eventDate DATE,
    createdBy VARCHAR(50) NOT NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (createdBy) REFERENCES users(userId)
);

CREATE TABLE IF NOT EXISTS homepage_content (
    contentId INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (userId, name, email, phone, address, pass, role) VALUES
('admin1', 'System Admin', 'admin@portal.com', '01700000000', 'Dhaka', 'admin123', 'admin'),
('galib', 'Galib Hasan', 'galib@portal.com', '01711111111', 'Dhaka', '1234', 'user'),
('amit', 'Amit Roy', 'amit@portal.com', '01722222222', 'Dhaka', '1234', 'user'),
('shop1', 'Rahim Store', 'rahim@portal.com', '01733333333', 'Dhaka', '1234', 'business');

INSERT INTO post_categories (categoryName) VALUES
('General'), ('Help Needed'), ('Buy & Sell'), ('Event'), ('Lost & Found');

INSERT INTO product_categories (pCategoryName) VALUES
('Grocery'), ('Electronics'), ('Clothing'), ('Books'), ('Home & Kitchen');

INSERT INTO businesses (ownerId, businessName, description, address, phone, status) VALUES
('shop1', 'Rahim General Store', 'Daily grocery and household items', 'Banani, Dhaka', '01733333333', 'approved');

INSERT INTO homepage_content (section, title, content) VALUES
('hero', 'Welcome to our Community Portal', 'Connect with neighbours, share news and shop from local businesses.'),
('about', 'About Us', 'This portal brings the community and local shops together in one place.');
