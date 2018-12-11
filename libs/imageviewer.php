<?php

function deepsearch($pieces){
    
    $tagoverview = array();
    
    $levelzerotags = array(); 
    $levelonetags = array(); 
    $leveltwotags = array();
    $levelthreetags = array();
    
    $taglevels = db_select("tagsearch", "*", "tag like ?",$pieces);

    while( $arow = $taglevels->fetch(PDO::FETCH_ASSOC) ) {   
        $tagoverview[] = array("level"=> $arow['level'], "tag" => $arow['tag']);
        if ($arow['level'] == 0){              
            $levelzerotags[] = $arow['id']; //add level 0 tag
            
            $ids = db_select("tags_1", "tag,id", "parent = $arow[id]");
            foreach ($ids->fetchAll() as $value) {
                $tagoverview[] = array("level"=> 1, "tag" => $value['tag']);
                $levelonetags[] = $value['id']; //Add id of tag 
            }
            
            $ids = db_select("tags_2", "tag,id", "parent in  ('" . implode("','", $levelonetags) . "')   ");
            foreach ($ids->fetchAll() as $value) {
                $tagoverview[] = array("level"=> 2, "tag" => $value['tag']);
                $leveltwotags[] = $value['id']; //Add id of tag 
            }  

            $ids = db_select("tags_3", "tag,id", "parent in  ('" . implode("','", $leveltwotags) . "')   ");
            foreach ($ids->fetchAll() as $value) {
                $tagoverview[] = array("level"=> 3, "tag" => $value['tag']);
                $levelthreetags[] = $value['id']; //Add id of tag 
            }     
        }else if ($arow['level'] == 1){              
            $levelonetags[] = $arow['id']; //add level 1 tag
            
            $ids = db_select("tags_2", "tag,id", "parent = $arow[id]");
            foreach ($ids->fetchAll() as $value) {
                $tagoverview[] = array("level"=> 2, "tag" => $value['tag']);
                $leveltwotags[] = $value['id']; //Add id of tag 
            }

            $ids = db_select("tags_3", "tag,id", "parent in  ('" . implode("','", $leveltwotags) . "')   ");
            foreach ($ids->fetchAll() as $value) {
                $tagoverview[] = array("level"=> 3, "tag" => $value['tag']);
                $levelthreetags[] = $value['id']; //Add id of tag 
            }     
        }else if ($arow['level'] == 2){     
            $leveltwotags[] = $arow['id'];
            
            $ids = db_select("tags_3", "tag,id", "parent = $arow[id]");
            foreach ($ids->fetchAll() as $value) {
                $tagoverview[] = array("level"=> 3, "tag" => $value['tag']);
                $levelthreetags[] = $value['id']; //Add id of tag 
            }    
        }else if ($arow['level'] == 3){   
            $levelthreetags[] = $arow['id'];
        }    
    }
    
    trigger_error("tag overview " . print_r($tagoverview,true));

     //Clean arrays then return to parent function
    $cleanlevelzerotags = array_unique($levelzerotags);
    $cleanlevelonetags = array_unique($levelonetags);
    $cleanleveltwotags = array_unique($leveltwotags);
    $cleanlevelthreetags = array_unique($levelthreetags);
    
    $reutnarray = array();
    $reutnarray[0] = $cleanlevelzerotags;
    $reutnarray[1] = $cleanlevelonetags;
    $reutnarray[2] = $cleanleveltwotags;
    $reutnarray[3] = $cleanlevelthreetags;
    return $reutnarray;
 
}



