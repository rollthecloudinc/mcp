<?php
if(!$nav) {
	echo '<p class="notice">Menu Failed</p>';
	return;
}

//echo '<pre>',print_r($menu),'</pre>';

if($nav['display_title'] == 1) {
	echo "<h3>{$this->out($nav['menu_title'])}</h3>";
}

if(!empty($menu)) {
	echo $objModule->paintMenu($menu);
} else {
	printf('<p class="notice">%s Menu Empty</p>',$this->out($nav['menu_title']));
}
?>