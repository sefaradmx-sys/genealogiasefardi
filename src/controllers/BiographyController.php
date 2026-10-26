<?php
declare(strict_types=1);

final class BiographyController
{
    public static function index(): void
    {
        $bios = [];
        try {
            $bios = Db::fetchAll(
                'SELECT * FROM biographies WHERE published = 1
                 ORDER BY sort_order, full_name'
            );
        } catch (Throwable $e) {
        }
        view('biographies/index', [
            'title' => 'Biografías',
            'public' => true,
            'bios' => $bios,
        ]);
    }

    public const FAMILIES = [
        'garcia-garza' => [
            'name' => 'García Garza',
            'intro' => 'Descendientes del Alférez Lorenzo García de Ábrego y de Juana de la Garza González (1622–1669), hija del Capitán Blas de la Garza Falcón, y figuras ilustres del apellido García en Coahuila y Nuevo León. Cuando la filiación directa aún está en investigación, cada ficha lo indica.',
        ],
        'garza-garcia' => [
            'name' => 'Garza García',
            'intro' => 'La rama Garza García del noreste mexicano, de raíz en los pobladores fundadores del Nuevo Reino de León.',
        ],
    ];

    /** /personas-ilustres and /personas-ilustres/{family} */
    public static function illustrious(array $params = []): void
    {
        $fam = $params['family'] ?? null;
        if ($fam !== null && !isset(self::FAMILIES[$fam])) {
            http_response_code(404);
            view('errors/404', ['title' => 'No encontrado', 'public' => true]);
            return;
        }
        $bios = [];
        try {
            $bios = Db::fetchAll("SELECT * FROM biographies WHERE published = 1 AND category = 'ilustre' ORDER BY sort_order, full_name");
        } catch (Throwable $e) {
        }
        $groups = [];
        foreach (self::FAMILIES as $k => $f) {
            if ($fam && $fam !== $k) continue;
            $groups[$k] = $f + ['bios' => array_values(array_filter($bios, function ($b) use ($k) { return ($b['family_group'] ?? 'garcia-garza') === $k; }))];
        }
        $title = $fam ? ('Personas ilustres — familia ' . self::FAMILIES[$fam]['name']) : 'Personas ilustres';
        $appUrl = rtrim($GLOBALS['config']['app_url'] ?? 'https://genealogiasefardi.site', '/');
        view('biographies/ilustres', [
            'title' => $title,
            'public' => true,
            'groups' => $groups,
            'family' => $fam,
            'bodyClass' => 'museo-3d-page',
            'metaDescription' => 'Personas ilustres de las familias García Garza y Garza García: biografías documentadas con fuentes. Genealogía Sefardí.',
            'canonical' => $appUrl . '/personas-ilustres' . ($fam ? '/' . $fam : ''),
        ]);
    }

    public static function show(array $params): void
    {
        $slug = $params['slug'] ?? '';
        $bio = null;
        try {
            $bio = Db::fetch(
                'SELECT * FROM biographies WHERE slug = ? AND published = 1',
                [$slug]
            );
        } catch (Throwable $e) {
        }
        if (!$bio) {
            $moved = [
                'dionisio-garcia-fuente' => 'dionisio-garcia-fuentes',
                'antonio-garcia-garrido' => 'antonio-garcia-carrillo',
            ];
            if (isset($moved[$slug])) {
                header('Location: /biografias/' . $moved[$slug], true, 301);
                return;
            }
            http_response_code(404);
            view('errors/404', ['title' => 'Biografía no encontrada', 'public' => true]);
            return;
        }
        $sources = Db::fetchAll(
            'SELECT bs.*, s.title AS source_title, s.media_path, s.visibility
             FROM biography_sources bs
             LEFT JOIN sources s ON s.id = bs.source_id
             WHERE bs.biography_id = ?
             ORDER BY bs.sort_order, bs.id',
            [(int)$bio['id']]
        );
        $lineage = [];
        try {
            $lineage = Db::fetchAll(
                'SELECT * FROM biography_lineage_links
                 WHERE biography_id = ?
                 ORDER BY sort_order, id',
                [(int)$bio['id']]
            );
        } catch (Throwable $e) {
        }
        // Attach linked source titles for lineage steps
        foreach ($lineage as &$step) {
            $step['proof_sources'] = [];
            if (!empty($step['source_ids'])) {
                $ids = array_filter(array_map('intval', explode(',', (string)$step['source_ids'])));
                foreach ($ids as $sid) {
                    $s = Db::fetch('SELECT id, title, visibility, media_path FROM sources WHERE id = ?', [$sid]);
                    if ($s) {
                        $step['proof_sources'][] = $s;
                    }
                }
            }
        }
        unset($step);

        $person = null;
        if (!empty($bio['person_id'])) {
            $person = Db::fetch('SELECT id, primary_name FROM persons WHERE id = ?', [(int)$bio['person_id']]);
        }

        $appUrl = rtrim($GLOBALS['config']['app_url'] ?? 'https://genealogiasefardi.site', '/');
        $pageUrl = $appUrl . '/biografias/' . $bio['slug'];

        $name = (string)$bio['full_name'];
        $summary = trim((string)($bio['summary'] ?? ''));
        if ($summary === '') {
            $summary = $name . ' — biografía documentada con fuentes en Genealogía Sefardí (Nuevo León / Coahuila).';
        }
        // Help search: include common name variants in description
        $variants = $name;
        if (stripos($name, 'Garza') !== false) {
            $variants .= '. También: Garza Falcón, de la Garza Falcón, Blas de la Garza';
        }
        $metaDescription = mb_substr($summary . ' ' . $variants . ' Archivo y pruebas en genealogiasefardi.site.', 0, 320);
        $ogImage = $appUrl . '/public/assets/og-default.png';
        if (!empty($bio['portrait_path'])) {
            $ogImage = $appUrl . '/media/biography/' . (int)$bio['id'];
        }
        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $name,
            'description' => $summary,
            'url' => $pageUrl,
            'image' => !empty($bio['portrait_path']) ? ($appUrl . '/media/biography/' . (int)$bio['id']) : null,
            'mainEntityOfPage' => $pageUrl,
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Genealogía Sefardí',
                'url' => $appUrl,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        view('biographies/show', [
            'title' => $name . ' — biografía y fuentes',
            'public' => true,
            'bio' => $bio,
            'sources' => $sources,
            'lineage' => $lineage,
            'person' => $person,
            'pageUrl' => $pageUrl,
            'metaDescription' => $metaDescription,
            'canonical' => $pageUrl,
            'ogImage' => $ogImage,
            'ogType' => 'profile',
            'jsonLd' => $jsonLd,
        ]);
    }

