<?php
declare(strict_types=1);
/**
 * Public sitemap for SEO (bios + archive + home).
 */
define('BASE_PATH', __DIR__);
$config = require BASE_PATH . '/config.php';
$GLOBALS['config'] = $config;
require BASE_PATH . '/src/helpers.php';
require BASE_PATH . '/src/Db.php';

header('Content-Type: application/xml; charset=UTF-8');
$base = rtrim($config['app_url'] ?? 'https://genealogiasefardi.site', '/');
$urls = [
    ['loc' => $base . '/', 'prio' => '1.0'],
    ['loc' => $base . '/biografias', 'prio' => '0.9'],
    ['loc' => $base . '/archivo', 'prio' => '0.9'],
    ['loc' => $base . '/sefarad-mx/', 'prio' => '0.8'],
];
try {
    foreach (Db::fetchAll('SELECT slug, updated_at FROM biographies WHERE published = 1') as $b) {
        $urls[] = [
            'loc' => $base . '/biografias/' . rawurlencode((string)$b['slug']),
            'prio' => '0.95',
            'lastmod' => !empty($b['updated_at']) ? date('Y-m-d', strtotime((string)$b['updated_at'])) : null,
        ];
    }
} catch (Throwable $e) {
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
    }
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>" . $u['prio'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
