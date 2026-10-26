<?php
declare(strict_types=1);

/**
 * One-shot installer: creates schema + demo seed.
 * Protected by storage/install.token. After success, refuses re-run.
 */
define('BASE_PATH', __DIR__);

header('Content-Type: text/html; charset=utf-8');

$tokenFile = BASE_PATH . '/storage/install.token';
$lockFile = BASE_PATH . '/storage/install.lock';

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function load_config(): array
{
    $configFile = BASE_PATH . '/config.php';
    if (is_file($configFile)) {
        return require $configFile;
    }
    $jsonFile = BASE_PATH . '/.hostgator-db.json';
    if (is_file($jsonFile)) {
        $j = json_decode(file_get_contents($jsonFile), true);
        if (!$j) {
            throw new RuntimeException('JSON de DB inválido.');
        }
        return [
            'app_name' => 'Genealogía Sefardí',
            'app_url' => 'https://' . ($j['domain'] ?? 'genealogiasefardi.site'),
            'debug' => false,
            'timezone' => 'America/Monterrey',
            'db' => [
                'driver' => 'mysql',
                'host' => $j['host'] ?? 'localhost',
                'port' => 3306,
                'name' => $j['db'],
                'user' => $j['user'],
                'password' => $j['password'],
                'charset' => 'utf8mb4',
            ],
            'session_name' => 'gsfardi_session',
        ];
    }
    throw new RuntimeException('No hay config.php ni .hostgator-db.json');
}

function ensure_token(string $tokenFile): string
{
    if (!is_dir(dirname($tokenFile))) {
        mkdir(dirname($tokenFile), 0755, true);
    }
    if (!is_file($tokenFile)) {
        $token = bin2hex(random_bytes(24));
        file_put_contents($tokenFile, $token);
        @chmod($tokenFile, 0600);
        return $token;
    }
    return trim(file_get_contents($tokenFile));
}

function already_installed(PDO $pdo): bool
{
    try {
        $r = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        if (!$r) {
            return false;
        }
        $c = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        return $c > 0;
    } catch (Throwable $e) {
        return false;
    }
}

$config = null;
$error = null;
$success = null;
$token = ensure_token($tokenFile);

if (is_file($lockFile)) {
    $error = 'La instalación ya se completó (existe storage/install.lock). Elimine ese archivo solo si necesita reinstalar.';
}

