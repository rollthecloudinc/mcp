<?php 
if($node) {
    
	printf('<h2>%s</h2>',$this->out($node['node_title']));
	
	/*
	* Show path to editor 
	*/
	if($display_edit_link == 1) {
		printf('<a class="edit" href="%s">Edit</a>',$EDIT_PATH);
	}
	
	/*
	* Display node entry content 
	*/
	$this->display_block($node['node_content'],$node['content_type'],$objModule);
	
	if($display_comments == 1) {
		/*
		* Add new Comment form 
		*/
		echo $comment;
	
		/*
		* List node comments 
		*/
		echo $comments;
	}
} else {
	echo 'Content Not Found';
}
?>