<?php
header('Content-Type: text/javascript');
echo $this->_objMCP->fetch($REQUEST_CONTENT,$objModule);  
?>