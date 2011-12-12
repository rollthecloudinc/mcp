<?php 
/*
* Displays meta data 
*/
class MCPUtilDisplayMeta extends MCPModule {
	
	public function execute($arrArgs) {
            
                /*
                * Get defined meta data 
                */
                $meta = $this->_objMCP->getMetaData();
            
                /*
                * Load in some defaults. 
                */
                $meta['content-type'] = array(
                    'value'=>'text/html; charset=utf-8'
                    ,'attr'=>array()
                );
            
            
                /*
                * Load template with meta data. 
                */
                $this->_arrTemplateData['meta'] = $meta;
		
		/*
		* Sites Meta data that will placed in head of master template 
		*/
		return 'Meta/Meta.php';
		
	}
	
}
?>