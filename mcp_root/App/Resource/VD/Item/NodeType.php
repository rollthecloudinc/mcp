<?php
$this->import('App.Core.DAO');
$this->import('App.Core.VDItem');

/*
* Node Type virtual display item
*/ 
 class MCPVDItemNodeType extends MCPDAO implements VDItem {

	public function getTable() {
		return 'MCP_NODE_TYPES';
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
				'column'=>'node_types_id'
				,'binding'=>null
			)
			,'site'=>array(
				'column'=>'sites_id'
				,'binding'=>'Site'
			)
			,'creator'=>array(
				'column'=>'creators_id'
				,'binding'=>'User'
			)
			,'pkg'=>array(
				'column'=>'pkg'
				,'binding'=>null
			)
			,'system_name'=>array(
				'column'=>'system_name'
				,'binding'=>null
			)
			,'human name'=>array(
				'column'=>'human_name'
				,'binding'=>null
			)
			,'description'=>array(
				'column'=>'description'
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
			,'nodes'=>array(
				'column'=>'node_types_id'
				,'binding'=>'Node'
			)
		);
	
	}

} 
?>