<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// Set Mode
$_GET['logMode'] = (!isset($_GET['logMode']) ? "1rec" : $_GET['logMode']);

// Run the universal login script
require(SYS_PATH . "/controller/login.php");

// $loginResponse is provided here, which includes Auth's master_id if this site requires it
// Provide any custom login handling here

// Create necessary tables
MySupplies::createRow($uniID);
MyEnergy::createRow($uniID);

header("Location: /");