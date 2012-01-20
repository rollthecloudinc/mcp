<?php 
/*
* Base Node data access layer 
*/
$this->import('App.Core.DAO');
class MCPDAONode extends MCPDAO {
	
	/*
	* Generic method to list all nodes
	* 
	* @param str select columns
	* @param str where clause
	* @param str order by clause
	* @param str limit clause
	* @return array [nodes,found rows]
	* 
	* @todo: refine bind variable implementation
	* 
	*/
	public function listAll($strSelect='n.*',$mixWhere=null,$strSort=null,$strLimit=null) {
		
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
			      ,n.nodes_id tmp_nodes_id
			      ,n.node_types_id tmp_node_types_id
			   FROM 
			      MCP_NODES n
			  INNER
			   JOIN
			      MCP_USERS u
			     ON
			      n.authors_id = u.users_id	
			  INNER
			   JOIN
			      MCP_NODE_TYPES t
			     ON
			      n.node_types_id = t.node_types_id	      
			      %s %s %s'
			,$strSelect
			,$strWhere === null?'':" WHERE $strWhere"
			,$strSort === null?'':" ORDER BY $strSort"
			,empty($strLimit) ? '' :"LIMIT $strLimit"
		);
		
		//echo '<p>',$strSQL,'</p>';
		
		$arrNodes = $this->_objMCP->query($strSQL,$arrBound);
		
		/*
		* Add in dynamic fields - Internal columns used to add dynamic field data after removed
		*/
		foreach($arrNodes as &$arrNode) {
			$arrNode = $this->_objMCP->addFields($arrNode,$arrNode['tmp_nodes_id'],'MCP_NODE_TYPES',$arrNode['tmp_node_types_id']);
			unset($arrNode['tmp_nodes_id'],$arrNode['tmp_node_types_id']);
		}
		
		if( empty($strLimit) ) {
			return $arrNodes;
		}
		
		return array(
			$arrNodes
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
	}
	
	/*
	* List all nodes method for use with navigation link datasource callback 
	*/
	public function fetchNodes($strSelect='n.*',$strWhere=null,$strSort=null,$strLimit=null) {
		$args = func_get_args();
		return array_shift(call_user_func_array(array($this,'listAll'),$args));
	}
	
	/*
	* Lists all node comments
	* 
	* @param nodes id
	* @param str select columns
	* @param str additional where filtering
	* @param str order by clause
	* @param str limit clause (triggers found rows)
	* @return array nodes comments
	* 
	* @todo: support variable binding
	*/
	public function fetchNodesComments($intId,$strSelect='c.*',$mixWhere=null,$strSort=null,$strLimit=null) {
		
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
			"SELECT 
			      %s %s 
			   FROM 
			      MCP_COMMENTS c 
			   LEFT OUTER
			   JOIN
			      MCP_USERS u
			     ON
			      c.commenter_id = u.users_id
			  WHERE 
			      c.comment_type = 'node'
			    AND 
			      c.comment_types_id  = %s 
			     %s
			     %s"
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$this->_objMCP->escapeString($intId)
			,$strWhere === null?'':"AND $strWhere"
			,$strSort === null?'':" ORDER BY $strSort"
			,$strLimit === null?'':" LIMIT $strLimit"
		);
		
		$arrRows = $this->_objMCP->query($strSQL,$arrBound);
		
		if($strLimit === null) {
			return $arrRows;
		}
		
		/*
		* Limit triggers found rows to be selected 
		*/
		return array(
			$arrRows
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
	}
	
