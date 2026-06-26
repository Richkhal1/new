CREATE DATABASE IF NOT EXISTS legacy_bank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'legacy_user'@'localhost' IDENTIFIED BY 'legacy_pass_2025';
GRANT ALL PRIVILEGES ON legacy_bank.* TO 'legacy_user'@'localhost';
FLUSH PRIVILEGES;

USE legacy_bank;

CREATE TABLE IF NOT EXISTS users (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  date_of_birth DATE DEFAULT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  address_street VARCHAR(255) DEFAULT NULL,
  address_city VARCHAR(100) DEFAULT NULL,
  address_state VARCHAR(50) DEFAULT NULL,
  address_zip VARCHAR(20) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  ssn VARCHAR(11) DEFAULT NULL,
  id_type VARCHAR(50) DEFAULT NULL,
  id_number VARCHAR(100) DEFAULT NULL,
  employment_status VARCHAR(50) DEFAULT NULL,
  employer_name VARCHAR(255) DEFAULT NULL,
  occupation VARCHAR(255) DEFAULT NULL,
  account_purpose VARCHAR(255) DEFAULT NULL,
  security_question VARCHAR(255) DEFAULT NULL,
  security_answer VARCHAR(255) DEFAULT NULL,
  agreed_tos TINYINT(1) NOT NULL DEFAULT 0,
  agreed_electronic TINYINT(1) NOT NULL DEFAULT 0,
  kyc_status ENUM('none','pending','verified','rejected') NOT NULL DEFAULT 'none',
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  status ENUM('active','suspended','pending','pending_kyc') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS accounts (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) UNSIGNED NOT NULL,
  account_number VARCHAR(20) NOT NULL UNIQUE,
  account_type VARCHAR(50) NOT NULL DEFAULT 'Legacy Spending Account',
  balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  routing_number VARCHAR(20) NOT NULL DEFAULT '031101279',
  status ENUM('active','suspended','closed') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_id INT(11) UNSIGNED NOT NULL,
  type ENUM('deposit','withdrawal','transfer_in','transfer_out','credit','debit') NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  reference VARCHAR(50) DEFAULT NULL,
  status ENUM('pending','completed','failed','flagged') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transfers (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  from_account_id INT(11) UNSIGNED NOT NULL,
  to_account_number VARCHAR(20) NOT NULL,
  to_account_name VARCHAR(255) DEFAULT NULL,
  amount DECIMAL(15,2) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  auth_code VARCHAR(50) DEFAULT NULL,
  auth_type ENUM('none','imf','swift','cot') DEFAULT 'none',
  status ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at TIMESTAMP NULL DEFAULT NULL,
  processed_by INT(11) UNSIGNED DEFAULT NULL,
  FOREIGN KEY (from_account_id) REFERENCES accounts(id) ON DELETE CASCADE,
  FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kyc_documents (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) UNSIGNED NOT NULL,
  document_type ENUM('id_front','id_back','proof_of_address','selfie') NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) DEFAULT NULL,
  file_size INT(11) DEFAULT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('bank_name', 'Legacy National Bank'),
('bank_logo', 'images/logo.jpg'),
('currency', 'USD'),
('transfer_auth_required', 'false'),
('auth_type', 'none'),
('imf_code', ''),
('swift_code', ''),
('cot_code', ''),
('support_email', 'support@legacy.com'),
('support_phone', '1-800-555-0123');
