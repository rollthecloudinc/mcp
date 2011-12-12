<?php 

foreach($messages as $type => &$msgs) {
	
	if( empty($msgs) ) continue;
	
	echo '<ul class="message '.$type.'">';
	
	foreach($msgs as $message) {
		printf(
			'<li>%s</li>'
			,htmlentities($message['normal'])
		);
	}
	
	echo '</ul>';
	
}

?>