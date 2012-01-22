<?php 
/*
* Root level master module 
* 
* NOTE: Module uses sites master template rather
* than having an associated mirrored template file.
*/
class MCPUtilMaster extends MCPModule {
	
	public function execute($arrArgs) {
		switch(basename($_SERVER['SCRIPT_NAME'])) {
			case 'asset.php':
				return $this->_executeAsset();
                    
			case 'css.php':
				return $this->_executeCSS();
				
			case 'js.php':
				return $this->_executeJS();
				
			case 'img.php':
				return $this->_executeImg();
				
			case 'file.php':
				return $this->_executeDownloadRequest();
				
			case 'mod.php':
				return $this->_executeModuleRequest();
				
			case 'dao.php':
				return $this->_executeDAORequest();
				
			case 'public.php':
				return $this->_executePublicRequest();
			
			default:
				return $this->_executeIndex();
		}
	}
	
	/*
	* Normal request 
	*/
	protected function _executeIndex() {
		
		// Get the requested modules name
		$strRequestModule = $this->_objMCP->getModule();
		
		// Get the request arguments
		$arrRequestArgs = $this->_objMCP->getArgs();
		
		// Directory name of current site
		$strSite = $this->_objMCP->getSite();
		
		// Execute and Get requested modules content
		// NOTE: These variables will exist in the global template namespace
		$this->_objMCP->assign('TITLE',$this->_objMCP->getConfigValue('site_title'));		
		
                $this->_objMCP->setMetaData('title',$this->_objMCP->getConfigValue('site_title'));
                
                // assets ---------------------------------------------------------
                
                $this->_objMCP->addCss(array(
                    'path'=>'/lib/jquery-plugin/ui/v1.8.13/css/ui-lightness/jquery-ui-1.8.13.custom.css'
                ));
                $this->_objMCP->addJs(array(
                    'path'=>'/lib/jquery/v1.5/jquery-1.5.min.js'
                ));
                $this->_objMCP->addJs(array(
                    'path'=>'/lib/jquery-plugin/ui/v1.8.13/js/jquery-ui-1.8.13.custom.min.js'
                ));
                $this->_objMCP->addJs(array(
                     'path'=>'/lib/ckeditor/v3.5.2/ckeditor.js'
                    ,'bundle'=>false
                ));
                $this->_objMCP->addJs(array(
                    'path'=>'/lib/ckeditor/v3.5.2/adapters/jquery.js'
                    ,'bundle'=>false
                ));
                $this->_objMCP->addJs(array(
                    'path'=>'/theme/admin/default/js/form.js'
                ));
                $this->_objMCP->addCss(array(
                    'path'=>'/lib/bootstrap/v1.4.0/bootstrap.css'
                ));
                
                // -----------------------------------------------------------------
                
                $this->_objMCP->assign('REQUEST_CONTENT',$this->_objMCP->executeModule("Site.$strSite.Module.$strRequestModule",$arrRequestArgs));
		
		// The / character triggers root relative template retrieval
		return '/'.$this->_objMCP->getMasterTemplate();
		
	}
	
	/*
	* Module request 
	*/
	protected function _executeModuleRequest() {
		
		/*
		* Make master template blank 
		*/
		$this->_objMCP->setMasterTemplate('/Component/Util/Template/Master/Blank.php');
		
		/*
		* Execute request as normal 
		*/
		return $this->_executeIndex();
		
	}
	
