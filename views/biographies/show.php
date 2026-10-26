<?php $isIlustre = ($bio['category'] ?? '') === 'ilustre'; ?>
<article class="bio-page<?= $isIlustre ? ' bio-ilustre' : '' ?>">
  <header class="bio-hero">
    <?php if (!empty($bio['portrait_path'])): ?>
      <figure class="bio-portrait-frame"><img class="bio-portrait" src="/media/biography/<?= (int)$bio['id'] ?>" alt="Retrato de <?= e($bio['full_name']) ?>"></figure>
    <?php elseif ($isIlustre): ?>
      <?php $esc = (($bio['family_group'] ?? 'garcia-garza') === 'garza-garcia') ? 'escudo-garza-garcia' : 'escudo-garcia-garza'; ?>
      <figure class="bio-portrait-frame bio-escudo"><img class="bio-portrait" src="/public/assets/img/<?= $esc ?>.png" alt="Escudo de la familia <?= ($esc === 'escudo-garza-garcia') ? 'Garza García' : 'García Garza' ?>"></figure>
    <?php elseif (false): ?>
      <?php $nm = preg_replace('/^(Lic|Dr|Cap|Ing)\.\s+/u', '', (string)$bio['full_name']); $pp = preg_split('/\s+/u', trim($nm)); ?>
      <div class="bio-portrait-frame bio-portrait-empty" aria-hidden="true"><span><?= e(mb_strtoupper(mb_substr($pp[0] ?? '', 0, 1) . mb_substr($pp[count($pp) > 2 ? count($pp) - 2 : 1] ?? '', 0, 1))) ?></span></div>
    <?php endif; ?>
    <div class="bio-hero-text">
      <p class="eyebrow"><?= $isIlustre ? 'Personas ilustres' : 'Biografía pública' ?></p>
      <h1><?= e($bio['full_name']) ?></h1>
      <?php if ($bio['subtitle']): ?><p class="bio-sub"><?= e($bio['subtitle']) ?></p><?php endif; ?>
      <?php if ($bio['lifespan_label']): ?><p class="bio-dates"><?= e($bio['lifespan_label']) ?></p><?php endif; ?>
      <?php if ($bio['summary']): ?><p class="lede"><?= e($bio['summary']) ?></p><?php endif; ?>
      <?php if ($isIlustre): ?>
        <nav class="bio-toc"><a href="#vida">Vida</a><a href="#obra">Obra y cargos</a><a href="#legado">Legado</a><a href="#fuentes">Fuentes</a></nav>
      <?php endif; ?>
      <?php if (!empty($sources)): ?>
        <p class="proof-banner"><strong><?= count($sources) ?> pruebas documentales</strong> enlazadas desde el archivo · <a href="#fuentes-bio">ver listado</a></p>
      <?php endif; ?>
    </div>
  </header>

  <?php
  $bodyHtml = (string)$bio['body_html'];
  if (!empty($sources)) {
      $n = count($sources);
      $callout = '<div class="fuentes-callout"><p><strong>' . $n . ' fuentes documentales del archivo</strong> respaldan esta biografía (actas, matrimonios, testamentos y pruebas de filiación). '
          . 'Las referencias de arriba son sólo las principales; el listado completo está en <a href="#fuentes-bio">Pruebas y documentos del archivo (' . $n . ')</a>.</p></div>';
      if (preg_match('~<ol class="bio-sources">.*?</ol>~su', $bodyHtml)) {
          $bodyHtml = preg_replace('~(<ol class="bio-sources">.*?</ol>)~su', '$1' . $callout, $bodyHtml, 1);
      } else {
          $bodyHtml .= '<h2 id="fuentes">Fuentes</h2>' . $callout;
      }
  }
  ?>
  <div class="card bio-body">
    <?= $bodyHtml /* trusted seed / admin HTML */ ?>
  </div>

