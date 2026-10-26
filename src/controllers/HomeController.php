<?php
declare(strict_types=1);

final class HomeController
{
    public static function index(): void
    {
        if (Auth::check()) {
            redirect('/panel');
        }
        $featuredBios = [];
        try {
            $featuredBios = Db::fetchAll(
                'SELECT id, slug, full_name, subtitle, lifespan_label, summary, portrait_path
                 FROM biographies WHERE published = 1 AND is_featured = 1
                 ORDER BY sort_order, full_name LIMIT 6'
            );
        } catch (Throwable $e) {
            // table may not exist until upgrade
        }
        $archiveCount = 0;
        try {
            $archiveCount = (int)Db::fetch(
                "SELECT COUNT(*) AS c FROM sources
                 WHERE visibility IN ('shared','archive') AND media_path IS NOT NULL AND media_path <> ''"
            )['c'];
        } catch (Throwable $e) {
        }
        view('home/landing', [
            'title' => 'Inicio',
            'public' => true,
            'featuredBios' => $featuredBios,
            'archiveCount' => $archiveCount,
        ]);
    }
}
