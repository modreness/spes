<?php

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/*function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'uloga' => $_SESSION['user_uloga'] ?? null,
        'ime' => $_SESSION['user_ime'] ?? null,
    ];
}
*/
function current_user() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /login');
        exit;
    }
}

function require_role($role) {
    if (!is_logged_in() || $_SESSION['user_uloga'] !== $role) {
        http_response_code(403);
        echo "Zabranjen pristup.";
        exit;
    }
}

function get_user_by_id($id) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

