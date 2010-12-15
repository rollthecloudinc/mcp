<?php
$this->import('App.Core.DAO');
$this->import('App.Core.VDItem');

/*
* Site virtual display item
*/ 
 class MCPVDItemSite extends MCPDAO implements VDItem {

	public function getTable() {
		return 'MCP_SITES';
	}
	
	public function getFieldEntityType() {
		return '';
	}
	
	public function getFieldEntityId() {
		return '';
	}

	public function getFields() {
	
		return array(
			'id'=>array(
				'column'=>'sites_id'
				,'binding'=>null
			)
			,'creator'=>array(
				'column'=>'creators_id'
				,'binding'=>'User'
			)
			,'name'=>array(
				'column'=>'site_name'
				,'binding'=>null
			)
			,'directory'=>array(
				'column'=>'site_directory'
				,'binding'=>null
			)
			,'prefix'=>array(
				'column'=>'site_prefix'
				,'binding'=>null
			)
			,'updated'=>array(
				'column'=>'updated_on_timestamp'
				,'binding'=>null
			)
			,'created'=>array(
				'column'=>'created_on_timestamp'
				,'binding'=>null
			)
			,'domain'=>array(
				'column'=>'domain'
				,'binding'=>null
			)
		);
	
	}
	

} 
?>