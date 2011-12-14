<?php
namespace UI\Element\Common\Form;

class Video implements \UI\Element {

    public function settings() {
        return array( 
               'base_name'=>array(
                   'required'=>true
               )
        );
    }
    
    public function html($settings,\UI\Manager $ui) {
        
        $strFile = $this->_getFileElement($ui,$settings);
        $strContainer = $this->_getContainersElement($ui,$settings);
        $strCodec = $this->_getCodecsElement($ui,$settings);
        
        return $ui->draw('Common.Listing.Tree',array(
            'data'=>array(
                 array('value'=>$strFile)
                ,array('value'=>$strContainer)
                ,array('value'=>$strCodec)
            )
        ));
        
    }
    
    
    /*
    * Get form element for video file upload
    *
    * @param obj \Ui\Manager
    * @return str  
    */
    protected function _getFileElement($ui,$settings) {
        extract($settings);
        
        return $ui->draw('Common.Form.File',array(
            'name'=>$base_name
            ,'id'=>'dsaddfs'
        ));
        
    }
    
    /*
    * Get form element to contain codecs
    *
    * @param obj \Ui\Manager
    * @return str  
    */
    protected function _getCodecsElement($ui,$settings) {
        extract($settings);
        
        return $ui->draw('Common.Form.Select',array(
            'name'=>$base_name.'[codec]'
            ,'id'=>'xxx'
            ,'data'=>array(
                'values'=>array(
                     array('values'=>'','label'=>'Pick Codec')
                    ,array('value'=>'1','label'=>'One')
                    ,array('value'=>'2','label'=>'Two')
                    ,array('value'=>'3','label'=>'Three')
                )
            )
            ,'value'=>''
        ));
        
    }
    
    /*
    * Get form element to contain containers
    *
    * @param obj \Ui\Manager
    * @return str  
    */
    protected function _getContainersElement($ui,$settings) {
        extract($settings);
        
        return $ui->draw('Common.Form.Select',array(
            'name'=>$base_name.'[container]'
            ,'id'=>'xxxccc'
            ,'data'=>array(
                'values'=>array(
                     array('values'=>'','label'=>'Pick Format')
                    ,array('value'=>'1','label'=>'One')
                    ,array('value'=>'2','label'=>'Two')
                    ,array('value'=>'3','label'=>'Three')
                )
            )
            ,'value'=>''
        ));
        
    }

}
?>