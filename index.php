<?php
include_once('libs/config.php');
include_once('libs/imageviewer.php');
//TrimRequest();
$content = '';
$sidebar = '';

//Root Login
if (hasLogin() && !isset($_REQUEST['s'])){
    $_REQUEST['s'] = 'loadviewer';
}else if (!isset($_REQUEST['s'])){
	if (isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] == 'stm' && $_POST['password'] == 'pass'){ //Else Check password
		$_SESSION['access'] = 'granted';
	}else{ //Else login
		include_once(_HTML_TEMPLATES . 'login.php');
        $content = loginPrint();
        printShell($content);
	}
}

if (isset($_REQUEST['a']) && hasLogin()){
	if ($_REQUEST['a'] == 'json'){
		include_once('libs/json.php'); //This will spit back json
		die();
	}else{
		die(_SERVER_SCREAM);
	}
}else if(isset($_REQUEST['s']) && hasLogin()){
    $print = false;
	if ($_REQUEST['s'] =='loadform' ){
		include_once(_HTML_TEMPLATES . 'imageloader.php');
		$content = imageloaderPrint(); //This will load the main form
        $print = true;
	}else if ($_REQUEST['s'] == 'loadviewer' ){
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
        $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$data = imageviewer(0,$search,$page); //This will load the main form
        
        $content = $data['content'];
        $sidebar = $data['sidebar'];
        $script = $data['js'];
		$print = true;
	}else if ($_REQUEST['s'] =='loadimage' ){
		$data = imageviewer($_REQUEST['id']); //This will load the main form
        $content = $data['content'];
        $sidebar = $data['sidebar'];
		$print = true;
	}else if ($_REQUEST['s'] =='searchtag'){   
        if (isset($_REQUEST['searchtagtext'])){
            $searchfor = $_REQUEST['searchtagtext'];
            trim($searchfor);
            if ($searchfor == ''){
                $searchfor = null; //Images without any tags
            }
        }else{
            $searchfor = '';
        }
        
        $data = imageviewer(0,$searchfor); //This will load the main form
        $content = $data['content'];
        $sidebar = $data['sidebar'];
        $script = $data['js'];
		$print = true;
    }else if ($_REQUEST['s'] =='deleteimage'){
        $imageid = $_REQUEST['imageid'];
        $result = deleteimage($imageid);
        $data = imageviewer(0,'',0); //This will load the main form
        
        $content = $data['content'];
        $sidebar = $data['sidebar'];
		$print = true;
    }   
    printShell($content,$sidebar,$script);  
}else{
	include_once(_HTML_TEMPLATES . 'login.php');
    $content = loginPrint();
    printShell($content);
}


function printShell($content,$sidebar = '',$script = ''){
    include_once(_HTML_TEMPLATES . 'header.php');
    $headernav = getheader();
    include_once(_HTML_TEMPLATES . 'shell.php');
    die();
}

?>