	/*
	* Fetch node types
	* 
	* @param str select clause
	* @param str where clause
	* @param str sort clause
	* @param str limit statement
	* @return array node types
	* 
	* @todo: refine bind variable support
	*/
	public function fetchNodeTypes($strSelect='t.*',$mixFilter=null,$strSort=null,$strLimit=null) {
		
		// bound paramters
		$arrBound = array();
		
		// bound paramter resolution
		if(is_array($mixFilter) === true) {
			$arrBound = $mixFilter;
			$strFilter = array_shift($arrBound);
		} else {
			$strFilter = $mixFilter;
		}
		
		/*
		* Build SQL 
		*/
		$strSQL = sprintf(
			'SELECT 
                  %s
                  %s
			   FROM 
			      MCP_NODE_TYPES t 
                  %s
                  %s
                  %s'
         	,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
         	,$strSelect
         	,$strFilter === null?'':"WHERE $strFilter"
         	,$strSort === null?'':"ORDER BY $strSort"
         	,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		/*
		* fetch node types
		*/
		$arrNodeTypes = $this->_objMCP->query($strSQL,$arrBound);
		
		if($strLimit === null) {
			return $arrNodeTypes;
		}
		
		/*
		* Otherwise grab number of found rows also 
		*/
		return array(
			$arrNodeTypes
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
		
	}
	
	/*
	* Fetch nodes data by id
	* 
	* @param int nodes id
	* @param bool accept cached node?
	* @return array nodes data
	*/
	public function fetchById($intId,$boolCache=true) {
		
		/*
		* Caching option checks to see if a cached version exists and if so returns
		* that avoiding the logic necessary to build the node from scratch. 
		*/
		/*if( $boolCache === true ) {
			$arrCachedNode = $this->_getCachedNode($intId);	
			if( $arrCachedNode !== null ) {
				return $arrCachedNode;
			}
		}*/
		
		/*$strSQL = sprintf(
			'SELECT %s,node_types_id tmp_node_types_id FROM MCP_NODES WHERE nodes_id = %u'
			,$strSelect
			,(int) $intId
		);*/
		
		// fetch node
		$arrNode = array_pop($this->_objMCP->query(
			'SELECT * FROM MCP_NODES WHERE nodes_id = :nodes_id'
			,array(
				':nodes_id'=>(int) $intId
			)
		));
		
		// decorate node with dynamic field values
		if( $arrNode !== null ) {
			$arrNode = $this->_objMCP->addFields($arrNode,$intId,'MCP_NODE_TYPES',$arrNode['node_types_id']);
		}
		
		// remove the entity id
		// unset($arrNode['tmp_node_types_id']);
		
		return $arrNode;
	}
	
	/*
	* Fetch node type by id
	* 
	* @param int node types id
	* @param str select columns
	* @return array node type data
	*/
	public function fetchNodeTypeById($intId) {
		
		/*$strSQL = sprintf(
			'SELECT %s FROM MCP_NODE_TYPES WHERE node_types_id = ?'
			,$strSelect
		);*/
		
		
		return array_pop($this->_objMCP->query(
			'SELECT * FROM MCP_NODE_TYPES WHERE node_types_id = :node_types_id'
			,array(
				':node_types_id'=>(int) $intId
			)
		));
	}
	
	/*
	* Fetch node type by name
	* 
	* Takes care of all the hassle of splitting up the name and package for
	* node types belonging to a package eg. My.Package::whatever
	* 
	* @param str node type full/display name
	* @param str select clause
	* @param int sites id (defaults to current site)
	* @return arr node type data
	*/
	public function fetchNodeTypeByName($strNodeTypeName,$strSelect='*',$intSitesId=null) {
		
		$pkg = null;
		$name = null;
			
		/*
		* Resolve actual name and possible package node type belongs to 
		*/
		if(strpos($strNodeTypeName,'::') !== false) {
			list($pkg,$name) = explode('::',$strNodeTypeName,2);
		} else {
			$name = $strNodeTypeName;
		}
		
		/*
		* Set-up bindings 
		*/
		$bind = array($name);

		/*
		* When node type belongs to package add it to bindings 
		*/
		if($pkg !== null) {
			$bind[] = $pkg;
		}
		
		/*
		* Add sites id to bindings 
		*/
		$bind[] = $intSitesId === null?$this->_objMCP->getSitesId():$intSitesId;
		
		/*
		* Build final SQL to select node type 
		*/
		$strSQL = sprintf(
			'SELECT %s FROM MCP_NODE_TYPES WHERE system_name = ? AND pkg %s AND sites_id = ?'
			,$strSelect
			,$pkg === null?" = '' ":' = ?'
		);
		
		// run query
		return array_pop($this->_objMCP->query($strSQL,$bind));		
	}
	
	/*
	* Get name of node type for embedding in URLs, display and other purposes
	* 
	* @param node arr node type data
	*/
	public function getNodeTypeName($arrNodeType) {
		
		$name = $arrNodeType['system_name'];
		
		// node types with a package get it pre-pended
		if( !empty($arrNodeType['pkg']) ) {
			$name = "{$arrNodeType['pkg']}::$name";
		}
		
		return $name;
		
	}
	
	/*
	* Fetch node comment by id
	* 
	* @param int comments id
	* @param str select columns
	* @return array comment data
	*/
	public function fetchCommentById($intId) {
		
		return array_pop($this->_objMCP->query(
			'SELECT * FROM MCP_COMMENTS WHERE comments_id = :comments_id'
			,array(
				':comments_id'=>(int) $intId
			)
		));
			
	}
	
	/*
	* Fetch available content types for nodes
	* 
	* @return array content types
	*/
	public function fetchContentTypes($strType='content_type') {
		
		$arrResult = $this->_objMCP->query('DESCRIBE MCP_NODES');
		$arrContentTypes = array();
		
		foreach($arrResult as $arrColumn) {
			if(strcmp($strType,$arrColumn['Field']) == 0) {
				
				foreach(explode(',',str_replace("'",'',trim(trim($arrColumn['Type'],'enum('),')'))) as $strValue) {
					$arrContentTypes[] = array('value'=>$strValue,'label'=>$strValue);
				}
				
				break;
			}
		}
		
		return $arrContentTypes;
	}
	
	/*
	* Locate node with same url
	* 
	* @param str node url
	* @param int sites id
	* @param int node types id
	* @return array blog
	*/
	public function fetchNodeByUrl($strNodeUrl,$intSitesId,$nodeTypesId) {
		
		$strSQL = "SELECT * FROM MCP_NODES WHERE BINARY node_url = ? AND sites_id = ? AND deleted = 0 AND node_types_id = ?";
		
		return array_pop($this->_objMCP->query($strSQL,array((string) $strNodeUrl,(int) $intSitesId,(int) $nodeTypesId )));
	}
	
	/*
	* Fetch node archive nan for site, user or site and user combination
	* 
	* [@param] int sites id
	* [@param] int users id
	* @return array archive
	*/
	public function fetchNodeArchiveNav($intSitesId=null,$intUsersId=null) {
		
		// bound query params
		// bind published by default
		$arrBound = array(1);
		
		$arrArchive = array();
		
		$strSQL = sprintf(
			'SELECT
			      MONTH(b.created_on_timestamp) month
			      ,YEAR(b.created_on_timestamp) year
			      ,COUNT(*) nodes
			   FROM
			      MCP_NODES n
			  WHERE
			      n.deleted = 0
			    AND
			      n.blog_published = ?
			      %s
			      %s
			  GROUP
			     BY
			      month
			      ,year
			  ORDER
			     BY
			      year DESC
			      ,month DESC'
			,$intSitesId === null?'':' AND sites_id = ?'
			,$intUsersId === null?'':' AND authors_id = ?'
		);
		
		// add bound paramters
		if($intSitesId !== null) {
			$arrBound[] = (int) $intSitesId;
		}	
		if($intUsersId !== null) {
			$arrBound[] = (int) $intUsersId;
		}
		
		foreach($this->_objMCP->query($strSQL,$arrBound) as $arrRow) {
			
			// total blogs in year
			if(isset($arrArchive[$arrRow['year']])) {
				$arrArchive[$arrRow['year']]['nodes']+= $arrRow['nodes'];
			} else {
				$arrArchive[$arrRow['year']]['nodes'] = $arrRow['nodes'];
			}
			
			$arrArchive[$arrRow['year']]['months'][$arrRow['month']] = array('blogs'=>$arrRow['nodes']); 
		}
		
		return $arrArchive;

	}
	
	/*
	* 
	*/
	public function fetchDataSourceNodeArchiveNav($intSitesId) {
		$arrData = $this->fetchNodeArchiveNav($intSitesId);
		$arrReturn = array();
		
		foreach($arrData as $strYear=>$arrYear) {
			$arr = array('id'=>"$strYear",'label'=>"$strYear",'children'=>array(),'vars'=>"00-$strYear");
			foreach($arrYear['months'] as $strMonth=>$arrMonth) {
				$arr['children'][] = array(
					'id'=>$strMonth.$strYear
					,'label'=>$strMonth
					,'vars'=>(strlen($strMonth)==1?"0$strMonth":$strMonth)."-$strYear"
				);
			}
			$arrReturn[] = $arr;
		}
		
		return $arrReturn;
	}
	
	/*
	* Insert or update node
	*/
	public function saveNode($arrNode) {	
		
		/*
		* Get fields native to table
		*/
		$schema = $this->_objMCP->query('DESCRIBE MCP_NODES');
		
		$native = array();
		foreach($schema as $column) {
			$native[] = $column['Field'];
		}
		
		/*
		* Siphon dynamic fields
		*/
		$dynamic = array();
		
		foreach(array_keys($arrNode) as $field) {
			if(!in_array($field,$native)) {
				$dynamic[$field] = $arrNode[$field];
				unset($arrNode[$field]);
			}
		}
		
		/*
		* Start transaction 
		*/
		$this->_objMCP->begin();
		
		try {
			
			$intId = $this->_save(
				$arrNode
				,'MCP_NODES'
				,'nodes_id'
				,array('node_url','node_title','node_subtitle','node_content','content_type','intro_type','intro_content')
				,'created_on_timestamp'
			); 
		
			/*
			* Save dynamic fields 
			*/
			$this->_objMCP->saveFieldValues($dynamic,(isset($arrNode['nodes_id'])?$arrNode['nodes_id']:$intId),'MCP_NODE_TYPES',$arrNode['node_types_id']);
		
			/*
			* Update node cache 
			*/
			$this->_setCachedNode( isset($arrNode['nodes_id'])?$arrNode['nodes_id']:$intId );
			
			/*
			* Commit transaction 
			*/
			$this->_objMCP->commit();
			
		} catch(MCPDBException $e) {
                    
                        // echo $e->getMessage();
			
			/*
			* If something went wrong rollback transaction 
			*/
			$this->_objMCP->rollback();
			
			/*
			* Throw more refined/specific exception 
			*/
			throw new MCPDAOException( $e->getMessage() );
			
		}
		
		return $intId;
		
	}
	
	/*
	* Insert or update comment
	*/
	public function saveNodeComment($arrComment) {	
		return $this->_save(
			$arrComment
			,'MCP_COMMENTS'
			,'comments_id'
			,array('comment_type','commenter_first_name','commenter_last_name','commenter_email','comment_content','content_type')
			,'created_on_timestamp'
		);		
	}
	
	/*
	* Insert or update node type
	*/
	public function saveNodeType($arrNodeType) {
		
		return $this->_save(
			$arrNodeType
			,'MCP_NODE_TYPES'
			,'node_types_id'
			,array('system_name','human_name','pkg','description','theme_tpl','form_tpl')
			,'created_on_timestamp'
			,null
			
			// Special argument to ignore casting empty string to NULL
			,array('pkg')
		);		
	}
	
	/*
	* Delete a node type(s)
	* 
	* NOTE: All deletes in system are carried out as soft-deletes to be safe. Any item
	* with a deleted column value of NULL is considered "deleted" and 0 not deleted. To truly remove items
	* completely from the database or any other storage mechanism "purge" will be used such as;
	* purgeNodeType. However, for safety reasons any item MUST be deleted before purged.
	* 
	* @param mix sinle integer values or array of integer values ( MCP_NODE_TYPES primary key )
	* 
	* @todo: support variable binding
	*/
	public function deleteNodeTypes($mixNodeTypesId) {

		/*
		* Node types and nodes will be deleted. Fields and field
		* values will be kept in tact for now considering they can't
		* be accessed without the node entry, so it isn't hurting anything
		* and makes it easier to revert or undelete something that may
		* have been accidently deleted. The same will hold true for comments.
		*/
		$strSQL = sprintf(
		   'UPDATE 
			      MCP_NODE_TYPES
			  LEFT OUTER
			  JOIN
			      MCP_NODES
			    ON
			      MCP_NODE_TYPES.node_types_id = MCP_NODES.node_types_id
			   SET
			      MCP_NODE_TYPES.deleted = NULL
			     ,MCP_NODES.deleted = NULL
			 WHERE
			     MCP_NODE_TYPES.node_types_id IN (%s)'
			     
			,is_array($mixNodeTypesId) ? $this->_objMCP->escapeString(implode(',',$mixNodeTypesId)) : $this->_objMCP->escapeString($mixNodeTypesId)
		);
		
		// echo "<p>$strSQL</p>";
		return $this->_objMCP->query($strSQL);
		
	}
	
	/*
	* Delete node(s)
	* 
	* @param mix single interger value or array of integers ( MCP_NODES primary key )
	* 
	* @todo: support variable binding
	*/
	public function deleteNodes($mixNodeId) {
            
                $time = time();

		/*
		* nodes will be deleted. Fields and field
		* values will be kept in tact for now considering they can't
		* be accessed without the node entry, so it isn't hurting anything
		* and makes it easier to revert or undelete something that may
		* have been accidently deleted. The same will hold true for comments.
		*/
		$strSQL = sprintf(
			"UPDATE
			      MCP_NODES n
                           LEFT OUTER
                           JOIN
                              MCP_FIELDS f
                             ON
                              n.node_types_id = f.entities_id
                            AND
                              f.entity_type = 'MC_NODE_TYPES'
                            AND
                              f.deleted = 0
                           LEFT OUTER
                           JOIN
                              MCP_FIELD_VALUES fv
                             ON
                              f.fields_id = fv.fields_id
                            AND
                              n.nodes_id = fv.rows_id
                            AND
                              fv.deleted = 0
                           LEFT OUTER
                           JOIN
                              MCP_FIELDS r
                             ON
                              r.db_ref_table = 'MCP_NODES'
                            AND
                              r.db_ref_col = 'nodes_id'
                            AND
                              r.deleted = 0
                           LEFT OUTER
                           JOIN
                              MCP_FIELD_VALUES rv
                             ON
                              r.fields_id = rv.fields_id
                            AND
                              n.nodes_id = r.db_int
                            AND
                              r.deleted = 0
			    SET
			       n.deleted = NULL
                              ,n.deleted_on_timestamp = FROM_UNIXTIME(".$time.")
                              
                              ,fv.deleted = NULL
                              ,fv.deleted_on_timestamp = FROM_UNIXTIME(".$time.")
                                  
                              ,r.deleted = NULL
                              ,r.deleted_on_timestamp = FROM_UNIXTIME(".$time.")
                                  
                              ,rv.deleted = NULL
                              ,rv.deleted_on_timestamp = FROM_UNIXTIME(".$time.")
			  WHERE
			      n.nodes_id IN (%s)"
			      
			,is_array($mixNodeId) ? $this->_objMCP->escapeString(implode(',',$mixNodeId)) : $this->_objMCP->escapeString($mixNodeId)
		);
                
                /*
                * Also need to delete any virtual fk references 
                */
                
                $this->debug($strSQL);
		
		// echo "<p>$strSQL</p>";
		//return $this->_objMCP->query($strSQL);
	
	}
	
	/*
	* Delete node comment(s)
	* 
	* @param mix single integer value or array of integer values ( MCP_COMMENTS primary key )
	*/
	public function deleteNodesComments($mixNodeCommentId) {
		
		/*
		* @TODO: This is a little trickly considering the tree structure of the comment
		* data. What will most likely happen here is select all the nodes. Than recall this
		* function for any children until the leaf is meet. This should delete all branches. A single
		* query is not practical here. 
		*/
		
	}
	
	/*
	* Create node URL safe title
	* 
	* @param str node title
	* @return str url safe node title
	*/
	public function engineerNodeUrl($strNodeTitle) {
		
		/*
		* Replace some common illegal characters and remove slashes.
		* NOTE: Slashes must be removed because they will break the application URL decoding system
		*/
		$strNodeTitle = str_replace(array(' ','/'),array('_',''),$strNodeTitle);
		
		/*
		* Use PHP native filter function to further santize title 
		*/
		return filter_var($strNodeTitle,FILTER_SANITIZE_URL);
	}
	
	/*
	* Get node stored in cache 
	* 
	* @param int nodes id
	* @return array node data
	*/
	private function _getCachedNode($intId) {
		return $this->_objMCP->getCacheDataValue("node_{$intId}",$this->getPkg());
	}
	
	/*
	* Update cache with most  up to date snapshot of node
	* 
	* @param int nodes id
	* @return bool
	*/
	private function _setCachedNode($intId) {
		
		/*
		* Bypass cache and get most recent node state 
		*/
		$arrNode = $this->fetchById($intId,'*',false);
		
		/*
		* Save node to cache 
		*/
		return $this->_objMCP->setCacheDataValue("node_{$intId}",$arrNode,$this->getPkg());
		
	}
	
}
?>