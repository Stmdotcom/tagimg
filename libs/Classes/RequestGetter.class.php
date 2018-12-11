<?php

/**
 * Cleans Requests objects and only gets what is requested
 * 
 * @author Steven Marsh <steven.m@geoop.com>
 */
class RequestGetter{

	const REQUEST_GET = 'GET';
	const REQUEST_REQUEST = 'REQUEST';
	const REQUEST_POST = 'POST';
	
	const REQUEST_TYPE_NUMERIC = 'numeric';
	const REQUEST_TYPE_TEXT = 'text';
	const REQUEST_TYPE_BOOL = 'bool';
	const REQUEST_TYPE_ISSET = 'isset';
	
	const BOOL_IGNORE = 'IGNORE';
	const BOOL_FALSE = 'FASLE';
	const BOOL_TRUE = 'TRUE';
	
	private $_defaultNumeric = 0;
	private $_defaultText = '';
	private $_defaultBool = self::BOOL_IGNORE; //Three bool types TRUE, FALSE, IGNORE.
	
	private $_requestVarArray = array();
	private $_cleanedRequests = array();
	private $_isCutArray = array();
	private $_cutWarn = false;
	private $_defaultBads = true;
	private $_defaultEmptys = true;
	
	/**
	 * Structured getter/setter function for request varables
	 * @param BOOL $defaultBads Will replace bad request var that are expected to defaults
	 * @param BOOL $defaultEmptys Will replace empty request var that are expected to defaults
	 * @param BOOL $warnNonCuts Throw warrnings if a text varable is used and hasn't been escaped (dbcut)
	 */
	function __construct($defaultBads,$defaultEmptys,$warnNonCuts = false) {	
		if (is_bool($defaultBads)){
			$this->_defaultBads = $defaultBads;
		}else{
			trigger_error("Incorrect init of requestGetter must init bools (DefaultBads)",E_USER_ERROR);
		}
		if (is_bool($defaultEmptys)){
			$this->_defaultEmptys = $defaultEmptys;
		}else{
			trigger_error("Incorrect init of requestGetter must init bools (Default Emptys)",E_USER_ERROR);
		}
		if (is_bool($warnNonCuts)){
			$this->_cutWarn = $warnNonCuts;
		}else{
			trigger_error("Incorrect init of requestGetter must init bools (Cut Warn)",E_USER_ERROR);
		}
	}
	
	function getRequestArray(){
		return $this->_cleanedRequests;
	}
		
	function __set($name, $value) {
		$this->_cleanedRequests[$name] = $value;
	}

	function __get($name) {
		if(isset($this->_cleanedRequests[$name])){
			if ($this->_cutWarn && ($this->_requestVarArray[$name]['type'] == self::REQUEST_TYPE_TEXT)  && ($this->_isCutArray[$name] != true)){
				trigger_error("SECURITY WARNING :: Use of unescaped value from request varables!!! $name => " . print_r($this->_cleanedRequests[$name],true), E_USER_WARNING);
			}
			return $this->_cleanedRequests[$name];
		}else{
			return null;
		}
	}

	function __unset($name) {
		unset($this->_cleanedRequests[$name]);
	}

	function __isset($name) {
		return isset($this->_cleanedRequests[$name]);
	}
		
