<?php 
if($nav_link !== null) {
	
	/*
	* Navigation link edit and back link 
	*/
	if($display_edit_link == 1) {
	printf(
		'<a href="%s">%s</a>'
		,$link_path
		,$edit_link?'Back To Link':'Edit Link');
	}
		
	/*
	* Navigation link header 
	*/	
	if($nav_link['header_content'] && !$edit_link) {
		$this->display_block($nav_link['header_content'],$nav_link['header_content_type']);
	}
}

/*
* Route content 
*/
if($content_type) {
	$this->display_block($ROUTE_CONTENT,$content_type);
} else {
	echo $ROUTE_CONTENT; 
}

/*
* Navigation link footer 
*/
if($nav_link !== null && $nav_link['footer_content'] && !$edit_link) {
	$this->display_block($nav_link['footer_content'],$nav_link['footer_content_type']);
}
?>