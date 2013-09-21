<?php

function ucWordsByUnderscore($string) {
    $words = explode('_', $string);
    foreach ($words as $word => $value) {
        $words[$word] = ucfirst($value);
    }
    return implode('', $words);
}