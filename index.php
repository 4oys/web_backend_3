<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

$host = 'localhost';
$dbname = 'u82564';
$username = 'u82564';
$password = '1341640';  

$allowedLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $fio = trim($_POST['fio'] ?? '');
    if (empty($fio)) {
        $errors['fio'] = 'Заполните ФИО.';
    } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $fio)) {
        $errors['fio'] = 'ФИО должно содержать только буквы, пробелы и дефисы.';
    } elseif (strlen($fio) > 150) {
        $errors['fio'] = 'ФИО не должно превышать 150 символов.';
    }
    
    $phone = trim($_POST['phone'] ?? '');
    if (empty($phone)) {
        $errors['phone'] = 'Заполните телефон.';
    } elseif (!preg_match('/^(\+7|8)[0-9]{10}$/', $phone)) {
        $errors['phone'] = 'Телефон должен быть в формате +7XXXXXXXXXX или 8XXXXXXXXXX (11 цифр).';
    }
    
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Заполните email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email адрес.';
    }
    
    $birth_date = $_POST['birth_date'] ?? '';
    if (empty($birth_date)) {
        $errors['birth_date'] = 'Заполните дату рождения.';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $birth_date);
        $today = new DateTime();
        $minDate = new DateTime('-150 years');
        if (!$date || $date > $today || $date < $minDate) {
            $errors['birth_date'] = 'Введите корректную дату рождения.';
        }
    }
    
    $gender = $_POST['gender'] ?? '';
    $allowedGenders = ['male', 'female', 'other'];
    if (empty($gender)) {
        $errors['gender'] = 'Укажите пол.';
    } elseif (!in_array($gender, $allowedGenders)) {
        $errors['gender'] = 'Выбран недопустимый пол.';
    }
    
    $languages = $_POST['languages'] ?? [];
    if (empty($languages)) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования.';
    } else {
        foreach ($languages as $lang) {
            if (!in_array($lang, $allowedLanguages)) {
                $errors['languages'] = 'Выбран недопустимый язык программирования.';
                break;
            }
        }
    }
    
    $biography = trim($_POST['biography'] ?? '');
    if (strlen($biography) > 1000) {
        $errors['biography'] = 'Биография не должна превышать 1000 символов.';
    }
    
    $contract_agreed = isset($_POST['contract_agreed']) ? 1 : 0;
    if (!$contract_agreed) {
        $errors['contract_agreed'] = 'Необходимо подтвердить ознакомление с контрактом.';
    }
    
    if (empty($errors)) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO applications (fio, phone, email, birth_date, gender, biography, contract_agreed) 
                VALUES (:fio, :phone, :email, :birth_date, :gender, :biography, :contract_agreed)
            ");
            $stmt->execute([
                ':fio' => $fio,
                ':phone' => $phone,
                ':email' => $email,
                ':birth_date' => $birth_date,
                ':gender' => $gender,
                ':biography' => $biography,
                ':contract_agreed' => $contract_agreed
            ]);
            
            $applicationId = $pdo->lastInsertId();
            
            $langIdStmt = $pdo->prepare("SELECT id FROM programming_languages WHERE name = :name");
            $insertLangStmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (:app_id, :lang_id)");
            
            foreach ($languages as $langName) {
                $langIdStmt->execute([':name' => $langName]);
                $langId = $langIdStmt->fetchColumn();
                if ($langId) {
                    $insertLangStmt->execute([
                        ':app_id' => $applicationId,
                        ':lang_id' => $langId
                    ]);
                }
            }
            
            $pdo->commit();
            $success = true;
            
        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $errors['db'] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат сохранения анкеты</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .result-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
        }
        .result-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 32px;
            color: white;
            text-align: center;
        }
        .result-body { padding: 32px; }
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            padding: 14px 18px;
            border-radius: 12px;
            border-left: 4px solid #16a34a;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            padding: 14px 18px;
            border-radius: 12px;
            border-left: 4px solid #dc2626;
            margin-bottom: 20px;
        }
        .error-list {
            background: #fef2f2;
            padding: 16px;
            border-radius: 12px;
            margin-top: 16px;
        }
        .error-list ul { margin-left: 20px; }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover { text-decoration: underline; }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="result-container">
    <div class="result-header">
        <h1>📋 Результат обработки</h1>
        <p>Серверная валидация и сохранение в БД</p>
    </div>
    <div class="result-body">
        
        <?php if ($success): ?>
            <div class="alert-success">
                ✅ Данные успешно сохранены в базу данных!
            </div>
            <p>Спасибо за регистрацию! Ваша анкета принята.</p>
            <a href="index.html" class="back-link">← Заполнить новую анкету</a>
            
        <?php elseif (!empty($errors)): ?>
            <div class="alert-error">
                ❌ При обработке формы обнаружены ошибки
            </div>
            <div class="error-list">
                <strong>Исправьте следующие ошибки:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="index.html" class="back-link">← Вернуться к форме</a>
            
        <?php else: ?>
            <p>Форма не была отправлена.</p>
            <a href="index.html" class="back-link">→ Перейти к форме</a>
        <?php endif; ?>
        
    </div>
</div>
</body>
</html>
