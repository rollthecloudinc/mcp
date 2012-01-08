<?php
/*
* Dat access layer manages files such as; pdf, flash, ect 
*/
$this->import('App.Core.DAO');
class MCPDAOFile extends MCPDAO {
	
	/*
	* Determines normal file extensions based on its mime type ( reverse self::fetchMimeByExt() )
	* 
	* @param str mime type
	* @return str extension
	*/
	public function fetchExtByMime($mime) {

		list($type,$subtype) = explode('/',$mime);
		$arrMime = array_pop($this->_objMCP->query(
			'SELECT ext FROM MCP_ENUM_MIME_TYPES WHERE type = :type AND subtype = :subtype'
			,array(
				 ':type'=>(string) $type
				,':subtype'=>(string) $subtype
			)
		));
		
		return $arrMime !== null?$arrMime['ext']:null;
		
	}
	
	/*
	* Fetch file by id
	* 
	* @param int files id
	* @param str columns to select
	* @return array image data
	*/
	public function fetchById($intId) {
		
		/*
		* Fetch image data 
		*/
		$arrFile = array_pop($this->_objMCP->query(
			'SELECT * FROM MCP_MEDIA_FILES WHERE files_id = :files_id'
			,array(
				 ':files_id'=>(int) $intId
			)
		));
		
		if( $arrFile !== null ) {
			// full path to the file
			$arrFile['file_path'] = $this->_objMCP->getNormalFilePath().DS.'file_'.$arrFile['files_id'].'.'.$this->fetchExtByMime($arrFile['file_mime']);
		}
		
		return $arrFile;
		
	}
	
	/*
	* Insert new file 
	* 
	* @param arr  file data
	* @param bool write file to file directory
	* @return int images id
	*/
	public function insert($arrFile,$boolWrite=false) {
                
		/*
		* Insert file data into database 
		*/
		$intId = $this->_objMCP->query(
			"INSERT INTO MCP_MEDIA_FILES (sites_id,creators_id,file_label,file_mime,file_size,created_on_timestamp) VALUES (:sites_id,:users_id,:name,:type,:size,NOW())"
                        ,array(
                             ':sites_id'    => (int) $this->_objMCP->getSitesId()
                            ,':users_id'    => (int) $this->_objMCP->getUsersId()
                            ,':name'        => $arrFile['name']
                            ,':type'        => $arrFile['type']
                            ,':size'        => $arrFile['size']
			)
		);
		
		/*
		* Save file to file directory
		*/
		if($boolWrite === true && $intId) {
			// @TODO: throw some type of exception of this fails ie. directory unwritable, etc
			// rename($strTmp,"{$this->_objMCP->getNormalFilePath()}/file_{$intId}.{$this->fetchExtByMime($arrFile['type'])}");
			
			// place the uploaded file in the normal file directory
			move_uploaded_file($arrFile['tmp_name'],"{$this->_objMCP->getNormalFilePath()}/file_{$intId}.{$this->fetchExtByMime($arrFile['type'])}");
			
		} else {
			// unlink($strTmp);	
		}
		
		return $intId;
		
	}
	
}
?>