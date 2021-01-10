<?php
/**
 * @author Matt Lisiak
 * API for Creative Cash Draw Solutions interface
 *
 * Compile API Documentation using apidoc:
 * node_modules/apidoc/bin/apidoc -i api/ -o apidoc/
 */

$options = [];

// uncomment for debugging
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$options = ['settings' => ['displayErrorDetails' => true]];*/

// require our api configuration variables
require_once('config.php');

// Require our route files for Slim to use
require_once('api/Change.php');
require_once('api/File.php');
require_once('api/Translation.php');

// Slim setup
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
$app = new Slim\App($options);

/* --- Load in API routes --------------------------------------------------- */
// File API
$app->post('/api/file', 'API\File:postFile');

// Translation API
$app->get('/api/translation/{lang}/{code}', 'API\Translation:getTranslation');

// Change API
$app->get('/api/change/denomination[/{lang}]', 'API\Change:getChangeDenomination');
$app->get('/api/change/{owed}/{paid}[/{lang}]', 'API\Change:getChange');

$app->run();
