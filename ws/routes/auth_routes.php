<?php
require_once __DIR__ . '/../controllers/AuthController.php';

Flight::route('POST /login', ['AuthController', 'login']);
Flight::route('GET /logout', ['AuthController', 'logout']);
Flight::route('GET /admin_accueil', ['AuthController', 'adminDashboard']);
Flight::route('GET /client_accueil', ['AuthController', 'clientDashboard']);