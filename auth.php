
<?php
error_log("Solicitud recibida en auth.php: " . file_get_contents("php://input"));

require 'db.php'; // Conexión a la base de datos

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['action']) && $data['action'] === 'register') {
        $username = $data['username'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);

        echo json_encode(["message" => "✅ Usuario registrado exitosamente"]);
    } elseif (isset($data['action']) && $data['action'] === 'login') {
        $username = $data['username'];
        $password = $data['password'];

        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $token = bin2hex(random_bytes(16));
            $pdo->prepare("UPDATE users SET token = ? WHERE id = ?")->execute([$token, $user['id']]);
            echo json_encode(["message" => "✅ Login exitoso", "token" => $token]);
        } else {
            echo json_encode(["error" => "❌ Credenciales incorrectas"]);
        }
    }
}
?>
