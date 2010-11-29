<?php
$this->import('App.Core.Resource');
class MCPCookieManager extends MCPResource {

	public function  __construct(MCP $objMCP) {
		parent::__construct($objMCP);
	}
	
	public function setDataValue($strName,$mixData,$boolGlobal=false,$intExpire=0,$boolSite=false,$intRunner=0) {
		
		if($intRunner == 0) {
			$strIndex = 'k'.($boolGlobal === true?0:$this->_objMCP->getSitesId());
			$strName = sprintf('%s[%s]',$strIndex,$strName);
		}
	
		if(is_array($mixData)) {
			foreach($mixData as $strIndex=>$mixValue) {
				$this->setDataValue(sprintf('%s[%s]',$strName,$strIndex),$mixValue,$boolGlobal,$intExpire,$boolSite,($intRunner+1));
			}
		} else {
			if($boolSite === true) {
				setcookie($strName,$mixData,$intExpire,'/',".{$this->_objMCP->getDomain()}");
			} else {
				setcookie($strName,$mixData,$intExpire,'/');
			}
		}
	
	}
	
	public function getDataValue($strName,$boolGlobal=false) {	
		$strIndex = 'k'.($boolGlobal === true?0:$this->_objMCP->getSitesId());	
		return isset($_COOKIE[$strIndex],$_COOKIE[$strIndex][$strName])?$_COOKIE[$strIndex][$strName]:null;
	}

}
?>