	/*
	* Data request
	*/
	protected function _executeDAORequest() {
		
		/*
		* Declare template vars 
		*/
		$intError = 0;
		$mixData = null;
		$strFault = '';
		
		/*
		* Make master template blank 
		*/
		$this->_objMCP->setMasterTemplate('/Component/Util/Template/Master/Blank.php');
		
		/*
		* Get dao post info 
		*/
		$arrDAO = $this->_objMCP->getGet('dao');
		
		$strPkg = isset($arrDAO['pkg'])?$arrDAO['pkg']:null;
		$strMethod = isset($arrDAO['method'])?$arrDAO['method']:null;
		$arrArgs = isset($arrDAO['args'])?$arrDAO['args']:null;
		
		/*
		* Checks to run before even attempting request 
		* 
		* NOTE: All below checks are vital to securing restful data access interface
		*/
		if(empty($strPkg)) {
			$strFault = 'Package required';
			$intError = 1;
		}
		
		if(!$intError && empty($strMethod)) {
			$strFault = 'Method required';
			$intError = 2;
		}
		
		/*
		* When no errors exist attempt to import DAO 
		*/
		if(!$intError && $this->_objMCP->import($strPkg,false) && !class_exists('MCP'.array_pop(explode(PKG,$strPkg)))) {
			$strFault = "$strPkg doesn't exist";
			$intError = 3;
		}
		
		/*
		* Create instance of DAO 
		*/	
		if(!$intError) {	
			$objDAO = $this->_objMCP->getInstance($strPkg,array($this->_objMCP));
			
			/*
			* Make sure no one is trying to access aplication level modules - treat as if doesn't exist for security reasons
			*/
			if(!$intError && !($objDAO instanceof MCPDAO)) {
				$intError = 3;
				$strFault = "$strPkg doesn't exist";
			}
			
			/*
			* Make sure method exists 
			*/
			if(!$intError && !method_exists($objDAO,$strMethod)) {
				$intError = 5;
				$strFault = "$strPkg doesn't have a $strMethod method";
			}
			
			/*
			* Make request 
			*/
			if(!$intError) {
				$mixData = call_user_func_array(array($objDAO,$strMethod),$arrArgs);
			}
			
		}
		
		/*
		* Assign template data 
		*/
		$this->_objMCP->assign('DAO_REQUEST_DATA',$mixData);
		$this->_objMCP->assign('DAO_REQUEST_ERROR',$intError);
		$this->_objMCP->assign('DAO_REQUEST_FAULT',$strFault);
		
		return str_replace(ROOT,'',$this->getTemplatePath()).'/DAO/DAO.php';
		
	}
	
	/*
	* Stylesheet request 
	*/
	protected function _executeCSS() {
		
		$file = sprintf(
			$this->_objMCP->getCSSFolder().'/%s%s'
			,$this->_objMCP->getModule()
			,$this->_objMCP->getArgs()?DS.implode(DS,$this->_objMCP->getArgs()):''
		);
	
		$this->_objMCP->assign('REQUEST_CONTENT',$file);
		
		return str_replace(ROOT,'',$this->getTemplatePath()).'/CSS/CSS.php';
	}
	
	/*
	* JavaScript request 
	*/
	protected function _executeJS() {
		
		$file = sprintf(
			$this->_objMCP->getJSFolder().'/%s%s'
			,$this->_objMCP->getModule()
			,$this->_objMCP->getArgs()?DS.implode(DS,$this->_objMCP->getArgs()):''
		);
	
		$this->_objMCP->assign('REQUEST_CONTENT',$file);
		
		return str_replace(ROOT,'',$this->getTemplatePath()).'/JS/JS.php';
	}
        
        /*
        * Asset request 
        */
        protected function _executeAsset() {
            
                $files = array();
                $type = $this->_objMCP->getModule();
                
                if(!in_array($type,array('css','js'))) {
                    throw new Exception('Asset type must be one of css or js');
                }
            
                $assets = $this->_objMCP->getSessionValue(MCP::SESSION_ASSET_KEY);
                
                foreach($assets[$type] as &$data) {
                    
                    if(isset($data['bundle']) && !$data['bundle']) {
                        continue;
                    }
                    
                    $path = null;
                    
                    if(strpos($data['path'],DS) === 0) {
                        $path = WWW.$data['path'];
                    } else {
                        $path = ROOT.$data['path'];
                    }
                    
                    if(isset($data['inline'])) {
                    
                        $files[] = array('inline'=>$data['inline']);
                        
                    } else if(preg_match('/.*?\/\*$/',$path)) {
                            foreach(scandir(rtrim(rtrim($path,'*'),DS)) as $file) {
                                if(strpos($file,".$type") !== false) {
                                    $files[] = array('file'=>rtrim($path,'*').$file);
                                }
                            }
                    } else {
                        
                        if($path === null || !file_exists($path)) {
                            throw new Exception('Unable to locate asset file.');
                        } else {
                            $files[] = array('file'=>$path);
                        }
                        
                    }
                    
                }
                
                $this->_arrTemplateData['assets'] = $files;
                
                switch($type) {
                   case 'js':
                       header("Content-Type: text/javascript");
                       break;
                   
                   case 'css':
                       header("Content-Type: text/css");
                       break;
                   
                   default:
                }
                
                return str_replace(ROOT,'',$this->getTemplatePath()).'/Asset/Asset.php';
            
        }
	
