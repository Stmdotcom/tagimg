<?php
function getSidebarTagHTML($id){
    
ob_start('mb_output_handler');

?>   
    <div class='sidebar'>
        <div id="newtagarea">
            <form name='newtagform' id='newtagform'>
            <input type="hidden"  name="imageid" value="<?php echo $id; ?>" />
            <label for="newtagtext">Enter a Tag... </label>
            <input type="text" name="newtagtext" class="tagDropDown" ddtype="any" id="newtagtext" value='' maxlength='100'/>
            </form>
            <button type="button" onclick="submittag()">Add Tag</button>
        </div>
        <div>
            <form name="deleteimage" id="deleteimage" method='post' action='index.php?s=deleteimage'>
                 <input type="hidden" name="imageid" id="imageid" value='<?php echo $id; ?>'/>    
            </form>
            <button type="button" onclick="deletetheimage();">Delete Image</button>
        </div>
        <div>
            <form name='freshtagform' id='freshtagform'>
                <div><label for="freshtag0text">Enter Tag 0... </label><input type="text" class="tagDropDown" ddtype="0" name="freshtag0text" id="freshtag0text" value='' maxlength='100'/></div>
                <div><label for="freshtag1text">Enter Tag 1... </label><input type="text" class="tagDropDown" ddtype="1" name="freshtag1text" id="freshtag1text" value='' maxlength='100'/></div>
                <div><label for="freshtag2text">Enter Tag 2... </label><input type="text" class="tagDropDown" ddtype="2" name="freshtag2text" id="freshtag2text" value='' maxlength='100'/></div>
                <div><label for="freshtag3text">Enter Tag 3... </label><input type="text" class="tagDropDown" ddtype="3" name="freshtag3text" id="freshtag3text" value='' maxlength='100'/></div>
             </form>
            <button type="button" onclick="submitfreshtag()">Add Tags</button>
            
            <button type="button" onclick="flipContent(1)">Back</button>
            <br>
            <br>
            <button type="button" onclick="slideshow(-1)"><</button>
            <button type="button" onclick="slideshow(1)">></button>
        </div>
        <div id="tagarea">
        <?php
            echo imagetaglist($id);
            ?>
        </div>
    </div>
<?php

$sidebar = ob_get_contents();
ob_end_clean();
return $sidebar;
}