<?php

class MCPPermissionFormPerm extends MCPModule {
    
    private
    
    // either User or Role
    $_strPerm,
            
    // User ID or Role ID
    $_intId,
    
    // Root type such as; Content, Vocabulary and Menu
    $_strContext,  
            
    // The subtype such as; project, section or primary
    $_strType,
            
    // Items page
    $_boolItems,
            
    // Page number when viewing individual items
    $_intPage,
       
    // permission data access object
            
    $_objDAOPerm,
            
    // standard form stuff
            
    $_objValidator,
            
    $_arrFrmPost,
            
    $_arrFrmValues,
    $_arrFrmErrors,
            
    // cached form config
    $_arrCachedFrmConfig;
    
    public function __construct(MCP $objMCP,MCPModule $objParentModule,$arrConfig=null) {
        parent::__construct($objMCP,$objParentModule,$arrConfig);
        $this->_init();
    }
    
    protected function _init() {
        
        // Permission data access object
        $this->_objDAOPerm = $this->_objMCP->getInstance('Component.Permission.DAO.DAOPermission',array($this->_objMCP));
        
        // Get validation object
        $this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
        
        // Get form post data
        $this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
        
        // Reset values and errors
        $this->_arrFrmValues = array();
        $this->_arrFrmErrors = array();
        
    }
    
    /*
    * Process frm values 
    */
    protected function _process() {
        
        $this->_setFrmValues();
        
        /*
	* Validate form values 
	*/
	if($this->_arrFrmPost !== null) {
            $this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
	}
        
        /*
        * Save form data 
        */
        if($this->_arrFrmPost !== null && empty($this->_arrFrmErrors)) {
            $this->_frmSave();
        }
        
    }
    
    /*
    * Set form values 
    */
    protected function _setFrmValues() {
        
        if($this->_boolItems === true) {
            return;
        }
        
        if($this->_arrFrmPost !== null) {
            $this->_setFrmSave();
        } else {
            $this->_setFrmEdit();
        }
    }
    
    /*
    * Set values from post array 
    */
    protected function _setFrmSave() { 
        
        foreach($this->_getFrmFields() as $strField) {
            switch($strField) {
                default:
                    $this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:'';
            }
        }
        
    }
    
    /*
    * Set values from current permission.
    */
    protected function _setFrmEdit() {
        
        switch($this->_strPerm) {
            case 'User':
                $arrPerm = array_pop($this->_objDAOPerm->fetchUserPermissions($this->_intId,$this->_getItemType(),array($this->_strType !== null?$this->_strType:0)));
                break;
            
            case 'Role':
                $arrPerm = array_pop($this->_objDAOPerm->fetchRolePermissions($this->_intId,$this->_getItemType(),array($this->_strType !== null?$this->_strType:0)));
                break;
            
            default;
                throw new Exception('Only able to manage user and role permissions at this time.');
        }
        
        foreach($this->_getFrmFields() as $strField) {
            switch($strField) {
                default:
                    $this->_arrFrmValues[$strField] = strlen($arrPerm[$strField]) === 0?'':((int) $arrPerm[$strField]);
            }
        }
        
    }
    
    /*
    * Get form name.
    *
    * @return str name   
    */
    protected function _getFrmName() {
        return 'frmPerms';
    }
    
    /*
    * Get form fields 
    * 
    * @return array 
    */
    protected function _getFrmFields() {
        return array_keys($this->_getFrmConfig());
    }
    
    /*
    * Determine whether form implements given permission
    * field. Not all permission tiers share use the same
    * fields.   
    * 
    * @param str field
    * return bool 
    */
    protected function _implementsField($strField) {
        
        switch($this->_getTier()) {
            case 3:
                return in_array($strField,array('read','read_own','edit','edit_own','delete','delete_own'));
                
            case 2:
                return in_array($strField,array('read','read_own','edit','edit_own','delete','delete_own','add','read_child','read_own_child','edit_child','edit_own_child','delete_child','delete_own_child'));
                
            default:
                return in_array($strField,array('add','edit','edit_own','read','read_own','delete','delete_own'));
        }
        
    }
    
