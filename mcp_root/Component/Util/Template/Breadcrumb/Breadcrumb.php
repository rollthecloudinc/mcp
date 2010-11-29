<ol>
<?php
foreach($breadcrumbs as $breadcrumb) {
	
	printf(
		'<li><a href="%s">%s</a></li>'
		,$breadcrumb['href']
		,$breadcrumb['label']
	);
	
}
?>
</ol>