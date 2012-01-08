<?php 
/*
* Image data access object 
*/
$this->import('App.Core.DAO');
class MCPDAOImage extends MCPDAO {
	
	/*
	* Fetch images mime type from its extension ( reverse self::fetchExtByMime() )
	* 
	* @param str extension
	* @return str mime type
	*/
	public function fetchMimeByExt($ext) {
		
		// @todo: temporary work-around for multiple image/jpeg mimes
		if( $ext == 'jpeg' ) {
			return 'image/jpeg';
		}

		$arrMime = array_pop($this->_objMCP->query(
			"SELECT CONCAT(type,'/',subtype) mime FROM MCP_ENUM_MIME_TYPES WHERE type = 'image' AND ext = :ext"
			,array(
				 ':ext'=>(string) $ext
			)
		));
		
		return $arrMime !== null?$arrMime['mime']:null;
		
	}
	
	/*
	* Determines images extensions based on its mime type ( reverse self::fetchMimeByExt() )
	* 
	* @param str mime type
	* @return str extension
	*/
	public function fetchExtByMime($mime) {
		
		// @todo: temporary work-around for multiple image/jpeg mimes
		if( $mime == 'image/jpeg' ) {
			return 'jpeg';
		}

		list($type,$subtype) = explode('/',$mime);
		$arrMime = array_pop($this->_objMCP->query(
			"SELECT ext FROM MCP_ENUM_MIME_TYPES WHERE type = 'image' AND subtype = :subtype"
			,array(
				':subtype'=>(string) $subtype
			)
		));
		
		return $arrMime !== null?$arrMime['ext']:null;
		
	}
	
	/*
	* Fetch image by id
	* 
	* @param int images id
	* @param str columns to select
	* @return array image data
	*/
	public function fetchById($intId,$strSelect='*') {
		
		/*
		* Fetch image data 
		*/
		return array_pop($this->_objMCP->query("SELECT $strSelect FROM MCP_MEDIA_IMAGES WHERE images_id = {$this->_objMCP->escapeString($intId)}"));
		
	}
	
	/*
	* Resize image 
	* 
	* @param arr image data
	* @return array resized image data
	*/
	public function resize($arrImage,$intWidth=0,$intHeight=0) {
		
		if(isset($arrImage['tmp_name'])) {
			$arrImage['image'] = file_get_contents($arrImage['tmp_name']);
		}
		
		/*
		* Create image resource 
		*/
		$objImage = imagecreatefromstring($arrImage['image']);
		
		/*
		* Calculate image ratio 
		*/
		$intRatio = imagesx($objImage) / imagesy($objImage); 
		
		/*
		* Calculate images new width and height 
		*/
       	if ($intHeight == 0) {
   			$intNewWidth = $intWidth;
          	$intNewHeight = $intWidth / $intRatio;
        } else {
          	$intNewHeight = $intHeight;
          	$intNewWidth = $intHeight * $intRatio;
        } 
        
        /*
        * Create new image 
        */
        $objNewImage = imagecreatetruecolor($intNewWidth,$intNewHeight); 
        
        /*
        * Resize old image and copy new to new image 
        */
        imagecopyresized($objNewImage,$objImage,0,0,0,0,$intNewWidth,$intNewHeight,imagesx($objImage),imagesy($objImage)); 
        
        /*
        * Set-up the buffer and overwrite image contents 
        */
        ob_start();
        switch($arrImage['type']) {
			case 'image/jpg':
			case 'image/jpeg':
				imagejpeg($objNewImage,null,100);
				break;
				
			case 'image/gif':
				imagegif($objNewImage);
				break;
					
			case 'image/png':
				
				/*
				* Save transparency information
				*/
				imagealphablending($objNewImage,false);
				imagesavealpha($objNewImage,true);			
				
				imagepng($objNewImage,null,0);
				break;
					
			case 'image/bmp':
				imagebmp($objNewImage);
				break;
        }
        /*
        * overwrite existing image with resized image 
        */
        $arrImage['image'] = ob_get_contents();
        
        /*
        * Clean the buffer 
        */
        ob_end_clean();
        
        /*
        * Override preliminary images width and height with resized with and height 
        */
        $arrImage['width'] = imagesx($objNewImage);
        $arrImage['height'] = imagesy($objNewImage);
        
        
        /*
        * Destroy image resources 
        */
        imagedestroy($objImage);
        imagedestroy($objNewImage);
        
        /*
        * Return new image data 
        */
        return $arrImage;
		
	}
	
