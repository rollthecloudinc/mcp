<?php 

// Link to edit
if($display_edit_link) {
    echo $this->ui('Common.Field.Link',array(
        'label'=>'Edit'
        ,'url'=>$EDIT_PATH
        ,'class'=>'edit'
    ));
}


echo $TPL_OVERRIDE_CONTENT; 

?>
