<?php
class MCPViewView extends MCPModule {
	
	private
	
	/*
	* View data access layer 
	*/
	$_objDAOView
	
	/*
	* The view data that is being displayed
	*/
	,$_arrView;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		
		// Get the view data access layer
		$this->_objDAOView = $this->_objMCP->getInstance('Component.View.DAO.DAOView',array($this->_objMCP));
		
	}
	
	/*
	* Get the current view
	* 
	* @return array view data
	*/
	private function _getView() {
		return $this->_arrView;
	}
	
	/*
	* Set the current view
	* 
	* @param array view data
	*/
	private function _setView($arrView) {
		$this->_arrView = $arrView;
	}
	
	public function execute($arrArgs) {
		
		// Get the view id to display
		$intViewsId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		// Set view data
		if($intViewsId !== null) {
			$this->_setView( $this->_objDAOView->fetchViewById($intViewsId) );
			
			$this->_objDAOView->buildView( $this->_getView() );
			
		}
		
		// Assign raw view data to template
		$this->_arrTemplateData['view'] = $this->_getView();
		
		return 'View/View.php';
		
	}
	
}
?>