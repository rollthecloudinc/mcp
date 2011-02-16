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
	,$_intPage = 1
	
	/*
	* Internal redirect 
	*/
	,$_strRequest;
	
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
		$this->_intPage = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):1;
		
		// Internal redirect - sued to switch between edit and read nested modules
		$this->_strRequest = !empty($arrArgs) && in_array($arrArgs[0],array('Edit','View'))?array_shift($arrArgs):null;
		
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
			foreach( $this->_arrTemplateData['rows'] as $id=>$row) {
				
				// ---------------------------------------------------------------------------
				
				// Change row
				$this->_arrTemplateData['row'] = $row;
				$this->_arrTemplateData['id'] = $id;
				
				// URL to edit entity
				$this->_arrTemplateData['edit'] = '';
				if(isset($row['allow_edit']) && $row['allow_edit']) {
					$this->_arrTemplateData['edit'] = "{$this->getBasePath(false)}/{$this->_intPage}/Edit/$id";
				}
				
				// URL to view full, individual entity
				$this->_arrTemplateData['read'] = '';
				if(isset($row['allow_read']) && $row['allow_read']) {
					$this->_arrTemplateData['read'] = "{$this->getBasePath(false)}/{$this->_intPage}/View/$id";
				}
				
				// -------------------------------------------------------------------------------
				
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
		
		// Back label 
		$this->_arrTemplateData['back_label'] = 'Content';
		
		// Redirect back link
		$this->_arrTemplateData['back_link'] = "{$this->getBasePath(false)}/{$this->_intPage}";
		
		
		/*
		* Handle internal redirection -------------------------------------------------------------
		*/
		
		// Edit entity
		if( $this->_objView && strcmp('Edit',$this->_strRequest) === 0) {
			
			switch($this->_objView->base) {
				
				case 'Node': // edit node
					$strTpl = 'View/Redirect.php';
					$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
						'Component.Node.Module.Form.Entry'
						,$arrArgs
						,null
						,array($this)
					);
					
					break;
				
				default:
				
			}
			
		} else if ( $this->_objView && strcmp('View',$this->_strRequest) === 0 ) {
			
			switch($this->_objView->base) {
				
				case 'Node': // view node content
					$strTpl = 'View/Redirect.php';
					$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
						'Component.Node.Module.View.Entry'
						,$arrArgs
						,null
						,array($this)
					);
			
				default:
					
			}
			
		}
		
		// Assign raw view data to template
		$this->_arrTemplateData['view'] = $this->_objView;
		
		// Return the pagination template
		return $strTpl;
		
	}
	
	/*
	* manufacturer new base path from arguments supplied in request
	* 
	* @return str base path
	*/
	public function getBasePath($redirect=true) {
		$strBasePath = parent::getBasePath();
		
		// add redirect flag
		if($redirect === true && $this->_strRequest !== null) {
			$strBasePath.= "/{$this->_strRequest}";
		}
		
		return $strBasePath;
	}
	
}
?>