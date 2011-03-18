<?php 

/*
* Build alphabetization interface 
*/
echo '<ol class="alphabetizer">';

/*
* Link to view all 
*/
printf(
	'<li%s>%s</li>'
	,$letter === null?' class="current"':''
	,$letter === null?'<span>All</span>':sprintf('<a href="%s">All</a>',$base_path)
);

foreach($alphabet as $alpha) {
	
	$current = strcmp($alpha,$letter) === 0;
	
	printf(
		'<li%s>%s</li>'
		,$current?' class="current"':''
		,$current?"<span>$alpha</span>":sprintf('<a href="%s/%s">%s</a>',$base_path,$alpha,$alpha)
	);
}
echo '</ol>';

?>