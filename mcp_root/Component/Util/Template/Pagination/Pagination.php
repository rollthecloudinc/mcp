<?php
if($total_pages <= $visible_pages) {
    $page_start = 1;
    $page_end = $total_pages;
} else if($page <= ceil($visible_pages/2)) {
    $page_start = 1;
    $page_end = $visible_pages;
} else if($page > ($total_pages - ceil($visible_pages/2))) {
    $page_start = $total_pages - (ceil(($visible_pages/2)*2)-1);
    $page_end = $total_pages;
} else {
    $page_start = $page-(floor($visible_pages/2));
    $page_end = $page+(floor($visible_pages/2));
}
echo '<div class="pager">';
printf(
    '<div class="summary"><p class="pages">%u %s</p><p class="total">%u %s</p></div>'
    ,$total_pages
    ,$total_pages == 1?'Page':'Pages'
    ,$found_rows
    ,$found_rows == 1?$label:$label
);
printf('<ul class="pagination">');
printf(
    '<li class="first">%s%s%s</li>'
    ,$page == 1?'':sprintf('<a href="%s/%u/%s">',$base_path,1,$query_string)
    ,'First'
    ,$page == 1?'':'</a>'
);    
printf(
    '<li class="previous">%s%s%s</li>'
    ,$page == 1?'':sprintf('<a href="%s/%u/%s">',$base_path,($page-1),$query_string)
    ,'Previous'
    ,$page == 1?'':'</a>'
);
foreach(range($page_start,$page_end,1) as $i) {
    /*if($i == $intPageStart) printf('<ol class="pages">');*/
    printf(
        '<li%s>%s%s%s</li>'
        ,$page == $i?' class="current"':''
        ,$page == $i?'':sprintf('<a href="%s/%u/%s">',$base_path,$i,$query_string)
        ,$i
        ,$page == $i?'':'</a>'
    );
    /*if($i == $intPageEnd) printf('</ol>');*/
}
printf(
	'<li class="next">%s%s%s</li>'
	,$page == $total_pages?'':sprintf('<a href="%s/%u/%s">',$base_path,($page+1),$query_string)
    ,'Next'
    ,$page == $total_pages?'':'</a>'
);
printf(
    '<li class="last">%s%s%s</li>'
    ,$page == $total_pages?'':sprintf('<a href="%s/%u/%s">',$base_path,$total_pages,$query_string)
    ,'Last'
    ,$page == $total_pages?'':'</a>'
);
printf('</ul>');
echo '</div>';
?>