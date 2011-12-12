<?php 
/*
* Route data access layer 
*/
class MCPDAORoute extends MCPDAO {
	
	/*
	* Get menu link by route
	* 
	* @param str module name
	* @param int sites id
	* @return array route data 
	*/
	public function fetchRoute($strPath,$intSitesId) {
		
		$strSQL =
                   'SELECT 
                         l.menu_links_id
                      FROM 
                         MCP_MENU_LINKS l
                     INNER
                      JOIN
                         MCP_MENUS m
                        ON
                         l.menus_id = m.menus_id
                     WHERE 
                         l.path = :path 
                       AND 
                         m.sites_id = :sites_id 
                       AND 
                         l.deleted = 0';
		
		return array_pop(array_pop($this->_objMCP->query(
			$strSQL
			,array(
				 ':path'=>(string) $strPath
				,':sites_id'=>(int) $intSitesId
			)
		)));
	}
	
}
?>