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
	,EDIT 		= 4;
	
	public function read($ids);
	public function add($ids);
	public function delete($ids);
	public function edit($ids);

}