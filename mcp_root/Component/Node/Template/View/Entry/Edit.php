<?php 
if($node === null) return;
//printf('<a href="%s">Content</a>',$BASE_PATH);

$this->_objMCP->addBreadcrumb(array(
    'label'=>'Content'
    ,'url'=>BASE_PATH
));

echo $EDIT_TPL;
?>