	/*
	* Convert image to grayscale 
	* 
	* @param array image data
	* @retrun array converted image data
	*/
	public function grayscale($arrImage) {
		
		if(isset($arrImage['tmp_name'])) {
			$arrImage['image'] = file_get_contents($arrImage['tmp_name']);
		}
		
		/*
		* Create image resource 
		*/
		$objImage = imagecreatefromstring($arrImage['image']);
		
		/*
		* Create grayscale image resource 
		*/
		$width = imagesx($objImage);
		$height = imagesy($objImage);
		$objGrayScaleImage = imagecreate($width,$height);
		
		
		/*
		* Create 256 color pallete 
		*/
		$arrPalette = array();
		for($c=0;$c<256;$c++) {
			$arrPalette[$c] = imagecolorallocate($objGrayScaleImage,$c,$c,$c);
		}
		
		/*
		* Read original colors pixel by pixel 
		*/
		for($y=0;$y<$height;$y++) {
			for($x=0;$x<$width;$x++) {
				$rgb = imagecolorat($objImage,$x,$y);
				$r = ($rgb >> 16) & 0xFF;
 				$g = ($rgb >> 8) & 0xFF;
 				$b = $rgb & 0xFF;
 				
 				/*
 				* Convert rgb values to grayscale 
 				*/
 				$gs = (($r*0.299)+($g*0.587)+($b*0.114));
 				imagesetpixel($objGrayScaleImage,$x,$y,$arrPalette[$gs]);
			}
		}
		
        /*
        * Set-up the buffer and overwrite image contents 
        */
        ob_start();
        switch($arrImage['type']) {
			case 'image/jpg':
			case 'image/jpeg':
				imagejpeg($objGrayScaleImage,null,100);
				break;
				
			case 'image/gif':
				imagegif($objGrayScaleImage);
				break;
					
			case 'image/png':
				imagepng($objGrayScaleImage,null,0);
				break;
					
			case 'image/bmp':
				imagebmp($objGrayScaleImage);
				break;
        }
        /*
        * overwrite existing image with resized image 
        */
        $arrImage['image'] = ob_get_contents();
        
        /*
        * Clean the buffer 
        */
        ob_end_clean();
        
        /*
        * Destroy image resources 
        */
        imagedestroy($objImage);
        imagedestroy($objGrayScaleImage);
        
        /*
        * Return new image data 
        */
        return $arrImage;
		
	}
	
	/*
	* Insert new image 
	* 
	* @param arr image data
	* @param bool write image file to image directory
	* @return int images id
	*/
	public function insert($arrImage,$boolWrite=false) {
		
		if(isset($arrImage['tmp_name'])) {
			$arrImage['image'] = file_get_contents($arrImage['tmp_name']);
		}
		
		/*
		* Create temporary file to write image to 
		*/
		$strTmp = tempnam('','img');
		
		/*
		* Create image resource 
		*/
		$objImage = imagecreatefromstring($arrImage['image']);
		
		/*
		* Write image to temporary file 
		*/
		switch($arrImage['type']) {
			case 'image/jpg':
			case 'image/jpeg':
				imagejpeg($objImage,$strTmp,100);
				break;
				
			case 'image/gif':
				imagegif($objImage,$strTmp);
				break;
					
			case 'image/png':
				imagepng($objImage,$strTmp,0);
				break;
					
			case 'image/bmp':
				imagebmp($objImage,$strTmp);
				break;
		}
		
		/*
		* Get images width,height and filesize 
		*/
		$arrImage['width'] = imagesx($objImage);
		$arrImage['height'] = imagesy($objImage);
		$arrImage['size'] = sprintf("%u", filesize($strTmp));
		
		/*
		* Cleanup temporary resources 
		*/
		imagedestroy($objImage);
		
		/*
		* Insert image data into database 
		*/
		$intId = $this->_objMCP->query(
			'INSERT INTO MCP_MEDIA_IMAGES (sites_id,creators_id,image_label,image_mime,image_size,image_width,image_height,md5_checksum,image_alt,image_caption,created_on_timestamp) VALUES (:sites_id,:users_id,:name,:type,:size,:width,:height,:checksum,:alt,:caption,NOW())'
			,array(
                             ':sites_id'    => $this->_objMCP->getSitesId()
                            ,':users_id'    => $this->_objMCP->getUsersId()
                            ,':name'        => $arrImage['name']
                            ,':type'        => $arrImage['type']
                            ,':size'        => $arrImage['size']
                            ,':width'       => $arrImage['width']
                            ,':height'      => $arrImage['height']
                            ,':checksum'    => md5($arrImage['image'],false)
                            ,':alt'         => isset($arrImage['image_alt'])?$arrImage['image_alt']:null
                            ,':caption'     => isset($arrImage['image_caption'])?$arrImage['image_caption']:null
                        )
		);
		
		/*
		* Save image to image file directory or destory it
		*/
		if($boolWrite === true && $intId) {
			// @TODO: throw some type of exception of this fails ie. directory unwritable, etc
			rename($strTmp,"{$this->_objMCP->getImageFilePath()}/image_{$intId}.{$this->fetchExtByMime($arrImage['type'])}");
		} else {
			unlink($strTmp);	
		}
		
		return $intId;
		
	}
        
        /*
        * This method can be used to update image meta data. When saving a new image the
        * insert method should be called instead. Otherwise, the image itself will not be properly
        * moved to the disk and referened in the database. This method only exists to update meta
        * data like alt and caption associated with an image. 
        * 
        * @param array image data 
        */
        public function updateMetaData($arrImage) {
            
            $arrMetaData = array();
            
            /*
            * Id required to trigger update 
            */
            if(isset($arrImage['images_id']) && !empty($arrImage['images_id'])) {
                $arrMetaData['images_id'] = $arrImage['images_id'];
            } else if(isset($arrImage['value']) && !empty($arrImage['value'])) {
                $arrMetaData['images_id'] = $arrImage['value'];
            } else {
                return;
            }
            
            /*
            * Only thing really safe to update will be the caption
            * and alt. Everything else is derived based on the image itself
            * thus should not be changed unless the actual image changes.   
            */
            foreach(array('image_alt','image_caption') as $field) {
                if(isset($arrImage[$field])) {
                    $arrMetaData[$field] = $arrImage[$field];
                }
            }
            
            /*
            * Go no further when no meta data exists. 
            */
            if(empty($arrMetaData)) {
                return;
            }
            
            // $this->_objMCP->debug($arrMetaData);
            
            /*
            * Save images meta data to the db 
            */
            return $this->_save(
                 $arrMetaData
		,'MCP_MEDIA_IMAGES'
		,'images_id'
		,array('image_alt','image_caption')
            );
            
        }
        
	
}
?>