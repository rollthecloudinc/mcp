<?php
namespace UI\Element\Entity;

class Node implements \UI\Element {
	
	public function settings() {
		return array(
			'node'=>array(
				'required'=>true
			)
                    
                        /*
                        * Display link to edit node 
                        */
                        ,'display_edit_link'=>array(
                                'required'=>false
                                ,'default'=>false
                        )
                    
                        /*
                        * URL to edit node 
                        */
                        ,'edit_url'=>array(
                                'required'=>false
                                ,'default'=>''
                        )
                    
                        /*
                        * Display comments 
                        */
                        ,'display_comments'=>array(
                                'required'=>false
                                ,'default'=>false
                        )
                    
                    
                        /*
                        * Comment form 
                        */
                        ,'comment_form'=>array(
                                'required'=>false
                               ,'default'=>''
                        )
                    
                        /*
                        * Comments 
                        */
                        ,'comments'=>array(
                                'required'=>false
                                ,'default'=>''
                        )
                    
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
                extract($settings);
                
                $out = '';
                
                // get title
                $out.= $ui->draw('Common.Field.Heading',array(
                    'heading'=>$node['node_title']
                    ,'type'=>'h2'
                ));
                
                // Link to edit
                if($display_edit_link) {
                    $out.= $ui->draw('Common.Field.Link',array(
                        'label'=>'Edit'
                        ,'url'=>$edit_url
                        ,'class'=>'edit'
                    ));
                }
                
                // Content
                $out.= $ui->draw('Common.Field.Content',array(
                    'content'=>$node['node_content']
                    ,'type'=>$node['content_type']
                ));
                
                //comments
                if($display_comments) {
                   $out.= $comment_form;
                   $out.= $comments;
                }
                
                return $out;
		
	}
	
}
?>
