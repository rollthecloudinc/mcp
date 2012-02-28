<?php
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

/*
* MCP_CONFIG 0 (all site configs)
* MCP_CONFIG 1 (full site config) - id represents the site - 0 represents all sites
* MCP_CONFIG:name_of_field - 1
* MCP_CONFIG:name_of_field - 0 
*/
class MCPPermissionConfig extends MCPDAO implements MCPPermission {
    
        /*
        * Configuration item type prefix identifier. This
        * will make it possible to identify all permissions
        * for config fields by merely looking for cfg-.
        *
        * Permissions for the master config will still use MCP_CONFIG
        * as the item type. However, individual fields will be in the form
        * of cfg-name_of_field    
        */
        const FIELD_PREFIX = 'cfg:';
        
	public function edit($ids,$intUserId=null) {
            
            // Get all config fields available
            $arrFields = $this->_getConfigFields(true);
            
            echo '<pre>',print_r($arrFields,true),'</pre>';
            
            exit;
		
        }
	
	public function read($ids,$intUserId=null) {
		
	}
        
        /*
        * It is possible to add configuation fields using fields
        * this is not something that is supported by the GUI. Though
        * is is completely supported entering the data manually into the db. 
        */
	public function add($ids,$intUserId=null) {
		
            // this will be handled by the field permission plugin - extending the config
            
	}
        
        /*
        * It will not be possible to delete configuaration fields at this time. In
        * Theory it could be supported in that one would be able to delete fields
        * that have been added to configuration as fields. Though for now I am going
        * to leave it be.  
        */
	public function delete($ids,$intUserId=null) {	
	}
        
        
        /*
        * Get a list of all available configuration fields for the current site. 
        * 
        * @param str optional prefix to supply to each field name
        * @return array fields 
        */
        protected function _getConfigFields($boolPrefix=false) {
            
            // Get all available fields
            $arrFields = array_keys($this->_objMCP->getConfigSchema());
            
            // When no prefix needs to be applied return data as is
            if($boolPrefix === false) {
                return $arrFields;
            }
            
            // Otherwise apply prefix
            $arrPrefixed = array();
            foreach($arrFields as $strField) {
                $arrPrefixed[] = self::FIELD_PREFIX.$strField;
            }
            
            return $arrPrefixed;
            
        }
        
	
}

?>