    /*
    * Get form configuration 
    * 
    * @return array form configuration 
    */
    protected function _getFrmConfig() {  
        
        if($this->_arrCachedFrmConfig !== null) {
           return $this->_arrCachedFrmConfig; 
        }
        
        
        $arrConfig = $this->_objMCP->getFrmConfig('Component.Permission.Module.Form.Perm','frm',true);     
        
        /*
        * Each tier implements a different set of permissions. Turn off
        * permissions which the tier does not implement.
        */
        foreach(array_keys($arrConfig) as $strField) {
            if(!$this->_implementsField($strField)) {
                unset($arrConfig[$strField]);
            }
        }
        
        $this->_arrCachedFrmConfig = $arrConfig;
        return $this->_arrCachedFrmConfig;
        
    }
    
    /*
    * Get form legend
    * 
    * @return str legend title  
    */
    protected function _getLegend() {
        
        switch($this->_getTier()) {
            case 3:
                switch($this->_getItemType()) {
                    case 'MCP_MENU_LINKS':
                        $arrType = $this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP))->fetchMenuById((int) $this->_strType);
                        return 'Menu > '.$arrType['human_name'].' > Links';
                        
                    case 'MCP_NODES':
                        $arrType = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP))->fetchNodeTypeById((int) $this->_strType);
                        return 'Content > '. $arrType['human_name'].' > Nodes';
                        
                    case 'MCP_TERMS':
                        $arrType = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP))->fetchVocabularyById((int) $this->_strType);
                        return 'Vocabulary > '.$arrType['human_name'].' > Terms';
                    
                    default:
                        
                }
                
            case 2:
                
                switch($this->_getItemType()) {
                    case 'MCP_MENUS':
                        $arrType = $this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP))->fetchMenuById((int) $this->_strType);
                        return 'Menu > '.$arrType['human_name'];
                        
                    case 'MCP_NODE_TYPES':
                        $arrType = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP))->fetchNodeTypeById((int) $this->_strType);
                        return 'Content > '. $arrType['human_name'];
                        
                    case 'MCP_VOCABULARY':
                        $arrType = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP))->fetchVocabularyById((int) $this->_strType);
                        return 'Vocabulary > '.$arrType['human_name'];
                    
                    default:
                        
                }
                
            default:
                
                switch($this->_strContext) {
                
                    case 'Menu':
                        return 'Menu'; 
                
                    case 'Vocabulary':
                        return 'Vocabulary'; 
               
                    case 'Content':
                        return 'Content';
                
                    default:
                        return 'Globals';
                }
        }
        
    }
    
    /*
    * Get tier
    * 
    * @return int tier  
    */
    protected function _getTier() {
        
        if($this->_boolItems === true) {
            
            return 3;
            
        } else if($this->_strType !== null) {
            
            return 2;
            
        } else {
            
            return 1;
            
        }
        
    }
    
    /*
    * This only applies to tier three which permissions van be assigned
    * directly to nodes, terms and links.
    * 
    * @param int SQL offset
    * @param int limit number of rows per page
    * @return int number of found rows  
    */
    public function paginate($intOffset,$intLimit) {
        
        if($this->_getTier() !== 3) {
            return array();
        }
        
        switch($this->_strContext) {
            case 'Vocabulary':
                $objDAO = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
                $arrData = array();
                $strTitle = 'system_name';
                $strId = 'vocabulary_id';
                $strItemType = 'MCP_TERMS';
                break;
            
            case 'Content':
                $objDAO = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
                $arrData = $objDAO->listAll('n.*',"n.deleted = 0 AND n.node_types_id = {$this->_strType}",null,"$intOffset,$intLimit");
                $strTitle = 'node_title';
                $strId = 'nodes_id';
                $strItemType = 'MCP_NODES';
                break;
                
            case 'Menu':
                $objDAO = $this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP));
                $arrData = array();
                $strTitle = 'system_name';
                $strId = 'menu_links_id';
                $strItemType = 'MCP_MENU_LINKS';
                break;
            
            default:
                throw new Exception('Permissions not implemented by context');
        }
        
        $arrIds = array();             
        foreach($arrData[0] as &$arrItem) {
            $arrIds[] = (int) $arrItem[$strId];
        }
        
        switch($this->_strPerm) {
            case 'User':
                $arrPerms = $this->_objDAOPerm->fetchUserPermissions($this->_intId,$strItemType,$arrIds);
                break;
            
            case 'Role':
                $arrPerms = $this->_objDAOPerm->fetchRolePermissions($this->_intId,$strItemType,$arrIds);
                break;
            
            default:
                throw new Exception('Unable to resolve MCPDAOPermission permission fetch method.');
        }
        
        $arrItems = array();
        foreach($arrData[0] as $arrItem) {
            $arrItem = array(
                 'id'=>(int) $arrItem[$strId]
                ,'title'=>$arrItem[$strTitle]
            );
            $arrItem = array_merge($arrItem,$arrPerms[$arrItem['id']]);
            $arrItems[] = $arrItem;
        }
        
        $this->_arrTemplateData['items'] = $arrItems;
        return $arrData[1];
        
    }
    
    /*
    * Save permission data 
    */
    protected function _frmSave() {
        
        $arrValues = array();
        
        if($this->_boolItems === true) {
           
           foreach($this->_arrFrmPost as $intId=>$arrValue) {
               if(is_array($arrValue) && !empty($intId)) {
                    $arrVal = array_slice($arrValue,0);
                    $arrVal['item_type'] = $this->_getItemType();
                    $arrVal['item_id'] = $intId;
                    $arrValues[] = $arrVal;
               } else {
                   continue;
               }
           }
           
        } else {
            $arrVal = $this->_arrFrmValues; 
           
            // Mixin item type and item ID
            $arrVal['item_type'] = $this->_getItemType();
            $arrVal['item_id'] = $this->_getTier() === 1?0:$this->_strType;
            
            $arrValues = array($arrVal);
        }
        
        // $this->debug($arrValues);
        
        try {
        
            switch($this->_strPerm) {
                case 'User':
                    $this->_objDAOPerm->saveUserPermissions($this->_intId,$arrValues);
                    break;

                case 'Role':
                    $this->_objDAOPerm->saveRolePermissions($this->_intId,$arrValues);
                    break;

                default:
                    throw new Exception('Unable to save permissions.');
            }
            
            $this->_objMCP->addSystemStatusMessage('Permissions sucessfully updated.');
            
            if($this->_getTier() < 3) {
                $this->_setFrmEdit();
            }
        
        } catch(MCPDAOException $e) {
            
            $this->_objMCP->addSystemErrorMessage('Unable to save permissions.');
            
        } catch(MCPDBException $e) {
            
            $this->_objMCP->addSystemErrorMessage('Unable to save permissions.');
            
        }
        
    }
    
    /*
    * Get template to use.
    * 
    * @return str template name  
    */
    protected function _getTplName() {
        return 'Tier'.$this->_getTier();
    }
    
    /*
    * Utility method to get table headers for tier3 display showing
    * individual item permissions.  
    *
    * @return array table headers  
    */
    protected function _getTier3TblHeaders() {
        
        $mcp = $this->_objMCP;
        $name = $this->_getFrmName().'[%u][%s]';
        $id = 'item-%u-%s';
        
        $funcToCheckbox = function($value,$row,$header) use ($mcp,$name,$id) {
            return $mcp->ui('Common.Form.Select',array(
                'data'=>array(
                    'values'=>array(
                        array('value'=>'','label'=>'-'),
                        array('value'=>'1','label'=>'Y'),
                        array('value'=>'0','label'=>'N')
                    )
                ),
                'name'=>sprintf($name,$row['id'],$header['column']),
                'id'=>sprintf($id,$row['id'],str_replace('_','-',$header['column'])),
                'value'=>strlen($value) === 0?'':$value
            ));
        };
        
        return array(
            array(
                'label'=>'ID',
                'column'=>'id',
                'mutation'=>null
            ),
            array(
                'label'=>'Title',
                'column'=>'title',
                'mutation'=>null
            ),
            array(
                'label'=>'Read',
                'column'=>'read',
                'mutation'=>$funcToCheckbox
            ),
            array(
                'label'=>'Read Own',
                'column'=>'read_own',
                'mutation'=>$funcToCheckbox
            ),
            array(
                'label'=>'Edit',
                'column'=>'edit',
                'mutation'=>$funcToCheckbox
            ),
            array(
                'label'=>'Edit Own',
                'column'=>'edit_own',
                'mutation'=>$funcToCheckbox
            ),
            array(
                'label'=>'Delete',
                'column'=>'delete',
                'mutation'=>$funcToCheckbox
            ),
            array(
                'label'=>'Delete Own',
                'column'=>'delete_own',
                'mutation'=>$funcToCheckbox
            )
        );
        
    }
    
    /*
    * Get item Type
    *
    * @return str item type   
    */
    protected function _getItemType() {
        
        $strType = null;
        
        switch($this->_strContext) {
            
            case 'Vocabulary':
                $strType = 'MCP_VOCABULARY';
                if($this->_boolItems === true) {
                    $strType = 'MCP_TERMS';
                }
                break;
            
            case 'Content':
                $strType = 'MCP_NODE_TYPES';
                if($this->_boolItems === true) {
                    $strType = 'MCP_NODES';
                }
                break;
            
            case 'Menu':
                $strType = 'MCP_MENUS';
                if($this->_boolItems === true) {
                    $strType = 'MCP_MENU_LINKS';
                }
                break;
                
            default:        
            
        }
        
        return $strType;
        
    }
    
    public function execute($arrArgs) {
        
        $this->_strPerm = !empty($arrArgs) && in_array($arrArgs[0],array('User','Role'))?array_shift($arrArgs):null;
        $this->_intId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;    
        
        $this->_strContext = !empty($arrArgs)?array_shift($arrArgs):null;        
        $this->_strType = !empty($arrArgs)?array_shift($arrArgs):null;
        
        $this->_boolItems = !empty($arrArgs)?true:false;
        $this->_intPage = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):1;
        
        
        // process form
        $this->_process();
        

        $this->_arrTemplateData['entity'] = array(
                'plural'=>'Vocabularies',
                'singular'=>'Vocabulary',
                'item_type'=>'MCP_VOCABULARY',
                'child'=>array(
                    'plural'=>'Terms',
                    'singular'=>'Term',
                    'item_type'=>'MCP_TERMS'
                )
        );
        
        $this->_arrTemplateData['item'] = array(
            'singular'=>'Section',
            'plural'=>'Sections',
            'id'=>67
        );
        
        if($this->_boolItems === true) {
            $this->_arrTemplateData['PAGINATION_TPL'] = $this->_objMCP->executeComponent('Component.Util.Module.Pagination',array(20,$this->_intPage),'Component.Util.Template',array($this));       
        }
        
        $this->_arrTemplateData['action'] = $this->getBasePath();
        $this->_arrTemplateData['method'] = 'post';
        $this->_arrTemplateData['name'] = $this->_getFrmName();
        $this->_arrTemplateData['legend'] = $this->_getLegend();
        $this->_arrTemplateData['config'] = $this->_getFrmConfig();
        $this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
        $this->_arrTemplateData['values'] = $this->_arrFrmValues;
        $this->_arrTemplateData['layout'] = $this->getTemplatePath().DS.'Tier'.$this->_getTier().'Layout.php';
        $this->_arrTemplateData['headers'] = $this->_getTier3TblHeaders();
        
        return 'Perm/'.$this->_getTplName().'.php';
        
        
    }
    
    public function getBasePath($boolPage=true) {
        
        $strBasePath = parent::getBasePath();
        
        // Add permission type and id
        $strBasePath.= '/'.$this->_strPerm.'/'.$this->_intId;
        
        switch($this->_getTier()) {
            case 3:
                $strBasePath.= '/'.$this->_strContext.'/'.$this->_strType.'/Items';
                
                if($boolPage === true) {
                    $strBasePath.= '/'.$this->_intPage;
                }
                
                return $strBasePath;
                
            case 2:
                return $strBasePath.'/'.$this->_strContext.'/'.$this->_strType;
                
            default:
                return $strBasePath.'/'.$this->_strContext;
        }
        
    }
    
}
