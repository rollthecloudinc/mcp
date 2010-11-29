<?php
class MCPUserLogin extends MCPModule {

	private static
	
	/*
	* Number of times class has been created
	*/
	$_intInstances = 0;
	
	private
	
	/*
	* Unique instance number for object
	*/
	$_intInstance
	
	/*
	* Message to display above form
	*/
	,$_strMessage;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_intInstance = ++self::$_intInstances;
		$this->_init();
	}
	
	private function _init() {
		
		$arrPost = $this->_objMCP->getPost();
		$arrFrmData = isset($arrPost["frmUtilLogin_{$this->_intInstance}"])?$arrPost["frmUtilLogin_{$this->_intInstance}"]:null;
		
		$this->_arrTemplateData['frm_values'] = array();
		foreach($this->_getFrmConfig() as $strField=>$arrConfig) {
			$this->_arrTemplateData['frm_values'][$strField] = $arrFrmData === null || !isset($arrFrmData[$strField])?'':$arrFrmData[$strField]; 
		}
		
		if($arrFrmData === null) return;
		
		if(empty($this->_arrTemplateData['frm_values']['username']) && empty($this->_arrTemplateData['frm_values']['password'])) {
			$this->_strMessage = 'Username and Password required';
			return;
		} else if(empty($this->_arrTemplateData['frm_values']['username'])) {
			$this->_strMessage = 'Username required';
			return;
		} else if(empty($this->_arrTemplateData['frm_values']['password'])) {
			$this->_strMessage = 'Password required.';
			return;
		}
		
		/*
		* Attempt user login
		*/
		if($this->_objMCP->loginUser($this->_arrTemplateData['frm_values']['username'],$this->_arrTemplateData['frm_values']['password'])) {
			$this->_strMessage = 'logged in!';
		} else {
			$this->_strMessage = 'Login failed';
		}
		
		
	}
	
	public function redo() {
		$this->_init();
	}

	public function execute($arrArgs) {
	
		$this->_arrTemplateData['frm_name'] = "frmUtilLogin_{$this->_intInstance}";
		$this->_arrTemplateData['frm_action'] = $this->_objMCP->getBasePath();
		$this->_arrTemplateData['frm_method'] = 'post';
		$this->_arrTemplateData['frm_config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['frm_errors'] = array();
		$this->_arrTemplateData['instance_num'] = $this->_intInstance;
		$this->_arrTemplateData['base_url'] = $this->_objMCP->getBaseUrl();
		
		return 'Login/Login.php';
	}
	
	/*
	* Called form within template to print messages above form
	*/
	public function paintHeaderMessages() {
		if($this->_strMessage) {
			echo "<p>{$this->_strMessage}</p>";
		}
	}
	
	/*
	* Get login forms configuration
	*
	* @return array login form configuration settings
	*/
	private function _getFrmConfig() {
		return array(
			'username'=>array(
				'label'=>'Username'
				,'required'=>'Y'
				,'max'=>'25'
				,'min'=>'1'
			)
			,'password'=>array(
				'label'=>'Password'
				,'required'=>'Y'
				,'max'=>'10'
				,'min'=>'1'
			)
			,'remember'=>array(
				'label'=>'Remember Me'
				,'required'=>'N'
			)
		);
	}
	
}
?>