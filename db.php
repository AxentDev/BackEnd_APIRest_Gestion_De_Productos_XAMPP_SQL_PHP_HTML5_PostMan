<?php
$host = "localhost"; 
$dbname = "api_rest_php"; 
$username = "root";  
$password = "Egle123456789*";  // Si tienes contraseña en MySQL, agrégala aquí

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
?>
