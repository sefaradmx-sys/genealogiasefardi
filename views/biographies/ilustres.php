<?php
$ini = function (string $n): string {
    $n = preg_replace('/^(Lic|Dr|Cap|Ing)\.\s+/u', '', $n);
    $p = preg_split('/\s+/u', trim($n));
    return mb_strtoupper(mb_substr($p[0] ?? '', 0, 1) . mb_substr($p[count($p) > 2 ? count($p) - 2 : 1] ?? '', 0, 1));
};
$museo = [];
foreach ($groups as $key => $g) {
    $items = [];
    foreach ($g['bios'] as $b) {
        $items[] = [
            'name' => (string)$b['full_name'],
            'initials' => $ini((string)$b['full_name']),
            'lifespan' => (string)($b['lifespan_label'] ?? ''),
            'subtitle' => (string)($b['subtitle'] ?? ''),
            'img' => !empty($b['portrait_path']) ? '/media/biography/' . (int)$b['id'] : ('/public/assets/img/' . (($b['family_group'] ?? $key) === 'garza-garcia' ? 'escudo-garza-garcia' : 'escudo-garcia-garza') . '.png'),
            'escudo' => empty($b['portrait_path']),
            'href' => '/biografias/' . $b['slug'],
        ];
    }
    $museo[] = ['key' => $key, 'name' => 'Familia ' . $g['name'], 'items' => $items];
}
$v = 'm3d-2610c';
?>
<link rel="stylesheet" href="/public/assets/museo-3d.css?v=<?= $v ?>">
<div class="m3d" id="m3d">
  <div class="m3d-stage" id="m3d-stage" aria-hidden="true"></div>
  <div class="m3d-loading" id="m3d-loading"><div class="m3d-spin"></div><p>Abriendo la galería…</p></div>
  <header class="m3d-hud">
    <p class="eyebrow">Genealogía Sefardí · Museo</p>
    <h1>Personas ilustres</h1>
    <nav class="m3d-tabs">
      <a href="/personas-ilustres" class="<?= !$family ? 'on' : '' ?>">Todas las salas</a>
      <a href="/personas-ilustres/garcia-garza" class="<?= $family === 'garcia-garza' ? 'on' : '' ?>">García Garza</a>
      <a href="/personas-ilustres/garza-garcia" class="<?= $family === 'garza-garcia' ? 'on' : '' ?>">Garza García</a>
    </nav>
  </header>
  <div class="m3d-tip" id="m3d-tip"></div>
  <div class="m3d-help">Arrastre para mirar · rueda, flechas o deslice para caminar · pulse un retrato para abrir la biografía
    <button type="button" id="m3d-2d">Ver como lista</button></div>
  <div class="m3d-nav"><button type="button" id="m3d-back" aria-label="Retroceder">‹</button><button type="button" id="m3d-fwd" aria-label="Avanzar">›</button></div>

  <!-- Accessible 2D wall: used for reduced-motion, no-WebGL, and screen readers -->
  <div class="m3d-flat" id="m3d-flat">
    <?php foreach ($museo as $sala): ?>
    <section class="m3d-sala">
      <h2><span>Sala</span> <?= e($sala['name']) ?></h2>
      <?php if (!$sala['items']): ?><p class="empty">Sala en preparación.</p><?php endif; ?>
      <div class="m3d-wall">
        <?php foreach ($sala['items'] as $it): ?>
        <a class="m3d-obra" href="<?= e($it['href']) ?>">
          <span class="m3d-marco"><?php if ($it['img']): ?><img src="<?= e($it['img']) ?>" alt="<?= !empty($it['escudo']) ? 'Escudo familiar' : 'Retrato' ?> de <?= e($it['name']) ?>" loading="lazy"><?php else: ?><span class="m3d-mono"><?= e($it['initials']) ?></span><?php endif; ?></span>
          <span class="m3d-cedula"><strong><?= e($it['name']) ?></strong><?php if ($it['lifespan']): ?><small><?= e($it['lifespan']) ?></small><?php endif; ?><?php if ($it['subtitle']): ?><em><?= e($it['subtitle']) ?></em><?php endif; ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endforeach; ?>
    <p class="m3d-foot">¿Conoce un retrato de época? Compártalo para completar la galería. <a href="/biografias">Ver todas las biografías</a></p>
  </div>
</div>
<script type="application/json" id="m3d-data"><?= json_encode($museo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js" defer></script>
<script src="/public/assets/museo-3d.js?v=<?= $v ?>" defer></script>
