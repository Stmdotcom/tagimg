<?php
/**
 * Description of TaggerFiles
 *
 * @author Stm
 */
class TaggerFiles {
    
    public function read_folder_directory($dir = "root_dir/dir"){
        $listDir = array();
        if($handler = opendir($dir)) {
            while (($sub = readdir($handler)) !== FALSE) {
                if ($sub != "." && $sub != ".." && $sub != "Thumb.db" && $sub != "Thumbs.db") {
                    if(is_file($dir."/".$sub)) {
                        $listDir[] = $sub;
                    }elseif(is_dir($dir."/".$sub)){
                        $listDir[$sub] = ReadFolderDirectory($dir."/".$sub);
                    }
                }
            }
            closedir($handler);
        }
        return $listDir;
    }
    
    
    public function readFiles($thedir){
        if ($handle = opendir($thedir)) {
            echo "Directory handle: $handle\n";
            echo "Entries:\n";

            while (false !== ($entry = readdir($handle))) {

                if (TaggerUtil::isImage($entry))
                {
                    copy("$thedir" . "/" . "$entry'", "IMAGES/$entry"); 

                    echo "<img class='small' src='IMAGES/$entry'>";
                }
            }
            closedir($handle);
        }
    }
    
       
    private function checkFileExists($checkfile, $oringnalfile = '', $oringnalpath ='' , $incer = 1){
        if (file_exists($oringnalpath . $checkfile)) {
           $checkfile = $incer . "_" . $oringnalfile;
           $incer++;
           if ($incer > 200) {
               die(_SERVER_SCREAM);
           }
           return $this->checkFileExists($checkfile,$oringnalfile,$oringnalpath,$incer); 
        } else {
            return $checkfile;
        }
    }
    

    private function ResizeImage($fname_src, $fname_dest, $w=0, $h=0) {

        list($width,$height,$type) = getimagesize($fname_src);
        if(!in_array($type,array(IMAGETYPE_GIF,IMAGETYPE_PNG,IMAGETYPE_JPEG)))
            return false;
        if ($h == 0 && $w == 0) {
            $w = 150; //This might need modification
            $h = ($w/$width)*$height;
        }elseif($h == 0){
            $h = ($w/$width)*$height;
        }
        if ( $w/$h > ($width/$height)){
            $w = $h * ($width/$height);
            $h = $h;
        }else {
            $h = $w / ($width/$height);
            $w = $w;
        }
        $image = imagecreatetruecolor($w, $h);
        if (!$image) {
            return false;
        }
        error_log("filetype $type");
        switch($type){
            case IMAGETYPE_GIF:
                error_log("gif");
                $source = imagecreatefromgif($fname_src);
                break;
            case IMAGETYPE_PNG:
                error_log("png");
                $source = imagecreatefrompng($fname_src);
                imagealphablending($image, false);
                imagesavealpha($image,true);
                $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
                imagefilledrectangle($image, 0, 0, $w, $h, $transparent);
                break;
            case IMAGETYPE_JPEG:
                error_log("jpg");
                $source = ImageCreateFromJPEG($fname_src);
                break;
            default:
                error_log("unsupported file type");
                return false;
        }


        imagecopyresampled($image, $source, 0, 0, 0, 0, $w, $h, $width, $height);
        //@ImageCopyResized($omg, $source, 0, 0, 0, 0, $w+1, $h+1, ImageSX($source), ImageSY($source));
        switch($type){
            case IMAGETYPE_GIF:
                $result = imagegif($image,$fname_dest);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($image,$fname_dest);
                break;
            case IMAGETYPE_JPEG:
                $result = imagejpeg($image,$fname_dest);
                break;
            default:
                return false;
        }
        return $result;
    }      
    

    function loaddir($thedir,$thedest, $thegroup = '',$skipamount = 0){

        $dirs = getDirs($thedest);    
        error_log("DIRS " .$dirs);                  
        $theid = 'NULL';
        if ($thegroup != ''){
            $theid = db_getfield('group_names', 'id', "name_group like '$thegroup'"); 
            if ($theid == ''){
                $theid = db_insert('group_names', 'name_group, name_description', "'$thegroup','Do do...'");
            }
        }

        if ((strlen($thedir) > 0) && (strlen($thedest) > 0)){
            if ($handle = opendir($thedir)) {
                //echo "Directory handle: $handle\n";
                $skipper = 0;
                $loadedcount = 0;
                while (false !== ($entry = readdir($handle))) {
                    if ($skipamount > 0){
                        if ($skipper < $skipamount){
                            $skipper++;
                            continue;
                        }                   
                    }
                    if (TaggerUtil::isImage($entry)){
                        error_log("Read image " .$entry);
                        //Get file sizes etc 
                        $filesize = filesize("$thedir" . "/" . "$entry");
                        $md5size = md5_file("$thedir" . "/" . "$entry");
                        $exists = false;
                        //finger = UNHEX('$finger') OR  //add to query once id suplicate check is done
                        $existingmatch = db_select_first("images", "id", "size_hash = UNHEX('$md5size') AND size_bytes = $filesize");

                        //Do a duplicate match here
                        //There is a match
                       // while($row = $existingmatch->fetch(PDO::FETCH_ASSOC) ) {
                        if (isset($existingmatch['id']) && $existingmatch['id'] > 0){
                            error_log("FOUND " . $entry);
                            $exists = true;
                        }
                        if ($exists == false){
                            error_log("Saving " .$entry);
                            $cleanfile = $this->checkFileExists($entry ,$entry,$dirs['imagedir']);
                            error_log("Called " .$cleanfile);
                            $this->ResizeImage("$thedir" . "/" . "$entry", $dirs['thumbdir'] . _THUMB_FILENAME_HEAD .$cleanfile);
                            copy("$thedir" . "/" . "$entry", $dirs['imagedir'] . $cleanfile); 
                            $filetype = TaggerUtil::get_file_extension($entry);
                            $finger = getfingerprint($dirs['imagedir'] . $cleanfile);
                            db_insert('images', 
                                    "filename, file_type,display_name,finger,thumb_dir,main_dir,img_group,favorite,size_hash,size_bytes",
                                    "'$cleanfile','$filetype','$entry',UNHEX('$finger'),$dirs[thumbdir_id],$dirs[imagedir_id],$theid,0,UNHEX('$md5size'),$filesize");
                        }
                        $loadedcount++;
                    }

                    if ($loadedcount >= 10){
                        //Has loaded set
                        $returner = array('loader' => 't','done' =>'f','skip' => ($skipamount + 10),'thedir'=>$thedir,'thedest'=>$thedest,'thegroup'=>$thegroup);
                        return json_encode($returner);
                    }
                }
                closedir($handle);     
                $returner = array('loader' => 't','done' => 't','skip' => '0','thedir'=>$thedir,'thedest'=>$thedest,'thegroup'=>$thegroup);
                return json_encode($returner);
            }
        }else{
            
        }
    }
}
