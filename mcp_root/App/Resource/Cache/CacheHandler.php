<?php
/*
* Provides facade interface to caching data access layer. Delegates
* request to appropriate cache data access layer. Performes operations
* on top of data access layer when necessary.
*/
class MCPCacheHandler extends MCPResource {
	
	protected static
	
	/*
	* Singleton CacheHandler instance 
	*/
	$_objCacheHandler;
	
	protected
	
	/*
	* Image cache 
	*/
	$_objDAOImageCache
	
	/*
	* Serialized and atomic value cache 
	*/
	,$_objDAODataCache;
	
	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		$this->_init();
	}
	
	public static function createInstance(MCP $objMCP) {
		if(self::$_objCacheHandler === null) {
			self::$_objCacheHandler = new MCPCacheHandler($objMCP);
		}
		return self::$_objCacheHandler;
	}
	
	protected function _init() {
		
		/*
		* Get Image cache data access layer 
		*/
		$this->_objDAOImageCache = $this->_objMCP->getInstance('App.Resource.Cache.DAO.DAOImageCache',array($this->_objMCP));
		
		/*
		* Get Data cache data access layer 
		*/
		$this->_objDAODataCache = $this->_objMCP->getInstance('App.Resource.Cache.DAO.DAODataCache',array($this->_objMCP));
		
	}
	
	/*
	* Set cache value
	* 
	* @param str cached name
	* @param mix value
	* @param str package cached value belongs to
	* @return bool success/failure
	*/
	public function setDataValue($strName,$mixValue,$srPkg=null) {
		return $this->_objDAODataCache->setCacheValue($strName,$mixValue,$srPkg);
	}
	
	/*
	* Get cache value
	* 
	* @param str cached name
	* @param str package cached value belongs to
	* @return mix cached value
	*/
	public function getDataValue($strName,$strPkg=null) {
		return $this->_objDAODataCache->fetchCacheValue($strName,$strPkg);
	}
	
	/*
	* Expire cache value
	* 
	* @param str cached name
	* @param str package cached value belongs to
	* @return bool success/failure
	*/
	public function expireDataValue($strName,$strPkg=null) {
		return $this->_objDAODataCache->expireCacheValue($strName,$strPkg);
	}
	
	/*
	* Add image to cache 
	* 
	* @param array base image data
	* @param array cache image data
	* @param array options
	* @return cached images id
	*/
	public function cacheImage($arrBaseImage,$arrImage,$arrOptions) {
		return $this->_objDAOImageCache->cacheImage($arrBaseImage,$arrImage,$arrOptions);
	}
	
	/*
	* Fetch cached image 
	* 
	* @param array image data
	* @param array cached options
	* @return array cached image data
	*/
	public function fetchImage($arrImage,$arrOptions) {
		return $this->_objDAOImageCache->fetchImage($arrImage,$arrOptions);
	}
	
}
?>
