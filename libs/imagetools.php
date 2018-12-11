<?php

function imageCompare(){
    //mysql_connect('localhost','username','password');
    //mysql_select_db('dbname');     

    // Include Dupe Finder and set completed files path.
    include_once ("libs/Classes/phpDupeImage.php");
    $di = new phpDupeImage();
    
    $di->completed_files_path = '/www/website/httpdocs/completed';
    $di->image_table = 'image_info';
    $di->fingerprint_field = 'fingerprint';
    $di->filename_field = 'pictures_image';

    foreach ($_FILES as $file) {
        $filename = $file['tmp_name'];
        $filedata = pathinfo($filename); 
        $checksum = $di->is_unique($filename);
        if ($checksum != -1) {
            move_uploaded_file($filename, $di->completed_files_path."/".$filedata['basename']);
            // Add the file and its checksum to your database here.
        } else {
            // Image was not unique. Tell the user or something.
        }
    }
}


function getfingerprint($filename){
	include_once('libs/Classes/phpDupeImage.php');
	$di = new phpDupeImage();
	$checksum = $di->fingerprint($filename);
	return $checksum;
}


?>