<?php if (!empty($sources) || !$isIlustre): ?>
  <div class="card" id="fuentes-bio">
    <h2>Fuentes · Pruebas y documentos del archivo<?= !empty($sources) ? ' (' . count($sources) . ')' : '' ?></h2>
    <?php if (empty($sources)): ?>
      <p class="empty">Sin fuentes enlazadas aún.</p>
    <?php else: ?>
      <p class="meta"><?= count($sources) ?> documentos citables. Pulse para abrir cada uno en el archivo (vista previa).</p>
      <ol class="rel-list proof-list fuentes-full">
        <?php foreach ($sources as $s): ?>
          <li>
            <?php if (!empty($s['source_id']) && !empty($s['source_title'])): ?>
              <?php if (is_public_visibility($s['visibility'] ?? 'archive')): ?>
                <a href="/archivo/<?= (int)$s['source_id'] ?>"><strong><?= e($s['source_title']) ?></strong></a>
              <?php else: ?>
                <strong><?= e($s['source_title']) ?></strong>
              <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($s['citation_text'])): ?>
              <div class="hint"><?= e($s['citation_text']) ?></div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </div>
  <?php endif; ?>


  <?php
  $pdfUrl = null;
  foreach ($sources as $s) {
      if (!empty($s['media_path']) && preg_match('/\.pdf$/i', $s['media_path']) && !empty($s['source_id'])) {
          $pdfUrl = '/media/source/' . (int)$s['source_id'];
          break;
      }
  }
  ?>
  <?php if ($bio['slug'] === 'francisco-javier-garcia-gaona'): ?>
  <div class="card">
    <h2>Documento fuente de esta biografía</h2>
    <p>PDF del autor con tablas de filiación paterna y materna, biografía personal y aclaraciones.</p>
    <?php if ($pdfUrl): ?>
      <?php render_media_viewer($pdfUrl, 'document.pdf', 'Documento fuente'); ?>
      <p class="hint" style="margin-top:.75rem"><a href="/archivo/4">Abrir ficha en el archivo público</a></p>
    <?php else: ?>
      <p class="empty">Consulte el PDF en <a href="/archivo/4">Archivo #4</a>.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($lineage)): ?>
  <section class="card lineage-section">
    <h2>Filiación documentada</h2>
    <p class="meta">Línea de ascendencia con pruebas · etiquetas <span class="badge probado">probado</span> / <span class="badge pendiente">pendiente</span> según el estado real de la evidencia (sin inventar fuerza).</p>
    <ol class="lineage-chain">
      <?php foreach ($lineage as $step): ?>
        <li class="lineage-step">
          <div class="lineage-head">
            <span class="badge <?= e($step['status']) ?>"><?= e(proof_status_label($step['status'])) ?></span>
            <?php if ($step['generation_label']): ?><span class="meta"><?= e($step['generation_label']) ?></span><?php endif; ?>
          </div>
          <strong><?= e($step['person_name']) ?></strong>
          <?php if ($step['relationship_label']): ?><div class="meta"><?= e($step['relationship_label']) ?></div><?php endif; ?>
          <?php if ($step['claim_text']): ?><p><?= e($step['claim_text']) ?></p><?php endif; ?>
          <?php if ($step['notes']): ?><p class="hint"><?= e($step['notes']) ?></p><?php endif; ?>
          <div class="lineage-proofs">
            <?php if (!empty($step['archive_source_id'])): ?>
              <a class="btn btn-sm" href="/archivo/<?= (int)$step['archive_source_id'] ?>">Ver prueba</a>
            <?php endif; ?>
            <?php foreach ($step['proof_sources'] ?? [] as $ps): ?>
              <?php if (($ps['id'] ?? 0) == ($step['archive_source_id'] ?? -1)) continue; ?>
              <?php if (is_public_visibility($ps['visibility'] ?? '')): ?>
                <a class="btn btn-sm btn-outline" href="/archivo/<?= (int)$ps['id'] ?>"><?= e(mb_substr($ps['title'], 0, 40)) ?></a>
              <?php elseif (Auth::check()): ?>
                <a class="btn btn-sm btn-outline" href="/sources/<?= (int)$ps['id'] ?>"><?= e(mb_substr($ps['title'], 0, 40)) ?></a>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </section>
  <?php endif; ?>

  <div class="card cite-block">
    <h2>Cómo citar esta página</h2>
    <blockquote>
      <?= e($bio['preferred_citation'] ?: ($bio['full_name'] . ', Genealogía Sefardí.')) ?>
      URL: <?= e($pageUrl) ?>
    </blockquote>
    <?php if (!empty($sources)): ?><p class="hint">Fuentes: <?= count($sources) ?> documentos del archivo público de Genealogía Sefardí (ver <a href="#fuentes-bio">listado completo</a>). Al citar un documento concreto, use su ficha en /archivo/{número}.</p><?php endif; ?>
    <?php if (Auth::canEdit()): ?>
      <p><a class="btn btn-sm btn-outline" href="/panel/biografias/<?= (int)$bio['id'] ?>/edit">Editar biografía</a></p>
    <?php endif; ?>
  </div>
</article>
<style>
.fuentes-callout{margin:1rem 0 0;padding:.85rem 1rem;border-left:4px solid #c4a15a;background:#f3e7cf;color:#2a1a10;border-radius:4px}
.fuentes-callout a{color:#2a1a10;font-weight:600;text-decoration:underline}
.bio-escudo img{object-fit:contain;background:#f3e7cf}
ol.fuentes-full{padding-left:1.6rem}ol.fuentes-full li{margin:.35rem 0}
</style>
