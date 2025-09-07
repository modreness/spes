<?php

function dani() {
    return [
        'pon' => 'Ponedjeljak',
        'uto' => 'Utorak',
        'sri' => 'Srijeda',
        'cet' => 'Četvrtak',
        'pet' => 'Petak',
        'sub' => 'Subota',
        'ned' => 'Nedjelja'
    ];
}
function smjene() {
    return [
        'jutro' => 'Jutro',
        'popodne' => 'Popodne',
        'vecer' => 'Večer'
    ];
}


function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}