	/*
	* Public download folder access - serves up files in download directly for download
	*/
	protected function _executeDownloadRequest() {
		
		/*
		* Merge all arguments 
		*/
		$arrArgs = array_merge(array($this->_objMCP->getModule()),$this->_objMCP->getArgs());
		
		/*
		* Make file path 
		*/
		if( count($arrArgs) === 1 && is_numeric($arrArgs[0]) ) {
			
			// Get the file data access layer
			$objDAOFile = $this->_objMCP->getInstance('App.Resource.File.DAO.DAOFile',array($this->_objMCP));
			
			// Fetch the file info
			$arrFile = $objDAOFile->fetchById($arrArgs[0]);
			
			// create path to file
			$strFilePath = $arrFile['file_path'];
			
		} else {
			// file stored in site folder
			$strFilePath = $this->_objMCP->getDownloadFolder().DS.implode(DS,$arrArgs);
		}
		
		// echo "<p>$strFilePath</p>"; exit;
		
		/*
		* Validate that file exists 
		*/
		if(!file_exists($strFilePath)) {
			$this->_objMCP->assign('REQUEST_CONTENT',"<p>You may not download that file.</p>");
		} else {
		
			/*
			* Get file info 
			*/
			$strType = filetype($strFilePath);
			$strFileName = basename($strFilePath);
		
			/*
			* Set headers
			*/
			header("Content-type: $strType");
			header("Content-Disposition: attachment;filename=$strFileName");
			header("Content-Transfer-Encoding: binary");
			header('Pragma: no-cache');
			header('Expires: 0');
		
			/*
			* Assign files binary content as request
			*/
			$this->_objMCP->assign('REQUEST_CONTENT',file_get_contents($strFilePath));
		}
		
		/*
		* Return file server template 
		*/
		return str_replace(ROOT,'',$this->getTemplatePath()).'/File/File.php';
		
	}
	
