<?php
define("LOC_CLASSES", "../includes/classes/");
define("LOC_TEMPLATES", "templates/");

require_once(LOC_CLASSES."BluePrint.class.php");
require_once(LOC_CLASSES."TimeMachine.class.php");
$template=new BluePrint(LOC_TEMPLATES."install.template.html");
$TimeMachine=new TimeMachine();

$BPInstallVars=array(
"URL_CSS"=>"css/",
"URL_IMAGES"=>"images/",
"PageTitle"=>"PHPBasePlate Installer v1.0",
"PageTitle"=>"PHPBasePlate Installer v1.0",
"MetaDescription"=>"Meta Description",
"MetaKeywords"=>"Meta,Keywords",
"PageAuthor"=>"Steven Graham",
);
$template->multiSet($BPInstallVars);
############


$content="
<div class='panel panel-primary'>
    <div class='panel-heading'>Site</div>
    <div class='panel-body'>
        <div class='form-group '>
        <label for='BaseURL'>Base URL</label>
            <div class='inner-addon left-addon'>
                <i class='glyphicon glyphicon-globe'></i>      
                <input name='baseurl'type='text' value='".( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://')  . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'])."/' id='BaseURL' class='form-control' placeholder='Base URL' />
            </div>
        </div>
        <div class='form-group '>
        <label for='AdminEmail'>Server Admin Email</label>
            <div class='inner-addon left-addon'>
                <i class='glyphicon glyphicon-envelope'></i>      
                <input name='adminemail'type='text' id='AdminEmail' class='form-control' placeholder='Admin Email' />
            </div>
        </div>
        <div class='form-group '>
        <label for='TimeZoneCountry'>TimeZone</label>
            <div class='inner-addon left-addon'>     
                <select name='TZCountry' class='form-control'>
                <option value='NULL'>- Time Zones -</option>";
                
                $TIMEZONES=$TimeMachine->getTimeZones("full");
                //die("<pre>".print_r($TIMEZONES['Africa'],true)."</pre>");
                foreach($TIMEZONES as $K=>$V)
                {
                    $content.="<optgroup label='$K'>";
                    foreach($V as $K2=>$V2)
                    {
                        $content.="<option value='$K/$V2'>$K/$V2</option>";
                    }
                    $content.="</optgroup>";
                }
                
                $content.="</select>
            </div>
        </div>
    </div>
</div>

<div class='panel panel-primary'>
    <div class='panel-heading'>Database</div>
    <div class='panel-body'>
        <div class='form-group'>
            <label for='DBEnabled'>Enabled</label>&nbsp;&nbsp;
            <input name='dbenables' type='checkbox' id='DBEnabled' data-toggle='toggle'>
        </div>
        <div class='form-group '>
        <label for='DBHostname'>Hostname</label>
            <div class='inner-addon left-addon'>
                <i class='glyphicon glyphicon-globe'></i>      
                <input name='dbusername'type='text' id='DBUsername' class='form-control' placeholder='Hostname' />
            </div>
        </div>
        <div class='form-group '>
        <label for='DBUsername'>Username</label>
            <div class='inner-addon left-addon'>
                <i class='glyphicon glyphicon-user'></i>      
                <input name='dbusername'type='text' id='DBUsername' class='form-control' placeholder='Username' />
            </div>
        </div>
        <div class='form-group '>
        <label for='DBPsssword'>Password</label>
            <div class='inner-addon left-addon'>
                <i class='glyphicon glyphicon-lock'></i>      
                <input name='dbpassword' type='password' id='DBPassword' class='form-control' placeholder='Password' />
            </div>
        </div>
        <label for='DBName'>Database</label>
            <div class='inner-addon left-addon'>
                <i class='glyphicon glyphicon-tasks'></i>      
                <input name='dbpassword' type='text' id='DBName' class='form-control' placeholder='Database Name' />
            </div>
        </div>
    </div>
</div>";

############
$template->set("BodyContent", $content);
echo $template->output();
?>