<?php

// Automatsko učitavanje svih helper fajlova
foreach (glob(__DIR__ . "/*.php") as $filename) {
    if (basename($filename) !== "load.php") {
        require_once $filename;
    }
}
global $pdo;
$pdo = db();

