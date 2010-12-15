<?php
$this->import('App.Core.DAO');
$this->import('App.Core.VDItem');

/*
* User virtual display item
*/ 
class MCPVDItemUser extends MCPDAO implements VDItem {

	public function getTable() {
		return 'MCP_USERS';
	}
	
	public function getFieldEntityType() {
		return 'MCP_SITES';
	}
	
	public function getFieldEntityId() {
		return 'sites_id';
	}

	public function getFields() {
	
		return array(
			'id'=>array(
				'column'=>'users_id'
				,'binding'=>null
			)
			,'site'=>array(
				'column'=>'sites_id'
				,'binding'=>'Site'
			)
			,'username'=>array(
				'column'=>'username'
				,'binding'=>null
			)
			,'email'=>array(
				'column'=>'email_address'
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
			,'login'=>array(
				'column'=>'last_login_timestamp'
				,'binding'=>null
			)
			,'banned'=>array(
				'column'=>'banned_until_timestamp'
				,'binding'=>null
			)
		);
	
	}

}
?>