	/**
	 * Checks if a value exists
	 * @param string Key of value
	 * @return boolean
	 */
	public function exists($key){
		if (!isset($this->_requestVarArray[$key])){
			return false;
		}
		$tempChecker = $this->_requestVarArray[$key];
		$type =	$tempChecker['type'];
		$valCheck = $this->_cleanedRequests[$key];
		
		if ($type == self::REQUEST_TYPE_NUMERIC){
			if ($valCheck !== NULL && is_numeric($valCheck)){
				return true;
			}else{
				return false;
			}
		}else if ($type == self::REQUEST_TYPE_TEXT){
			if ((strlen(trim($valCheck)) > 0) && $valCheck !== NULL){
				return true;
			}else{
				return false;
			}
		}else if ($type == self::REQUEST_TYPE_BOOL){
			if (is_bool($valCheck) && $valCheck !== NULL){
				return true;
			}else{
				return false;
			}
		}else if ($type == self::REQUEST_TYPE_ISSET){
			if ($valCheck === "1" && $valCheck !== NULL){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	
	/**
	 * Returns a value
	 * @param type $key
	 * @return boolean
	 */
	public function getVal($key, $cut = false,$cleanwild = false){
		if (isset($this->_cleanedRequests[$key])){
			$localReturn = $this->_cleanedRequests[$key];
			
			if ($cleanwild == true){
				//$localReturn = db_escape_pg_wildcards($localReturn);
			}
			if ($cut == true){
				if ($this->_cutWarn){
					$this->_isCutArray[$key] = true;
				}
				return dbcut($localReturn);
			}else{
				if ($this->_cutWarn && ($this->_requestVarArray[$key]['type'] == self::REQUEST_TYPE_TEXT)  && ($this->_isCutArray[$key] != true)){
					trigger_error("SECURITY WARNING :: Use of unescaped value from request varables!! $key => " . print_r($localReturn,true), E_USER_WARNING);		
					if ($this->apostCheck($localReturn) === false){
						trigger_error("CRITICAL SECURITY WARNING :: VALUE ABOVE FAILED SIMPLE ' TEST, WOULD BREAK SQL IF USED!!", E_USER_WARNING);
					}
				}
				return $localReturn;
			}
		}else{
			return NULL;
		}
	}
	
	/**
	 * Get value as HTML escaped printable 
	 * @param type $key
	 * @return null
	 */
	public function getHTMLVal($key){
		if (isset($this->_cleanedRequests[$key])){
			return phpToHTML($this->_cleanedRequests[$key]);
		}else{
			return NULL;
		}
	}
	
	/**
	 * Test function to see if a string it "cut" correctly
	 * THIS IS NOT A CLEANER OR DB SAFE CHECK FUNCTION, FOR DEBUG PURPOSES ONLY
	 * @param type $value
	 * @return boolean
	 */
	private function apostCheck($value){
		$stack = 0;
		//Full the stack
		for ($i = 0, $j = mb_strlen($value); $i < $j; $i++) {
			if ($value[$i] === "'"){
				$stack++;
			}
		}
		if($stack & 1){//Is odd
			return false;
		}else{
			return true;
		}
	}
	
	public function setVal($key,$value){
		$this->_cleanedRequests[$key] = $value;
	}

	public function initGet($name,$type,$length = 0,$doCut = false){		
		return $this->initSingle(self::REQUEST_GET, $name,$type,$length,$doCut);
	}
	public function initPost($name,$type,$length = 0,$doCut = false){		
		return $this->initSingle(self::REQUEST_POST, $name,$type,$length,$doCut);
	}
	public function initRequest($name,$type,$length = 0,$doCut = false){		
		return $this->initSingle(self::REQUEST_REQUEST, $name,$type,$length,$doCut);
	}
	
	private function initSingle($rtype, $name,$type,$length,$doCut){		
		$this->addAllCheckSingle($name, $type, $rtype,$length);	
		$requestCheck = false;
		if ($rtype == self::REQUEST_POST){
			$requestCheck = isset($_POST[$name]) ? true : false;
			if($requestCheck){
				$tempValue = $_POST[$name];
			}
		}else if ($rtype == self::REQUEST_GET){
			$requestCheck = isset($_GET[$name]) ? true : false;
			if($requestCheck){
				$tempValue = $_GET[$name];
			}
		}else if ($rtype == self::REQUEST_REQUEST){
			$requestCheck = isset($_REQUEST[$name]) ? true : false;
			if($requestCheck){
				$tempValue = $_REQUEST[$name];
			}
		}
		
		if ($requestCheck){		
			$validatedValue = $this->validateRequest($name,$tempValue, $type, $length, $doCut);
			$this->_cleanedRequests[$name] = $validatedValue[$name];
			return $validatedValue[$name];
		}else{ //Expected but not found
			if ($this->_defaultBads){
				$filledValue =  $this->fillBads($name, $type);
				$this->_cleanedRequests[$name] = $filledValue[$name];
				return $filledValue;
			}else{ //Else null bads
				$this->_cleanedRequests[$name] = NULL;
				return NULL;
			}
		}
	}
	
	public function addAllCheckSingle($key,$type,$rtype,$length = 0){	
		$tempArray = array('type' => $type, 'length' => $length,'rtype' => $rtype);	
		$this->_requestVarArray[$key] = $tempArray;
	}
	
	
	public function addAllCheckArray($requestVarArray){	
		foreach ($requestVarArray as $varName => $varData) {		
			//Check type
			if (isset($varData['type'])){
				//Do nothing
			}else{
				return false; //Fale need a type
			}
			
			//Check length
			if (isset($varData['length'])){
				// Do nothing
			}else{
				$varData['length'] = 0; //length 0 is unlimited
			}
			
			//Check rtype
			if (isset($varData['rtype'])){
				// Do nothing
			}else{
				//Do nothing
			}
			$this->_requestVarArray[$varName] = $varData;//Repack the checked input params
		}
		return true;
	}


	public function initAllRequests($doCut = false){
		
		if (count($this->_requestVarArray) == 0){
			return false;
		}
		
		foreach ($this->_requestVarArray as $key => $value){	
			if ($value['rtype'] == self::REQUEST_POST){
				$resalt = $this->getAPost($key, $value['type'],false, $value['length'], $doCut);	
			}else if ($value['rtype'] == self::REQUEST_REQUEST){
				$resalt = $this->getARequest($key, $value['type'],false, $value['length'], $doCut);	
			}else if ($value['rtype'] == self::REQUEST_GET){
				$resalt = $this->getAGet($key, $value['type'],false, $value['length'], $doCut);	
			}else{
				continue;
			}
			if ($resalt !== false){
				$this->_cleanedRequests[$key] = $resalt[$key];
			}else{
				continue;
			}
		}	
		return $this->_cleanedRequests;
	}
	
	private function getARequest($name,$type,$flat = true,$length = 0,$doCut = false){		
		if (isset($_REQUEST[$name])){
			$tempValue = $_REQUEST[$name];
			
			$validatedValue = $this->validateRequest($name,$tempValue, $type, $length, $doCut);
			if ($flat){
				return $validatedValue[$name];
			}else{
				return $validatedValue;
			}
		}else{ //Expected but not found
			if ($this->_defaultBads){
				$newBad = $this->fillBads($name, $type);
				if ($flat){
					return $newBad[$name];
				}else{
					return $newBad;
				}
			}else{
				if ($flat){
					return NULL;
				}else{
					return array($name => NULL);
				}
			}
		}
	}
	
	private function getAPost($name,$type,$flat = true,$length = 0,$doCut = false){		
		if (isset($_POST[$name])){
			$tempValue = $_POST[$name];
			$validatedValue = $this->validateRequest($name,$tempValue, $type, $length, $doCut);
			if ($flat){
				return $validatedValue[$name];
			}else{
				return $validatedValue;
			}
		}else{ //Expected but not found
			if ($this->_defaultBads){
				$newBad = $this->fillBads($name, $type);
				if ($flat){
					return $newBad[$name];
				}else{
					return $newBad;
				}
			}else{
				if ($flat){
					return NULL;
				}else{
					return array($name => NULL);
					
				}
			}
		}
	}
	
	private function getAGet($name,$type,$flat = true,$length = 0,$doCut = false){		
		if (isset($_GET[$name])){
			$tempValue = $_GET[$name];
			$validatedValue = $this->validateRequest($name,$tempValue, $type, $length, $doCut);
			if ($flat){
				return $validatedValue[$name];
			}else{
				return $validatedValue;
			}
		}else{ //Expected but not found
			if ($this->_defaultBads){
				$newBad = $this->fillBads($name, $type);
				if ($flat){
					return $newBad[$name];
				}else{
					return $newBad;
				}
			}else{
				if ($flat){
					return NULL;
				}else{
					return array($name => NULL);	
				}
			}
		}
	}
	
	private function fillBads($key, $type){
		if ($type == self::REQUEST_TYPE_NUMERIC){
			return  array($key => $this->_defaultNumeric);
		}else if ($type == self::REQUEST_TYPE_TEXT){
			return array($key => "$this->_defaultText");
		}else if ($type == self::REQUEST_TYPE_BOOL){
			if ($this->_defaultBool === self::BOOL_TRUE){
				return array($key => true);
			}else if ($this->_defaultBool === self::BOOL_FALSE){
				return array($key => false);
			}else if ($this->_defaultBool === self::BOOL_IGNORE){
				return array($key => NULL);
			}
		}else{
			return  array($key => NULL);
		}
	}
	
	private function validateRequest($key,$tempValue,$type,$length,$doCut){
		if ($type == 'numeric'){
			if (is_numeric($tempValue)){
				return array($key => ($tempValue* 1)); //Check further....
			}else{
				if($this->_defaultBads){
					return array($key => $this->_defaultNumeric);
				}else{
					return array($key => NULL);
				}
			}
		}else if ($type == self::REQUEST_TYPE_TEXT){				
			if ($length > 0){ //If length then trim string
				$tempValue  = substr($tempValue , 0, $length);
			}				
			if ($doCut){
				if ($this->_cutWarn){
					$this->_isCutArray[$key] = true;
				}
				$tempValue = dbcut($tempValue);
			}else if ($this->_cutWarn){
				$this->_isCutArray[$key] = false;
			}
			return array($key => "$tempValue");
		}else if ($type == self::REQUEST_TYPE_BOOL){ //If bool is here then it is assumed true unless 'f'
			if ($tempValue == 'f'){
				return array($key => false); 
			}else{
				return array($key => true); //Mark as a litiral true
			}		
		}else if ($type == self::REQUEST_TYPE_ISSET){				
			return array($key => "1");
		}		
	}
}
?>
