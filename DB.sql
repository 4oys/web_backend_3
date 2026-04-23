-- ============================================
-- ЗАДАНИЕ 3: База данных для анкеты разработчика
-- Логин: u82564
-- ============================================

-- 1. Таблица заявок (основная)
CREATE TABLE IF NOT EXISTS applications (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    fio VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    biography TEXT,
    contract_agreed TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Таблица языков программирования (справочник)
CREATE TABLE IF NOT EXISTS programming_languages (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Заполнение справочника (12 языков)
INSERT INTO programming_languages (name) VALUES 
('Pascal'), ('C'), ('C++'), ('JavaScript'), ('PHP'), 
('Python'), ('Java'), ('Haskell'), ('Clojure'), 
('Prolog'), ('Scala'), ('Go');

-- 4. Таблица связи (один ко многим) - правильная структура
CREATE TABLE IF NOT EXISTS application_languages (
    application_id INT(10) UNSIGNED NOT NULL,
    language_id INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (application_id, language_id),
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES programming_languages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;