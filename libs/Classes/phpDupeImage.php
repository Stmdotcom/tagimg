<?php

// Copyright pawz, 2010.
// Free for any profit or non-profit use.
// You may freely distribute or modify this code.

class phpDupeImage {
	
	public  $colouroffsettype = 'normal';
	private $colourOffsets = array('red' => .30,'green' => .59, 'blue' => .11 );
	private $colourOffsetsHDTV = array('red' => .21,'green' => .72, 'blue' => .07 );
	
    // Width for thumbnail images we use for fingerprinting.
    // Default is 150 which works pretty well.
    public $thumbWidth = 150;                               
    // Sets how sensitive the fingerprinting will be. 
    // Higher numbers are less sensitive (more likely to match). Floats are allowed.
    public $sensitivity = 2;                                
    // Sets how much deviation is tolerated between two images when doing an thorough comparison.
    public $deviation = 10;                                 
    // Defines what table the image fingerprints are stored in.
    public $image_table = 'pictures';                       
    // Defines the name of the field which contains the fingerprint.
    public $fingerprint_field = 'pictures_fingerprint';     
    // Sets the name of the field which contains the filename.
    public $filename_field = 'pictures_image';              
    // Sets the path to where completed files are stored for checking against.
    public $completed_files_path = '';                      
    // Sets the width and height of the thumbnail sized image we use for deep comparison.
    public $small_size = 16;                                


    /* *******************************************************
    *   Is Unique
    *
    *   This function checks whether the file is unique
    *   (ie, the checksum is not already in the database)
    *   If the fingerprint is already in the database, it 
    *   calls are_duplicates to compare them in more detail
    *   It returns an md5 hash if unique or -1 if not.
    ******************************************************* */
    public function is_unique($filename,$strict = false) {

        $fingerprint = $this->fingerprint($filename);
			
		$output =  db_select("$this->image_table", "count(id)" , "$this->fingerprint_field = '$fingerprint'");
		$row = $output->fetch(PDO::FETCH_ASSOC);
		echo (print_r($row));
        if ($row > 0) {
            // If similar files exist, check them
            $match_found = 1;
			if ($strict){
                $match_found = 0;

                $imageone = $this->getsamllimage($filename);
                
                $datas = db_select("images,dir_lib", "location, filename", 
                        "main_dir = dir_lib.id and dir_lib.type = 1 and images.{$this->fingerprint_field} = '$fingerprint'", array($fingerprint));
               //$datas = db_select("$this->image_table", "count(id)" , "$this->fingerprint_field = '$fingerprint'");
                
                while( $row = $datas->fetch(PDO::FETCH_ASSOC) ) { 
                    if ($this->are_duplicates($imageone, 
                        $this->completed_files_path."/".$row[$this->filename_field])) {
                        $match_found = 1;
                        continue;
                    }
                }
            }
            if ($match_found === 0) {
                return -1;
            } else {
                return $fingerprint;
            }
        } else {
            // No matching fingerprints found so return true.
            return true;
        }

    }
 
	private function getsamllimage($file1){
		$image1_src = @imagecreatefromjpeg($file1);
		list($image1_width, $image1_height) = getimagesize($file1);
        $image1_small = imagecreatetruecolor($this->small_size, $this->small_size);
	    imagecopyresampled($image1_small, $image1_src, 0, 0, 0, 0, $this->small_size, $this->small_size, $image1_width, $image1_height);
		return $image1_small;
	}
	
    /* *******************************************************
    *   Are Duplicates
    *
    *   This function compares two images by resizing them
    *   to a common size and then analysing the colours of
    *   each pixel and calculating the difference between
    *   both images for each colour channel and returns
    *   an index representing how similar they are.
    ******************************************************* */
    private function are_duplicates($file1, $file2) {

		//STEVEN: There is a major flaw in the fucntion as the base image is rebuild on each compare when only the image being compared to needs to be rebuild
		$image1_small = $file1;
        // Load in both images and resize them to 16x16 pixels
        $image2_src = @imagecreatefromjpeg($file2);
        list($image2_width, $image2_height) = getimagesize($file2);
        $image2_small = imagecreatetruecolor($this->small_size, $this->small_size);
        imagecopyresampled($image2_small, $image2_src, 0, 0, 0, 0, $this->small_size, $this->small_size, $image2_width, $image2_height);

        // Compare the pixels of each image and figure out the colour difference between them
        for ($x = 0; $x < 16; $x++) {
            for ($y = 0; $y < 16; $y++) {
                $image1_color = imagecolorsforindex($image1_small, 
                imagecolorat($image1_small, $x, $y));
                $image2_color = imagecolorsforindex($image2_small, 
                imagecolorat($image2_small, $x, $y));
                $difference +=  abs($image1_color['red'] - $image2_color['red']) + 
                                abs($image1_color['green'] - $image2_color['green']) +
                                abs($image1_color['blue'] - $image2_color['blue']);
            }
        }
        $difference = $difference / 256;
        if ($difference <= $this->deviation) {
            return 1;
        } else {
            return 0;
        }

    }

