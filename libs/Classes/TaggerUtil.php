<?php

/**
 * Description of ImageUtil
 *
 * @author Stm
 */
class TaggerUtil {
    
    
   static $_IMAGE_ALLOW = array('png','jpeg','jpg','gif');
    
   static function isImage($file_name){
        if (in_array(strtolower(TaggerUtil::get_file_extension($file_name)), TaggerUtil::$_IMAGE_ALLOW)){
            return true;
        }else{
            return false;
        }
    }
    
    static function get_file_extension($file_name) {
        return substr(strrchr($file_name,'.'),1);
    }
    
    
    static function isImageRead($fullname){
        list($width,$height,$type) = getimagesize($fullname);
        if(!in_array($type,array(IMAGETYPE_GIF,IMAGETYPE_PNG,IMAGETYPE_JPEG))){
            return false;
        }else{
            return $type;
        }
    }
    
    static function countImages($dirtocount){
        $dir = new DirectoryIterator($dirtocount);
        foreach($dir as $file ){
            $x += ($this->isImage($file)) ? 1 : 0;
        }
        return $x;
    }
    
}
