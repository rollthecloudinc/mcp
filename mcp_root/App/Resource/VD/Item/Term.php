<?php 
$this->import('App.Core.DAO');
$this->import('App.Core.VDItem');

/*
* Term virtual display item
*/
class MCPVDItemTerm extends MCPDAO implements VDItem {

	public function getTable() {
		return 'MCP_TERMS';
	}
	
	public function getFieldEntityType() {
		return 'MCP_VOCABULARY';
	}
	
	public function getFieldEntityId() {
		return 'vocabulary_id';
	}

	public function getFields() {
	
		return array(
			'id'=>array(
				'column'=>'terms_id'
				,'binding'=>null
			)
			,'creator'=>array(
				'column'=>'creators_id'
				,'binding'=>'User'
			)
			,'system name'=>array(
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
			,'weight'=>array(
				'column'=>'weight'
				,'binding'=>null
			)
			,'created'=>array(
				'column'=>'created_on_timestamp'
				,'binding'=>null
			)
			,'updated'=>array(
				'column'=>'updated_on_timestamp'
				,'binding'=>null
			)
			,'vocabulary'=>array(
				'column'=>'parent_id'
				,'binding'=>'Vocabulary'
			)
			,'parent'=>array(
				'column'=>null
				,'binding'=>null
			)
		);
	
	}

}
?>