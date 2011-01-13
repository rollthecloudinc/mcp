<?php
$this->import('App.Core.DAO');
$this->import('App.Core.VDItem');

/*
* Node virtual display item
*/
class MCPVDItemNode extends MCPDAO implements VDItem {

	public function getTable() {
		return 'MCP_NODES';
	}
	
	public function getFieldEntityType() {
		return 'MCP_NODE_TYPES';
	}
	
	public function getFieldEntityId() {
		return 'node_types_id';
	}

	public function getFields() {
	
		return array(
			'id'=>array(
				'column'=>'nodes_id'
				,'binding'=>null
			)
			,'site'=>array(
				'column'=>'sites_id'
				,'binding'=>'Site'
			)
			,'author'=>array(
				'column'=>'authors_id'
				,'binding'=>'User'
			)
			,'classification'=>array(
				'column'=>'node_types_id'
				,'binding'=>'NodeType'
			)
			,'published'=>array(
				'column'=>'node_published'
				,'binding'=>null
			)
			,'title'=>array(
				'column'=>'node_title'
				,'binding'=>null
			)
			,'subtitle'=>array(
				'column'=>'subtitle'
				,'binding'=>null
			)
			,'teaser'=>array(
				'column'=>'intro_content'
				,'binding'=>null
			)
			,'body'=>array(
				'column'=>'node_content'
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
		);
	
	}

} 
?>