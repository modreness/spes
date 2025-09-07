<?php
require_once __DIR__ . '/../helpers/load.php';

$pdo = db();

// Provjera username
if (isset($_GET['username'])) {
    $username = trim($_GET['username']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $broj = $stmt->fetchColumn();

    header('Content-Type: application/json');
    echo json_encode(['postoji' => $broj > 0]);
    exit;
}

// Provjera JMBG-a
if (isset($_GET['jmbg'])) {
    $jmbg = trim($_GET['jmbg']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM kartoni WHERE jmbg = ?");
    $stmt->execute([$jmbg]);
    $broj = $stmt->fetchColumn();

    header('Content-Type: application/json');
    echo json_encode(['postoji' => $broj > 0]);
    exit;
}

// Provjera pacijent_id
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM kartoni WHERE pacijent_id = ?");
    $stmt->execute([$id]);
    $broj = $stmt->fetchColumn();

    header('Content-Type: application/json');
    echo json_encode(['postoji' => $broj > 0]);
    exit;
}

// Ako nijedan parametar nije poslan
http_response_code(400);
echo json_encode(['greska' => 'Nedostaje parametar.']);
