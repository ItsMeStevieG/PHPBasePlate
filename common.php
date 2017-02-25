<?php
### Include every class and function set we use
require_once(LOC_CLASSES."ToolChest.class.php"); //Include ToolChest Utilities
require_once(LOC_CLASSES."Blunder.class.php"); //Include Blunder Error Handler
require_once(LOC_CLASSES."OOPSQL.class.php"); //Include OOPSQL MySQLi Database Handler
require_once(LOC_CLASSES."BluePrint.class.php"); //Include BluePrint Template Engine
require_once(LOC_CLASSES."TimeMachine.class.php"); //Include TimeMachine TimeZone/Time COnversion Manager

### Instantiate the major classes here
$utils=new ToolChest(); //Instantiate Blunder Error Handler

$elog=new Blunder(NULL,LOC_LOGPATH); //Instantiate Blunder Error Handler

if(DB_ENABLE==true)
{
    $dbo=OOPSQL::getInstance(); //Create MySQL DB Instance
    $dbo->connect($cfg[LOC]["DB_HOSTNAME"], $cfg[LOC]["DB_USERNAME"], $cfg[LOC]["DB_PASSWORD"], $cfg[LOC]["DB_DATABASE"]); //Connect to DB and get Connection Object
}

$template=new BluePrint(THEME_PATH); //BluePrint Theme Switcher

/* Make Konstruct paths available to BluePrint */
$KonstructBPPaths=array(
"BASE_URL"=>$CFG[LOC]["BASE_URL"],
"URL_CSS"=>URL_CSS,
"URL_IMAGES"=>URL_IMAGES,
"URL_JS"=>URL_JS,
"CURRENT_DATETIME"=>date("Y-m-s H:i:s"),
"PHP_VERSION"=>phpversion()
);
$template->multiSet($KonstructBPPaths);
?>