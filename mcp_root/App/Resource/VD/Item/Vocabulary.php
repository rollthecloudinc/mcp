<?php
$this->import('App.Core.DAO');
$this->import('App.Core.VDItem');

/*
* Vocabulary virtual display item
*/
 class MCPVDItemVocabulary extends MCPDAO implements VDItem {

	public function getTable() {
		return 'MCP_VOCABULARY';
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
				'column'=>'vocabulary_id'
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
			,'human name'=>array(
				'column'=>'human_name'
				,'binding'=>null
			)
			,'system name'=>array(
				'column'=>'system_name'
				,'binding'=>null
			)
			,'pkg'=>array(
				'column'=>'pkg'
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
		);
	
	}

} 
?>