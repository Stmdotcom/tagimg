<?php

include_once('libs/config.php');
include_once('Classes/TaggerFiles.php');

if ($_REQUEST['a'] == 'json'){
    if ($_REQUEST['jaction'] == 'loaddir' ){
        $thedir = $_REQUEST['thedir'];
        $thedest = $_REQUEST['thedest'];
        $thegroup = $_REQUEST['thegroup'];
        $_REQUEST['skip'] = isset($_REQUEST['skip']) ? $_REQUEST['skip'] : 0; 
        $skipamount = $_REQUEST['skip'];
        $fileObj = new TaggerFiles();
		echo $fileObj->loaddir($thedir,$thedest,$thegroup,$skipamount);
		die();
    }else if ($_REQUEST['jaction'] =='loadset' ){
        die();
        
    }else if ($_REQUEST['jaction'] == 'loadimage'){    
        trigger_error("HITT");
        $id = $_REQUEST['id'];
        if (isNumAboveZero($id)){
            include_once ('libs/html/tagSideBar.php');
            include_once('libs/imageviewer.php');
            $sideBar = getSidebarTagHTML($id);
            $content = displayimage($id);   
            $returnArray = array("state" => 'good',"data" => $sideBar, "data_2" => $content);
        }else{
            $returnArray = array("state" => 'bad');
        }
        echo json_encode($returnArray);
		die();
    }else if ($_REQUEST['jaction'] == 'savetag'){
        $imageid = $_REQUEST['imageid'];
        $tagtext = $_REQUEST['newtagtext'];
        echo addtag($imageid,$tagtext);
        die();
    }else if ($_REQUEST['jaction'] == 'savefreshtag'){
        
        $tagtexts = array();
        $tagtexts[0] = isset($_REQUEST['freshtag0text']) ? $_REQUEST['freshtag0text'] : '' ;
        $tagtexts[1] = isset($_REQUEST['freshtag1text']) ? $_REQUEST['freshtag1text'] : '' ;
        $tagtexts[2] = isset($_REQUEST['freshtag2text']) ? $_REQUEST['freshtag2text'] : '' ;
        $tagtexts[3] = isset($_REQUEST['freshtag3text']) ? $_REQUEST['freshtag3text'] : '' ;
        $tagtexts[4] = isset($_REQUEST['freshtag4text']) ? $_REQUEST['freshtag4text'] : '' ;
        echo addfreshtag($tagtexts);
        die();
    }else if ($_REQUEST['jaction'] == 'findimages'){    
        $imageid = $_REQUEST['imageid'];
        $html =  imagetaglist($imageid);
        die($html);
    }else if ($_REQUEST['jaction'] == 'autotag'){
        $term = $_REQUEST['q'];
        $type = $_REQUEST['type'];
        echo autoComplete('tagsearch','tag',$term,$type);
        die();
    }else if ($_REQUEST['jaction'] == 'loadimagetags'){  
        $id = $_REQUEST['imageid'];
        echo imagetaglist($id);
    }else if ($_REQUEST['jaction'] == 'getzip'){
        $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$files = buildFileArray($search);
        
        if (count($files) > 0){
            zipSet($files); //Return zip file
        }else{
            return null;
        }
    }
die();
}
?>
