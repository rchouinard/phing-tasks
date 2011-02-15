<?php
/**
 * Part of the Phing tasks collection by Ryan Chouinard.
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2010 Ryan Chouinard
 * @license New BSD License
 */

defined('PHING_TEST_BASE') || define('PHING_TEST_BASE', dirname(__FILE__));

set_include_path(
    realpath(dirname(__FILE__) . '/../classes') . PATH_SEPARATOR .
    realpath(dirname(__FILE__) . '/classes') . PATH_SEPARATOR .
    get_include_path()
);

require_once(dirname(__FILE__) . '/classes/phing/BuildFileTest.php');
require_once('phing/Phing.php');

Phing::startup();