<?php
$len = count($plugins) - 1;
foreach($plugins as $i=>$plugin) {
    
    if($i == 0 || $i == 3) {
        echo '<div class="row">';
    }
    
    echo '<div class="span3">';
    echo '<h3>',$plugin['title'],'&nbsp; <sup><a href="',sprintf($t1_base_url,$plugin['context']),'">Globals</a></sup></h3>';
    
    if(isset($plugin['items']) && !empty($plugin['items'])) {
        echo '<ul>';
        foreach($plugin['items'] as $item) {
            printf(
                '<li><a href="%s">%s</a></li>',
                sprintf($t2_base_url,$plugin['context'],(string) $item['id']),
                $item['label']
            );
        }
        echo '</ul>';
    }
    
    echo '</div>';
    
    if($i == $len || $i == 4) {
        echo '</div>';
    }
    
}