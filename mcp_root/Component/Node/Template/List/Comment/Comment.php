<?php 
if(empty($comments)) {
	return;
}

echo '<ul>';
foreach($comments as $index=>$comment) {
	echo '<li>';
	
	printf(
		'<ul>
			<li>%s</li>
			<li>%s</li>
		</ul>'
		,$this->out(
			
			// change behavpr based on whether commenter was a user
			$comment['commenter_id'] === null?
		
			// show first name and firt initial when none-user made comment
			$comment['commenter_first_name'].' '.strtoupper($comment['commenter_last_name']{0}).'.':
			
			// show username when commenter is a user
			$comment['username'])
			
		,$this->ui('Common.Field.Date',array(
			'date'=>$comment['created_on_timestamp']
		))
	);
	
	switch($comment['comment_type']) {
		
		/*
		* PHP content 
		*/
		case 'php':
			eval('?>'.$comment['comment_content']);
			break;
		
		/*
		* HTML content 
		*/
		case 'html':
			echo $comment['comment_content'];
			break;
		
		/*
		* Textual content 
		*/
		case 'text':
		default:
			echo strip_tags($comment['comment_content']);
		
	}
	
	echo '</li>';
}
echo '</ul>';

?>