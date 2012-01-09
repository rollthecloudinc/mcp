<?php 
/*
* Manage cached images 
*/
$this->import('App.Core.DAO');
class MCPDAOImageCache extends MCPDAO {
	
	/*
	* Fetch cached image 
	* 
	* @param array image data
	* @param array cached options
	* @return array cached image data
	*/
	public function fetchImage($arrImage,$arrOptions) {
		
		/*
		* Build option filter 
		*/
		foreach($arrOptions as $strOption=>$strValue) {
			$arrFilter[] = sprintf(
				"(cio.images_option = '%s' AND cio.images_value = '%s')"
				,$this->_objMCP->escapeString($strOption)
				,$this->_objMCP->escapeString($strValue)
			);
		}
		
		/*
		* Build query to match options to cached image 
		*/
		$strSQL = sprintf(
			"SELECT 
			      DISTINCT ci.cached_images_id 
			   FROM
			      MCP_CACHED_IMAGES ci
			  INNER 
			   JOIN
			      MCP_CACHED_IMAGES_OPTIONS cio
			     ON
			      ci.cached_images_id = cio.cached_images_id
			  WHERE
			      ci.base_images_id = '%s'
			    AND
			      (%s)
			    AND
			      ci.cached_images_id IN
			      (SELECT
			            ci.cached_images_id
			         FROM
			            MCP_CACHED_IMAGES ci
			         INNER
			          JOIN
			            MCP_CACHED_IMAGES_OPTIONS cio
			            ON
			             ci.cached_images_id = cio.cached_images_id
			         GROUP
			            BY
			             ci.cached_images_id
			        HAVING
			             COUNT(*) = %u)"
			,md5($arrImage['image'])
			,implode(' OR ',$arrFilter)
			,count($arrFilter)
		);
		
		$arrCachedImage = array_pop($this->_objMCP->query($strSQL));
		$arrCache = null;
		
		/*
		* Get cached images data 
		*/
		if($arrCachedImage !== null) {
			/*
			* Reset cache array 
			*/
			$arrCache = array();
			
			/*
			* Locate image file 
			*/
			$file = array_pop(glob("{$this->_objMCP->getImageCachePath()}/cache_{$arrCachedImage['cached_images_id']}.*"));
			
			/*
			* Get raw image contents 
			*/
			$arrCache['image'] = @file_get_contents($file);
			
			/*
			* Get path info to determine image type 
			*/
			$info = pathinfo($file);
			
			/*
			* Determine image type 
			*/
			$arrCache['type'] = $this->_fetchMimeByExt($info['extension']);
		}
			
		return $arrCache;
		
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
		
		/*
		* Build option insert statement w/ cached images id placeholder
		*/
		$arrOptionInsert = array();
		foreach($arrOptions as $strOption=>$strValue) {
			$arrOptionInsert[] = sprintf(
				"({cached_images_id},'%s','%s')"
				,$this->_objMCP->escapeString($strOption)
				,$this->_objMCP->escapeString($strValue)
			);
		}
		
		/*
		* Buld SQL insert for options 
		*/
		$strOptionInsert = sprintf(
			'INSERT INTO MCP_CACHED_IMAGES_OPTIONS (cached_images_id,images_option,images_value) VALUES %s'
			,implode(',',$arrOptionInsert)
		);
		
		/*
		* Build SQL for inserting cached image 
		*/
		$strImageInsert = sprintf(
			"INSERT INTO MCP_CACHED_IMAGES (base_images_id) VALUES ('%s')"
			,md5($arrBaseImage['image'])
		);
		
		/*
		* Insert image 
		*/
		$intCachedImagesId = $this->_objMCP->query($strImageInsert);
		
		/*
		* Write image to cache 
		*/
		$this->_writeImageToCache($intCachedImagesId,$arrImage);
		
		/*
		* Insert options 
		* 
		* Replace cached images id placeholder with actual cached images id
		*/
		$this->_objMCP->query(str_replace('{cached_images_id}',$intCachedImagesId,$strOptionInsert));
		
		/*
		* Return cached images id 
		*/
		return $intCachedImagesId ;
		
	}
	
	/*
	* Write image to the cache folder
	* 
	* @param int cached images id
	* @param array image data
	* @return bool success/failure such as; permissions issue
	*/
	private function _writeImageToCache($intCachedImagesId,$arrImage) {
		
		/*
		* Create image resource 
		*/
		$objImage = imagecreatefromstring($arrImage['image']);
			
		/*
		* Write image to new file in image cache directory 
		*/
		switch($arrImage['type']) {
			case 'image/jpg':
			case 'image/jpeg':
				imagejpeg($objImage,"{$this->_objMCP->getImageCachePath()}/cache_$intCachedImagesId.".str_replace('image/','',$arrImage['type']),100);
				break;
				
			case 'image/gif':
				imagegif($objImage,"{$this->_objMCP->getImageCachePath()}/cache_$intCachedImagesId.gif");
				break;
					
			case 'image/png':
				imagepng($objImage,"{$this->_objMCP->getImageCachePath()}/cache_$intCachedImagesId.png",0);
				break;
					
			case 'image/bmp':
				imagebmp($objImage,"{$this->_objMCP->getImageCachePath()}/cache_$intCachedImagesId.bmp");
				break;
        }
        
        /*
        * Return success or failure 
        */
        return true;
		
	}
	
	/*
	* Fetch images mime type from its extension
	* 
	* @param str extension
	* @return str mime type
	*/
	private function _fetchMimeByExt($ext) {
		switch($ext) {
			case 'jpg':
				return 'image/jpg';
				
			case 'jpeg':
				return 'image/jpeg';
				
			case 'gif':
				return 'image/gif';
					
			case 'png':
				return 'image/png';
					
			case 'bmp':
				return 'image/bmp';
		}
	}
	
}
?>