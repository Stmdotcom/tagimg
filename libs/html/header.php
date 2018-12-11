<?php
function getheader(){
ob_start('mb_output_handler');

?>
<div class="imageview-header" width="100%;"> 
    <span><a href="index.php?s=loadviewer">Image Viewer</a></span>
    <span><a href="index.php?s=loadform">Image Loader</a></span>
</div>
<?php

$header = ob_get_contents();
ob_end_clean();
return $header;
}
?>
