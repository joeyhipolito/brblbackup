<?php
function autoloader($className) {
    $directories = array(
        'libs',
        'system',
        'app/dao',
        'app/controllers',
        'app/models',
    );
    
    foreach ($directories as $dir) {
        $file = "$dir/$className.php";
        if (file_exists($file)) {
            require $file;
        }
    }
}

spl_autoload_register('autoloader');
