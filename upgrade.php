<?php
declare(strict_types=1);

/**
 * One-shot upgrade: multi-user + archive + biographies + lineage.
 * Token: storage/upgrade.token — lock: storage/upgrade.lock
 */
define('BASE_PATH', __DIR__);
header('Content-Type: text/html; charset=utf-8');

$tokenFile = BASE_PATH . '/storage/upgrade.token';
$lockFile = BASE_PATH . '/storage/upgrade.lock';
require BASE_PATH . '/sql/seed_bios_lineage.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function load_config(): array
{
    if (is_file(BASE_PATH . '/config.php')) {
        return require BASE_PATH . '/config.php';
    }
    $jsonFile = BASE_PATH . '/.hostgator-db.json';
    if (is_file($jsonFile)) {
        $j = json_decode(file_get_contents($jsonFile), true);
        if (!$j) throw new RuntimeException('JSON de DB inválido.');
        return ['db' => [
            'host' => $j['host'] ?? 'localhost', 'port' => 3306,
            'name' => $j['db'], 'user' => $j['user'], 'password' => $j['password'],
            'charset' => 'utf8mb4',
        ]];
    }
    throw new RuntimeException('No hay config.php ni .hostgator-db.json');
}

function ensure_token(string $tokenFile): string
{
    if (!is_dir(dirname($tokenFile))) mkdir(dirname($tokenFile), 0755, true);
    if (!is_file($tokenFile)) {
        $token = bin2hex(random_bytes(24));
        file_put_contents($tokenFile, $token);
        @chmod($tokenFile, 0600);
        return $token;
    }
    return trim(file_get_contents($tokenFile));
}

function column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

$error = null; $success = null; $log = [];
$token = ensure_token($tokenFile);
if (is_file($lockFile)) {
    $error = 'La actualización ya se completó (storage/upgrade.lock). Elimínelo solo para reaplicar.';
}
$config = null;
try { $config = load_config(); } catch (Throwable $e) { $error = $e->getMessage(); }

$run = false; $provided = '';
if (!$error && ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['run']))) {
    $provided = trim($_POST['token'] ?? $_GET['token'] ?? '');
    $run = true;
}

