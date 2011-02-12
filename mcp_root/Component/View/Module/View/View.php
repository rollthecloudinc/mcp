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
	,$_objView
	
	/*
	* Current page numebr for views with pagination enabled 
	*/
	,$_intPage;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		
		// Get the view data access layer
		$this->_objDAOView = $this->_objMCP->getInstance('Component.View.DAO.DAOView',array($this->_objMCP));
		
	}
	
	/*
	* Pagination callback 
	* 
	* NOTE: used when view standard paging is enabled
	* 
	* @param int offset
	* @param int limit
	* @return int found rows
	*/
	public function paginate($intOffset, $intLimit) {
		
		// Fetch views rows
		$data = $this->_objDAOView->fetchRows( $this->_objView , $intOffset );
		
		// Assign row data
		$this->_arrTemplateData['rows'] = array_shift( $data );
		
		// return the number of found rows
		return array_shift($data);
		
	}
	
	/*
	* Alphabetize callback 
	* 
	* NOTE: used when view alphabetize is enabled
	* 
	* @param str letter
	* @return int found rows
	*/
	public function alphabetize($strLetter) {
		
	}
	
	public function execute($arrArgs) {
		
		// Get the view id to display
		$intViewsId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		// Extract page number
		$this->_intPage = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		// Set view data
		if($intViewsId !== null) {
			$this->_objView = $this->_objDAOView->fetchViewById($intViewsId);
		}
		
		// Fetch row data for the view
		$strTpl = 'View/View.php';
		
		if( $this->_objView !== null ) { 
			
			// standard numerical pagination
			if( $this->_objView->paginate ) {
				
				$this->_arrTemplateData['pager'] = $this->_objMCP->executeComponent(
					'Component.Util.Module.Pagination'
					,array( $this->_objView->limit , $this->_intPage)
					,'Component.Util.Template'
					,array($this)
				);

			// no pagination
			} else {
				
				$this->_arrTemplateData['pager'] = '';
				
				// Fetch all rows w/ any type of pagination
				$this->_arrTemplateData['rows'] = $this->_objDAOView->fetchRows( $this->_objView );
				
			}
			
			$content = '';
			
			// Theme the data
			foreach( $this->_arrTemplateData['rows'] as $row) {
				
				//Change row
				$this->_arrTemplateData['row'] = $row;
				
				// reload template data
				$this->loadTemplateData();
				
				// Fetch styled row content
				$content.= $this->_objMCP->fetch( ROOT.str_replace('*',$this->_objMCP->getSite(),$this->_objView->template_row) , $this );
			}
			
			// Assign inner content
			$this->_arrTemplateData['content'] = $content;
			
			// When a wrapper template has been defined use that to wrap the content otherwise use default
			$strTpl = $objView->template_wrap?$objView->template_wrap:$strTpl;
			
		}
		
		// Assign raw view data to template
		$this->_arrTemplateData['view'] = $this->_objView;
		
		// Return the pagination template
		return $strTpl;
		
	}
	
}
?>