	/*
	* Image request 
	*/
	protected function _executeImg() {
		
		/*
		* Merge all arguments 
		*/
		$arrArgs = array_merge(array($this->_objMCP->getModule()),$this->_objMCP->getArgs());
		
		/*
		* Image path 
		*/
		$strImagePath = '';
		
		/*
		* Image params
		*/
		$arrParams = array();
		
		/*
		* Index of last file path arg 
		*/
		$intFileIndex = 0;
		
		/*
		* Flag whether image is stored in db 
		*/
		$boolDbImage = false;
		
		/*
		* Build base image path
		*/
		foreach($arrArgs as $intIndex=>$strArg) {
			
			$intFileIndex = $intIndex;
			$strImagePath.= "/$strArg";
			
			/*
			* Grabs image stored in db vs. site file folder 
			*/
			if($intIndex == 0 && is_numeric($strArg)) {
				$boolDbImage = true;
				break;
			}
			
			if(preg_match('/^.*?\.(jpeg|jpg|gif|png)$/',$strArg)) break;
		}
		
		/*
		* Build param array 
		*/
		$arrLeftOver = array_slice($arrArgs,++$intFileIndex);
		
		for($i=0;$i<count($arrLeftOver);$i++) {
			if(!isset($arrLeftOver[$i+1])) break;
			$arrParams[$arrLeftOver[$i]] = $arrLeftOver[++$i];
		}
		
		/*
		* Get image DAO 
		*/
		$objDAOImage = $this->_objMCP->getInstance('App.Resource.File.DAO.DAOImage',array($this->_objMCP));
		
		/*
		* make image file path
		* 
		* 1.) image stored by system
		* 2.) site image, stored in site directory by user
		*/
		if($boolDbImage === true) {
			
			/*
			* Get image info 
			*/
			$image = $objDAOImage->fetchById((int) trim($strImagePath,'/'));
			
			if($image !== null) {
				/*
				* Build out path to image on file system
				*/
				$file = $this->_objMCP->getImageFilePath()."/image_{$image['images_id']}.{$objDAOImage->fetchExtByMime($image['image_mime'])}";
			} else {
				/*
				* Issue 404 for not found image (may want to move this to a template?)
				*/
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
				exit;
			}
			
		} else {
			$file = $this->_objMCP->getImgFolder().$strImagePath;
		}
		
		/*
		* Get image contents 
		*/
		$arrImage['image'] = @file_get_contents($file);
		
		/*
		* Attempt to locate cached image 
		*/
		$arrCachedImage = null;
		if(!empty($arrParams)) {
			$arrCachedImage = $this->_objMCP->getCacheImage($arrImage,$arrParams);			
		}
		
		if($arrCachedImage === null) {
			/*
			* Get images mime type from files extension
			*/
			$info = pathinfo($file);	
			$arrImage['type'] = $objDAOImage->fetchMimeByExt($info['extension']);
			
			/*
			* Save copy of base image data for caching 
			*/
			$arrBaseImage = $arrImage;
		
			/*
			* Apply image resize
			*/
			if(isset($arrParams['w']) || isset($arrParams['h'])) {
				$arrImage = $objDAOImage->resize($arrImage,(isset($arrParams['w'])?$arrParams['w']:0),(isset($arrParams['h'])?$arrParams['h']:0));
			}
		
			/*
			* Apply grayscale transformation 
			*/
			if(isset($arrParams['gs'])) {
				$arrImage = $objDAOImage->grayscale($arrImage);
			}
			
			/*
			* Cache image if it has params 
			*/
			if(!empty($arrParams)) {
				$this->_objMCP->setCacheImage($arrBaseImage,$arrImage,$arrParams);
			}
			
		} else {
			/*
			* Set image as cached image 
			*/
			$arrImage = $arrCachedImage;
		}
		
		/*
		* Set requests image header
		*/
		header("Content-Type: {$arrImage['type']}");
		header('Cache-Control: max-age=3600');
		
		$this->_objMCP->assign('REQUEST_CONTENT',$arrImage['image']);
		return str_replace(ROOT,'',$this->getTemplatePath()).'/Image/Image.php';
	}
	
	/*
	* Show file from public directory 
	* 
	* NOTE: Implementation pulled from download action w/ download headers removed
	* 
	* @return str template
	*/
	public function _executePublicRequest() {
		
		/*
		* Merge all arguments 
		*/
		$arrArgs = array_merge(array($this->_objMCP->getModule()),$this->_objMCP->getArgs());
		
		/*
		* Make file path 
		*/
		if( count($arrArgs) === 1 && is_numeric($arrArgs[0]) ) {
			
			// Get the file data access layer
			$objDAOFile = $this->_objMCP->getInstance('App.Resource.File.DAO.DAOFile',array($this->_objMCP));
			
			// Fetch the file info
			$arrFile = $objDAOFile->fetchById($arrArgs[0]);
			
			// create path to file
			$strFilePath = $arrFile['file_path'];
			
		} else {
			// file stored in site folder
			$strFilePath = $this->_objMCP->getPublicFolder().DS.implode(DS,$arrArgs);
		}
		
		/*
		* Validate that file exists 
		*/
		if(!file_exists($strFilePath)) {
			$this->_objMCP->assign('REQUEST_CONTENT',"<p>File not accessible through http.</p>");
		} else {
		
			/*
			* Get file info 
			*/
			$strType = filetype($strFilePath);
			$strFileName = basename($strFilePath);
			
			/*
			* Eventually this needs to be replaced with a way to read the mime for every file 
			*/
			if(preg_match('/^.*?\.swf$/',$strFilePath)) {
				$strType = 'application/x-shockwave-flash';
				header("Content-Disposition: inline");
			}
		
			/*
			* Set headers
			*/
			header("Content-type: $strType");
		
			/*
			* Assign files binary content as request
			*/
			$this->_objMCP->assign('REQUEST_CONTENT',file_get_contents($strFilePath));
		}
		
		/*
		* Return file server template 
		*/
		return str_replace(ROOT,'',$this->getTemplatePath()).'/Public/Public.php';
		
	}
	
}
?>