<?php
function imageloaderPrint(){
    ob_start('mb_output_handler');

?>

        <img src='img/header.jpg' class='small'>
        <form name='dirform' id='dirform' method='post' action='index.php?a=json&jaction=loaddir'>
        <div>Source <input type='text' name='thedir'></input></div>
        
        <input type="file" id="files" name="files[]" multiple />
        <output id="list"></output>
        
        <div>Destination <input type='text' name='thedest'></input></div>
        <div>Group <input type='text' name='thegroup'></input></div>
        <a href="javascript:submitDirectory('dirform');" >SUBMIT</a>
        </form>
<?php
$header = ob_get_contents();
ob_end_clean();
return $header;
}
?>