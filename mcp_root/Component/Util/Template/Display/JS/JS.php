<?php 
foreach($scripts as $script) {
    echo sprintf('<script type="text/javascript" src="%s">%s</script>',$script['src'],$script['inline']).PHP_EOL;
}