    /** Admin-only: official biographies (portrait + text). */
    public static function editForm(array $params): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($params['id'] ?? 0);
        $bio = Db::fetch('SELECT * FROM biographies WHERE id = ?', [$id]);
        if (!$bio) {
            http_response_code(404);
            view('errors/404', ['title' => 'No encontrado']);
            return;
        }
        view('biographies/form', [
            'title' => 'Editar biografía',
            'bio' => $bio,
            'action' => '/panel/biografias/' . $id,
        ]);
    }

    public static function update(array $params): void
    {
        Auth::requireRole(['admin']);
        verify_csrf();
        $id = (int)($params['id'] ?? 0);
        $bio = Db::fetch('SELECT * FROM biographies WHERE id = ?', [$id]);
        if (!$bio) {
            flash('error', 'No encontrada.');
            redirect('/biografias');
        }

        $data = [
            'full_name' => trim($_POST['full_name'] ?? $bio['full_name']),
            'subtitle' => trim($_POST['subtitle'] ?? '') ?: null,
            'lifespan_label' => trim($_POST['lifespan_label'] ?? '') ?: null,
            'summary' => trim($_POST['summary'] ?? '') ?: null,
            'body_html' => $_POST['body_html'] ?? $bio['body_html'],
            'preferred_citation' => trim($_POST['preferred_citation'] ?? '') ?: null,
            'published' => !empty($_POST['published']) ? 1 : 0,
        ];

        if (!empty($_POST['remove_portrait']) && !empty($bio['portrait_path'])) {
            $abs = BASE_PATH . '/' . ltrim((string)$bio['portrait_path'], '/');
            if (is_file($abs)) {
                @unlink($abs);
            }
            $data['portrait_path'] = null;
        }

        if (!empty($_FILES['portrait']) && ($_FILES['portrait']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $rel = upload_file($_FILES['portrait'], 'bios/portraits');
                if ($rel) {
                    // remove previous portrait file if replacing
                    if (!empty($bio['portrait_path']) && empty($_POST['remove_portrait'])) {
                        $oldPath = BASE_PATH . '/' . ltrim((string)$bio['portrait_path'], '/');
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $data['portrait_path'] = $rel;
                }
            } catch (Throwable $e) {
                flash('error', 'Imagen: ' . $e->getMessage());
                redirect('/panel/biografias/' . $id . '/edit');
            }
        }

        Db::update('biographies', $data, 'id = :id', ['id' => $id]);
        flash('success', 'Biografía actualizada' . (isset($data['portrait_path']) ? ' (con retrato).' : '.'));
        redirect('/biografias/' . $bio['slug']);
    }

    public static function adminIndex(): void
    {
        Auth::requireRole(['admin']);
        $bios = Db::fetchAll('SELECT * FROM biographies ORDER BY sort_order, full_name');
        view('biographies/admin', [
            'title' => 'Gestionar biografías',
            'bios' => $bios,
        ]);
    }
}
