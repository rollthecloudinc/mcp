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
	,$_arrLinks;
	
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
		
		$strFileContents = file_get_contents($strTemplateFile);
		
		ob_start();
		$this->_compileTemplate($strFileContents,$objModule);
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
	* @param str file contents
	* @param obj module
	*/
	private function _compileTemplate($strFileContents,$objModule=null) {
		
		// Extract associated module variables into global scope
		extract($this->_arrTemplateData);
		if($objModule !== null) {
			extract($this->_arrTemplateData[$objModule->getName()]);
		}
		
		eval('?>'.$strFileContents);
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
	* @param str menu location
	*/
	public function nav($strLocation) {
		//echo $this->_objMCP->executeComponent('Component.Navigation.Module.Menu',array($strLocation));
		
		// TESTING!!!
		echo $this->_objMCP->executeComponent('Component.Menu.Module.Menu',array('main_menu'));
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
			echo $this->_objMCP->executeComponent('Component.Navigation.Module.Menu.Admin',array());		
		}
		
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
		echo $this->_objMCP->executeComponent('Component.Util.Module.SystemMessage',array());
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
	* Inspired by Smarty html_options method because it is beyond useful.
	* Used to render options list for a select menu. 
	*/
	public function html_options($arrOptions) {
		/*
		* Return string 
		*/
		$strReturn = '';
		
		/*
		* Extract values, output and selected
		*/
		$arrValues = isset($arrOptions['values'])?$arrOptions['values']:null;
		$arrOutput = isset($arrOptions['output'])?$arrOptions['output']:null;
		$strSelected = isset($arrOptions['selected'])?$arrOptions['selected']:null;
		
		/*
		* Values and output are required to build option list 
		*/
		if($arrValues === null || $arrOutput === null) return $strReturn;
		
		$intValues = count($arrValues);
		for($i=0;$i<$intValues;$i++) {
			$strReturn.=
			sprintf(
				'<option value="%s"%s>%s</option>'
				,$arrValues[$i]
				,$strSelected == $arrValues[$i]?' selected="selected"':''
				,htmlentities($arrOutput[$i])
			);
		}
		
		return $strReturn;
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
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	* Form builder 
	* 
	* @param array frm key value pairs:
	* - name
	* - action
	* - config
	* - values
	* - errors
	* - legend
	*/
	private function _deprecated_build_form($frm) {
		
		echo $this->ui('Common.Form.Form',$frm);
		return;
		
		
		
		$name = isset($frm['name'])?$frm['name']:'';
		$action = isset($frm['action'])?$frm['action']:'';
		$config = isset($frm['config'])?$frm['config']:array();
		$values = isset($frm['values'])?$frm['values']:array();
		$errors = isset($frm['errors'])?$frm['errors']:array();
		$legend = isset($frm['legend'])?$frm['legend']:'';
		$idbase = isset($frm['idbase'])?$frm['idbase']:'frm-';
		$submit = isset($frm['submit'])?$frm['submit']:'Save';
		
		
		
		
		// clear button
		$clear =  isset($frm['clear'])?$frm['clear']:'';
		
		?>
		<form name="<?php echo $name; ?>" id="<?php echo $name; ?>" action="<?php echo $action; ?>" method="POST" enctype="multipart/form-data">
		<fieldset>
			<legend><?php echo $legend; ?></legend>
			<?php
			if(!empty($config)) {
			
				print('<ul>');
				foreach($config as $field=>$data) {
					
					/*
					* Skip static fields 
					*/
					if(isset($data['static']) && strcmp('Y',$data['static']) == 0) continue;
					
					/*
					* Disabled attribute 
					*/
					$strDisabled = isset($data['disabled']) && $data['disabled'] == 'Y'?' disabled="disabled"':'';
				
					/*
					* Print the field label 
					*/
					/*printf(
						'<li class="%2$s%s"><label for="%s%1$s"'.$strDisabled.'>%s%s</label>'
						,strtolower(str_replace('_','-',$field))
						,$idbase
						,$data['label']
						,isset($data['required']) && $data['required'] == 'Y'?'&nbsp<span class="required">*</span>':''
					);*/
					
					echo '<li>'.$this->ui('Common.Form.Label',array(
						'for'=>$idbase.strtolower(str_replace('_','-',$field))
						,'label'=>$data['label']
						,'required'=>isset($data['required']) && $data['required'] == 'Y'?true:false
					));
					
					$loops = isset($data['multi'])?$data['multi']:1;
					
					for($i=0;$i<$loops;$i++) {
				
						/*
						* Print the field input, select, radio, checkbox, etc 
						*/
						if(isset($data['values'])) {
						
							/*$strSize = isset($data['size'])?' size="'.$data['size'].'"':'';
					
							printf(
								'<select name="%s[%s]%s" id="%s%s"'.$strDisabled.$strSize.'>'
								,$name
								,$field
								,(isset($data['multi'])?'[]':'')
								,$idbase
								,strtolower(str_replace('_','-',$field))
							);*/
						
							/*
							* This format can be used as a callback for recursive select menus 
							*/
							/*$func = create_function(
								'$func,$data,$template,$values,$field,$runner=0'					
								,'foreach($data[\'values\'] as $option_value) {
									printf(
										\'<option class="depth-%u" value="%s"%s>%s</option>\'
										,$runner
										,$option_value[\'value\']
										,$values[$field] == $option_value[\'value\']?\' selected="selected"\':\'\'
										,$template->out($option_value[\'label\'])
									);						
									if(isset($option_value[\'values\']) && !empty($option_value[\'values\'])) {
										call_user_func($func,$func,$option_value,$template,$values,$field,($runner+1));
									}				
								}');*/
						
							/*
							* Build select menu 
							*/
							//call_user_func($func,$func,$data,$this,$values,$field);
					
							//print('</select>');
							
							echo $this->ui('Common.Form.Select',array(
								'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?'[]':''))
								,'id'=>$idbase.strtolower(str_replace('_','-',$field))
								,'data'=>$data
								,'value'=>$values[$field]
								,'size'=>isset($data['size'])?$data['size']:null
								,'disabled'=>$strDisabled?true:false
							));
							
						
						} else if(isset($data['textarea'])) {
						
							/*printf(
							'<textarea name="%s[%s]%s" id="%s%s"'.$strDisabled.'>%s</textarea>'
								,$name
								,$field
								,(isset($data['multi'])?'[]':'')
								,$idbase
								,strtolower(str_replace('_','-',$field))
								,isset($data['multi'])?isset($values[$field][$i])?$values[$field][$i]:'':$values[$field]
							);*/	

							echo $this->ui('Common.Form.TextArea',array(
								'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?'[]':''))
								,'id'=>$idbase.strtolower(str_replace('_','-',$field))
								,'disabled'=>$strDisabled?true:false
								,'value'=>isset($data['multi'])?isset($values[$field][$i])?$values[$field][$i]:'':$values[$field]								
							));
						
						} else {
					
							switch(isset($data['type'])?$data['type']:'') {
								case 'bool':
									$input_type = 'checkbox';
									break;
							
								default:
									$input_type = 'text';
							}
							
							/*
							* Override for file input 
							*/
							if(isset($data['image'])) {
								$input_type = 'file';
								
								// show image preview
								/*if(!is_array($values[$field]) && is_numeric($values[$field])) {
									printf(
										'<img src="%s/%u">'
										,'http://local.mcp4/img.php'
										,$values[$field]
									);
								}*/
								
							}
							
							$val = isset($data['multi'])?isset($values[$field][$i])?$values[$field][$i]:'':$values[$field];
					
							printf(
								'<input type="%s" name="%s[%s]%s" value="%s"%sid="%s%s"%s'.$strDisabled.$this->close(false)
								,$input_type
								,$name
								,$field
								,(isset($data['multi'])?'[]':'')
								,strcmp($input_type,'checkbox') == 0?'1':$val
								,isset($data['max'])?'maxlength="'.$data['max'].'"':' '
								,$idbase
								,strtolower(str_replace('_','-',$field))
								,strcmp($input_type,'checkbox') == 0 && $val?' checked="checked"':''
							);
					
						}
						
					}
				
					/*
					* Print field errors 
					*/
					if(isset($errors[$field])) printf('<p>%s</p>',$this->out($errors[$field]));
				
					print('</li>');
				
				} 
			
				/*
				* Submit button 
				*/
				printf('<li class="save"><input type="submit" name="%s[save]" value="%s" id="%s%s"></li>',$name,$submit,$idbase,'save');
				
				/*
				* Clear button 
				*/
				if(strlen($clear) !== 0) {
					printf('<li class="save"><input type="submit" name="%s[clear]" value="%s" id="%s%s"></li>',$name,$clear,$idbase,'save');
				}
				
				print('</ul>');
			} else {
				print('<p>No config available for site</p>');
			}
		?>	
		</fieldset>
		</form><?php	
	}
	
	
	
	
}
?>