    /* *******************************************************
    *   Fingerprint
    *
    *   This function analyses the filename passed to it and
    *   returns an md5 checksum of the file's histogram.
    ******************************************************* */
    public function fingerprint($filename) {

        // Load the image. Escape out if it's not a valid image     
        $type = TaggerUtil::isImageRead($filename);
        switch($type){
            case IMAGETYPE_GIF:
                error_log("gif");
                $image = @imagecreatefromgif($filename);
                break;
            case IMAGETYPE_PNG:
                error_log("png");
                $image = @imagecreatefrompng($filename);
                break;
            case IMAGETYPE_JPEG:
                error_log("jpg");
                $image = @imagecreatefromjpeg($filename);
                break;
            default:
                error_log("unsupported file type");
                return -1;
        }
        
        if (!$image) {
            return -1;
        }

        // Create thumbnail sized copy for fingerprinting
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = $this->thumbWidth / $width;
        $newwidth = $this->thumbWidth;
        $newheight = round($height * $ratio); 
        $smallimage = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($smallimage, $image, 0, 0, 0, 0, 
        $newwidth, $newheight, $width, $height);
        //$palette = imagecreatetruecolor(1, 1);
        $gsimage = imagecreatetruecolor($newwidth, $newheight);

        // Convert each pixel to greyscale, round it off, and add it to the histogram count
        //$greyscaleout = 0;
        
        $numpixels = $newwidth * $newheight;
        $histogram = array();
        for ($i = 0; $i < $newwidth; $i++) {
            for ($j = 0; $j < $newheight; $j++) {
                $pos = imagecolorat($smallimage, $i, $j);
                $cols = imagecolorsforindex($smallimage, $pos);
                $r = $cols['red'];
                $g = $cols['green'];
                $b = $cols['blue'];
                // Convert the colour to greyscale using 30% Red, 59% Blue and 11% Green //This wording is incorrect, order is "r g b"
				if ($this->colouroffsettype == 'HDTV'){
					$greyscale = round(($r * $this->colourOffsetsHDTV['red']) + ($g * $this->colourOffsetsHDTV['green']) + ($b * $this->colourOffsetsHDTV['blue'])); 
				}else{
                    $greyscale = round(($r * $this->colourOffsets['red']) + ($g * $this->colourOffsets['green']) + ($b * $this->colourOffsets['blue'])); 
				}				
                
                
                
                $greyscale++;
                
              //  while ($greyscaleout < 300){
               //     error_log("Greyscaleout "  . print_r($greyscale,true));
              //      $greyscaleout++;
              //  }
                
                $value = (round($greyscale / 16) * 16) -1;
                
                $histogram[$value] = isset($histogram[$value])? $histogram[$value] + 1 : 1;
                
                //$histogram[$value]++;
            }
        }

        // Normalize the histogram by dividing the total of each colour by the total number of pixels
        $max = 0;
        $normhist = array();
        $pixelcounter = 0;
        foreach ($histogram as $value => $count) {
            $normhist[$value] = $count / $numpixels;
             if ($normhist[$value] > $max) {
                $max = $normhist[$value];
            }
            $pixelcounter++;
            
        }
       // error_log("HISTOGRAM DUMP " . print_r($histogram,true));
       // error_log("NUM PIXEL " . $pixelcounter);
      //  error_log("NUM PIXEL2 " . $numpixels);      
       // error_log("NORMAL HISTOGRAM DUMP " . print_r($normhist,true));

        // Create a string from the histogram (with all possible values)
        $histstring = "";
        for ($i = 15; $i <= 255; $i = $i + 16) {
            if (isset($normhist[$i])){        
                $h = ($normhist[$i] / $max) * $this->sensitivity;
                $height = round($h);
                $histstring .= $height;
            }else{
                $histstring .= '0';
            }
        }

       // error_log("Histstring " . $histstring);
        
        // Destroy all the images that we've created
        imagedestroy($image);
        imagedestroy($smallimage);
        imagedestroy($gsimage);

        // Generate an md5sum of the histogram values and return it
        return $checksum = md5($histstring);
    }
}           
?>