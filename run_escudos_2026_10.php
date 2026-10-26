<?php
declare(strict_types=1);
if (($_GET['t'] ?? '') !== '53949a6db5092b89a50bc17adb6e9ad4') { http_response_code(403); echo 'forbidden'; exit; }
define('BASE_PATH', __DIR__);
$GLOBALS['config'] = require BASE_PATH . '/config.php';
require BASE_PATH . '/src/helpers.php';
require BASE_PATH . '/src/Db.php';
$out = [];
foreach (Db::fetchAll("SELECT id, slug, family_group, portrait_path FROM biographies WHERE category='ilustre'") as $b) {
    if (!empty($b['portrait_path'])) continue;
    $f = ($b['family_group'] ?? 'garcia-garza') === 'garza-garcia' ? 'escudo-garza-garcia' : 'escudo-garcia-garza';
    $path = 'storage/uploads/bios/portraits/' . $f . '.png';
    if (!is_file(BASE_PATH . '/' . $path)) { $out[] = "missing $path"; continue; }
    Db::query('UPDATE biographies SET portrait_path = ? WHERE id = ?', [$path, (int)$b['id']]);
    $out[] = "portrait {$b['slug']} -> $path";
}
// Connect lineage proofs to biography_sources (no new documents)
$bio = Db::fetch("SELECT id FROM biographies WHERE slug='francisco-javier-garcia-gaona'");
if ($bio) {
    $bid = (int)$bio['id'];
    $have = [];
    foreach (Db::fetchAll('SELECT source_id FROM biography_sources WHERE biography_id=? AND source_id IS NOT NULL', [$bid]) as $r) $have[(int)$r['source_id']] = 1;
    $max = (int)(Db::fetch('SELECT COALESCE(MAX(sort_order),0) m FROM biography_sources WHERE biography_id=?', [$bid])['m']);
    $added = 0;
    foreach (Db::fetchAll('SELECT person_name, archive_source_id, source_ids FROM biography_lineage_links WHERE biography_id=? ORDER BY sort_order,id', [$bid]) as $st) {
        $ids = array_filter(array_map('intval', explode(',', (string)$st['source_ids'])));
        if (!empty($st['archive_source_id'])) array_unshift($ids, (int)$st['archive_source_id']);
        foreach ($ids as $sid) {
            if (isset($have[$sid]) || !Db::fetch('SELECT id FROM sources WHERE id=?', [$sid])) continue;
            $have[$sid] = 1;
            Db::query('INSERT INTO biography_sources (biography_id, source_id, citation_text, sort_order) VALUES (?,?,?,?)', [$bid, $sid, 'Prueba de filiación: ' . $st['person_name'], ++$max]);
            $added++;
        }
    }
    $out[] = "fjgg sources total=" . count($have) . " added=$added";
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
@unlink(__FILE__);
