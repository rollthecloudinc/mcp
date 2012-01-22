<?php 

foreach($messages as $type => &$msgs) {
	
	if( empty($msgs) ) continue;
        
        
        switch($type) {
            case 'status':
                $cls = 'success';
                break;
            
            default:
                $cls = $type;
        }
	
	echo '<div class="alert-message '.$cls.'">';
	
	foreach($msgs as $message) {
		printf(
			'<p>%s</p>'
			,htmlentities($message['normal'])
		);
	}
	
	echo '</div>';
	
}

?>