<?php 
/*
* Dynamic field data access layer 
*/
class MCPDAOField extends MCPDAO {
	
	/*
	* Generic method to list all fields
	* 
	* @param str select columns
	* @param str where clause
	* @param str order by clause
	* @param str limit clause
	* @return array [fields,found rows]
	*/
	public function listFields($strSelect='f.*',$mixWhere=null,$strSort=null,$strLimit=null) {
		
		// bound paramters
		$arrBound = array();
		
		// bound paramter resolution
		if(is_array($mixWhere) === true) {
			$arrBound = $mixWhere;
			$strWhere = array_shift($arrBound);
		} else {
			$strWhere = $mixWhere;
		}
		
		$strSQL = sprintf(
			'SELECT 
			      SQL_CALC_FOUND_ROWS %s
			   FROM 
			      MCP_FIELDS f     
			      %s %s %s'
			,$strSelect
			,$strWhere === null?'':" WHERE $strWhere"
			,$strSort === null?'':" ORDER BY $strSort"
			,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		$arrResult = $this->_objMCP->query($strSQL,$arrBound);
		
		if($strLimit === null) {
			return $arrResult;
		}
		
		return array(
			$arrResult
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
	}
	
	/*
	* Get field values
	* 
	* @param int row id
	* @param str entity type
	* @param int entities id
	* @param int sites id
	* @return all dynamic fields to item
	*/
	public function fetchFieldValues($intRowsId,$strEntityType,$intEntitiesId=null,$intSitesId=null) {
		
		/*
		* fetch values for fields 
		* 
		* Intention is to keep this light considering the amount of times
		* it may run per a request. All available table indexes are used
		* to increase effeciency.
		*/
		$strSQL = sprintf(
			"SELECT
			     f.cfg_name field_name
			     ,CASE
			     
			         WHEN fv.fields_id IS NULL AND f.cfg_default IS NOT NULL
			         THEN f.cfg_default
			     
			         WHEN fv.fields_id IS NOT NULL AND f.db_value = 'varchar'
			         THEN fv.db_varchar
			         
			         WHEN fv.fields_id IS NOT NULL AND f.db_value = 'bool'
			         THEN fv.db_bool
			         
			         WHEN fv.fields_id IS NOT NULL AND f.db_value = 'int'
			         THEN fv.db_int
			         
			         WHEN fv.fields_id IS NOT NULL AND f.db_value = 'price'
			         THEN fv.db_price
			         
			         WHEN fv.fields_id IS NOT NULL AND f.db_value = 'text'
			         THEN fv.db_text
			         
			         ELSE NULL END field_value
			  FROM
			     MCP_FIELDS f 
			  LEFT OUTER
			  JOIN
			     MCP_FIELD_VALUES fv
			    ON
			     fv.fields_id = f.fields_id
			   AND
			     fv.rows_id = %s
			 WHERE
			     f.sites_id = %s
			  AND
			     f.entity_type = '%s'
			  AND
			     f.entities_id %s"
			,$this->_objMCP->escapeString($intRowsId)
			,$this->_objMCP->escapeString($intSitesId !== null?$intSitesId:$this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strEntityType)
			,$intEntitiesId !== null?"= {$this->_objMCP->escapeString($intEntitiesId)}":' IS NULL'
		);
		
		// fetch the fielded data
		$arrFields = $this->_objMCP->query($strSQL);
		
		return $arrFields;
		
	}
	
	/*
	* Build form configuration array w/ dynamic fields
	* 
	* @param str entity type
	* @param int entities_id
	* @param int sites_id
	* @param str mixin data
	* @return array dynamic form config
	*/
	public function getFrmConfig($strEntityType,$intEntitiesId=null,$intSitesId=null,$strMixin=null) {
		
		/*
		* fetch fields 
		*/
		$fields = $this->listFields(
			'f.*'
			,sprintf(
				"f.entity_type = '%s' AND f.sites_id = %s AND f.entities_id %s"
				,$this->_objMCP->escapeString($strEntityType)
				,$this->_objMCP->escapeString($intSitesId === null?$this->_objMCP->getSitesId():$intSitesId)
				,$intEntitiesId === null?'IS NULL':" = {$this->_objMCP->escapeString($intEntitiesId)}"
			)
		);
		
		/*
		* Build XML string
		*/
		$strXML = '';
		
		foreach($fields as $field) {
			
			$name = $field['cfg_name'];
			unset($field['cfg_name']);
			
			$values = '';
			
			foreach($field as $attr=>$value) {
				if(strpos($attr,'cfg_') === 0) {
					if(empty($value)) continue;	
					
					$key = substr($attr,4);
					
					if(in_array($key,array('dao_pkg','dao_method','dao_args'))) continue;
					
					$values.= "<$key>$value</$key>";
				}
			}
			
			/* Build expected DAO callback format */
			if(!empty($field['cfg_dao_pkg']) && !empty($field['cfg_dao_method'])) {
				$values.= '<dao>';
				
				$values.= '<pkg>'.$field['cfg_dao_pkg'].'</pkg>';
				$values.= '<method>'.$field['cfg_dao_method'].'</method>';
				
				if(!empty($field['cfg_dao_args'])) {
					$args = unserialize(base64_decode($field['cfg_dao_args']));
					if(is_array($args)) {
						$values.= '<args>';
						foreach($args as $arg) {
							if(empty($arg)) continue;
							$values.= '<arg>'.$arg.'</arg>';
						}
						$values.= '</args>';
					}
				}
				
				$values.= '</dao>';
			}
			
			$strXML.= "<$name>$values</$name>";
		}
		
		// Wrap XML include indirect mixin
		$strXML = "<?xml version=\"1.0\"?><mod><frm".($strMixin !== null?" mixin=\"$strMixin\"":'').">$strXML</frm></mod>";
		
		/*
		* Convert string to XML object
		*/
		$xml = simplexml_load_string($strXML);
		
		/*
		* Convert to config 
		*/
		return $this->_objMCP->getFrmConfig($xml,'frm');
		
	}
	
	/*
	* Get field by id
	* 
	* @param int fields id
	* @param str columns to select
	*/
	public function fetchFieldById($intId,$strSelect='*') {
		$strSQL = "SELECT $strSelect FROM MCP_FIELDS WHERE fields_id = ?";
		return array_pop($this->_objMCP->query($strSQL,array((int) $intId )));
	}
	
	/*
	* Get all possible DB value storage types for dynamic fields
	* 
	* @return array fields  (compatible with XML def callback)
	*/
	public function fetchDBValueTypes() {
		
		$arrResult = $this->_objMCP->query('DESCRIBE MCP_FIELDS');
		$arrValueTypes = array();
		
		foreach($arrResult as $arrColumn) {
			if(strcmp('db_value',$arrColumn['Field']) == 0) {
				
				foreach(explode(',',str_replace("'",'',trim(trim($arrColumn['Type'],'enum('),')'))) as $strValue) {
					$arrValueTypes[] = array('value'=>$strValue,'label'=>$strValue);
				}
				
				break;
			}
		}
		
		return $arrValueTypes;
		
	}
	
	/*
	* Fetch all possible entities to assign to a field
	* 
	* @dependent: self::_fetchNodeTypes()
	* 
	* @return array entities (compatible with XML def callback)
	*/
	public function fetchEntities() {
		
		/*
		* Get all tables in application 
		*/
		$tbls = $this->_objMCP->query('SHOW TABLES');
		
		/*
		* Build output 
		*/
		$arrTables = array(
			array(
				'label'=>'Core'
				,'value'=>''
				,'values'=>array()
			)
		);
		
		foreach($tbls as $tbl) {	
			
			// name of table
			$value = array_pop($tbl);
			$label = $value;
			
			/*
			* Remove MCP from label
			*/
			if(strpos($label,'MCP_') === 0) {
				$label = substr($label,4);
			}
			
			/*
			* Fields may not have fields added to them (should be obvious) 
			*/
			if(strcmp('MCP_FIELDS',$value) === 0) continue;
			
			// build option
			$arrTables[0]['values'][] = array(
				'value'=>$value
				,'label'=>$label
			);
			
			/*
			* Add child mixins: node types 
			*/
			if(strcmp('MCP_NODE_TYPES',$value) === 0) {
				$arrTables[0]['values'][(count($arrTables[0]['values'])-1)]['values'] = $this->_fetchNodeTypes();
			
			/*
			* Add child mixins: vocabularies 
			*/
			} else if(strcmp('MCP_VOCABULARY',$value) === 0) {
				$arrTables[0]['values'][(count($arrTables[0]['values'])-1)]['values'] = $this->_fetchVocabularies();

			/*
			* Add child mixins: sites 
			*/
			} else if(strcmp('MCP_SITES',$value) === 0) {
				$arrTables[0]['values'][(count($arrTables[0]['values'])-1)]['values'] = $this->_fetchSites();
			}
			
		}
		
		return $arrTables;
		
	}
	
	/*
	* Save field data (update or insert)
	* 
	* @param array field data
	* @return int (update: affacted rows,insert: pk)
	*/
	public function saveField($arrField) {
		return $this->_save(
			$arrField
			,'MCP_FIELDS'
			,'fields_id'
			,array('entity_type','entities_id','cfg_name','cfg_label','cfg_description','cfg_required','cfg_default','cfg_type','cfg_values','cfg_sql','cfg_dao_pkg','cfg_dao_method','db_value','db_ref_table','db_ref_col')
			,null
			,array('cfg_dao_args')
		);
	}
	
	/*
	* Save values for fields
	* 
	* @param array fields data
	* @param int rows id
	* @param str entity type
	* @param int entities id
	* @param int sites id
	* @return int affected rows
	*/
	public function saveFieldValues($arrFields,$intRowsId,$strEntityType,$intEntitiesId,$intSitesId=null) {
		
		/*
		* Might need to determine a better way to do this considering the query will be run 
		* once per field in its current state. It works, but there is probably a better way to do it. 
		* 
		* @TODO: Add validation handling to validator - so that db_value types will always be checked to be
		* compatibke with associated storage field.
		*/
		foreach($arrFields as $field_name=>$field_value) {
			
			/*
			* Handle special image resource field 
			*/
			if($this->isImageField($field_name,$strEntityType,$intEntitiesId,$intSitesId) === true) {
				$field_value = $this->_objMCP->getInstance('App.Resource.File.DAO.DAOImage',array($this->_objMCP))->insert($field_value,true);
			}
		
			$strSQL = sprintf(
				"INSERT IGNORE INTO MCP_FIELD_VALUES (fields_id,rows_id,db_varchar,db_text,db_int,db_bool,db_price)
				    SELECT
				         fields_id
				         ,%s rows_id
				         ,IF(db_value = 'varchar','%s',NULL) db_varchar
				         ,IF(db_value = 'text',IF(cfg_serialized = 0,'%2\$s','%s'),NULL) db_text
				         ,IF(db_value = 'int',%s,NULL) db_int
				         ,IF(db_value = 'bool',%4\$s,NULL) db_bool
				         ,IF(db_value = 'price','%2\$s',NULL) db_price
				      FROM
				         MCP_FIELDS
				     WHERE
				         sites_id = %s
				       AND
				         entity_type = '%s'
				       AND
				         entities_id %s
				       AND
				         cfg_name = '%s' ON DUPLICATE KEY UPDATE db_varchar = VALUES(db_varchar),db_text = VALUES(db_text),db_int = VALUES(db_int),db_bool = VALUES(db_bool),db_price = VALUES(db_price)"
				
				,$this->_objMCP->escapeString($intRowsId)
				,$this->_objMCP->escapeString($field_value)
				,base64_encode(serialize($field_value))
				,$this->_objMCP->escapeString((int) $field_value)
				,$this->_objMCP->escapeString($intSitesId !== null?$intSitesId:$this->_objMCP->getSitesId())
				,$this->_objMCP->escapeString($strEntityType)
				,$intEntitiesId !== null?"= {$this->_objMCP->escapeString($intEntitiesId)}":' IS NULL'
				,$this->_objMCP->escapeString($field_name)
			);
			
			$this->_objMCP->query($strSQL);
		
		}
		
	}
	
	/*
	* Determine whether field stores and image reference
	* 
	* @param str field name
	* @param str entity type
	* @param entities id
	* @param int sites id (defaults to current site)
	* @return bool true/false
	*/
	public function isImageField($strField,$strEntity,$intEntitiesId=null,$intSitesId=null) {
		
		/*
		* Fetch field data 
		*/
		$field = array_pop($this->listFields('f.cfg_image',sprintf(
			"f.cfg_name = '%s' AND f.entity_type = '%s' AND f.entities_id %s AND f.sites_id = %s"
			,$this->_objMCP->escapeString($strField)
			,$this->_objMCP->escapeString($strEntity)
			,$intEntitiesId === null?'IS NULL':" = {$this->_objMCP->escapeString($intEntitiesId)}"
			,$this->_objMCP->escapeString($intSitesId !== null?$intSitesId:$this->_objMCP->getSitesId())
		)));
		
		return $field !== null?$field['cfg_image'] == 1?true:false:false;
		
	}
	
	/*
	* Get node types for dynamic field selection drop down 
	* 
	* NOTE: This method does something that no other DAO does, that
	* is cross over to use another DAO. Therefore, that makes this
	* DAO unique in that it is essentially dependent on the Node
	* DAO. Remove the Node DAO method dependency and this will error.
	* This is done to reduce replicated code and use what we have already.
	* 
	* @return array site extendable node types
	*/
	protected function _fetchNodeTypes() {
		
		/*
		* Fetch node dao
		*/
		$objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
		
		/*
		* Get sites extendable node types (for now all node types are extendable)
		*/
		$types = $objDAONode->fetchNodeTypes(
			"t.node_types_id
			 ,t.pkg
			 ,IF(t.pkg IS NOT NULL,t.pkg,t.system_name) sort
			 ,t.system_name"
			,"t.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())}"
			,'pkg,sort ASC'
		);
		
		/*
		* Post process into pkg hierarchy 
		*/
		$arrTypes = array();
		foreach($types as $type) {
			
			// Get the option group to use
			$key = $type['pkg'] === null?'Core':$type['pkg'];
			
			// create package array / "optiongroup"
			if(!isset($arrTypes[$key])) {
				$arrTypes[$key] = array(
					'label'=>$key
					,'value'=>''
					,'values'=>array()
				);
			}
			
			// add the option
			$arrTypes[$key]['values'][] = array(
				'label'=>$type['system_name']
				,'value'=>"MCP_NODE_TYPES-{$type['node_types_id']}"
			);
			
		}
		
		return $arrTypes;
		
		
	}

	/*
	* Get node types for dynamic field selection drop down 
	* 
	* NOTE: This method does something that no other DAO does, that
	* is cross over to use another DAO. Therefore, that makes this
	* DAO unique in that it is essentially dependent on the Taxonomy
	* DAO. Remove the Taxonomy DAO method dependency and this will error.
	* This is done to reduce replicated code and use what we have already.
	* 
	* @return array site extendable vocabularies
	*/
	protected function _fetchVocabularies() {
		
		/*
		* Fetch taxonomy dao
		*/
		$objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
		
		/*
		* Get sites extendable vocabularies (for now all vocabularies are extendable)
		*/
		$vocabs = $objDAOTaxonomy->listVocabulary(
			"v.vocabulary_id
			 ,v.pkg
			 ,IF(v.pkg IS NOT NULL,v.pkg,v.system_name) sort
			 ,v.system_name"
			,"v.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())} AND v.deleted IS NULL"
			,'pkg,sort ASC'
		);
		
		/*
		* Post process into pkg hierarchy 
		*/
		$arrVocabs = array();
		foreach($vocabs as $vocab) {
			
			// Get the option group to use
			$key = $vocab['pkg'] === null?'Core':$vocab['pkg'];
			
			// create package array / "optiongroup"
			if(!isset($arrVocabs[$key])) {
				$arrVocabs[$key] = array(
					'label'=>$key
					,'value'=>''
					,'values'=>array()
				);
			}
			
			// add the option
			$arrVocabs[$key]['values'][] = array(
				'label'=>$vocab['system_name']
				,'value'=>"MCP_VOCABULARY-{$vocab['vocabulary_id']}"
			);
			
		}
		
		return $arrVocabs;
		
	}
	
	/*
	* Get node types for dynamic field selection drop down 
	* 
	* NOTE: This method does something that no other DAO does, that
	* is cross over to use another DAO. Therefore, that makes this
	* DAO unique in that it is essentially dependent on the Site
	* DAO. Remove the Site DAO method dependency and this will error.
	* This is done to reduce replicated code and use what we have already.
	* 
	* @return array site extendable sites
	*/
	protected function _fetchSites() {

		/*
		* Fetch site dao
		*/
		$objDAOSite = $this->_objMCP->getInstance('Component.Site.DAO.DAOSite',array($this->_objMCP));
		
		/*
		* Get extendable sites (for now all sites are extendable)
		*/
		return $objDAOSite->listAll(
			" CONCAT('MCP_SITES-',s.sites_id) value
			 ,s.site_name label"
			, null
			,'label ASC'
		);
		
	}
	
}
?>