try {
    $config = load_config();
} catch (Throwable $e) {
    $error = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $provided = trim($_POST['token'] ?? '');
    if (!hash_equals($token, $provided)) {
        $error = 'Token de instalación inválido.';
    } else {
        try {
            $db = $config['db'];
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $db['host'],
                (int)($db['port'] ?? 3306),
                $db['name'],
                $db['charset'] ?? 'utf8mb4'
            );
            $pdo = new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            if (already_installed($pdo) || is_file($lockFile)) {
                throw new RuntimeException('La base de datos ya tiene datos. Instalación rechazada.');
            }

            $schema = file_get_contents(BASE_PATH . '/schema.sql');
            $pdo->exec($schema);

            // --- Seed admin ---
            $adminHash = password_hash('ChangeMe8507!', PASSWORD_DEFAULT);
            $pdo->prepare(
                'INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)'
            )->execute(['Administrador', 'admin@genealogiasefardi.site', $adminHash, 'admin']);
            $adminId = (int)$pdo->lastInsertId();

            // --- Fictional Sephardic demo family (NOT real FamilySearch data) ---
            // Family: Navarro–Benveniste (fictional 18th–19th c. Nuevo León / Coahuila)
            $persons = [
                // id hints via order
                ['Don Isaac Navarro León', 'M', 0, 'abt 1760', 'Monterrey, Nuevo León', 'abt 1825', 'Monterrey, Nuevo León', 'Patriarca ficticio de la línea Navarro.'],
                ['Doña Raquel Benveniste Cohen', 'F', 0, 'abt 1765', 'Saltillo, Coahuila', 'abt 1830', 'Monterrey, Nuevo León', 'Esposa de Isaac; apellido ficticio sefardí.'],
                ['Simón Navarro Benveniste', 'M', 0, '12 Mar 1790', 'Monterrey, Nuevo León', '5 Aug 1862', 'Sabinas Hidalgo, Nuevo León', 'Hijo mayor; mercader.'],
                ['Lea Navarro Benveniste', 'F', 0, '1793', 'Monterrey, Nuevo León', '1848', 'Monterrey, Nuevo León', 'Hermana de Simón.'],
                ['Esther Abramowitz Navarro', 'F', 0, '1795', 'Villa de García, Nuevo León', '1870', 'Sabinas Hidalgo, Nuevo León', 'Esposa de Simón (ficticia).'],
                ['Jacobo Navarro Abramowitz', 'M', 0, '20 Jan 1820', 'Sabinas Hidalgo, Nuevo León', '1895', 'Monterrey, Nuevo León', 'Hijo de Simón y Esther.'],
                ['Miriam Solano Navarro', 'F', 0, '1825', 'Lampazos, Nuevo León', '1901', 'Monterrey, Nuevo León', 'Esposa de Jacobo.'],
                ['Daniel Navarro Solano', 'M', 0, '15 Jun 1850', 'Monterrey, Nuevo León', '1928', 'Monterrey, Nuevo León', 'Nieto; raíz demo del árbol.'],
                ['Sara Navarro Solano', 'F', 0, '1853', 'Monterrey, Nuevo León', '1930', 'Monterrey, Nuevo León', 'Hermana de Daniel.'],
            ];

            $ids = [];
            $insP = $pdo->prepare(
                'INSERT INTO persons (sex, living, notes, primary_name, birth_date, birth_place, death_date, death_place)
                 VALUES (?,?,?,?,?,?,?,?)'
            );
            $insN = $pdo->prepare('INSERT INTO person_names (person_id, full_name, is_primary) VALUES (?,?,1)');
            $insF = $pdo->prepare('INSERT INTO facts (person_id, type, date_text, place, description) VALUES (?,?,?,?,?)');

            foreach ($persons as $p) {
                $insP->execute([$p[1], $p[2], $p[7], $p[0], $p[3], $p[4], $p[5], $p[6]]);
                $pid = (int)$pdo->lastInsertId();
                $ids[] = $pid;
                $insN->execute([$pid, $p[0]]);
                $insF->execute([$pid, 'birth', $p[3], $p[4], null]);
                $insF->execute([$pid, 'death', $p[5], $p[6], null]);
            }

            // relationships: parent-child person1=parent, person2=child
            // spouses
            $insR = $pdo->prepare(
                'INSERT INTO relationships (type, person1_id, person2_id, date_text, place, notes) VALUES (?,?,?,?,?,?)'
            );
            // Isaac + Raquel
            $insR->execute(['spouse', $ids[0], $ids[1], 'abt 1788', 'Monterrey, Nuevo León', null]);
            // Isaac/Raquel -> Simón, Lea
            $insR->execute(['parent-child', $ids[0], $ids[2], null, null, null]);
            $insR->execute(['parent-child', $ids[1], $ids[2], null, null, null]);
            $insR->execute(['parent-child', $ids[0], $ids[3], null, null, null]);
            $insR->execute(['parent-child', $ids[1], $ids[3], null, null, null]);
            // Simón + Esther
            $insR->execute(['spouse', $ids[2], $ids[4], 'abt 1818', 'Sabinas Hidalgo, Nuevo León', null]);
            // Simón/Esther -> Jacobo
            $insR->execute(['parent-child', $ids[2], $ids[5], null, null, null]);
            $insR->execute(['parent-child', $ids[4], $ids[5], null, null, null]);
            // Jacobo + Miriam
            $insR->execute(['spouse', $ids[5], $ids[6], 'abt 1848', 'Monterrey, Nuevo León', null]);
            // Jacobo/Miriam -> Daniel, Sara
            $insR->execute(['parent-child', $ids[5], $ids[7], null, null, null]);
            $insR->execute(['parent-child', $ids[6], $ids[7], null, null, null]);
            $insR->execute(['parent-child', $ids[5], $ids[8], null, null, null]);
            $insR->execute(['parent-child', $ids[6], $ids[8], null, null, null]);

            // Extra facts
            $insF->execute([$ids[2], 'occupation', 'abt 1820', 'Sabinas Hidalgo, Nuevo León', 'Mercader de granos (dato ficticio).']);
            $insF->execute([$ids[7], 'baptism', '20 Jun 1850', 'Monterrey, Nuevo León', 'Bautismo ficticio en parroquia local.']);
            $insF->execute([$ids[0], 'residence', '1800', 'Monterrey, Nuevo León', 'Residencia documentada en padrón ficticio.']);

            // Sources
            $insS = $pdo->prepare(
                'INSERT INTO sources (title, citation, notes, media_path, created_by) VALUES (?,?,?,?,?)'
            );
            $insS->execute([
                'Acta bautismal ficticia — Daniel Navarro Solano',
                'Archivo Parroquial de Monterrey (simulado), Libro de bautismos 1850, f. 42.',
                'Documento de demostración. No corresponde a un expediente real de FamilySearch ni archivo eclesiástico.',
                null,
                $adminId,
            ]);
            $s1 = (int)$pdo->lastInsertId();
            $insS->execute([
                'Testamento ficticio de Isaac Navarro León',
                'Protocolo notarial ficticio, Monterrey, ca. 1824.',
                'Creado solo para la demo del MVP.',
                null,
                $adminId,
            ]);
            $s2 = (int)$pdo->lastInsertId();
            $insS->execute([
                'Padrón municipal ficticio 1800 — Monterrey',
                'Archivo Municipal de Monterrey (simulado), padrón de vecinos 1800.',
                null,
                null,
                $adminId,
            ]);
            $s3 = (int)$pdo->lastInsertId();

            $insA = $pdo->prepare(
                'INSERT INTO source_attachments (source_id, attachable_type, attachable_id, reason_statement) VALUES (?,?,?,?)'
            );
            $insA->execute([$s1, 'person', $ids[7], 'Establece fecha y lugar de bautismo de Daniel (demo).']);
            $insA->execute([$s2, 'person', $ids[0], 'Nombra a los herederos Simón y Lea (demo).']);
            $insA->execute([$s3, 'person', $ids[0], 'Confirma residencia en Monterrey hacia 1800 (demo).']);

            // Memories
            $insM = $pdo->prepare(
                'INSERT INTO memories (title, type, body, media_path, created_by) VALUES (?,?,?,?,?)'
            );
            $insM->execute([
                'Historia familiar Navarro (relato ficticio)',
                'story',
                "Según la tradición oral inventada para esta demo, los Navarro llegaron al norte de Nuevo León a finales del siglo XVIII. "
                . "Esta narrativa no representa linajes reales ni datos de FamilySearch; sirve solo para probar la galería de memorias.",
                null,
                $adminId,
            ]);
            $m1 = (int)$pdo->lastInsertId();
            $pdo->prepare('INSERT INTO memory_tags (memory_id, person_id) VALUES (?,?), (?,?)')->execute([
                $m1, $ids[0], $m1, $ids[7],
            ]);

            // Proof chain
            $pdo->prepare(
                'INSERT INTO proof_chains (title, claim, status, notes) VALUES (?,?,?,?)'
            )->execute([
                'Filiación Simón ← Isaac (demo)',
                'Simón Navarro Benveniste es hijo de Don Isaac Navarro León y Doña Raquel Benveniste Cohen.',
                'probado',
                'Cadena de demostración con fuentes ficticias.',
            ]);
            $pc = (int)$pdo->lastInsertId();
            $insL = $pdo->prepare(
                'INSERT INTO proof_chain_links (proof_chain_id, source_id, sort_order, note) VALUES (?,?,?,?)'
            );
            $insL->execute([$pc, $s2, 1, 'El testamento nombra a Simón como hijo legítimo.']);
            $insL->execute([$pc, $s3, 2, 'Contexto de residencia de la familia en Monterrey.']);

            // Root person = Daniel
            $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?)')
                ->execute(['root_person_id', (string)$ids[7]]);

            $pdo->prepare(
                'INSERT INTO audit_log (user_id, entity_type, entity_id, action, summary) VALUES (?,?,?,?,?)'
            )->execute([$adminId, 'system', null, 'install', 'Instalación inicial con datos demo']);

            // Write config.php if missing
            if (!is_file(BASE_PATH . '/config.php')) {
                $export = var_export($config, true);
                file_put_contents(BASE_PATH . '/config.php', "<?php\nreturn " . $export . ";\n");
            }

            file_put_contents($lockFile, date('c') . "\ninstalled\n");
            @chmod($lockFile, 0644);

            // Invalidate token after success (optional harden)
            @unlink($tokenFile);

            $success = 'Instalación completada. Usuario admin: admin@genealogiasefardi.site — cambie la contraseña tras el primer acceso.';
        } catch (Throwable $e) {
            $error = 'Error de instalación: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instalación — Genealogía Sefardí</title>
  <style>
    body{font-family:Georgia,serif;background:#f4f0e8;color:#2c2416;margin:0;padding:2rem}
    .box{max-width:520px;margin:0 auto;background:#fffdf8;border:1px solid #c4b59a;padding:2rem;border-radius:6px}
    h1{font-size:1.4rem;margin-top:0}
    label{display:block;margin:.8rem 0 .3rem;font-size:.9rem}
    input[type=text]{width:100%;padding:.5rem;box-sizing:border-box}
    button{margin-top:1rem;background:#5c4a32;color:#fff;border:0;padding:.6rem 1.2rem;cursor:pointer;border-radius:4px}
    .err{background:#fde8e8;border:1px solid #c44;padding:.75rem;margin-bottom:1rem}
    .ok{background:#e8f5e9;border:1px solid #2e7d32;padding:.75rem;margin-bottom:1rem}
    .hint{font-size:.85rem;color:#666;margin-top:.5rem}
    code{background:#eee;padding:0 .25rem}
  </style>
</head>
<body>
<div class="box">
  <h1>Instalación — Genealogía Sefardí</h1>
  <?php if ($error): ?>
    <div class="err"><?= h($error) ?></div>
    <?php if (str_contains($error, 'install.lock')): ?>
      <p><a href="/">Ir al sitio</a></p>
    <?php endif; ?>
  <?php elseif ($success): ?>
    <div class="ok"><?= h($success) ?></div>
    <p><a href="/login">Iniciar sesión</a></p>
  <?php else: ?>
    <p>Creará el esquema MySQL y datos demo (familia ficticia Navarro–Benveniste).</p>
    <?php if ($config): ?>
      <p class="hint">Base: <code><?= h($config['db']['name']) ?></code> @ <code><?= h($config['db']['host']) ?></code></p>
    <?php endif; ?>
    <form method="post">
      <label for="token">Token de instalación</label>
      <input type="text" id="token" name="token" required autocomplete="off"
             placeholder="Lea storage/install.token vía SFTP">
      <p class="hint">El token se generó en <code>storage/install.token</code> al abrir esta página.</p>
      <button type="submit">Instalar ahora</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
