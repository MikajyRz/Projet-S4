<?php
require_once __DIR__ . '/../controllers/InteretsController.php';

// Routes pour les intérêts
Flight::route('GET /interets/par-mois', ['InteretsController', 'getInteretsParMois']);
Flight::route('GET /interets/totaux', ['InteretsController', 'getInteretsTotaux']);
Flight::route('GET /interets/par-type', ['InteretsController', 'getInteretsParType']);
Flight::route('GET /interets/statistiques', ['InteretsController', 'getStatistiques']);
Flight::route('POST /interets/generer-test', ['InteretsController', 'genererDonneesTest']); 