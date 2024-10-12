<?php if(empty($breadcrumbs)) return; ?>

<ul class="breadcrumb">
<?php
foreach($breadcrumbs as $index=>$breadcrumb) {
    //$breadcrumb['label'] = "<span>{$breadcrumb['label']}</span>";
    
    $divider = '<span class="divider">/</span>';
    
    if($index == (count($breadcrumbs) - 1)) {
        $divider = '';
    }
    
    echo '<li>',$this->ui('Common.Field.Link',$breadcrumb),' '.$divider.'</li>';
}
?>
</ul>