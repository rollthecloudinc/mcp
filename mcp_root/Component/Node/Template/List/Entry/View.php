<?php
if($display_pagination == 1) { echo $PAGINATION_TPL; }
printf('<a class="back" href="%s">%s</a>',$BASE_PATH,$edit_label);
echo $VIEW_TPL;
?>