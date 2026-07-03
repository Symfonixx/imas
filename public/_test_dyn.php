<?php
// Isolate which header suppresses Chrome's XML tree viewer.
$mode = $_GET['mode'] ?? 'all';

header('Content-Type: application/xml; charset=UTF-8');

if ($mode === 'all' || $mode === 'cookie') {
    setcookie('probe_cookie', 'x', ['path' => '/']);
}
if ($mode === 'all' || $mode === 'cache') {
    header('Cache-Control: no-cache, private');
}

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
echo '  <url>'."\n";
echo '    <loc>https://imas.test/en</loc>'."\n";
echo '    <changefreq>daily</changefreq>'."\n";
echo '    <priority>1.0</priority>'."\n";
echo '  </url>'."\n";
echo '</urlset>'."\n";
