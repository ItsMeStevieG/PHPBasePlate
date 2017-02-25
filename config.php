<?php
/** PHPBasePlate v1.0
 * Copyright (c) 2017 Steven Graham
 * 
 * Konstruct (Intelligent Extenible Configuration Manager) V1.0
 * */
$CFG = array(); //MAIN CONFIGURATION ARRAY !!!DO NOT DELETE!!!
 
 
/* Error Configuration ************************************************/
define("SHOW_ERRORS", true); //PHP ini_set('display errors')
define("ERROR_LEVEL", "E_ALL ^ E_NOTICE"); // http://php.net/manual/en/function.error-reporting.php
define("TIMEZONE", "Australia/Sydney"); // http://www.php.net/manual/en/timezones.php
define("DB_ENABLE", false); // Enables/Disables OOPSQL Db Connection
define("THEME", "default"); //BluePrint Theme Name --> /includes/templates/THEMENAME.theme.html
###

/* Main Configuration Switcher ******************************************/
define("LOC", 0); //ie. $CFG[0], $CFG[1]
###

//Server 1: Development Server
$CFG[0]["DB_HOSTNAME"] = "localhost";
$CFG[0]["DB_USERNAME"] = "root";
$CFG[0]["DB_PASSWORD"] = "";
$CFG[0]["DB_DATABASE"] = "";
$CFG[0]["DB_TABLEPREFIX"] = "";
$CFG[0]["BASE_URL"] = "http://localhost/PHPBasePlate/";
$CFG[0]["ADMIN_URL"] = "http://localhost/PHPBasePlate/admin/";
$CFG[0]["ADMIN_EMAIL"] = "webmaster@computerm8.com";
$CFG[0]["PROJECT_NAME"] = "PHPBasePlate";
$CFG[0]["PROJECT_VERSION"] = "1.0.0";

//Server 2: Production Server
$CFG[1]["DB_HOSTNAME"] = "host2.example.com";
$CFG[1]["DB_USERNAME"] = "host2dbuser";
$CFG[1]["DB_PASSWORD"] = "host2dbpass";
$CFG[1]["DB_DATABASE"] = "host2db";
$CFG[1]["DB_TABLEPREFIX"] = "";
$CFG[1]["BASE_URL"] = "http://host2.example.com/";
$CFG[1]["ADMIN_URL"] = "http://host2.example.com/admin/";
$CFG[1]["ADMIN_EMAIL"] = "admin@host2.example.com";
$CFG[1]["PROJECT_NAME"] = "Example Project Name";
$CFG[1]["PROJECT_VERSION"] = "1.0.0";



#################################################################
# DONT EDIT ANYTHING BELOW THIS LINE
#################################################################

/* Set an easy to use definition for the table prefix */
define("DB_PREFIX", $CFG[LOC]["DB_TABLEPREFIX"]);

/* File system locations *********************************************/
define("BASE_LOCATION", str_replace('\\', '/', dirname(__FILE__))."/");
define("LOC_INCLUDES",  BASE_LOCATION."includes/"); //FileSystem Path to Includes Directory
define("LOC_CLASSES",   LOC_INCLUDES."classes/"); //FileSystem Path to classes Directory
define("LOC_OOPSQLTABLES",   LOC_INCLUDES."classes/oopsqltables/"); //FileSystem Path to OOPSQL DBT Classes Directory
define("LOC_PACKAGES",  LOC_INCLUDES."packages/"); //FileSystem Path to Packages Directory
define("LOC_FUNCTIONS",     LOC_INCLUDES."functions/"); //FileSystem Path to Functions Directory
define("LOC_TEMPLATES",   LOC_INCLUDES."templates/"); //FileSystem Path to Templates Directory
define("LOC_LOGPATH",   BASE_LOCATION."logs/error.log"); //FileSystem Path to Error Log Directory

define("LOC_SITE_RESOURCES",    BASE_LOCATION."resources/"); //FileSystem Path to Resources Directory
define("LOC_IMAGES",        LOC_SITE_RESOURCES."images/"); //FileSystem Path to Images Directory
define("LOC_CSS",   LOC_SITE_RESOURCES."css/"); //FileSystem Path to CSS Directory
define("LOC_JS",    LOC_SITE_RESOURCES."js/"); //FileSystem Path to JavaScript Directory

/* URL locations *****************************************************/
define("URL_SITE_RESOURCES",    $CFG[LOC]["BASE_URL"]."resources/"); //Absolute URL Path to Resources Directory
define("URL_IMAGES",        URL_SITE_RESOURCES."images/"); //Absolute URL Path to Images Directory
define("URL_CSS",   URL_SITE_RESOURCES."css/"); //Absolute URL Path to CSS Directory
define("URL_JS",    URL_SITE_RESOURCES."js/"); //Absolute URL Path to JavaScript Directory

/* Timezone Settings */
date_default_timezone_set(TIMEZONE); // Set TimeZone

/* WARNINGS **********************************************************/
ini_set("display_errors", SHOW_ERRORS); //Set php.ini display_errors
error_reporting(ERROR_LEVEL); //Set PHP error_reporting

/* BluePrint Theme File Path */
define("THEME_PATH", LOC_TEMPLATES.THEME.".template.html");

/* REQUIRE COMMON FILE to instantiate classes*/
require_once("common.php");
?>
