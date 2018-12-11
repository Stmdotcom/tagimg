<?php
session_start();

function db_init(){
	global $mysqldb;
	try {
		$mysqldb['conn'] = new PDO("mysql:host=$mysqldb[host];dbname=$mysqldb[db]", "$mysqldb[user]", "$mysqldb[pass]",array(
			PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => true, PDO::ERRMODE_EXCEPTION => true
		));
	} catch (PDOException $e) {
		error_log("DB Init Error!: " . $e->getMessage() . "<br/>");
		die();
	}
}


function db_direct_query($query){
    global $mysqldb;
	db_init();
    try{
        $stmt = $mysqldb['conn']->query($query);
        return $stmt;
    }catch (PDOException $err) {
        debugTrace();
        error_log("SQL ERROR!! " . $err->getMessage());
        error_log("SQK TRACE " . print_r($err->getTrace(),true));
        die();
    }
}

function db_select($table, $fields, $where, $varables = null) {
	global $mysqldb;
	db_init();
	if($where != '1' && strlen($where) > 0) {
		$where = 'WHERE '.$where;
	}else {
		$where = '';
	}	
    $query = "SELECT $fields FROM $table $where";   
    error_log($query);
    if ($varables != null){
        $stmt = $mysqldb['conn']->prepare($query);
        $stmt->execute($varables);
        if($stmt->errorCode() != 0) {
            $errors = $stmt->errorInfo();
            debugTrace();
            error_log("SQL ERROR!! " . $errors[2]);
            die();
        }
        
    }else{
        try{
            $stmt = $mysqldb['conn']->query($query);
        }catch (PDOException $err) {
            debugTrace();
            error_log("SQL ERROR!! " . $err->getMessage());
            error_log("SQK TRACE " . print_r($err->getTrace(),true));
            die();
        }
    }
    
    return $stmt;

}

function db_select_first($table, $fields, $where, $varables = null) {
	$data = db_select($table, $fields, $where . " LIMIT 1",$varables );
    if ($data->rowCount() > 0) {
        return $data->fetch(PDO::FETCH_ASSOC);
    }else{
        return '';
    }
}

/**
 *
 * @global type $mysqldb
 * @param type $table
 * @param type $fields
 * @param type $values
 * @param type $id
 * @return type id of the inserted row
 */
function db_insert($table, $fields='', $values='', $id='id') {
	global $mysqldb;
	db_init();
	$fields = $fields != ''?", $fields":"";
	$values = $values != ''?", $values":"";
	$query = "INSERT INTO $table ($id $fields) VALUES (DEFAULT $values)";
    
    $stmt = $mysqldb['conn']->prepare($query);
    $stmt->execute();
 
    if($stmt->errorCode() == 0) {
        $insertid = $mysqldb['conn']->lastInsertId();
    } else {
       $errors = $stmt->errorInfo();
       error_log("SQL ERROR!! " . $errors[2]);
       error_log("QUERY DUMP ". $query);
       die();
    }
    return $insertid;
}

function db_update($table, $set, $where) {
    global $mysqldb;
	db_init();
	$query = "UPDATE $table SET $set WHERE $where";
    
    $stmt = $mysqldb['conn']->prepare($query);
    $stmt->execute();
 
    if($stmt->errorCode() == 0) {
    } else {
       $errors = $stmt->errorInfo();
       error_log("SQL ERROR!! " . $errors[2]);
       debugTrace();
       die();
    }
	return $stmt;
}

function db_getfield($table, $fld, $q) {
	$data = db_select($table, $fld, $q);
	if ($data->rowCount()) {
        return $data->fetchColumn();
    }else{
        return '';
    }
}


function db_delete($table, $where, $limit = '') {
	global $mysqldb;
	db_init();
	if ($limit) $limit = " LIMIT $limit";
	$query = "DELETE FROM $table WHERE $where $limit";
    
    $stmt = $mysqldb['conn']->prepare($query);
    $stmt->execute();
 
    if($stmt->errorCode() == 0) {
        return true;
    } else {
       $errors = $stmt->errorInfo();
       error_log("SQL ERROR!! " . $errors[2]);
       error_log("QUERY DUMP ". $query);
       die();
    }
}

function debugTrace(){
	array_walk(debug_backtrace(),create_function('$a,$b','error_log("{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']});");'));
}


function dbcut($string){
    global $mysqldb;
	db_init();
    return $mysqldb['conn']->quote($string);
}

function TrimArray(&$array) {
	foreach ($array as $k=>$v) {
		if (!is_array($v)) {
			$array[$k] = dbcut($v);
		}else{
			TrimArray($array[$k]);
		}
	}
}

function TrimRequest() {
	foreach ($_POST as $k=>$v) if (!is_array($v)) $_POST[$k] = dbcut($v);
	foreach ($_REQUEST as $k=>$v) if (!is_array($v)) $_REQUEST[$k] = dbcut($v);
	foreach ($_GET as $k=>$v) if (!is_array($v)) $_GET[$k] = dbcut($v);
}


?>