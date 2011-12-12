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
	if($nav_link['content_header'] && !$edit_link) {
		$this->display_block($nav_link['content_header'],$nav_link['content_header_type']);
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
if($nav_link !== null && $nav_link['content_footer'] && !$edit_link) {
	$this->display_block($nav_link['content_footer'],$nav_link['content_footer_type']);
}
?>