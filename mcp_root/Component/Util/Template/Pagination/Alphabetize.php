<?php 

/*
* Build alphabetization interface 
*/
echo '<div class="pagination"><ul class="alphabetizer">';

/*
* Link to view all 
*/
printf(
	'<li%s>%s</li>'
	,$letter === null?' class="active"':''
	,$letter === null?'<a href="#">All</a>':sprintf('<a href="%s">All</a>',$base_path)
);

foreach($alphabet as $alpha) {
	
	$current = strcmp($alpha,$letter) === 0;
	
	printf(
		'<li%s>%s</li>'
		,$current?' class="active"':''
		,$current?"<a href=\"#\">$alpha</a>":sprintf('<a href="%s/%s">%s</a>',$base_path,$alpha,$alpha)
	);
}
echo '</ul></div>';

?>