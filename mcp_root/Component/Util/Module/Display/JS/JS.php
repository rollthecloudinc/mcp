<?php 
/*
* Imports JS files 
*/
class MCPUtilDisplayJS extends MCPModule {
	
	public function execute($arrArgs) {
            
                $scripts = array();
            
                /*
                * get all JS files assigned this request 
                */
                $js = $this->_objMCP->getJs();
                
                /*
                * Add magical aggregation file 
                */
                $scripts[] = array('src'=>'/asset.php/js');
                
                /*
                * Find all files that we are not able to aggregate
                * together and serve in the sinle asset request.  
                */
                foreach($js as $file) {
                    if(isset($file['bundle']) && !$file['bundle']) {
                        $scripts[] = array(
                             'src'=>$file['path']
                            ,'inline'=>isset($file['inline'])?$file['inline']:''
                        );
                    }
                }
                
                /*
                * Assign to template 
                */
                $this->_arrTemplateData['scripts'] = $scripts;
		
		/*
		* Sites JavaScript that will placed in head of master template 
		*/
		return 'JS/JS.php';
		
	}
	
}
?>