function imageviewer($id = 0,$search = '',$page = 0){
    
    $returnArray = array();
    $idList = array();
    $returnHTML  = '';
    $limit = 45;
    $offset = $limit * $page; 
    if ($id == 0){
        if ($search == '' || $search === null){
            if ($search === ''){
                $datas = db_select("dir_lib, dir_lib as dir_lib2, images",
                        "images.id as imageid, filename,dir_lib.location, dir_lib2.location as thumblocation",
                        "(main_dir = dir_lib.id and dir_lib.type = 1 ) AND (thumb_dir = dir_lib2.id and dir_lib2.type = 0 )  
                         order by filename LIMIT $limit OFFSET $offset"); 
            }else if ($search === null){
                $datas = db_select("dir_lib, dir_lib as dir_lib2, images LEFT JOIN tag_link ON images.id = tag_link.linkimage",
                        "images.id as imageid, filename,dir_lib.location, dir_lib2.location as thumblocation",
                        "(main_dir = dir_lib.id and dir_lib.type = 1 ) AND (thumb_dir = dir_lib2.id and dir_lib2.type = 0 )  
                         AND tag_link.id is null
                         order by filename LIMIT $limit OFFSET $offset"); 
            }
            
            
           // $returnHTML .= getSidebarHTML();
            $returnArray['sidebar'] = getSidebarHTML();
            
            $returnHTML .= "<div class='thumbframe'>";       
            while( $row = $datas->fetch(PDO::FETCH_ASSOC) ) {           
                $idList[] = $row['imageid'];             
                $returnHTML .= displayimagethumb($row['imageid'],$row['filename'],$row['thumblocation']); 
            }
            $returnHTML .= "<div class='pages'>";    
            $datas = db_select_first("dir_lib, dir_lib as dir_lib2, images",
                "count(images.id) as thecount",
                "(main_dir = dir_lib.id and dir_lib.type = 1 ) AND (thumb_dir = dir_lib2.id and dir_lib2.type = 0 ) order by filename",array($search)); 

        //db_getfield($table, $fld, $q)
            $count = $datas["thecount"];

            $count = ($count > 450) ? 450 : $count; //limit ot 450
            
            $pagehtml = '';
            $pagenum = 1;
            $pagehtml = "<a href='index.php?s=loadviewer&page=0'>1</a>";
            while ($count > $limit){
                $pagenum++;
                $linknum = $pagenum -1;
                $pagehtml .= "<a href='index.php?s=loadviewer&page=$linknum'>$pagenum</a>";
                $count = $count - $limit;
            }   
            $returnHTML .= $pagehtml; 
            $returnHTML .= "</div></div>";
           // $returnArray['content'] = $returnHTML;
        }else{
           
            $files = searchImages($search, $page);
             if ($files !== false){
              // $returnHTML .=  
               $returnArray['sidebar'] = getSidebarHTML($search);

                $returnHTML .= "<div class='thumbframe'>";  
            
           
                foreach ($files as $value) {
                    $idList[] = $value['id'];
                    $returnHTML .= displayimagethumb($value['id'],$value['fname'],$value['tloc']); 
                }    
                
            }else{
                $returnHTML .= getSidebarHTML($search);
               $returnHTML .= "<div class='thumbframe'> <h1>!!!NO IMAGES FOUND!!!</h1>";   
              
            }
            $returnHTML .= "</div>";
        }
        $returnArray['content'] = $returnHTML;
    }else{
       include_once 'libs/html/tagSideBar.php';
       $returnArray['sidebar'] = getSidebarTagHTML($id);
       $returnHTML .= displayimage($id);    
    }
    $returnArray['content'] = $returnHTML;
    if (count($idList) > 0){
          $returnArray['js'] = arrayToJS($idList,'loadedimg');
    }
   
    return $returnArray;
}

function arrayToJS($arr,$name){ 
    $json = json_encode($arr);
    return "var $name = $json";
}


function getSidebarHTML($search = ''){
    
    ob_start('mb_output_handler');
?>   
    <div class='sidebar'>Filter on a Tag...
        <div id="searchtagarea">
            <form name='searchtagform' id='searchtagform' method='get'>
                <input type="hidden" id="lastsearch" name="lastsearch" value="<?php echo $search; ?>" />
                <input type="hidden" name="s" id="searchtagtext" value="searchtag"/>
                <input type="text" name="searchtagtext" id="searchtagtext" value="<?php echo $search; ?>"/>
                <input type="submit" value="Submit">
            </form>
        </div>
        <a href="index.php?a=json&jaction=getzip&search=<?php echo $search; ?>" >Get Zip</a>
        <a href="javascript:void(0);" onclick="dump()" >Tester</a>
    </div>
<?php


$sidebar = ob_get_contents();
ob_end_clean();
return $sidebar;
}




function displayimagethumb($imageid, $entry,$thumbdir){
    return "<a class='thumb' href='javascript:void(0);' onclick='loadImageDynamic($imageid)' ><img class='small' src='$thumbdir". _THUMB_FILENAME_HEAD ."$entry'></a>";
    
    //return "<a class='thumb' href='index.php?s=loadimage&id=$imageid' ><img class='small' src='$thumbdir". _THUMB_FILENAME_HEAD ."$entry'></a>";
}

function displayimage($id){ 
    $returnHTML = '';
    //db_getfield("images,dir_lib", "", "main_dir = dir_lib.id and dir_lib.type = 1 and images.id = $id");
    $outarray = db_select_first("images,dir_lib", "location, filename", "main_dir = dir_lib.id and dir_lib.type = 1 and images.id = ?", array($id));
    if ($outarray != ''){
        $returnHTML .= "<img class='full' src='$outarray[location]$outarray[filename]'></a>";
    }else{
        $returnHTML .= _SERVER_SCREAM;
    }
    return $returnHTML;
}

function displaydir($imagedir,$thumbdir){
    if (strlen($imagedir) > 0){
        $handle = opendir($imagedir);
        if ($handle) { 
            while (false !== ($entry = readdir($handle))) {
                if (TaggerUtil::isImage($entry)){           
                    echo "<a href='$imagedir$entry' ><img class='small' src='$thumbdir". _THUMB_FILENAME_HEAD ."$entry'></a>";
                }
            }
            closedir($handle);
        }
    }
}

?>