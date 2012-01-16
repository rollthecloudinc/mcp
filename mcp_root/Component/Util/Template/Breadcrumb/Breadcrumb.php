<?php if(empty($breadcrumbs)) return; ?>

<ol id="breadcrumbs">
<?php
foreach($breadcrumbs as $breadcrumb) {
    $breadcrumb['label'] = "<span>{$breadcrumb['label']}</span>";
    echo '<li>',$this->ui('Common.Field.Link',$breadcrumb),'</li>';
}
?>
</ol>