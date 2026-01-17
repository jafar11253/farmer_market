-- ======================================================
-- FULL DATABASE DUMP: farm_market
-- Compatible with MySQL 8+
-- ======================================================
-- NOTE: Select your database in phpMyAdmin first, then import this file

SET FOREIGN_KEY_CHECKS = 0;

-- =====================
-- USERS
-- =====================
CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL,
  role ENUM('buyer','farmer','admin') NOT NULL,
  district VARCHAR(100) DEFAULT NULL,
  profile_info TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY username (username),
  UNIQUE KEY email (email)
) ENGINE=InnoDB;

INSERT INTO users VALUES
(5,'admin','$2y$10$K.0f0ZbLqKxZfP6zqJ6K.Ow.3fW5fE8wZfP6zqJ6K.Ow.3fW5fE8w','admin@farmmarket.com','admin','Dhaka','Platform Administrator','2025-11-26 14:06:50'),
(6,'jafar','$2y$10$33hM8WnwEML9fvbobMKmbOqM0hNa6380M30PCbqHFSvilRqZDkFtW','jafaremon2003@gmail.com','farmer','Dhaka','just a farmer','2025-11-26 14:34:31'),
(7,'buyer','$2y$10$invHpB98/3g9dlQ4qusyT.0FozsOOFO3rQ7nvW2rUq5yzkflvsUzm','buyer@gmail.com','buyer','Dhaka',NULL,'2025-11-26 14:41:31'),
(9,'admin1','$2y$10$hcb63N0bPiYdhIg.sFFwnu8aet.QXpJIX9gy4PZy3d7wjLYS9EMTy','admin1@farmmarket.com','admin','Dhaka','Platform Administrator','2025-11-26 15:07:50'),
(12,'Sadik','$2y$10$29nH/cnIa1k6B3RNXijMJ.Cf5qO2Wpdr/nLUlIsEbu9mD9Zp8QbEi','sadik@gmail.com','farmer','Rajshahi',NULL,'2025-12-14 08:23:47'),
(13,'Arman','$2y$10$MZFHjojs7Rvk2ssfpuCp5evci4VDZGmVQarxfb0YT1MZqTalRqSkO','arman@gmail.com','farmer','Chattogram','Farmer','2025-12-14 08:28:02'),
(14,'Abdullah','$2y$10$CwHHc0zGp.vvQ/kiihT4tedKZc2XXGjN2Hwu/Wqn3..8WS71E9ACq','abdullah@gmail.com','farmer','Barishal',NULL,'2025-12-14 09:44:12');

-- =====================
-- CATEGORIES
-- =====================
CREATE TABLE categories (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO categories VALUES
(1,'Vegetables'),
(2,'Fruits'),
(3,'Grains'),
(4,'Dairy');

-- =====================
-- LISTINGS
-- =====================
CREATE TABLE listings (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  category_id INT NOT NULL,
  title VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2),
  quantity INT NOT NULL,
  images TEXT,
  type ENUM('fixed','auction') NOT NULL,
  starting_bid DECIMAL(10,2),
  min_increment DECIMAL(10,2) DEFAULT 5.00,
  auction_start DATETIME,
  auction_end DATETIME,
  district VARCHAR(100),
  status ENUM('active','sold','ended') DEFAULT 'active',
  winner_id INT,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (winner_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO listings VALUES
(7,6,2,'Fresh Apple','Organic apples',200.00,40,'["assets/img/uploads/apple.jpg"]','fixed',NULL,5,NULL,NULL,'Dhaka','active',NULL),
(8,6,2,'Fresh Orange','Fresh fruits',250.00,40,'["assets/img/uploads/orange.jpg"]','fixed',NULL,5,NULL,NULL,'Dhaka','active',NULL),
(10,12,4,'Milk','Fresh Milk',100.00,15,'["assets/img/uploads/milk.jpg"]','fixed',NULL,5,NULL,NULL,'Rajshahi','active',NULL);

-- =====================
-- BIDS
-- =====================
CREATE TABLE bids (
  id INT NOT NULL AUTO_INCREMENT,
  listing_id INT NOT NULL,
  user_id INT NOT NULL,
  bid_amount DECIMAL(10,2) NOT NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (listing_id) REFERENCES listings(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- =====================
-- CART
-- =====================
CREATE TABLE cart (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  listing_id INT NOT NULL,
  quantity INT NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (listing_id) REFERENCES listings(id)
) ENGINE=InnoDB;

-- =====================
-- ORDERS
-- =====================
CREATE TABLE orders (
  id INT NOT NULL AUTO_INCREMENT,
  listing_id INT NOT NULL,
  user_id INT NOT NULL,
  quantity INT,
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('pending','shipped','delivered') DEFAULT 'pending',
  commission_deducted DECIMAL(10,2) NOT NULL,
  payment_status ENUM('pending','paid','failed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (listing_id) REFERENCES listings(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- =====================
-- REVIEWS
-- =====================
CREATE TABLE reviews (
  id INT NOT NULL AUTO_INCREMENT,
  order_id INT NOT NULL,
  reviewer_id INT NOT NULL,
  reviewed_id INT NOT NULL,
  rating INT CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  type ENUM('buyer_to_farmer','farmer_to_buyer') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (reviewer_id) REFERENCES users(id),
  FOREIGN KEY (reviewed_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- =====================
-- CONFIG
-- =====================
CREATE TABLE config (
  id INT NOT NULL AUTO_INCREMENT,
  key_name VARCHAR(50) NOT NULL,
  value VARCHAR(50) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO config VALUES (1,'commission_rate','3');

-- =====================
-- COMMISSION EARNINGS
-- =====================
CREATE TABLE commission_earnings (
  id INT NOT NULL AUTO_INCREMENT,
  order_id INT NOT NULL,
  commission_amount DECIMAL(10,2) NOT NULL,
  earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

