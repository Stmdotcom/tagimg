<?php

include_once('libs/sql.php');
include_once('libs/imagetools.php');
include_once('libs/functions.php');
include_once('Classes/TaggerUtil.php');

ini_set('display_errors', 'off');
ini_set('error_log', 'C:\wamp\www\imagetagger\logging\error_log.log');

$imagearray = array('png','jpeg','jpg','gif');

DEFINE('_SERVER_ROOT',"C:/wamp/www/imagetagger/");
DEFINE('_HTML_TEMPLATES',"libs/html/");
DEFINE('_IMAGE_DIRECTORY',"IMAGES/");
DEFINE('_THUMB_DIRECTORY',"IMAGES/thumbnails/");
DEFINE('_THUMB_FILENAME_HEAD',"thumb_");
DEFINE('_SERVER_SCREAM', "*Server screams internally*");

$mysqldb['db'] = 'image_info';
$mysqldb['host'] = 'localhost';
$mysqldb['user'] = 'root';
$mysqldb['pass'] = 'asdf';




?>