<?php

/**
 * Plugin Name:       Strategy11 Developer Applicant Challenge
 * Description:       Developer Applicant Challenge
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Author:            Nick Stewart
 * Author URI:        https://nickstewart.me
 *
 * @package Challenge
 */

if (file_exists(__DIR__ . '/src/vendor/autoload.php')) {
	require_once __DIR__ . '/src/vendor/autoload.php';
}
use Nickstewart\Challenge\Loader;

$challenge = new Loader();
$challenge->setup();

$GLOBALS['blade'] = $challenge->init_blade_views();
