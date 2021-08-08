<?php

include_once __DIR__.'/functions.php';

if (array_key_exists('extract', $_GET)) {
    $fileUrl = is_scalar($_GET['extract']) && (string) $_GET['extract'] !== '' ? $_GET['extract'] : null;
    echo $fileUrl ? loadFile($fileUrl) : '404';
} else {
    $loadedPageResponse    = loadPage($_SERVER['REQUEST_URI']);
    $normalizePageResponse = normalizePageResponse($loadedPageResponse);
    $domDocument           = prepareDomDocument($normalizePageResponse);
    $textNodes             = extractDomTextNodes($domDocument);
    /** @var DOMNode $node */
    foreach($textNodes as $node) {
        $sixLetterWords         = extractNodeSixLetterWords($node->nodeValue);
        $suffixedSixLetterWords = prepareMarkedSixLetterWords($sixLetterWords);
        $node->nodeValue        = str_replace($sixLetterWords, $suffixedSixLetterWords, $node->nodeValue);
    }
    echo $domDocument->saveHTML();
}

