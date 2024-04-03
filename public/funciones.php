<?php
    $function_name = "putenv";
    if ( function_exists($function_name) ) {
        echo "$function_name está disponible";
    }
    else {
        echo "$function_name no está disponible";
    }
?>
