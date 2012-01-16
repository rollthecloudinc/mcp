<?php
foreach($assets as $file) {
    if(isset($file['inline'])) {
        echo $file['inline'];
    } else {
        include($file['file']);
    }
}