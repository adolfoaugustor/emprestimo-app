<?php

if (!function_exists('maskMiddleCharacters')) {
    function maskMiddleCharacters($input) {
        $length = strlen($input);
        if ($length <= 8) {
            return $input;
        }

        $start = substr($input, 0, 3);
        $middle = '*****';
        $end = substr($input, 8);

        return $start . $middle . $end;
    }
}