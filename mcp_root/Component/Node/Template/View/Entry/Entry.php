<?php 
if($node) {
    
        // $this->debug($node);
    
    
    
        foreach($schema as $field) {
            
            $index = null;
            
            if($field['dynamic']) {
                $index = $field['name'];
            } else {
                $index = $field['column'];
            }
            
            if($index === null || !isset($node[$index])) {
                continue;
            }
            
            if(!empty($field['relation_type'])) {
       
            } else {
                //echo "<div>{$node[$index]}</div>";
            }
            
        }
    
    
    
    
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