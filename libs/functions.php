<?php

function searchImages($search = '',$page = 0){
    
    $limit = 45;
    $offset = $limit * $page;    
    $offsetInject = "OFFSET $offset";
    
    if ($offset > 0){
        $offsetInject = "";
    }
    
    $imageArray = array();  
    $pieces = explode(" ", $search);

    if (count($pieces) > 5){
        //Too many serach terms
         return false;
    }
    
    $count = 0;
    $tagcount = 0;
    
    trigger_error("PIECENS" . print_r($pieces,true));
    
    foreach ($pieces as $value) {
        $searchQuery = '';
        $or = '';
        $return = deepsearch(array($value));
        
        trigger_error("RETURN for $value " . print_r($return,true));
        
        foreach ($return[0] as $element) {
            $searchQuery .= "$or (tagsearch.id = $element AND tagsearch.level = 0)";
            $or = ' OR ';
            $tagcount++;
        }
        
        foreach ($return[1] as $element) {
            $searchQuery .= "$or (tagsearch.id = $element AND tagsearch.level = 1)";
            $or = ' OR ';
            $tagcount++;
        }
        foreach ($return[2] as $element) {
            $searchQuery .= "$or (tagsearch.id = $element AND tagsearch.level = 2)";
            $or = ' OR ';
            $tagcount++;
        }
        foreach ($return[3] as $element) {
            $searchQuery .= "$or (tagsearch.id = $element AND tagsearch.level = 3)";
            $or = ' OR ';
            $tagcount++;
        }

        if ($tagcount > 0){
            $datas = db_select("dir_lib, dir_lib as dir_lib2, images, tagsearch, tag_link",
                    "images.id as imageid, filename,dir_lib.location, dir_lib2.location as thumblocation",
                    "(main_dir = dir_lib.id and dir_lib.type = 1 ) 
            AND (thumb_dir = dir_lib2.id and dir_lib2.type = 0 ) 
            AND (($searchQuery)
            AND linkimage = images.id  
            AND (linktag = tagsearch.id AND tagsearch.level = taglevel))
            GROUP BY imageid, filename,dir_lib.location,thumblocation

            order by filename
            LIMIT $limit $offsetInject",$pieces); 

            while( $row = $datas->fetch(PDO::FETCH_ASSOC) ) { 
                $imageArray[$count][] = array('id' => $row['imageid'], 'fname' => $row['filename'],'loc' => $row['location'], 'tloc' =>$row['thumblocation']);
            }
            
            //Nothing found
            if (count($imageArray) == 0){
                return false;
            }
            $count++;

        }else {
            //No tags
            return false;
        }
 
    }
    
    
    $idArray =  array();
    
    foreach ($imageArray as $imageSearchSet) { 
        $idArrayCurrent = array();
        $newMatchArray = array();
        
        
        foreach ($imageSearchSet as $imageRow) {
            $idArrayCurrent[] = $imageRow;
        }
        
        //If the ID array is EVER blank then stop
        if (count($idArrayCurrent) == 0){
            trigger_error("FAIL ON inital array count");
            return false;
        }
        
        //Init for first loop
        if (count($idArray) <= 0 ){
            $idArray = $idArrayCurrent;
        }
        
        foreach ($idArray as $key => $valToCheck) {
            foreach ($idArrayCurrent as $secVal){
                if ($secVal['id'] == $valToCheck['id']){
                    $newMatchArray[] = $valToCheck;
                    //ID exists in new AND master array
                }
            }  
        }
        
       unset($idArrayCurrent);
        unset($idArray); //Unset the current array as the new match array becomes master
        $idArray = $newMatchArray;
        
        //Array cleared, no search tearm found.
        if (count($idArray) == 0 ){
           return false;
        }
    }
     return $idArray;
}




function getDirs($thedest){
    $thedirs = array();
     
    $_SESSION['imagedir'] = _IMAGE_DIRECTORY . $thedest . "/";
    $_SESSION['thumbdir'] = _THUMB_DIRECTORY . $thedest . "/";

    if(!file_exists($_SESSION['imagedir'])){
        $statea = mkdir($_SESSION['imagedir'], 0700);
    }else{
        $statea = true;
    }
    if (!file_exists($_SESSION['thumbdir'])){
        $stateb = mkdir($_SESSION['thumbdir'], 0700);
    }else{
        $stateb = true;
    }

    if (!$statea || !$stateb){
        die(_SERVER_SCREAM);
    }
    
    $datas = db_select("dir_lib", "*", "(location = '$_SESSION[imagedir]' AND type = 1) OR (location = '$_SESSION[thumbdir]' AND type = 0)");
    while( $row = $datas->fetch(PDO::FETCH_ASSOC) ) {
        
        //Image dir is found
        if ($row['type'] == 1){
            $thedirs['imagedir'] = $row['location'];
             $thedirs['imagedir_id'] = $row['id'];
        }else if ($row['type'] == 0){
             $thedirs['thumbdir'] = $row['location'];
              $thedirs['thumbdir_id'] = $row['id'];
        }
      
    }
    
    
    if (!isset($thedirs['imagedir'])){      
       $id = db_insert("dir_lib", "location,type","'$_SESSION[imagedir]',1");
       $thedirs['imagedir'] = $_SESSION['imagedir'];
       $thedirs['imagedir_id'] = $id;
       
    }
    if (!isset($thedirs['thumbdir'])){      
        $id = db_insert("dir_lib", "location,type","'$_SESSION[thumbdir]',0");
        $thedirs['thumbdir'] = $_SESSION['thumbdir'];
        $thedirs['thumbdir_id'] = $id;
        
    }
    return $thedirs;
}

function hasLogin(){
    if (isset($_SESSION['access']) && ($_SESSION['access'] == 'granted'))
    {
        return true;
    }else{
        return false;
    }
}

 

function addfreshtag($tagarray){
    $returnjson = array("result" => "bad");
    $currentparent = 0;
    $count = 0;
    $join = '';
    foreach ($tagarray as $value) {  
        if ($count == 0){     
            //Skip
        }else{
           $join = "_" . $count;
        }
        $exists = db_getfield("tags$join", "id", "tag like '$value'" );
        if ($exists  == ''){
            if ($currentparent == ''){
              $currentparent =    db_insert("tags", "tag", "'$value'");  
            }else{
              $currentparent =    db_insert("tags$join", "tag,parent", "'$value',$currentparent");
            }
        }else{
            $currentparent = $exists;
        }
        $count++;
    }
     
    if ($currentparent > 0){
       $returnjson['result'] = "ok";
    }
    $test =  json_encode($returnjson);
    return $test;
}

function addtag($imageid,$thetag){
    $returnjson = array("result" => "bad","imageid" => $imageid);

    $tagexists = db_select_first("tagsearch", "*", "tag like '$thetag' order by tag");
    $sucsses = (isset($tagexists['id'])  && $tagexists['id'] > 0) ? $tagexists['id'] : 0 ;
    if ($sucsses > 0){
        $check3 = db_getfield("tag_link", "id", "linkimage = $imageid AND linktag = $sucsses AND taglevel = $tagexists[level] ");
        if ($check3 == ''){
            $sucsses = db_insert("tag_link", "linkimage,linktag,taglevel","$imageid, $sucsses,$tagexists[level]");
        }else{
            $sucsses = 99;
        }
    }
   
    error_log($sucsses);
    
    if ($sucsses > 0){
       $returnjson['result'] = "ok";
    }
    error_log(print_r($returnjson,true));
    $test =  json_encode($returnjson);
    error_log(print_r($test,true));
    return $test;
}


function imagetaglist($imageid){
    
    $html = '';
    $dats = db_select("tagsearch ts, tag_link tl", "ts.tag,ts.level,ts.id", "tl.linktag = ts.id AND tl.taglevel = ts.level AND tl.linkimage = $imageid");
     while( $row = $dats->fetch(PDO::FETCH_ASSOC) ) {
         
         $tagDecr = $row['level'];
         $count = 0;
         $concatString = '';
         $comma = '';
         $joinString = '';
         
         
         while($tagDecr > 0){
            $tagDecr--;
            
            $joinString .= " LEFT OUTER JOIN tagsearch ts$count ON ts" . (($count - 1 >= 0) ? $count - 1 : '') .  ".parent = ts$count.id AND ts$count.level = $tagDecr ";
            $concatString .= $comma . 'ts' . $count .'.tag';
            $comma = ',",",';
            $count++;
         }
        $dats2 = db_select_first("tag_link tl, tagsearch ts" . $joinString,
                'concat(' . $concatString. ') AS parenttags', 
                "tl.linktag = ts.id AND tl.taglevel = ts.level AND tl.linkimage = $imageid");
 
         $html .= "<div class='atag'>$row[tag] {{{$dats2['parenttags']}}}</div>";
         
         
     }  
     return $html;
}

function isNumAboveZero($value){
	if (isset($value) && is_numeric($value)){
		if ($value > 0){
			return true;
		}
	}
	return false;
}

function deleteimage($theid){
    
    error_log("Calling id " . $theid);
    
    $primLocation = db_select_first("images,dir_lib", "location, filename", "main_dir = dir_lib.id and dir_lib.type = 1 and images.id = ?", array($theid));
     error_log("Prim location " . $primLocation);
    $thumbLocation = db_select_first("images,dir_lib", "location, filename", "thumb_dir = dir_lib.id and dir_lib.type = 0 and images.id = ?", array($theid));
       error_log("Thumb location " . $thumbLocation);
    if ($primLocation != '' && $thumbLocation != '' ){
        
        //error_log("Would delete ". $outarray['location'] . $outarray['filename']);
        
        if (is_file(_SERVER_ROOT . $primLocation['location'] . $primLocation['filename']) && is_file(_SERVER_ROOT .$thumbLocation['location'] .  _THUMB_FILENAME_HEAD . $thumbLocation['filename']))
        {
            $unlinkedmain = unlink(_SERVER_ROOT . $primLocation['location'] . $primLocation['filename']);
            $unlinkedthumb = unlink(_SERVER_ROOT .$thumbLocation['location'] .  _THUMB_FILENAME_HEAD . $thumbLocation['filename']);
            if ($unlinkedmain && $unlinkedthumb){
                db_delete("images", "id = $theid"); 
                db_delete("tag_link", "linkimage = $theid");
            }else{
                if(!$unlinkedmain){
                    error_log("ISSUE COULDNOT UNLINK :: " . _SERVER_ROOT . $primLocation['location'] . $primLocation['filename']);
                }
                if(!$unlinkedthumb){
                    error_log("ISSUE COULDNOT UNLINK :: " . _SERVER_ROOT .$thumbLocation['location'] .  _THUMB_FILENAME_HEAD. $thumbLocation['filename']);
                }
            }
        }else{
            error_log("ISSUE COULDNOT FIND :: " . _SERVER_ROOT .$thumbLocation['location'] . _THUMB_FILENAME_HEAD. $thumbLocation['filename']);
            error_log("...OR...");
            error_log("ISSUE COULDNOT FIND :: " . _SERVER_ROOT . $primLocation['location'] . $primLocation['filename']);
        }
        
    }else{
        echo _SERVER_SCREAM;
    }
}

function autoComplete($tbl,$field,$term = '',$type = ''){
    $response = array();
    $sqlParams = array();

    
    $term = trim($term);
    $term = (strlen($term) > 0) ? $term : die();
    $term = '%' . $term . '%';
    
    $sqlParams[] = $term;
    
    trigger_error("TYPE IS " . $type);
    $tagLevelLmit = '';
    if ($type == 'any'){
        $tbl = "all_tags";
    }else if (isNumAboveZero($type) && $type <= 4){
        $tagLevelLmit = " AND level = ?";
        $sqlParams[] = floor($type);
    }else if($type == 0){
        $tbl = "tags";
    }         
 
    $result = db_select("$tbl", "$field", "$field like ? $tagLevelLmit order by $field limit 5 ", $sqlParams);
    
	while($row = $result->fetch(PDO::FETCH_ASSOC)){
		$returned = array();
		$returned['name'] = $row[$field];	
		$response[] = $returned;
	}
    return $_REQUEST['callback'].'('.json_encode(array('result' => $response)).')';
}

function buildFileArray($search){
    
    if (strlen($search) > 0){

        $fileInfo = searchImages($search, $page);

        $files = array();
        foreach ($fileInfo as $value) {
            $files[] = $value['loc'] . $value['fname'];
        }
        return $files;
    }
}

function zipSet($files){
    $zip = new ZipArchive();
    $temp = tempnam('/tmp','imgarch');
    $zip->open($temp,  ZipArchive::CREATE) or die('open zip error');
    
    foreach ($files as $value) {
        if(is_file($value) && file_exists($value)){
            $zip->addFile($value); 
        }else{
            error_log("UNABLE TO ADD TO ZIP: $value" );
        }
    }
    $zip->close();
    
    header("Content-type: application/zip");
    header("Content-Disposition:attachment;filename=imgarch.zip");
    readfile($temp);
    exit;
    
}

?>