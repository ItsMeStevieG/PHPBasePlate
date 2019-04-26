<?php
require_once("config.php");

$multiset=array(
    "PageTitle"=>"Welcome to Steven-Graham.com",
    "MetaDescription"=>"Steven-Graham.com - I.T. services, sales, support, remote assistance, web design, web hosting, troubleshooting, networking, custom built computers and more.",
    "MetaKeywords"=>"Computer Repair, Computer Engineer, Web Design, Web Application Design, Web Developer, PHP, HTML, CSS",
    "PageAuthor"=>"Steven Graham",
    "BodyContent"=>$content
);

$template->multiSet($multiset);
echo $template->output();

?>
