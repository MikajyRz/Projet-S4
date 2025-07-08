<?php
require_once __DIR__ . '/../controllers/EFController.php';
require_once __DIR__ . '/../controllers/ClientController.php';
require_once __DIR__ . '/../controllers/FondController.php';
require_once __DIR__ . '/../controllers/RemboursementController.php';
require_once __DIR__ . '/../controllers/PdfController.php';


// Routes d'authentification
Flight::route('POST /login', ['AuthController', 'login']);
Flight::route('POST /register', ['AuthController', 'register']);

// Routes pour les clients
Flight::route('GET /clients', ['ClientController', 'getAll']);
Flight::route('GET /clients/@id', ['ClientController', 'getById']);
Flight::route('POST /clients', ['ClientController', 'create']);
Flight::route('PUT /clients/@id', ['ClientController', 'update']);
Flight::route('DELETE /clients/@id', ['ClientController', 'delete']);

// Routes pour les fonds
Flight::route('GET /fonds', ['FondController', 'getAll']);
Flight::route('GET /fonds/@id', ['FondController', 'getById']);
Flight::route('POST /fonds', ['FondController', 'create']);
Flight::route('PUT /fonds/@id', ['FondController', 'update']);
Flight::route('DELETE /fonds/@id', ['FondController', 'delete']);
Flight::route('GET /fonds/total', ['FondController', 'getTotalFonds']);
Flight::route('GET /fonds/disponibles', ['FondController', 'getFondsDisponibles']);

// Routes pour les type-pret
Flight::route('GET /type-pret', ['EFController', 'getAllTypePret']);
Flight::route('GET /type-pret/@id', ['EFController', 'getTypePretById']);
Flight::route('POST /type-pret', ['EFController', 'createTypePret']);
Flight::route('PUT /type-pret/@id', ['EFController', 'updateTypePret']);
Flight::route('DELETE /type-pret/@id', ['EFController', 'deleteTypePret']);

// Routes pour prets
Flight::route('GET /prets', ['EFController', 'getAllPrets']);
Flight::route('GET /pretsDemande', ['EFController', 'getAllDemande']);
Flight::route('GET /prets/@id', ['EFController', 'getPretById']);
Flight::route('POST /prets', ['EFController', 'createPret']);
Flight::route('POST /prets/valider/@id', ['EFController', 'validerPret']);
Flight::route('DELETE /prets/@id', ['EFController', 'deletePret']);
Flight::route('POST /prets/simuler', ['EFController', 'simulerRemboursement']);

// Routes pour remboursements
Flight::route('GET /remboursements', ['RemboursementController', 'getAll']);
Flight::route('GET /remboursements/@id', ['RemboursementController', 'getById']);
Flight::route('GET /remboursements/pret/@id_pret', ['RemboursementController', 'getByPretId']);
Flight::route('GET /remboursements/stats', ['RemboursementController', 'getStatistiques']);
Flight::route('GET /remboursements/par-mois', ['RemboursementController', 'getRemboursementsParMois']);

// Routes pour PDF
Flight::route('GET /pdf/pret/@id_pret', ['PdfController', 'generatePretPdf']);
Flight::route('GET /pdf-fpdf/pret/@id_pret', ['PdfController', 'generatePretFpdf']);