<?php
/**
 * API Configuration Variables
 * @author Matt Lisiak
 * The variables here are the default definitions, but they can be changed on a
 * single api call (via parameters) or globally (via editing this config file).
 * This allows, for example, clients which always use a comma for the decimal
 * point (such as some European countries) to change this attribute globally.
 */


define('SEPARATOR', ',');
define('DECIMAL', '.');

/* special random 'twist' case requested by client */
define('USE_DIVISOR_RANDOMIZER', true);
define('DIVISOR', .03);

define('LANGUAGE', 'en-US');

/* database connection params */
define('PDO_HOST', '127.0.0.1');
define('PDO_DB', 'cash_register');
define('PDO_USER', 'test');
define('PDO_PASS', 'test');
define('PDO_CHARSET', 'utf8mb4');
define('PDO_PORT', 3306);
