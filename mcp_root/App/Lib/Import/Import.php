<?php
class Import {

	private
	
	$_objConsole;

	public function __construct(Console $objConsole) {
		$this->_objConsole = $objConsole;
	}
	
	/*
	* Imports requested package
	*
	* @param str package
	* [@param] bool ignore error
	* @return bool
	*/
	public function import($strPkg,$boolError=true) {
		
		$strPath = ROOT.DS.str_replace(PKG,DS,$strPkg).'.php';		
		
		if(file_exists($strPath)) {		
			return require_once($strPath);			
		} 
		
		$intPos = strrpos($strPkg,PKG);
		$strPath = substr($strPath,0,-4).DS.substr($strPkg,$intPos+1).'.php';
		
		if(file_exists($strPath)) {
			return require_once($strPath);
		}
			
		return $boolError === true?$this->_objConsole->triggerError("Package $strPkg not found"):true;
		
	}
	
}
?>