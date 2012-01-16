<?php
$this->import('App.Core.Resource');
class MCPTemplate extends MCPResource {

	private static
	
	$_objTemplate;
	
	private
	
	$_arrTemplateData
	
	/*
	* styles and links extracted from modules
	* to be placed in head of master template file.
	*/
	,$_arrStyles
	,$_arrLinks
        
        /*
        * Site doctype 
        */
        ,$_doctype;
	
	/*
	* Create instance of Template
	*
	* @param object MCP
	* @return object Template
	*/
	public static function createInstance(MCP $objMCP) {
		if(self::$_objTemplate === null) {
			self::$_objTemplate = new MCPTemplate($objMCP);
		}
		return self::$_objTemplate;
	}

	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		$this->_init();
	}
	
	/*
	* Initiate Template
	*/
	private function _init() {
		$this->_arrTemplateData = array();
		$this->_arrLinks = array();
		$this->_arrStyles = array();
                
                /*
                * Maybe not the best place but it will work. 
                */
                $this->_doctype = $this->_objMCP->getConfigValue('site_doctype');
	}
	
	/*
	* Assign a variable by name to scope of template
	*
	* @param str variable name within template
	* @param mix variable value
	*/
	public function assign($strName,$mixValue) {
		$this->_arrTemplateData[$strName] = $mixValue;		
	}
	
	/*
	* Get variable assigned to the template
	* 
	* @param str name
	* @return mix variable value
	*/
	public function getTemplateVars($strName) {
		return isset($this->_arrTemplateData[$strName])?$this->_arrTemplateData[$strName]:null;
	}
	
	/*
	* Get contents of a Template
	*
	* @param str template file path
	* @return str template contents
	*/
	public function fetch($strTemplateFile,MCPModule $objModule=null) {
	
		if(!file_exists($strTemplateFile)) {
			$this->_objMCP->triggerError("Template file $strTemplateFile doesn't exist.");
		}
		
		ob_start();
                $this->_compileTemplate($strTemplateFile,$objModule);
		$strTemplateContents = ob_get_contents();
		
		/*
		* Remove all style and link tags from document
		* and store in array to be moved to head of master template.
		*/
		if($objModule !== null) {
			/* $strTemplateContents = preg_replace_callback('/(<style.*?>.*?<\/style>|<link.*?>)/si',array($this,'parseTags'),$strTemplateContents);*/
		}
		
		ob_end_clean();
		
		/*
		* When executing master template embed links and styles in head of document
		*/
		if($objModule instanceof MCPUtilMaster) {
			return $strTemplateContents;
			// return preg_replace('/<\/head>/si',"\n".implode("\n",$this->_arrLinks)."\n".implode("\n",$this->_arrStyles)."\n</head>",$strTemplateContents);
		}
		
		return $strTemplateContents;
	
	}
	
	/*
	* Makes it possible for the template to return without
	* affecting anything. 
	* 
	* @param str file path
	* @param obj module associated with template
	*/
	private function _compileTemplate($strTemplateFile,$objModule=null) {
		
		// Extract associated module variables into global scope
		extract($this->_arrTemplateData);
		if($objModule !== null) {
			extract($this->_arrTemplateData[$objModule->getName()]);
		}
		             
                include($strTemplateFile);
                
	}
	
	/*
	* Parse out and isolate style and link tags
	* from executed template contents.
	*
	* @param arr pattern matches
	* @return str empty string
	*/
	public function parseTags($arrMatches) {
		
		/*
		* Determine appropriate array to place data in
		*/
		if(strpos($arrMatches[0],'<style') === 0) {
			$this->_arrStyles[] = $arrMatches[0];
		} else {
			$this->_arrLinks[] = $arrMatches[0];
		}
		
		return '';
	}
	
	/*
	* ------------------------- Template helper methods ---------------------------------- 
	*/
	
	/*
	* htmlentities reference
	* 
	* @param mix data
	* @return str data
	*/
	public function out($mixInput) {
		return htmlentities($mixInput);
	}
	
	/*
	* Short-cut to print nav 
	* 
	* @param str menu name
	*/
	public function nav($strName) {
            
		//echo $this->_objMCP->executeComponent('Component.Navigation.Module.Menu',array($strLocation));
		
		// TESTING!!!
		echo $this->_objMCP->executeComponent('Component.Menu.Module.Menu',array($strName));
	}
	
	/*
	* Display admin nav
	* 
	* The admin nav is not managed under the normal navigation menu structure. It is a stand-alone
	* dynamic component.
	* 
	* The below is dirty, eventually this will be moved to it own separate module. I just need a way
	* to print it consistently for now though the implemention will change in the future.
	* 
	*/
	public function admin() {
		
		$perm = $this->_objMCP->getPermission(MCP::READ,'Route','Admin/*');
		if( $perm['allow'] ) {		
                    // old echo $this->_objMCP->executeComponent('Component.Navigation.Module.Menu.Admin',array());	
                    echo $this->_objMCP->executeComponent('Component.Menu.Module.Menu.Admin',array());	
		}
		
	}
        
	/*
	* Short-cut to print breadcrumbs
	*/
	public function breadcrumbs() {
            echo $this->_objMCP->executeComponent('Component.Util.Module.Breadcrumb');
	}
	
	/*
	* Short-cut to echo global request content HTML
	*/
	public function content() {
		echo $this->getTemplateVars('REQUEST_CONTENT');
	}
	
	/*
	* Short-cut to echo global login HTML 
	*/
	public function login() {
		echo $this->getTemplateVars('LOGIN');
	}
	
	/*
	* Short-cut to print system messages
	*/
	public function messages() {
		echo $this->_objMCP->executeComponent('Component.Util.Module.SystemMessage.User',array());
	}
	
	/*
	* Print sites doc-type 
	*/
	public function doctype() {
		
		switch($this->_objMCP->getConfigValue('site_doctype')) {
			
			case 'HTML 5':
				echo '<!DOCTYPE html>';
				break;
			
			case 'HTML 4.01 Transitional':
				echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
				break;
				
			case 'XHTML 1.0 Strict':
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				break;
				
			case 'XHTML 1.0 Transitional':
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				break;

			case 'HTML 4.01 Strict':
			default:			
				echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
				
		}
		
	}
	
	/*
	* Short-cut to echo browser title
	*/
	public function title() {
		echo $this->getTemplateVars('TITLE');
	}
	
	/*
	* Print footer from site config and execute module
	*/
	public function footer() {
		$footer = $this->_objMCP->getConfigValue('site_footer_module');
		if(strpos($footer ,'Site.') === 0) {
			echo $this->_objMCP->executeModule($footer,array());
		} else {
			echo $this->_objMCP->executeComponent($footer);
		}
	}
	
	/*
	* Print header from site config and execute module
	*/
	public function header() {
		$header = $this->_objMCP->getConfigValue('site_header_module');
		if(strpos($header,'Site.') === 0) {
			echo $this->_objMCP->executeModule($header,array());
		} else {
			echo $this->_objMCP->executeComponent($header);
		}
	}
	
	/*
	* Print site heading 
	*/
	public function heading() {
		echo $this->out($this->_objMCP->getConfigValue('site_heading'));
	}
	
	/*
	* Short-cut to display CSS files
	*/
	public function css() {
		echo $this->_objMCP->executeComponent($this->_objMCP->getConfigValue('site_css_module'));
	}
	
	/*
	* Short-cut to display JS files 
	*/
	public function js() {
		echo $this->_objMCP->executeComponent($this->_objMCP->getConfigValue('site_js_module'));
	}
	
	/*
	* Short-cut to display meta data
	*/
	public function meta() {
		echo $this->_objMCP->executeComponent($this->_objMCP->getConfigValue('site_meta_module'));
	}
	
	/*
	* Short-cut method to display dynamic content blocks 
	* 
	* @param str content to display
	* @param str content type
	* @param obj module
	*/
	public function display_block($strContent,$strType='text',$objModule=null) {
		
		if($objModule !== null) {
			extract($this->_arrTemplateData[$objModule->getName()]);
		}
		
		switch($strType) {
			/*
			* PHP content 
			*/
			case 'php':
				eval('?>'.$strContent);
				break;
			
			/*
			* HTML content 
			*/
			case 'html':
				echo $strContent;
				break;
			
			/*
			* Textual content s
			*/
			case 'text':
			default:
				echo strip_tags($strContent);
		}
	}
	
	public function build_form($frm) {	
		echo $this->ui('Common.Form.Form',$frm);
	}
        
	public function build_table($tbl) {	
		echo $this->ui('Common.Listing.Table',$tbl);	
		return;
	}
        
	public function date_format($strTimestamp) {
		return $this->ui('Common.Field.Date',array(
			'date'=>$strTimestamp
			,'type'=>'timestamp'
		));
	}
        
	public function build_tree($tree) {		
		echo $this->ui('Common.Listing.Tree',$tree);
		return;
	}
        
	public function ui($name,$options) {
		return $this->_objMCP->ui($name,$options);
	}
	
	/*
	* Used to close empty HTML element for seamless XHTML and HTML transition 
	* 
	* To be used with input and meta tags primarly
	* 
	* @param bool when true tag is printed
	* @return str when previous is false tag close is returned
	*/
	public function close($boolEcho=true) {
		/*
		* Determine whether XHTML or HTML is being used 
		*/
		switch($this->_doctype) {
				
			case 'XHTML 1.0 Strict':
			case 'XHTML 1.0 Transitional':
				if($boolEcho === true) echo '/>'; else return '/>';
				break;

			case 'HTML 5':
			case 'HTML 4.01 Transitional':
			case 'HTML 4.01 Strict':
			default:
				if($boolEcho === true) echo '>'; else return '>';
			
		}		
        }
	
}
?>