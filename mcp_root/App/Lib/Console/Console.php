<?php
class Console {

	private
	
	/*
	* Debugger enabled
	*/
	$_debugger;
	
	public function __construct() {
		
		// enable debugger by default
		$this->enableDebugger();
	}
	
	/*
	* Trigger program error
	*
	* @param str message
	* @param int error level
	* @param str file
	* @param int line number
	* @param arr context
	*/
	public function triggerError($strMessage,$intLevel=E_USER_NOTICE,$strFile=null,$intLine=null,$arrContext=null) {
		
	
		trigger_error($strMessage,$intLevel);
		
		if($this->_debugger) {
			echo '<pre>',debug_print_backtrace(),'</pre>';
		}
		
		die;
		
	}
	
	/*
	* Enables debugging
	*/
	public function enableDebugger() {
		$this->_debugger = true;
	}
	
	/*
	* Disables debugging
	*/
	public function disableDebugger() {
		$this->_debugger = true;
	}

}
?>