<?php

function db() {
    static $pdo;

    if (!$pdo) {
        $config = require __DIR__ . '/../../config/database.php';

        try {
            $pdo = new PDO("mysql:host={$config['host']};dbname={$config['db']};charset=utf8", $config['user'], $config['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Greška prilikom konekcije: " . $e->getMessage());
        }
    }

    return $pdo;
}
