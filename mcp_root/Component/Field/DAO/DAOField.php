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
	* Fetch the widgets compatible with a given field.
	* 
	* @param int field id
	* @return array collection of widgets
	*/
	public function fetchFieldWidgets($intFieldId) {
		
		$arrWidgets = array();
		
		// Get the field data
		$arrField = $this->fetchFieldById($intFieldId);
		
		// determine widgets compatible with field
		
		switch($arrField['db_value']) {
			
			case 'varchar':
			case 'text':
				$arrWidgets[] = array(
					'value'=>'text'
					,'label'=>'Text'
				);	
				$arrWidgets[] = array(
					'value'=>'textarea'
					,'label'=>'TextArea'
				);		
				$arrWidgets[] = array(
					'value'=>'password'
					,'label'=>'Password'
				);
				break;
				
			case 'date':
				$arrWidgets[] = array(
					'value'=>'date'
					,'label'=>'Date'
				);
				break;	

			case 'timestamp':
				$arrWidgets[] = array(
					'value'=>'timestamp'
					,'label'=>'Timestamp'
				);
				break;	
				
			case 'int':
				$arrWidgets[] = array(
					'value'=>'text'
					,'label'=>'Text'
				);
				$arrWidgets[] = array(
					'value'=>'select'
					,'label'=>'ComboBox'
				);
				break;

			case 'price':
				$arrWidgets[] = array(
					'value'=>'text'
					,'label'=>'Text'
				);
				break;
				
			case 'bool':
				$arrWidgets[] = array(
					 'value'=>'checkbox'
					,'label'=>'Checkbox'
				);
				$arrWidgets[] = array(
					'value'=>'select'
					,'label'=>'ComboBox'
				);
				break;
				
			default:
			
		}
		
		/* multi value compatible */
		if( $arrField['cfg_multi'] ) {
			$arrWidgets[] = array(
				'label'=>'Multi-Select'
				,'value'=>'multi_select'
			);
			$arrWidgets[] = array(
				'value'=>'checkbox_group'
				,'label'=>'Checkbox Group'
			);
		}
		
		/* media special case */
		if($arrField['cfg_media'] !== null) {
			$arrWidgets = array(
				array(
					'label'=>ucwords($arrField['cfg_media'])
					,'value'=>$arrField['cfg_media']
				)
			);
		}
		
		/* Foreign key contextual reference */
		if( $arrField['db_ref_context'] !== null ) {
			$arrWidgets[] = array(
				'value'=>'select'
				,'label'=>'ComboBox'
			);	
		}
		
		return $arrWidgets;
		
	}
	
	/*
	* Get field values
	* 
	* @TODO: Modify to fetch values for multiple rows using a single query. The one restraint
	* will be all rows are of the same entity, entity and entity id combo.
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
		* 
		* field_value_relation column is used so that all rows in result set have the same
		* associative keys in the end regardless of storing a foreign key.
		*/
		$strSQL = sprintf(
			"SELECT
			     f.cfg_name field_name
			     ,f.db_ref_table ref_table
			     ,f.db_ref_col ref_col
			     ,f.cfg_serialized serialized
			     ,f.cfg_multi multi
			     ,fv.field_values_id
			     ,fv.weight
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
			         
			         WHEN fv.fields_id IS NOT NULL AND f.db_value = 'timestamp'
			         THEN fv.db_timestamp
			         
			         WHEN fv.fields_id IS NOT NULL AND f.db_value = 'date'
			         THEN fv.db_date
			         
			         ELSE NULL END field_value
			         ,NULL field_value_relation
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
			     f.entities_id %s
			ORDER
			   BY
			     fv.weight ASC"
			,$this->_objMCP->escapeString($intRowsId)
			,$this->_objMCP->escapeString($intSitesId !== null?$intSitesId:$this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strEntityType)
			,$intEntitiesId !== null?"= {$this->_objMCP->escapeString($intEntitiesId)}":' IS NULL'
		);
		
		// echo "<p>$strSQL</p>";
		
		// fetch the fielded data
		$arrFields = $this->_objMCP->query($strSQL);
		
		$arrValues = array();	
		foreach($arrFields as $arrField) {
			
			$value = $arrField['field_value'];
			$relation = null;
			
			/*
			* Unserialize serialized values 
			*/
			if($arrField['serialized'] && $value !== null) {
				$value = unserialize( base64_decode( $value ) );
			}
			
			/*
			* Resolve foreign key references
			* 
			* reference table and column MUST be defined (no magic resolution here such as primary key of table)
			* 
			* NOTE: At this point in time relations to nodes, terms or users will include dynamic fields. 
			* 
			* WARNING: The only catch is adding it would make it possible to run into an infinite loop. Therefore,
			* at this time it is being left out though support may be added in the future pending handling of possible
			* infinite oop scenarios.
			*/
			if($arrField['ref_table'] !== null && $arrField['ref_col'] !== null) {
			
				if($value !== null) {
					/*
					* For now query the reltion table directly. In the future certain tables could be bound to specific DAOs
					* forwarding the responsibility of selecting the data to dao responsible for managing the given relational
					* entity. This approach would also add support for dynamic fields on relational entities as outlined above.
					*/				
					switch("{$arrField['ref_table']}::{$arrField['ref_col']}") {
						
						// field references an image
						case 'MCP_MEDIA_IMAGES::images_id':
							
							// get the images dao
							$objDAOImage = $this->_objMCP->getInstance('App.Resource.File.DAO.DAOImage',array($this->_objMCP));
							
							// fetch the image data
							$relation = $objDAOImage->fetchById((int) $value);  					
							break;
		
						// field references a node
						case 'MCP_NODES::nodes_id':
							
							// get the node dao
							$objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
							
							// fetch the node data
							$relation = $objDAONode->fetchById((int) $value);
							
							break;
		
						// field references a term
						case 'MCP_TERMS::terms_id':
							
							// get the taxonomy dao
							$objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
							
							// fetch the term data
							$relation = $objDAOTaxonomy->fetchTermById((int) $value);
							
							break;
							
						// field references a user
						case 'MCP_USERS::users_id':
							
							// get the user dao
							$objDAOUser = $this->_objMCP->getInstance('Component.User.DAO.DAOUser',array($this->_objMCP));
							
							// fetch the users data
							$relation = $objDAOUser->fetchById((int) $value);
							
							break;
							
						/*
						* NOTE: No handler exists for node types, sites, vocabularies and config values because
						* they shouldn't be referenced. The reference of such entities could easily
						* be supported but will likely result in more problems than they will solve.
						*/
							
						default:
						
					}
				}
			
			}
			
			$field = new MCPField();	

			// bind the to string value and field values primary key
			$field->setValue($value);
			$field->setId($arrField['field_values_id']);

			if($relation !== null) {
				foreach($relation as $prop=>$val) {
					$field->{$prop} = $val;
					//$field[$prop] = $val;
				}
			}
			
			if($arrField['multi'] == 1) {
				
				/*
				* Make empty array for multi-fields without any values 
				*/
				if( strlen($field) === 0 ) {
					$arrValues[$arrField['field_name']]['field_name'] = $arrField['field_name'];
					$arrValues[$arrField['field_name']]['field_value'] = array();
					continue;
				}
				
				$arrValues[$arrField['field_name']]['field_name'] = $arrField['field_name'];
				$arrValues[$arrField['field_name']]['field_value'][] = $field;
			} else {
				$arrValues[$arrField['field_name']]['field_name'] = $arrField['field_name'];
				$arrValues[$arrField['field_name']]['field_value'] = $field;
			}
			
		}
		
		// echo '<pre>',print_r($arrValues),'</pre>';
		
		return $arrValues;
		
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
					
					if(in_array($key,array('dao_pkg','dao_method','dao_args','multi','multi_limit','type'))) continue;
					
					$values.= "<$key>$value</$key>";
				
				}
			}
			
			/*
			* ---------------------------------------------------------------------------------------
			* Foreign key based relation handling - field is a foreign key to a term, node, vocabaulary, nodetype 
			*/
			if( !empty($field['db_ref_context']) ) {
				
				switch( $field['db_ref_context'] ) {
					
					case 'node': // node relation
						$values.= sprintf(
							'<dao>
								<pkg>Component.Node.DAO.DAONode</pkg>
						      	<method>fetchNodes</method>
						    		<args>
										<arg>n.nodes_id value,n.node_title label</arg>
						      			<arg>n.deleted = 0 AND n.sites_id = SITES_ID AND n.node_types_id = %s</arg>
						      			<arg>n.node_title ASC</arg>
						         	</args>
							 </dao>'
							,$this->_objMCP->escapeString( $field['db_ref_context_id'] )
						);
						break;
						
					case 'term': // term relation
					case 'term_child': // term child
						$values.= sprintf(
							'<dao>
								<pkg>Component.Taxonomy.DAO.DAOTaxonomy</pkg>
						      	<method>fetchTerms</method>
						    		<args>
										<arg>%s</arg>
						      			<arg>%s</arg>
						      			<arg>1</arg>
						      			<arg>
						      				<select>t.terms_id value,t.human_name label</select>
						      				<filter>t.deleted = 0</filter>
						      				<sort>t.weight ASC,t.human_name ASC</sort>
						      			</arg>
						         	</args>
							 </dao>'
							,$this->_objMCP->escapeString( $field['db_ref_context_id'] )
							,strcmp('term_child', $field['db_ref_context'] ) === 0?'term':'vocabulary'
						);
						break;
						
					case 'vocabulary': // vocabulary foreign key
						$values.=
							'<dao>
								<pkg>Component.Taxonomy.DAO.DAOTaxonomy</pkg>
						      	<method>listVocabulary</method>
						    		<args>
										<arg>v.vocabulary_id value,v.system_name label</arg>
						      			<arg>v.deleted = 0 AND v.sites_id = SITES_ID</arg>
						      			<arg>v.human_name ASC</arg>
						         	</args>
							 </dao>';
						break;
						
					case 'nodetype': // node type foreign key
						$values.=
							'<dao>
								<pkg>Component.Node.DAO.DAONode</pkg>
						      	<method>fetchNodeTypes</method>
						    		<args>
										<arg>t.node_types_id value,t.system_name label</arg>
						      			<arg>t.deleted = 0 AND t.sites_id = SITES_ID</arg>
						      			<arg>t.human_name ASC</arg>
						         	</args>
							 </dao>';
						break;
						
					case 'user': // user foreign key
						$values.=
							'<dao>
								<pkg>Component.User.DAO.DAOUser</pkg>
						      	<method>listAll</method>
						    		<args>
										<arg>users_id value,username label</arg>
						      			<arg>deleted = 0 AND sites_id = SITES_ID</arg>
						      			<arg>username ASC</arg>
						         	</args>
							 </dao>';
						break;				
						
					default:
				}
			
			/*
			* Field values are derived from a explicit DAO method call 
			* - contemplating removing this with the creation of field foreign key handlers it really
			* doesn't seem necessary.
			*/
			} else if(!empty($field['cfg_dao_pkg']) && !empty($field['cfg_dao_method'])) {
				$values.= '<dao>';
				
				$values.= '<pkg>'.$field['cfg_dao_pkg'].'</pkg>';
				$values.= '<method>'.$field['cfg_dao_method'].'</method>';
				
				if(!empty($field['cfg_dao_args'])) {
					$args = unserialize(base64_decode($field['cfg_dao_args']));
					
					if(is_array($args)) {
						$values.= '<args>';
						//foreach($args as $arg) {
							// if(empty($arg)) continue;
							
							/*if( is_array($arg) ) {
								$values.= '<arg serialized="true"><![CDATA['.base64_encode(serialize($arg)).']]></arg>';
							} else {
								$values.= '<arg>'.$arg.'</arg>';
							}*/
							
							$toArgs = function($args,$toArgs) {
								
								$strReturn = '';
								
								foreach($args as $strArgName => $arg) {
									
									$strUseName = is_numeric($strArgName)?'arg':$strArgName;
									
									if( is_array($arg) ) {
										$strReturn.= "<$strUseName>".$toArgs($arg,$toArgs)."</$strUseName>";
									} else {
										$strReturn.= "<$strUseName>$arg</$strUseName>";
									}
									
								}
								
								return $strReturn;
								
							};
							
							$values.= $toArgs($args,$toArgs);
							
						//}
						$values.= '</args>';
					} // end arg if
					
					
				}
				
				$values.= '</dao>';
			}
			
			/* Build in support for multi field value */
			if($field['cfg_multi'] != 0) {
				
				// absence of multi_limit translates to unlimited amount of multiple values for field
				// @TODO: integrate infinite number of value support into form management system. At this
				// time is only supports a fixed number. So for now use a fixed value.
				$values.= '<multi>'. ( empty($field['cfg_multi_limit']) ? 5 : $field['cfg_multi_limit'] ) .'</multi>';
				
			}
			
			if( strcmp('bool',$field['db_value']) == 0) {
				$values.= '<type>bool</type>';
			}
			
			/*
			* Special flag for a "field" - used to change behavior 
			*/
			$values.= '<dynamic_field>1</dynamic_field>';
			
			$strXML.= "<$name>$values</$name>";
			
		}
		
		// Wrap XML include indirect mixin
		$strXML = "<?xml version=\"1.0\"?><mod><frm".($strMixin !== null?" mixin=\"$strMixin\"":'').">$strXML</frm></mod>";
		
		/*
		* Convert string to XML object
		*/
		$xml = simplexml_load_string($strXML);
		
		/*ob_clean();
		header('Content-Type: text/xml');
		echo $xml->asXML();
		exit;*/
		
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
		
		/*
		* abstracted labels - probably make more sense to average user
		*/
		$labels = array(
			'image'=>'Image'
			,'file'=>'File'
			,'audio'=>'Audio'
			,'video'=>'Video'
			,'int'=>'Number'
			,'varchar'=>'Short Text'
			,'text'=>'Long Text'
			,'price'=>'Price'
			,'bool'=>'True/False'
			,'timestamp'=>'Timestamp'
			,'date'=>'Date'
		);
		
		foreach($arrResult as $arrColumn) {
			if(strcmp('db_value',$arrColumn['Field']) == 0 || strcmp('cfg_media',$arrColumn['Field']) == 0) {
				
				foreach(explode(',',str_replace("'",'',trim(trim($arrColumn['Type'],'enum('),')'))) as $strValue) {
					$arrValueTypes[] = array(
						'value'=>$strValue
						,'label'=>isset($labels[$strValue])?$labels[$strValue]:$strValue
					);
				}
				
			}
		}
		
		// ----------------------------------------------------------------------------------
		// Add dynamic entity references
		// ----------------------------------------------------------------------------------
		
		// DAOs needed to get node types, vocabularies and util (resolve states and countries)
		$objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
		$objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
		$objDAOUtil = $this->_objMCP->getInstance('Component.Util.DAO.DAOUtil',array($this->_objMCP));
		
		// node type or a node of type x
		$arrValueTypes[] = array(
		
			'label'=>'Classification'
			,'value'=>'nodetype'
			
			,'values'=> $objDAONode->fetchNodeTypes("CONCAT('node:',t.node_types_id) value,IF(t.pkg = '',t.system_name,CONCAT(t.pkg,'::',t.system_name)) label","t.deleted = 0 AND t.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())}")
		);
		
		// vocabulary of a term inside vocabulary x
		$arrValueTypes[] = array(
			'label'=>'Vocabulary'
			,'value'=>'vocabulary'
			
			,'values'=> $objDAOTaxonomy->listVocabulary("CONCAT('term:',v.vocabulary_id) value,IF(v.pkg = '',v.system_name,CONCAT(v.pkg,'::',v.system_name)) label","v.deleted = 0 AND v.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())}")
		);
		
		// user
		$arrValueTypes[] = array(
			'label'=>'User'
			,'value'=>'user'
		);
		
		// special state type
		$arrState = $objDAOUtil->fetchStateTerm();
		$arrValueTypes[] = array(
			'label'=>'State'
			,'value'=>"term_child:{$arrState['terms_id']}"
		);
		
		// special country type
		$arrCountry = $objDAOUtil->fetchCountryVocabulary();
		$arrValueTypes[] = array(
			'label'=>'Country'
			,'value'=>"term:{$arrCountry['vocabulary_id']}"
		);
		
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
			,array('entity_type','entities_id','cfg_name','cfg_label','cfg_description','cfg_required','cfg_default','cfg_type','cfg_values','cfg_sql','cfg_dao_pkg','cfg_dao_method','db_value','cfg_media','db_ref_table','db_ref_col','db_ref_context','cfg_widget')
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
		* @TODO: Get all field definitions to properly resolve m:n fields 
		*/
		
		/*
		* Build out SQL to select field definition data
		* 
		* sites_id = $intSitesId 
		* entity_type = $strEntityType
		* entities_id (IS NULL || $intRowsId)
		* cfg_name = {field_name}
		*/
		
		/*
		* Might need to determine a better way to do this considering the query will be run 
		* once per field in its current state. It works, but there is probably a better way to do it. 
		* 
		* @TODO: Add validation handling to validator - so that db_value types will always be checked to be
		* compatibke with associated storage field.
		*/
		foreach($arrFields as $field_name=>$field_value) {
			
			/*
			* Get field definition
			*/
			$arrField = array_pop($this->listFields('f.*',sprintf(
				"f.sites_id = %s AND f.entity_type = '%s' AND f.entities_id %s AND f.cfg_name = '%s'"
				,$this->_objMCP->escapeString($intSitesId !== null?$intSitesId:$this->_objMCP->getSitesId())
				,$this->_objMCP->escapeString($strEntityType)
				,$intEntitiesId !== null?"= {$this->_objMCP->escapeString($intEntitiesId)}":' IS NULL'
				,$this->_objMCP->escapeString($field_name)
			)));
			
			/*
			* Handle special image resource field 
			*/
			if( in_array($arrField['cfg_media'],array('file','image')) ) { // strcmp($arrField['cfg_media'],'image') == 0
				
				/*
				* Determine the correct DAO to use for uploading normal files 
				* such as; pdfs, images, video and audio.
				*/
				$objDAOUpload = null;
				switch( $arrField['cfg_media'] ) {
					case 'image':
						$objDAOUpload = $this->_objMCP->getInstance('App.Resource.File.DAO.DAOImage',array($this->_objMCP));
						break;
						
					case 'file':
						$objDAOUpload = $this->_objMCP->getInstance('App.Resource.File.DAO.DAOFile',array($this->_objMCP));
						break;
						
					case 'audio': // todo
					case 'video': // todo
					default:
						continue; // really an error todo
						
				}
				
				if($arrField['cfg_multi'] == 1) {
					
					foreach( array_keys($field_value) as $index) {
						
						if($field_value[$index] && isset($field_value[$index]['error']) && $field_value[$index]['error'] != 4) {
							$field_value[$index]['value'] = $objDAOUpload->insert($field_value[$index],true);
						} else {
							unset($field_value[$index]);
							continue;
						}
					
					}
					
				} else {
					
					if($field_value && isset($field_value['error']) && $field_value['error'] != 4) {
						$field_value = $objDAOUpload->insert($field_value,true);
					} else {
						continue;
					}				
					
				}
				
			/*
			* Handle special file field 
			*/
			} /*else if ( strcmp($arrField['cfg_media'],'file') == 0 ) {
				continue; // ignore for now
			}*/
			
			// @TODO: add handling for other media types such as; video, audio, file, ect
			
			/*
			* Switch to save field that represents m:n reltionship vs. 1:n 
			* Example, an images field that stores multiple images so that 
			* one doesn't need to hack the system using image_1,image_2,image_3,... which
			* would make other things very difficult and ineffecient.
			*/
			if($arrField['cfg_multi'] == 1) {
				$this->_saveScalarFieldValue($arrField['fields_id'],$intRowsId,$field_value);
			} else {
				$this->_saveAtomicFieldValue($arrField['fields_id'],$intRowsId,$field_value);
			}
		
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
		$field = array_pop($this->listFields('f.cfg_media',sprintf(
			"f.cfg_name = '%s' AND f.entity_type = '%s' AND f.entities_id %s AND f.sites_id = %s"
			,$this->_objMCP->escapeString($strField)
			,$this->_objMCP->escapeString($strEntity)
			,$intEntitiesId === null?'IS NULL':" = {$this->_objMCP->escapeString($intEntitiesId)}"
			,$this->_objMCP->escapeString($intSitesId !== null?$intSitesId:$this->_objMCP->getSitesId())
		)));
		
		return $field !== null?strcmp($field['cfg_media'],'image') == 0?true:false:false;
		
	}
	
	/*
	* Move field value that belongs to scalar field up or down based weight 
	* 
	* @param int field values id (MCP_FIELD_VALUES primary key)
	*/
	public function moveFieldValue($intFieldValuesId,$intWeight) {
		echo "<p>Move field value</p>";
	}
	
	/*
	* Delete a field value
	* 
	* This will physical remove the data, not hide it but delete it right away. Normally
	* delete hides things and purge deletes them but not here.
	* 
	* @param int field values id (MCP_FIELD_VALUES primary key)
	*/
	public function deleteFieldValue($intFieldValuesId) {
		echo "<p>Delete field value</p>";
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
			 ,IF(t.pkg <> '',t.pkg,t.system_name) sort
			 ,t.system_name"
			,"t.deleted = 0 AND t.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())}"
			,'pkg,sort ASC'
		);
		
		/*
		* Post process into pkg hierarchy 
		*/
		$arrTypes = array();
		foreach($types as $type) {
			
			// Get the option group to use
			$key = empty($type['pkg'])?'Core':$type['pkg'];
			
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
			 ,IF(v.pkg <> '',v.pkg,v.system_name) sort
			 ,v.system_name"
			,"v.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())} AND v.deleted = 0"
			,'pkg,sort ASC'
		);
		
		/*
		* Post process into pkg hierarchy 
		*/
		$arrVocabs = array();
		foreach($vocabs as $vocab) {
			
			// Get the option group to use
			$key = empty($vocab['pkg'])?'Core':$vocab['pkg'];
			
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
			, 's.deleted = 0'
			,'label ASC'
		);
		
	}

	/*
	* @param int fields id
	* @param int rows id
	* @param mix new value 
	*/
	private function _saveAtomicFieldValue($intFieldsId,$intRowsId,$mixValue) {
		
		/*
		* Determine whether to use an update or insert based on whether a value
		* exists for the field id and row id combination.
		*/
		$strSQL = sprintf(
			"SELECT
			      field_values_id
			   FROM
			      MCP_FIELD_VALUES
			  WHERE
			      fields_id = %s
			    AND
			      rows_id = %s
			  LIMIT
			      1"
			,$this->_objMCP->escapeString($intFieldsId)
			,$this->_objMCP->escapeString($intRowsId)
		);
		
		$arrFieldValue = array_pop($this->_objMCP->query($strSQL));
		
		/*
		* insert / update switch 
		*/
		if($arrFieldValue !== null) {
			
			return $this->_updateFieldValue($arrFieldValue['field_values_id'],$mixValue);
			
		} else {
			
			return $this->_insertFieldValue($intFieldsId,$intRowsId,$mixValue);
			
		}
		
	}
	
	/*
	* @param int fields id
	* @param int rows id
	* @param array values to save 
	*/
	private function _saveScalarFieldValue($intFieldsId,$intRowsId,$arrValues) {
		
		foreach($arrValues as $arrValue) {
			
			// id means the value is being changed that exists
			if( isset($arrValue['id']) && !empty($arrValue['id']) ) {
				
				// update	
				$this->_updateFieldValue($arrValue['id'],$arrValue['value']);
				
			} else {
							
				$this->_insertFieldValue($intFieldsId,$intRowsId,$arrValue['value']);
				
			}
			
		}
		
	}
	
	/*
	* @param int field values id (MCP_FIELD_VALUES primary key)
	* @param mix value
	*/
	private function _updateFieldValue($intFieldValuesId,$mixValue) {
		
		/*
		* When the value contains nothing delete it 
		*/
		if(strlen($mixValue) === 0) {
			return $this->_objMCP->query("DELETE FROM MCP_FIELD_VALUES WHERE field_values_id = {$this->_objMCP->escapeString($intFieldValuesId)}");
		}
		
		$serialized = base64_encode(serialize($mixValue));
		
		/*
		* Only one field will be updated with the new value all others will be NULL based
		* on the storage type.
		*/
		$strSQL =
			"UPDATE
			      MCP_FIELD_VALUES
			  INNER
			   JOIN
			      MCP_FIELDS
			     ON
			      MCP_FIELD_VALUES.fields_id = MCP_FIELDS.fields_id
			    SET	    
			       MCP_FIELD_VALUES.db_varchar = (
			            CASE
			                WHEN MCP_FIELDS.db_value = 'varchar'
			                THEN '{$this->_objMCP->escapeString( $mixValue )}'
			                ELSE NULL
			            END
			       )    
			      ,MCP_FIELD_VALUES.db_text = (
			           CASE
			               WHEN MCP_FIELDS.db_value = 'text' AND MCP_FIELDS.cfg_serialized = 1
			               THEN '$serialized'
			               WHEN MCP_FIELDS.db_value = 'text'
			               THEN '{$this->_objMCP->escapeString( $mixValue )}'
			               ELSE NULL
			           END
			       )		      
			      ,MCP_FIELD_VALUES.db_int = (
			           CASE
			               WHEN MCP_FIELDS.db_value = 'int'
			               THEN {$this->_objMCP->escapeString( (int) $mixValue )}
			               ELSE NULL
			           END
			       )			      
			      ,MCP_FIELD_VALUES.db_bool = (
			           CASE
			               WHEN MCP_FIELDS.db_value = 'bool'
			               THEN {$this->_objMCP->escapeString( (int) $mixValue )}
			               ELSE NULL
			           END
			       )			      
			      ,MCP_FIELD_VALUES.db_price = (
			           CASE
			               WHEN MCP_FIELDS.db_value = 'price'
			               THEN '{$this->_objMCP->escapeString( $mixValue )}'
			               ELSE NULL
			           END
			       )
			      ,MCP_FIELD_VALUES.db_timestamp = (
			           CASE
			               WHEN MCP_FIELDS.db_value = 'timestamp'
			               THEN '{$this->_objMCP->escapeString( $mixValue )}'
			               ELSE NULL
			           END
			       )	
			      ,MCP_FIELD_VALUES.db_date = (
			           CASE
			               WHEN MCP_FIELDS.db_value = 'date'
			               THEN '{$this->_objMCP->escapeString( $mixValue )}'
			               ELSE NULL
			           END
			       )	      
			  WHERE
			      MCP_FIELD_VALUES.field_values_id = {$this->_objMCP->escapeString($intFieldValuesId)}";
		
		// echo "<p>$strSQL</p>";
		return $this->_objMCP->query($strSQL);
		
	}
	
	/*
	* @param int fields id (MCP_FIELDS primary key)
	* @param int rows id 
	* @param mix value
	*/
	private function _insertFieldValue($intFieldsId,$intRowsId,$mixValue) {
		
		/*
		* When value is empty don't do anything 
		*/
		if(strlen($mixValue) === 0) {
			return true;
		}
		
		$serialized = base64_encode(serialize($mixValue));
		
		/*
		* Only one field will contain the value all others will be null based on the fields
		* storage type. 
		*/
		$strSQL =
			"INSERT IGNORE INTO MCP_FIELD_VALUES (fields_id,rows_id,db_varchar,db_text,db_int,db_bool,db_price,db_timestamp,db_date)
				    SELECT
				         fields_id
				         ,{$this->_objMCP->escapeString( $intRowsId )} rows_id
				         
				         ,CASE
				             WHEN db_value = 'varchar'
				             THEN '{$this->_objMCP->escapeString( $mixValue )}'
				             ELSE NULL
				          END db_varchar
				          
				         ,CASE
				             WHEN db_value = 'text' AND cfg_serialized = 1
				             THEN '$serialized'
				             WHEN db_value = 'text'
				             THEN '{$this->_objMCP->escapeString( $mixValue )}'
				             ELSE NULL
				          END db_text
				          
				         ,CASE
				             WHEN db_value = 'int'
				             THEN {$this->_objMCP->escapeString( (int) $mixValue )}
				             ELSE NULL
				          END db_int
				          
				         ,CASE
				             WHEN db_value = 'bool'
				             THEN {$this->_objMCP->escapeString( (int) $mixValue )}
				             ELSE NULL
				          END db_bool
				          
				         ,CASE
				            WHEN db_value = 'price'
				            THEN '{$this->_objMCP->escapeString( $mixValue )}'
				            ELSE NULL
				          END db_price
				          
				         ,CASE
				            WHEN db_value = 'timestamp'
				            THEN '{$this->_objMCP->escapeString( $mixValue )}'
				            ELSE NULL
				          END db_timestamp
				          
				         ,CASE
				            WHEN db_value = 'date'
				            THEN '{$this->_objMCP->escapeString( $mixValue )}'
				            ELSE NULL
				          END db_date
				          
				      FROM
				         MCP_FIELDS
				     WHERE
				         fields_id = {$this->_objMCP->escapeString( $intFieldsId )}";
		
		// echo "<p>$strSQL</p>";
		return $this->_objMCP->query($strSQL);
		
	}
	
}

class MCPField extends StdClass {
	
	private 
	
	$_value
	,$_id;
	
	public function setValue($value) {
		$this->_value = $value;
	}
	
	public function setId($id) {
		$this->_id = $id;
	}
	
	public function getId() {
		return $this->_id;
	}
	
	public function __toString() {
		return (string) $this->_value;
	}
	
}
?>