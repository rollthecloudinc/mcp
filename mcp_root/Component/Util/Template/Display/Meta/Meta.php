<?php
foreach($meta as $key=>&$data) {
    
    $base = '<meta name="%s" content="%s"';
    $close = $this->close(false); 
    
    switch($key) {
        case 'title':
            $base = '<%s>%s</%1$s';
            $close = '>';
            break;
        
        case 'content-type':
            $base = '<meta http-equiv="%s" content="%s"';
            break;
        
        default:
            
    }
    
    $base.= $close;
    
    // @todo add attribute support
   echo sprintf($base,$key,htmlentities($data['value'])).PHP_EOL;
    
}
?>