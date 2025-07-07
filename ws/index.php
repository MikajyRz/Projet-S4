<?php
require 'vendor/autoload.php';
require 'db.php';

session_start();
Flight::set('flight.base_url', '/Projet-S4/ws');

require 'routes/auth_routes.php';
require 'routes/etudiant_routes.php';

Flight::start();