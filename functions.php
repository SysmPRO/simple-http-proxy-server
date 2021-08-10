<?php

function loadPage(string $url): string {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL,"https://habr.com{$url}");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($curl);
    curl_close ($curl);
    return $response;
}

function loadFile(string $url): string {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($curl);
    $mimeType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    curl_close ($curl);
    header("Content-type: {$mimeType}");
    return $response;
}

function normalizePageResponse(string $html): string {
    $html = removeJsScripts($html);
    $html = removePosterImagePreloader($html);
    $html = replaceAllHabrUrlsWithLocal($html);
    $html = removeImagesPreloader($html);
    $html = replaceAllImagesUrlsWithLocal($html);
    return replaceAllImagesDataSrc($html);
}

function removeJsScripts(string $html): string {
    return preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);
}

function removePosterImagePreloader(string $html): string {
    return str_replace([' tm-company-card__branding_loading', '<div class="tm-company-card__branding-placeholder"><!----></div>'], '', $html);
}

function replaceAllHabrUrlsWithLocal(string $html): string {
    return str_replace('//habrahabr.ru', '//localhost:8000', $html);
}

function removeImagesPreloader(string $html): string {
    return str_replace('src="/img/image-loader.svg"', '', $html);
}

function replaceAllImagesUrlsWithLocal(string $html): string {
    return str_replace('="/img/', '="/?extract=https://habr.com/img/', $html);
}

function replaceAllImagesDataSrc(string $html): string {
    return str_replace('data-src="', 'src="/?extract=', $html);
}

function prepareDomDocument(string $html): DOMDocument {
    $domDocument = new DOMDocument;
    libxml_use_internal_errors(true);
    $domDocument->loadHTML($html);
    libxml_clear_errors();
    return $domDocument;
}

function extractDomTextNodes(DOMDocument $domDocument): DOMNodeList {
    $domXpath = new DOMXPath($domDocument);
    return $domXpath->query('/html/body//div[@id="app"]//text()');
}

function extractNodeSixLetterWords(string $nodeValue): array {
    $words          = preg_split('/\W+/u', $nodeValue, -1, PREG_SPLIT_NO_EMPTY);
    $sixLetterWords = array_filter($words, function(string $word) {
        return mb_strlen($word) === 6;
    });
    return array_unique($sixLetterWords);
}

function prepareMarkedSixLetterWords(array $sixLetterWords): array {
    return array_map(function(string $word): string {
        return "{$word}™";
    }, $sixLetterWords);
}
