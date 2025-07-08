<?php
require_once __DIR__ . '/../controllers/PretController.php';

Flight::route('POST /pret', ['PretController', 'create']);
Flight::route('GET /pret', ['PretController', 'getAll']);
Flight::route('GET /clients', ['PretController', 'getAllClients']);
Flight::route('GET /fonds', ['PretController', 'getFondsDisponibles']);

// Nouvelles routes pour la validation des prêts
Flight::route('POST /pret/valider', ['PretController', 'validerPret']);
Flight::route('POST /pret/refuser', ['PretController', 'refuserPret']);
Flight::route('GET /pret/en-attente', ['PretController', 'getPretsEnAttente']);
Flight::route('GET /pret/@id_pret/historique', ['PretController', 'getHistoriquePret']);
Flight::route('GET /banquiers', ['PretController', 'getAllBanquiers']);