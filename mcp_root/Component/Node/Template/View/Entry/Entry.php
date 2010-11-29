<div class="component-node-view-entry">
<?php 
if($node) {
	printf('<h2>%s</h2>',$this->out($node['node_title']));
	
	/*
	* Display node entry content 
	*/
	$this->display_block($node['node_content'],$node['content_type'],$objModule);
	
	/*
	* Show path to editor 
	*/
	if($display_edit_link == 1) {
		printf('<a href="%s">Edit</a>',$EDIT_PATH);
	}
	
	if($display_comments == 1) {
		/*
		* Add new Comment form 
		*/
		echo $this->_objMCP->executeComponent('Component.Node.Module.Form.Comment',array('Node',$node['nodes_id']),null,array($objModule));
	
		/*
		* List node comments 
		*/
		echo $this->_objMCP->executeComponent('Component.Node.Module.List.Comment',array($node['nodes_id']),null,array($objModule));
	}
} else {
	echo 'Content Not Found';
}
?>
</div>