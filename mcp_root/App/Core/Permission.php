<?php
/*
* Permission interface - all new plugin permissions MUST implement
* this interface to be compatible with plugin manager. 
*/
interface MCPPermission {

	const
	 READ 		= 1
	,ADD 		= 2
	,DELETE 	= 3
	,EDIT 		= 4
	,PURGE		= 5;
	
	public function read($ids,$intUserId=null);
	public function add($ids,$intUserId=null);
	public function delete($ids,$intUserId=null);
	public function edit($ids,$intUserId=null);
	// public function purge($ids); @TODO - removes completely vs. soft delete

}