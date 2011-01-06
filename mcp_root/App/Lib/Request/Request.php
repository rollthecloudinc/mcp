<?php
class Request {

	private
	
	/*
	* Requested modules name
	*/
	$_strRequestModule
	
	/*
	* Arguments following module inside URL
	*/
	,$_arrRequestArgs;

	public function __construct() {
	
		$arrServerData = $this->getServerData();
		$arrRequestArgs = isset($arrServerData['PATH_INFO'])?explode('/',trim($arrServerData['PATH_INFO'],'/')):array();
		
		// set requested module name 
		$this->_strRequestModule = array_shift($arrRequestArgs);
		
		// set everything following modules name as arguments
		$this->_arrRequestArgs = $arrRequestArgs;
	
	}
	
	/*
	* Get the requested modules name
	*
	* @return str
	*/
	public function getRequestModule() {
		return $this->_strRequestModule;
	}
	
	/*
	* Override the current requested module
	* 
	* @param str request module
	*/
	public function setRequestModule($strModule) {
		$this->_strRequestModule = $strModule;
	}
	
	/*
	* Get request arguments
	*
	* @return array
	*/
	public function getRequestArgs() {
		return $this->_arrRequestArgs;
	}
	
	/*
	* Get POST data
	*
	* @param post array key
	* @return array
	*/
	public function getPostData($strName=null) {	
		if($strName == null) {
			return $_POST;
		} else {
			$post = isset($_POST[$strName])?$_POST[$strName]:null;
			
			// add in files
			if($post !== null && isset($_FILES[$strName],$_FILES[$strName]['error'])) {
				
				foreach(array_keys($_FILES[$strName]['error']) as $field) {
					foreach(array_keys($_FILES[$strName]) as $attr) {
						
						// multiple file upload handling vs. single upload
						if( is_array($_FILES[$strName][$attr][$field]) ) {
							
							foreach($_FILES[$strName][$attr][$field] as $item=>$data) {
								$post[$field][$item][$attr] = isset($_FILES[$strName][$attr][$field][$item])?$_FILES[$strName][$attr][$field][$item]:null;
							}
							
						} else {
							
							$post[$field][$attr] = isset($_FILES[$strName][$attr][$field])?$_FILES[$strName][$attr][$field]:null;
							
						}
						
					}
				}
			}
			
			return $post;
			
		}
	}
	
	/*
	* Get SESSION data
	*
	* @return array
	*/
	public function getSessionData() {
		return $_SESSION;
	}
	
	/*
	* Get SERVER data
	*
	* @return array
	*/
	public function getServerData() {
		return $_SERVER;
	}
	
	/*
	* Get REQUEST data
	*
	* @return array
	*/
	public function getRequestData() {
		return $_REQUEST;
	}
	
	/*
	* Get GET data
	*
	* @return array
	*/
	public function getGetData($strName=null) {
		if($strName == null) {
			return $_GET;
		} else {
			return isset($_GET[$strName])?$_GET[$strName]:null;
		}
	}

}
?>