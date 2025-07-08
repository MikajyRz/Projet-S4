<?php
require_once __DIR__ . '/../controllers/EFController.php';
require_once __DIR__ . '/../controllers/ClientController.php';
require_once __DIR__ . '/../controllers/InteretController.php';

Flight::route('GET /interets/getSommeInteretsParMoisAnnee', ['InteretController', 'getSommeInteretsParMoisAnnee']);
// Flight::route('GET /interets/getDetailsInteretsEntreDates', ['InteretController', 'getDetailsInteretsEntreDates']);
Flight::route('GET /interets/getSommeInteretsEntreDatesViaMois', ['InteretController', 'getSommeInteretsEntreDatesViaMois']);

Flight::route('GET /interets/getDetailsInterets/@id_EF/@moisDebut/@anneeDebut/@moisFin/@anneeFin', 
    ['InteretController', 'getDetailsInteretsEntreDates']);

Flight::route('GET /interets/getSommeInterets(/@id_EF(/@moisDebut(/@anneeDebut(/@moisFin(/@anneeFin)))))', 
['InteretController', 'getSommeInteretsEntreDatesViaMois']);

Flight::route('GET /interets/simuler(/@id_pret)', ['InteretController', 'simulerRemboursement']);
Flight::route('GET /interets/valider/@id_pret', ['InteretController', 'validerRemboursement']);
