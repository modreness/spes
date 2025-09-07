<?php
session_start();
date_default_timezone_set('Europe/Sarajevo');

require_once __DIR__ . '/app/helpers/db.php';
require_once __DIR__ . '/routes/web.php';
require_once __DIR__ . '/app/models/load.php';

