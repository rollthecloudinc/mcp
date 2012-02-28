<?php
echo $this->ui('Common.Form.Form',array(
	 'name'=>$frmadd_name
	,'action'=>$action
	,'config'=>$config
	,'values'=>array('username'=>'')
	,'errors'=>$frmadd_errors
	,'legend'=>$frmadd_legend
	,'idbase'=>'role-users-add-'
)); 

/*
* Pagination 
*/
echo $PAGINATION_TPL;

/*
* Build out table of roles 
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$users
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>$frmlist_legend
	,'form_action'=>$action
	,'form_method'=>$method
	,'form_name'=>$frmlist_name
        ,'form_submit'=>true
));
