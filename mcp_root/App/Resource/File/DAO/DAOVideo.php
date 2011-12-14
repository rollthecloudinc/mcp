<?php
/*
* Video data access object 
*/
$this->import('App.Core.DAO');
class MCPDAOVideo extends MCPDAO {
    
    /*
    * Insert new video file
    * 
    * This method will be responsible for managing all the different
    * combination of containers and codecs for a given video file. It
    * will effectively make sure to replace an existing video format
    * that exists. It will also create a new video base when an id
    * value is not based with the root level element. All videos
    * will be located under the formats array. Those will be all the various
    * formats for a given video. Any format that includes an id will be updated
    * if a video file is specified. Otherwise, the format will be created. This
    * will also make sure that the combination of codec and container are unique
    * per video. Meaning a single video may not replicate a combination of formats.
    * When that happens an exception will occur and existing transaction should cease. Therefore,
    * video files will only be moved to the disk once ALL video formats are sucessfully
    * stored in the db.           
    *
    * @param array video data  
    */
    public function insert($arrVideo) {
        
        $this->_objMCP->debug($arrVideo);
        
        // $arrVideo[id] video_label
        // $arrVideo[formats][0] = array('codec'=>'','container'=>'','id'=>89)
        
    }
    
}
?>
