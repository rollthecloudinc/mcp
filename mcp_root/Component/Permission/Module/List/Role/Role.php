<?php
/*
* List roles
*/
class MCPPermissionListRole extends MCPModule {
	
	protected
	
	/*
	* Permission data access layer 
	*/
	$_objDAOPermission
	
	/*
	* Current page 
	*/
	,$_intPage = 1
	
	/*
	* Internal nested redirect
	*/
	,$_strRedirect = false;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		
		// Get Permission data access layer
		$this->_objDAOPermission = $this->_objMCP->getInstance('Component.Permission.DAO.DAOPermission',array($this->_objMCP));
		
	}
	
	/*
	* Pagination callback 
	* 
	* @param int offsset
	* @param int limit
	*/
	public function paginate($intOffset,$intLimit) {
		
		// fetch roles
		$arrResult = $this->_objDAOPermission->listRoles('r.*',$this->_getFilter(),$this->_getSort(),"{$this->_objMCP->escapeString($intOffset)},{$this->_objMCP->escapeString($intLimit)}");
		
		// Assign roles to template var
		$this->_arrTemplateData['roles'] = array_shift($arrResult);
		
		// Determine whether user is allowed to configure (edit) and delete roles
		$ids = array();
		foreach($this->_arrTemplateData['roles'] as $role) {
			$ids[] = $role['roles_id'];
		}
		if(!empty($ids)) {
			$perms = $this->_objMCP->getPermission(MCP::EDIT,'Role',$ids);
			$deletePerms = $this->_objMCP->getPermission(MCP::DELETE,'Role',$ids);
		}
		foreach($this->_arrTemplateData['roles'] as &$role) {
			$role['allow_edit'] = $perms[$role['roles_id']]['allow'];
			$role['allow_delete'] = $deletePerms[$role['roles_id']]['allow'];
		}
		
		// return number of found rows
		return array_shift($arrResult);
		
	}
	
	/*
	* Get SQL where clause
	* 
	* @return str SQL where clause
	*/
	protected function _getFilter() {
		
		/*
		* Get roles for site that have not been deleted 
		*/
		return "r.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())} AND r.deleted = 0";
		
	}
	
	/*
	* Get SQL order by clause
	* 
	* @return str SQL order by clause
	*/
	protected function _getSort() {
		
		/*
		* No sorting at this time 
		*/
		return null;
		
	}
	
	/*
	* Get the headers to dynamically builder the table to list the roles
	* 
	* @return array table UI configuration
	*/
	protected function _getHeaders() {
		
		$mcp = $this->_objMCP;
		$mod = $this;
		
		return array(
			array(
				'label'=>'Name'
				,'column'=>'human_name'
				,'mutation'=>null
			)
			,array(
				'label'=>'Group'
				,'column'=>'pkg'
				,'mutation'=>function($value,$row) {
					return empty($value)?'--':$value;
				}
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'roles_id'
				,'mutation'=>function($value,$row) use ($mcp,$mod) {
					
					if(!$row['allow_edit']) {
						return 'Configure';
					}
					
					return $mcp->ui('Common.Field.Link',array(
						'label'=>'Configure'
						,'url'=>"{$mod->getBasePath(false)}/Configure/{$value}"
					));
					
				}
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'roles_id'
				,'mutation'=>function($value,$row) use ($mcp) {
					return $mcp->ui('Common.Form.Submit',array(
						'label'=>'Delete'
						,'name'=>"frmRoleList[action][delete][$value]"
						,'disabled'=>!$row['allow_delete']
					));
				}
			)
		);
		
	}
	
	public function execute($arrArgs) {
            
                // Number of roles per page
		$intLimit = $this->getConfigValue('roles_per_page');
		
		// get the current page
		$this->_intPage = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):1;
		
		// Roles redirection to create and edit roles as nested module
		$this->_strRedirect = !empty($arrArgs) && in_array($arrArgs[0],array('Configure','Create'))?array_shift($arrArgs):null;
		
		// Paginate module
		$this->_arrTemplateData['PAGINATION_TPL'] = $this->_objMCP->executeComponent('Component.Util.Module.Pagination',array($intLimit,$this->_intPage),'Component.Util.Template',array($this));
		
		// Set configuration to build table display dynamically using ui element
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		// Determine whether use is allowed to create role
		$perm = $this->_objMCP->getPermission(MCP::ADD,'Role');
		$this->_arrTemplateData['allow_create_role'] = $perm['allow'];
		
		// Create new role URL
		$this->_arrTemplateData['create_link'] = "{$this->getBasePath(false)}/Create";
		
		// Form action,name and method
		$this->_arrTemplateData['frm_action'] = $this->getBasePath();
		$this->_arrTemplateData['frm_name'] = 'frmVocabularyList';
		$this->_arrTemplateData['frm_method'] = 'POST';
		
		return 'Role/Role.php';
	}
	
	/*
	* Get path to module state
	* 
	* @param bool add redirect variable
	* @return str base path
	*/
	public function getBasePath($redirect=true) {
		
		$strBasePath = parent::getBasePath();
		
		// add the page number
		 $strBasePath.= "/{$this->_intPage}";
		 
		 if($redirect === true && $this->_strRedirect) {
		 	$strBasePath.= "/{$this->_strRedirect}";
		 }
		 
		 return $strBasePath;
		
	}
	
}
?>