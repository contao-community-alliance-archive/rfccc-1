<?php
/**
 * generation of the installation id
 *
 * This file is part of the Contao rfccc-1 <https://github.com/Discordier/Contao-ER3>
 * This file is licensed under the Creative Commons Attribution-ShareAlike 3.0 Unported License <http://creativecommons.org/licenses/by-sa/3.0/legalcode>
 */

/* Test data begin --> */
$_SERVER['HTTP_HOST'] = 'www.contao.org';
define('VERSION', '2.10');
/* <-- Test data end */

// get version
$version = VERSION;

// get domain
$domain = $_SERVER['HTTP_HOST'];

// get directory
$cwd = dirname(__FILE__);

// generate id
$id = hash('sha256', $version . "\n" . $domain . "\n" . $cwd);

// output id
echo 'Your installation id is: ' . $id . "\n";
