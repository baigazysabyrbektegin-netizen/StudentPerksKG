-- Run once against an existing StudentPerks KG database.
ALTER TABLE users ADD COLUMN expiry_email_notifications TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE benefits MODIFY benefit_type ENUM('free', 'discounted', 'premium', 'trial', 'event') NOT NULL;
ALTER TABLE benefit_submissions MODIFY benefit_type ENUM('free', 'discounted', 'premium', 'trial', 'event') NULL;
CREATE TABLE user_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    benefit_id INT UNSIGNED NOT NULL,
    started_on DATE NOT NULL,
    ends_on DATE NULL,
    reminder_sent_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_registration (user_id, benefit_id),
    INDEX idx_registration_expiry (ends_on, reminder_sent_at),
    CONSTRAINT fk_registration_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_registration_benefit FOREIGN KEY (benefit_id) REFERENCES benefits(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
