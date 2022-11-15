<?php

function register () {
    parse_str($_SERVER['QUERY_STRING'], $query);
    if (isset($query['uuid'])) {
        $uuid = $query['uuid'];
        $path = "Users/$uuid";
        if (!file_exists($path))
            mkdir($path);
    }
}

register();

?>
