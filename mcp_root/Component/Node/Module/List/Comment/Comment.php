<?php 
/*
* List node comments 
*/
class MCPNodeListComment extends MCPModule {
	
	private
	
	/*
	* Node data access layer 
	*/
	$_objDAONode
	
	/*
	* Node to display comments for 
	*/
	,$_intNodesId;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Get Node DAO
		$this->_objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
	}
	
	public function execute($arrArgs) {
		
		/*
		* Extract nodes id 
		*/
		$this->_intNodesId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		/*
		* Fetch nodes comments 
		*/
		if($this->_intNodesId !== null) {
			$arrComments = $this->_objDAONode->fetchNodesComments($this->_intNodesId,'c.*,u.username','c.deleted = 0','c.created_on_timestamp ASC');
		} else {
			$arrComments = array();
		}
		
		/*
		* Set template data 
		*/
		$this->_arrTemplateData['comments'] = $arrComments;
		
		return 'Comment/Comment.php';
	}
	
}
?>