if ($run && !$error) {
    if (!hash_equals($token, $provided)) {
        $error = 'Token de actualización inválido.';
    } else {
        try {
            $db = $config['db'];
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], (int)($db['port'] ?? 3306), $db['name'], $db['charset'] ?? 'utf8mb4');
            $pdo = new PDO($dsn, $db['user'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            if (!column_exists($pdo, 'persons', 'owner_id')) {
                $pdo->exec('ALTER TABLE persons ADD COLUMN owner_id INT UNSIGNED NULL AFTER id, ADD INDEX idx_persons_owner (owner_id)');
                $log[] = 'persons.owner_id';
                try { $pdo->exec('ALTER TABLE persons ADD CONSTRAINT fk_persons_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL'); }
                catch (Throwable $e) { $log[] = 'FK owner: '.$e->getMessage(); }
            } else { $log[] = 'persons.owner_id ok'; }
            $pdo->exec('UPDATE persons SET owner_id = 1 WHERE owner_id IS NULL');

            if (!column_exists($pdo, 'persons', 'external_id')) {
                $pdo->exec("ALTER TABLE persons ADD COLUMN external_id VARCHAR(120) NULL AFTER notes, ADD INDEX idx_persons_external (owner_id, external_id)");
                $log[] = 'persons.external_id';
            }

            if (!column_exists($pdo, 'sources', 'visibility')) {
                $pdo->exec("ALTER TABLE sources ADD COLUMN visibility ENUM('private','shared','archive') NOT NULL DEFAULT 'private' AFTER created_by, ADD INDEX idx_sources_visibility (visibility)");
                $log[] = 'sources.visibility';
            } else { $log[] = 'sources.visibility ok'; }
            $pdo->exec("UPDATE sources s LEFT JOIN users u ON u.id = s.created_by SET s.visibility = 'archive' WHERE (u.role = 'admin' OR s.created_by = 1 OR s.created_by IS NULL)");

            if (!table_exists($pdo, 'user_settings')) {
                $pdo->exec("CREATE TABLE user_settings (
                  user_id INT UNSIGNED NOT NULL PRIMARY KEY,
                  root_person_id INT UNSIGNED NULL,
                  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                  FOREIGN KEY (root_person_id) REFERENCES persons(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $log[] = 'user_settings';
            }
            $pdo->exec("INSERT IGNORE INTO user_settings (user_id, root_person_id) SELECT 1, CAST(`value` AS UNSIGNED) FROM settings WHERE `key` = 'root_person_id' AND `value` IS NOT NULL AND `value` <> ''");

            if (!table_exists($pdo, 'biographies')) {
                $pdo->exec("CREATE TABLE biographies (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  person_id INT UNSIGNED NULL,
                  slug VARCHAR(160) NOT NULL,
                  full_name VARCHAR(255) NOT NULL,
                  subtitle VARCHAR(255) NULL,
                  lifespan_label VARCHAR(64) NULL,
                  summary TEXT NULL,
                  body_html MEDIUMTEXT NULL,
                  portrait_path VARCHAR(512) NULL,
                  preferred_citation VARCHAR(500) NULL,
                  is_featured TINYINT(1) NOT NULL DEFAULT 1,
                  sort_order INT NOT NULL DEFAULT 0,
                  published TINYINT(1) NOT NULL DEFAULT 1,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  UNIQUE KEY uq_bio_slug (slug),
                  INDEX idx_bio_featured (is_featured, published, sort_order),
                  FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $log[] = 'biographies';
            }
            if (!table_exists($pdo, 'biography_sources')) {
                $pdo->exec("CREATE TABLE biography_sources (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  biography_id INT UNSIGNED NOT NULL,
                  source_id INT UNSIGNED NULL,
                  citation_text TEXT NULL,
                  sort_order INT NOT NULL DEFAULT 0,
                  FOREIGN KEY (biography_id) REFERENCES biographies(id) ON DELETE CASCADE,
                  FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE SET NULL,
                  INDEX idx_bs_bio (biography_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $log[] = 'biography_sources';
            }
            if (!table_exists($pdo, 'biography_lineage_links')) {
                $pdo->exec("CREATE TABLE biography_lineage_links (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  biography_id INT UNSIGNED NOT NULL,
                  sort_order INT NOT NULL DEFAULT 0,
                  generation_label VARCHAR(120) NULL,
                  person_name VARCHAR(255) NOT NULL,
                  relationship_label VARCHAR(255) NULL,
                  claim_text TEXT NULL,
                  status ENUM('probado','pendiente','en_disputa') NOT NULL DEFAULT 'pendiente',
                  source_ids VARCHAR(255) NULL,
                  proof_chain_id INT UNSIGNED NULL,
                  archive_source_id INT UNSIGNED NULL,
                  notes TEXT NULL,
                  FOREIGN KEY (biography_id) REFERENCES biographies(id) ON DELETE CASCADE,
                  INDEX idx_bll_bio (biography_id, sort_order)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $log[] = 'biography_lineage_links';
            }

            seed_biographies_full($pdo, $log);
            seed_lineage_fjgg($pdo, $log);

            file_put_contents($lockFile, date('c') . "\n");
            @chmod($lockFile, 0600);
            $success = 'Actualización completada. Puede eliminar upgrade.php si lo desea.';
        } catch (Throwable $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
$providedGet = trim($_GET['token'] ?? '');
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><title>Actualización — Genealogía Sefardí</title>
<style>body{font-family:Georgia,serif;background:#f3efe6;color:#2a2218;max-width:560px;margin:3rem auto;padding:0 1rem}.box{background:#fffcf5;border:1px solid #d4c7b0;border-radius:8px;padding:1.5rem}.ok{color:#2e5a3c}.err{color:#8b2e2e}input,button{font:inherit;padding:.5rem;width:100%;box-sizing:border-box;margin:.4rem 0}button{background:#6b4423;color:#fff;border:none;cursor:pointer}ul{font-size:.9rem}</style>
</head><body><div class="box"><h1>Actualización multi-usuario</h1>
<?php if ($error): ?><p class="err"><?= h($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="ok"><?= h($success) ?></p><?php if ($log): ?><ul><?php foreach ($log as $l): ?><li><?= h($l) ?></li><?php endforeach; ?></ul><?php endif; ?>
<?php elseif (!is_file($lockFile)): ?>
<p>Aplica owner_id, visibility, user_settings, biographies, lineage + semillas (PDF/retrato FJGG).</p>
<form method="post" action="?token=<?= h(urlencode($providedGet)) ?>">
<label>Token</label>
<input type="text" name="token" required value="<?= h($providedGet) ?>" autocomplete="off">
<button type="submit">Ejecutar actualización</button>
</form>
<?php endif; ?>
</div></body></html>
