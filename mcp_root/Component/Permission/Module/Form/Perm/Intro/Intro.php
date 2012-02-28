<?php

class MCPPermissionFormPermIntro extends MCPModule {
    
    public function __construct(MCP $objMCP,MCPPermissionFormRole $objParentModule,$arrConfig=null) {
        parent::__construct($objMCP,$objParentModule,$arrConfig);
        
        if($objParentModule->getRole() === null) {
            throw new Exception('Module requires parent module with role.');
        }   
    }
    
    protected function _getData() {
        
        $arrPlugins = $this->_objMCP->getPermissionPlugins();
        $arrData = array();
        
        foreach($arrPlugins as $arrPlugin) {
            switch($arrPlugin['entity']) {
                
                case 'NodeType':
                    $arrData[] = array(
                        'title'=>'Content',
                        'context'=>'Content',
                        'items'=>array_shift(
                        $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP))
                        ->fetchNodeTypes('t.human_name label,t.node_types_id id',array('t.sites_id = ? and t.deleted = 0',$this->_objMCP->getSitesId()),null,'6')
                    ));
                    break;
                
                case 'Vocabulary':
                    $arrData[] = array(
                       'title'=>'Vocabularies',
                        'context'=>'Vocabulary',
                        'items'=>array_shift($this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP))
                        ->listVocabulary('v.human_name label,v.vocabulary_id id',"v.sites_id = {$this->_objMCP->getSitesId()} AND v.deleted = 0",null,'6')
                    ));
                    break;
                
                case 'Menu':
                    $arrData[] = array(
                        'title'=>'Menus',
                        'context'=>'Menu',
                        'items'=>array_shift($this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP))
                        ->listAllMenus('m.menus_id id,m.menu_title label',"m.sites_id = {$this->_objMCP->getSitesId()} AND m.deleted = 0",null,'6')
                    ));
                    break;
                
                case 'Site':
                    $arrData[] = array(
                        'title'=>'Sites',
                        'context'=>'Site',
                        'items'=>array_shift($this->_objMCP->getInstance('Component.Site.DAO.DAOSite',array($this->_objMCP))
                        ->listAll('s.site_name label,s.sites_id id','s.deleted = 0',null,'6')
                    ));
                    break;
                
                case 'Config':
                    $arrData[] = array(
                        'title'=>'Config',
                        'context'=>'Config',
                        'items'=>array_map(function($d) { return array('label'=>$d,'id'=>$d); },array_slice(array_keys($this->_objMCP->getEntireConfig()),0,6))
                    );
                    break;
                
                case 'Role':
                    $arrData[] = array(
                        'title'=>'Roles',
                        'context'=>'Role',
                        'items'=>array_shift($this->_objMCP->getInstance('Component.Permission.DAO.DAOPermission',array($this->_objMCP))
                        ->listRoles('r.human_name label,r.roles_id id',"r.sites_id = {$this->_objMCP->getSitesId()} AND r.deleted = 0",null,'6')
                    ));
                    break;
                
                case 'User':
                    $arrData[] = array(
                        'title'=>'Users',
                        'context'=>'User',
                        'items'=>array_shift($this->_objMCP->getInstance('Component.User.DAO.DAOUser',array($this->_objMCP))
                        ->listAll('username label,users_id id',"sites_id = {$this->_objMCP->getSitesId()} and deleted = 0",null,'6')
                    ));
                    break;
                
                default:
            }
        }
        
        return $arrData;
        
    }
    
    public function execute($arrArgs) {
        
        // Parent defines role.
        if(!empty($arrArgs) && strcasecmp('Role',$arrArgs[0]) === 0) {
            $arrArgs = array_slice($arrArgs,2);
        }
        
        $strTpl = 'Intro';
        $arrRole = $this->_objParentModule->getRole();
        
        if(!empty($arrArgs)) {
            
            $this->_objMCP->addBreadcrumb(array('url'=>$this->getBasePath(),'label'=>$arrRole['human_name']));
            
            $this->_arrTemplateData['TPL_REDIRECT'] = $this->_objMCP->executeComponent(
                'Component.Permission.Module.Form.Perm'
		,array_merge(array('Role',$arrRole['roles_id']),$arrArgs)
		,null
		,array($this)
            );
            $strTpl = 'Redirect';
            
        } else {
        
            $this->_arrTemplateData['plugins'] = $this->_getData();

            $this->_arrTemplateData['t1_base_url'] = $this->getBasePath().'/%s';
            $this->_arrTemplateData['t2_base_url'] =  $this->getBasePath().'/%s/%s';
        
        }
        
        return "Intro/$strTpl.php";
    }
    
}
