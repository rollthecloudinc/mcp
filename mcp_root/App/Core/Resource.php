<?php
/*
* Every class dependent on the MCP
* should extend this class. 
*/
class MCPResource {
	
	private
	
	/*
	* Package path of requested resource 
	*/
	$_strPkg;

	protected
	
	/*
	* MCP Object
	*/
	$_objMCP;

	public function __construct(MCP $objMCP) {
		$this->_objMCP = $objMCP;
		
		/*
		* Set package 
		*/
		$this->_strPkg = $objMCP->getInstancePkg();
	}
	
	/*
	* Get the path used to request this class
	* 
	* @param str relative path - ability to return parent packages via .. syntax
	* - For example pkg: x.y.z. w/ .. yields: x.y
	* 
	* @return str application path
	*/
	final public function getPkg($strRel='.') {
		
		if($strRel == '.') {
			return $this->_strPkg;
		}
		
		/*
		* Ability to go up ancestory relative to current package using .. or ../.. or ../../../ etc 
		*/
		$strPkg = $this->_strPkg;
		$intAncestory = count(explode('/',$strRel));
		
		for($i=0;$i<$intAncestory;$i++) {
			$pos = strrpos($strPkg,PKG);
			
			if($pos === false) {
				break;
			}
			
			$strPkg = substr($strPkg,0,$pos);
		}
		
		return $strPkg;
		
	}
        
        /*
        * Shortcut to debug method considering the amount of times it is called. 
        * - cut down key strokes
        * 
        */
        final public function debug($mixData) {
            $this->_objMCP->debug($mixData);
        }
	
	/*
	* Bubble events. By default events will not bubble for basic resource.
	*/
	public function getBubbleTarget() {
		return null;
	}

}
?>