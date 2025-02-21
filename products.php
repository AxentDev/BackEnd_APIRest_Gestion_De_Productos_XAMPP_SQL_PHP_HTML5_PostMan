<?php
require 'db.php'; // Conexión a la base de datos

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$token = isset($headers['Authorization']) ? trim(str_replace("Bearer ", "", $headers['Authorization'])) : null;

// Función para verificar el token
function checkAuthorization($token) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$user = null;
if ($token) {
    $user = checkAuthorization($token);
}

if (!$user) {
    echo json_encode(["error" => "❌ Unauthorized"]);
    exit;
}

// CRUD de productos
if ($method === 'GET') {
    // Obtener todos los productos
    $stmt = $pdo->query("SELECT * FROM products");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($method === 'POST') {
    // Crear un nuevo producto
    $data = json_decode(file_get_contents("php://input"), true);

    // Validar datos
    if (!isset($data['name'], $data['description'], $data['price'])) {
        echo json_encode(["error" => "❌ Datos incompletos"]);
        exit;
    }

    if (!is_numeric($data['price']) || $data['price'] <= 0) {
        echo json_encode(["error" => "❌ El precio debe ser un número positivo"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price) VALUES (?, ?, ?)");
    if ($stmt->execute([$data['name'], $data['description'], $data['price']])) {
        http_response_code(201);
        echo json_encode(["message" => "✅ Producto creado"]);
    } else {
        echo json_encode(["error" => "❌ Error al crear el producto"]);
    }
} elseif ($method === 'PUT') {
    // Actualizar un producto existente
    parse_str(file_get_contents("php://input"), $data);

    if (!isset($data['id'], $data['name'], $data['description'], $data['price'])) {
        echo json_encode(["error" => "❌ Datos incompletos"]);
        exit;
    }

    if (!is_numeric($data['price']) || $data['price'] <= 0) {
        echo json_encode(["error" => "❌ El precio debe ser un número positivo"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE id = ?");
    if ($stmt->execute([$data['name'], $data['description'], $data['price'], $data['id']])) {
        echo json_encode(["message" => "✅ Producto actualizado"]);
    } else {
        echo json_encode(["error" => "❌ Error al actualizar el producto"]);
    }
} elseif ($method === 'DELETE') {
    // Eliminar un producto
    parse_str(file_get_contents("php://input"), $data);

    if (!isset($data['id'])) {
        echo json_encode(["error" => "❌ ID del producto requerido"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$data['id']])) {
        echo json_encode(["message" => "✅ Producto eliminado"]);
    } else {
        echo json_encode(["error" => "❌ Error al eliminar el producto"]);
    }
}
?>
