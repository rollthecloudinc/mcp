<?php 
header('Content-Type: text/css');
header('Cache-control: public');
echo $this->_objMCP->fetch($REQUEST_CONTENT,$objModule); 
?>