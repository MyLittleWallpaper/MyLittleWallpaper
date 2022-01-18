<?php

declare(strict_types=1);

use MyLittleWallpaper\classes\Category\CategoryRepository;
use MyLittleWallpaper\classes\Database;

header('Content-Type: application/xml');

$document = new DOMDocument('1.0', 'utf-8');
$document->formatOutput = true;
$urlSet = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
$urlSet->setAttributeNS(
    'http://www.w3.org/2000/xmlns/',
    'xmlns:xsi',
    'http://www.w3.org/2001/XMLSchema-instance'
);
$urlSet->setAttributeNS(
    'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation',
    'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'
);

$baseURl = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['SERVER_NAME'] . '/';

$url = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'url');
$url->appendChild(
    $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'loc', $baseURl . 'c/all/')
);
$url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'changefreq', 'daily'));
$url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'priority', '0.8'));
$urlSet->appendChild($url);

$url = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'url');
$url->appendChild(
    $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'loc', $baseURl . 'c/all/featured')
);
$url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'changefreq', 'daily'));
$url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'priority', '0.7'));
$urlSet->appendChild($url);

$categoryRepo = new CategoryRepository(Database::getInstance());
foreach ($categoryRepo->getCategoryList() as $category) {
    $url = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'url');
    $url->appendChild(
        $document->createElementNS(
            'http://www.sitemaps.org/schemas/sitemap/0.9',
            'loc',
            $baseURl . 'c/' . $category->getUrlName() . '/'
        )
    );
    $url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'changefreq', 'daily'));
    $url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'priority', '0.6'));
    $urlSet->appendChild($url);
}

$url = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'url');
$url->appendChild(
    $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'loc', $baseURl . 'c/all/software')
);
$url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'changefreq', 'monthly'));
$url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'priority', '0.5'));
$urlSet->appendChild($url);

$url = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'url');
$url->appendChild(
    $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'loc', $baseURl . 'c/all/api-v1')
);
$url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'changefreq', 'monthly'));
$url->appendChild($document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'priority', '0.5'));
$urlSet->appendChild($url);

$document->appendChild($urlSet);

echo $document->saveXML();
