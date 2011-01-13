<?php
interface MCPDB {
	public function connect($strHost,$strUser,$strPwd,$strDb);
	public function disconnect();
	public function query($strSQL,$arrBind=array());
	public function affectedRows();
	public function lastInsertId();
	public function escapeString($strValue);
	public function isConnected();
	public function beginTransaction();
	public function rollback();
	public function commit();
}
?>