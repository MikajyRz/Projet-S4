<?php
require_once __DIR__ . '/../controllers/FondEtablissementController.php';

Flight::route('GET /fonds', ['FondEtablissementController', 'getFond']);
Flight::route('POST /fonds', ['FondEtablissementController', 'addFonds']);