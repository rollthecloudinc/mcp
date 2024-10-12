<?php 
/*
* Manages cached serialized and atomic values
*/
class MCPDAODataCache extends MCPDAO {
	
	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
	}
	
	/*
	* Set cache value
	* 
	* @param str cached name
	* @param mix value
	* @param str package
	* @return bool success/failure
	*/
	public function setCacheValue($strName,$mixValue,$strPkg=null) {
		return $this->_objMCP->query(sprintf(
			"INSERT INTO MCP_CACHED_DATA (sites_id,pkg,cache_name,cache_value,flush_cache,serialized,created_on_timestamp) VALUES (%s,%s,'%s','%s',0,%s,NOW()) ON DUPLICATE KEY UPDATE cache_value = VALUES(cache_value),flush_cache = VALUES(flush_cache)"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,empty($strPkg)?"''":"'{$this->_objMCP->escapeString($strPkg)}'"
			,$this->_objMCP->escapeString($strName)
			,is_array($mixValue)?base64_encode(serialize($mixValue)):$this->_objMCP->escapeString($mixValue)
			,is_array($mixValue)?'1':'0'
		));
	}
	
	/*
	* Get cache value
	* 
	* @param str cached name
	* @param str package
	* @return mix cached value
	*/
	public function fetchCacheValue($strName,$strPkg=null) {
		
		/*
		* Locate cached value if it hasn't been flushed 
		*/
		$arrValue = array_pop($this->_objMCP->query(sprintf(
			"SELECT * FROM MCP_CACHED_DATA WHERE sites_id = %s AND cache_name = '%s' AND pkg %s AND flush_cache = 0"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strName)
			,empty($strPkg)?"= ''":"= '{$this->_objMCP->escapeString($strPkg)}'"
		)));
		
		/*
		* Unserialize serialized data 
		*/
		if($arrValue !== null && $arrValue['serialized'] == 1) {
			$arrValue['cache_value'] = unserialize(base64_decode($arrValue['cache_value']));
		}
		
		return $arrValue;
		
	}
	
	/*
	* Expire cache value
	* 
	* @param str cached name
	* @param str package
	* @return bool success/failure
	*/
	public function expireCacheValue($strName,$strPkg=null) {
		return $this->_objMCP->query(
			"UPDATE MCP_CACHED_DATA SET flush_cache = 1 WHERE sites_id = %s AND cache_name = '%s' AND pkg %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strName)
			,empty($strPkg)?"= ''":"= '{$this->_objMCP->escapeString($strPkg)}'"
		);
	}
	
}
?>