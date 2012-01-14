<?php 
if($node) {
    
        echo $this->ui('Entity.Node',array(
             'node'=>$node
            ,'display_edit_link'=> $display_edit_link
            ,'edit_url'=>$EDIT_PATH
            ,'display_comments'=>$display_comments
            ,'comment_form'=>$comment
            ,'comments'=>$comments
        ));
        
} else {
	echo 'Content Not Found';
}
?>