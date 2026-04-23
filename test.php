<?php
// test.php - проверка подключения к базе данных
// ===========================================

$host = 'localhost';
$dbname = 'u82564';
$username = 'u82564';
$password = '1341640';  

echo "<h2>🔌 Проверка подключения к базе данных</h2>";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p style='color:green; font-weight:bold;'>✅ Подключение к базе данных успешно!</p>";
    
    // Проверяем таблицы
    $stmt = $pdo->query("SHOW TABLES");
    echo "<h3>📋 Таблицы в базе данных:</h3>";
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    // Проверяем количество языков
    $stmt = $pdo->query("SELECT COUNT(*) FROM programming_languages");
    $count = $stmt->fetchColumn();
    echo "<p>📊 Языков в справочнике: <strong>" . $count . "</strong> (должно быть 12)</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red; font-weight:bold;'>❌ Ошибка подключения: " . $e->getMessage() . "</p>";
}
?>