<?php
require 'vendor/autoload.php';
require 'db.php';

session_start();
Flight::set('flight.base_url', '/Projet-S4/ws');

require 'routes/auth_routes.php';
require 'routes/fond_routes.php';
require 'routes/type_pret_routes.php';
require 'routes/pret_routes.php';
require 'routes/interets_routes.php';

Flight::start();