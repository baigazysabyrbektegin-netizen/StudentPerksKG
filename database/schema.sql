CREATE DATABASE IF NOT EXISTS studentperks_kg
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE studentperks_kg;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS benefits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    provider VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    benefit_type ENUM('free', 'discounted', 'premium', 'trial') NOT NULL,
    eligibility_requirements TEXT NOT NULL,
    kyrgyzstan_status ENUM('confirmed', 'needs_verification', 'not_available') NOT NULL DEFAULT 'needs_verification',
    verification_requirements TEXT NOT NULL,
    claiming_instructions TEXT NOT NULL,
    official_url VARCHAR(500) NOT NULL,
    logo_url VARCHAR(500) NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    verification_date DATE NULL,
    expiration_date DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_benefits_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_benefits_category (category_id),
    INDEX idx_benefits_type (benefit_type),
    INDEX idx_benefits_status (kyrgyzstan_status),
    INDEX idx_benefits_featured (featured),
    FULLTEXT KEY ft_benefits_search (title, provider, description)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS saved_benefits (
    user_id INT UNSIGNED NOT NULL,
    benefit_id INT UNSIGNED NOT NULL,
    saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, benefit_id),
    CONSTRAINT fk_saved_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_saved_benefit FOREIGN KEY (benefit_id) REFERENCES benefits(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS benefit_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    benefit_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    reason VARCHAR(255) NOT NULL,
    status ENUM('open', 'reviewing', 'resolved', 'dismissed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_benefit FOREIGN KEY (benefit_id) REFERENCES benefits(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reports_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_reports_status (status)
) ENGINE=InnoDB;

INSERT INTO categories (name, slug, description) VALUES
    ('Software & developer tools', 'software', 'Professional software, developer tools, hosting, and cloud credits.'),
    ('AI & productivity', 'productivity', 'Tools for research, writing, notes, organization, and collaboration.'),
    ('Design & creative', 'design', 'Design, illustration, video, and creative software for students.'),
    ('Courses & certifications', 'courses', 'Online courses, coding programs, and professional certificates.'),
    ('Entertainment & streaming', 'entertainment', 'Music, video, gaming, and other entertainment benefits.'),
    ('Student discounts & deals', 'discounts', 'Useful discounts on technology, shopping, and subscriptions.')
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

INSERT INTO benefits (
    title, slug, provider, description, category_id, benefit_type,
    eligibility_requirements, kyrgyzstan_status, verification_requirements,
    claiming_instructions, official_url, featured, verification_date
)
SELECT
    'GitHub Student Developer Pack',
    'github-student-developer-pack',
    'GitHub',
    'A collection of professional developer tools and learning resources for verified students.',
    id,
    'free',
    'You must be a currently enrolled student at a recognized educational institution.',
    'needs_verification',
    'An institution-issued student email address or other accepted proof of enrollment may be required.',
    'Open the official student developer pack page, sign in with a GitHub account, and follow the student verification steps.',
    'https://education.github.com/pack',
    1,
    NULL
FROM categories WHERE slug = 'software'
ON DUPLICATE KEY UPDATE title = VALUES(title), updated_at = CURRENT_TIMESTAMP;

INSERT INTO benefits (
    title, slug, provider, description, category_id, benefit_type,
    eligibility_requirements, kyrgyzstan_status, verification_requirements,
    claiming_instructions, official_url, featured, verification_date
)
SELECT
    'JetBrains student licenses',
    'jetbrains-student-licenses',
    'JetBrains',
    'Free educational licenses for JetBrains IDEs and developer tools.',
    id,
    'free',
    'You must be a student or teacher at an eligible educational institution.',
    'needs_verification',
    'JetBrains may request an academic email address or proof of educational status.',
    'Visit the official educational products page and complete the academic verification process.',
    'https://www.jetbrains.com/community/education/',
    1,
    NULL
FROM categories WHERE slug = 'software'
ON DUPLICATE KEY UPDATE title = VALUES(title), updated_at = CURRENT_TIMESTAMP;

INSERT INTO benefits (
    title, slug, provider, description, category_id, benefit_type,
    eligibility_requirements, kyrgyzstan_status, verification_requirements,
    claiming_instructions, official_url, featured, verification_date
)
SELECT
    'Figma for Education',
    'figma-for-education',
    'Figma',
    'Collaborative interface design and prototyping tools for eligible education users.',
    id,
    'free',
    'Eligibility and regional availability depend on Figma education policy.',
    'needs_verification',
    'An academic email address or other proof of enrollment may be requested.',
    'Review the official education page and apply through Figma using your academic details.',
    'https://www.figma.com/education/',
    0,
    NULL
FROM categories WHERE slug = 'design'
ON DUPLICATE KEY UPDATE title = VALUES(title), updated_at = CURRENT_TIMESTAMP;

INSERT INTO benefits (
    title, slug, provider, description, category_id, benefit_type,
    eligibility_requirements, kyrgyzstan_status, verification_requirements,
    claiming_instructions, official_url, featured, verification_date
)
SELECT
    'Microsoft 365 Education',
    'microsoft-365-education',
    'Microsoft',
    'Productivity applications and collaboration services offered through eligible schools.',
    id,
    'free',
    'Availability is determined by the participating institution and Microsoft education policy.',
    'needs_verification',
    'A qualifying school email address is usually required.',
    'Check eligibility on Microsoft’s official education page using your institution email.',
    'https://www.microsoft.com/education/products/office',
    0,
    NULL
FROM categories WHERE slug = 'software'
ON DUPLICATE KEY UPDATE title = VALUES(title), updated_at = CURRENT_TIMESTAMP;
