<?php
require_once __DIR__ . '/../controllers/PretController.php';

Flight::route('POST /pret', ['PretController', 'create']);
Flight::route('GET /pret', ['PretController', 'getAll']);
Flight::route('GET /clients', ['PretController', 'getAllClients']);
Flight::route('GET /fonds', ['PretController', 'getFondsDisponibles']);