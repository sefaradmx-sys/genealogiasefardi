/* Museo 3D — Personas ilustres (Three.js r128). Walkable gallery corridor. */
(function () {
  'use strict';
  var root = document.getElementById('m3d'); if (!root) return;
  var main = root.closest('main'); if (main) root.style.setProperty('--m3dtop', Math.round(main.getBoundingClientRect().top + window.scrollY) + 'px');
  var data = []; try { data = JSON.parse(document.getElementById('m3d-data').textContent); } catch (e) {}
  var loading = document.getElementById('m3d-loading');
  function flat() { root.classList.add('is-flat'); loading.classList.add('done'); }
  document.getElementById('m3d-2d').addEventListener('click', function () {
    if (root.classList.contains('is-flat')) location.reload(); else { flat(); this.textContent = 'Ver en 3D'; }
  });
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  function webgl() { try { var c = document.createElement('canvas'); return !!(c.getContext('webgl') || c.getContext('experimental-webgl')); } catch (e) { return false; } }
  if (reduce || !webgl()) { flat(); return; }
  var tries = 0;
  (function wait() { if (window.THREE) start(); else if (tries++ < 80) setTimeout(wait, 100); else flat(); })();

  function start() {
    var T = window.THREE, stage = document.getElementById('m3d-stage'), tip = document.getElementById('m3d-tip');
    var W = 4.2, H = 4.4, GAP = 3.4, START = 3;
    var renderer = new T.WebGLRenderer({ antialias: true });
    renderer.setPixelRatio(Math.min(devicePixelRatio || 1, 2));
    renderer.outputEncoding = T.sRGBEncoding;
    stage.appendChild(renderer.domElement);
    var scene = new T.Scene(); scene.background = new T.Color(0x0c0705); scene.fog = new T.Fog(0x0c0705, 8, 34);
    var cam = new T.PerspectiveCamera(62, 1, 0.1, 100);

    // Layout: list of placements
    var place = [], two = data.length > 1;
    data.forEach(function (sala, si) {
      sala.items.forEach(function (it, i) {
        var side = two ? (si === 0 ? -1 : 1) : (i % 2 ? 1 : -1);
        var idx = two ? i : Math.floor(i / 2);
        place.push({ it: it, side: side, z: -(START + idx * GAP) });
      });
    });
    var maxIdx = place.reduce(function (m, p) { return Math.min(m, p.z); }, -START);
    var LEN = -maxIdx + 8;

    function canvasTex(w, h, draw) { var c = document.createElement('canvas'); c.width = w; c.height = h; draw(c.getContext('2d'), w, h); var t = new T.CanvasTexture(c); t.encoding = T.sRGBEncoding; t.anisotropy = 4; return t; }
    function wrapText(g, txt, x, y, maxW, lh, maxL) { var words = String(txt).split(' '), line = '', n = 0; for (var i = 0; i < words.length; i++) { var t = line + words[i] + ' '; if (g.measureText(t).width > maxW && line) { g.fillText(line.trim(), x, y); y += lh; line = words[i] + ' '; if (++n >= maxL - 1) { line = words.slice(i).join(' '); if (g.measureText(line).width > maxW) { while (g.measureText(line + '…').width > maxW) line = line.slice(0, -1); line += '…'; } break; } } else line = t; } g.fillText(line.trim(), x, y); }

    // Room
    var wallTex = canvasTex(512, 512, function (g, w, h) { g.fillStyle = '#4a2c1a'; g.fillRect(0, 0, w, h); for (var i = 0; i < 4000; i++) { g.fillStyle = 'rgba(' + (Math.random() > .5 ? '255,220,170' : '0,0,0') + ',' + Math.random() * .05 + ')'; g.fillRect(Math.random() * w, Math.random() * h, 2, 2); } g.strokeStyle = 'rgba(201,164,92,.25)'; g.lineWidth = 3; g.strokeRect(20, 20, w - 40, h - 40); });
    wallTex.wrapS = wallTex.wrapT = T.RepeatWrapping; wallTex.repeat.set(LEN / 4, 1);
    var floorTex = canvasTex(512, 512, function (g, w, h) { for (var y = 0; y < 8; y++) for (var x = 0; x < 2; x++) { var l = 18 + Math.random() * 10; g.fillStyle = 'hsl(25,45%,' + l + '%)'; g.fillRect(x * 256 + (y % 2) * 128, y * 64, 256, 64); g.strokeStyle = '#120a05'; g.strokeRect(x * 256 + (y % 2) * 128, y * 64, 256, 64); } });
    floorTex.wrapS = floorTex.wrapT = T.RepeatWrapping; floorTex.repeat.set(2, LEN / 4);
    var wallMat = new T.MeshStandardMaterial({ map: wallTex, roughness: .9 });
    [-1, 1].forEach(function (s) { var m = new T.Mesh(new T.PlaneGeometry(LEN, H), wallMat); m.rotation.y = -s * Math.PI / 2; m.position.set(s * W / 2 * 1, H / 2, -LEN / 2 + 4); scene.add(m);
      var rail = new T.Mesh(new T.BoxGeometry(.05, .12, LEN), new T.MeshStandardMaterial({ color: 0xc9a45c, metalness: .8, roughness: .3 })); rail.position.set(s * (W / 2 - .03), H - .5, -LEN / 2 + 4); scene.add(rail);
      var base = new T.Mesh(new T.BoxGeometry(.08, .3, LEN), new T.MeshStandardMaterial({ color: 0x1c120b })); base.position.set(s * (W / 2 - .04), .15, -LEN / 2 + 4); scene.add(base); });
    var floor = new T.Mesh(new T.PlaneGeometry(W, LEN), new T.MeshStandardMaterial({ map: floorTex, roughness: .55, metalness: .1 })); floor.rotation.x = -Math.PI / 2; floor.position.set(0, 0, -LEN / 2 + 4); scene.add(floor);
    var ceil = new T.Mesh(new T.PlaneGeometry(W, LEN), new T.MeshStandardMaterial({ color: 0x1a100a })); ceil.rotation.x = Math.PI / 2; ceil.position.set(0, H, -LEN / 2 + 4); scene.add(ceil);
    // end wall with title
    var endTex = canvasTex(1024, 1024, function (g, w, h) { g.fillStyle = '#3a2516'; g.fillRect(0, 0, w, h); g.fillStyle = '#c9a45c'; g.textAlign = 'center'; g.font = '40px Georgia'; g.fillText('GENEALOGÍA SEFARDÍ', w / 2, 420); g.font = 'italic 80px Georgia'; g.fillStyle = '#e8cf94'; g.fillText('Personas ilustres', w / 2, 530); g.font = '32px Georgia'; g.fillStyle = '#c9a45c'; g.fillText('Fin de la galería', w / 2, 610); });
    var end = new T.Mesh(new T.PlaneGeometry(W, H), new T.MeshStandardMaterial({ map: endTex })); end.position.set(0, H / 2, -LEN + 4); scene.add(end);
    // ceiling lamps
    for (var z = 0; z > -LEN + 4; z -= 4) { var lamp = new T.Mesh(new T.BoxGeometry(1.2, .04, .3), new T.MeshBasicMaterial({ color: 0xffe2a8 })); lamp.position.set(0, H - .02, z); scene.add(lamp); }

    scene.add(new T.HemisphereLight(0xffe7c2, 0x2b1b10, .55));
    var amb = new T.AmbientLight(0xffd9a0, .25); scene.add(amb);
    var camLight = new T.PointLight(0xffdca0, 1.1, 12, 1.6); scene.add(camLight);

    // Sala banners
    if (two) data.forEach(function (sala, si) { var s = si === 0 ? -1 : 1;
      var t = canvasTex(1024, 200, function (g, w, h) { g.fillStyle = '#1c120b'; g.fillRect(0, 0, w, h); g.strokeStyle = '#c9a45c'; g.lineWidth = 6; g.strokeRect(8, 8, w - 16, h - 16); g.textAlign = 'center'; g.fillStyle = '#c9a45c'; g.font = '30px Georgia'; g.fillText('S A L A', w / 2, 70); g.fillStyle = '#e8cf94'; g.font = 'italic 64px Georgia'; g.fillText(sala.name, w / 2, 150); });
      var b = new T.Mesh(new T.PlaneGeometry(2.6, .5), new T.MeshBasicMaterial({ map: t })); b.rotation.y = -s * Math.PI / 2; b.position.set(s * (W / 2 - .02), H - .25, -1.2); scene.add(b); });

    // Frames
    var hits = [], loader = new T.TextureLoader(); loader.setCrossOrigin('anonymous');
    var goldMat = new T.MeshStandardMaterial({ color: 0xc9a45c, metalness: .85, roughness: .32, emissive: 0x2a1a05 });
    var pw = 1.25, ph = 1.6, fy = 1.95;
    place.forEach(function (p) {
      var it = p.it, g = new T.Group();
      // frame (4 bars + inner bevel)
      var fw = .16;
      [[0, ph / 2 + fw / 2, pw + fw * 2, fw], [0, -ph / 2 - fw / 2, pw + fw * 2, fw], [-pw / 2 - fw / 2, 0, fw, ph], [pw / 2 + fw / 2, 0, fw, ph]].forEach(function (b) { var m = new T.Mesh(new T.BoxGeometry(b[2], b[3], .12), goldMat); m.position.set(b[0], b[1], .06); g.add(m); });
      var mono = canvasTex(400, 512, function (c, w, h) { var gr = c.createRadialGradient(w / 2, h / 2, 20, w / 2, h / 2, 320); gr.addColorStop(0, '#4a2f1b'); gr.addColorStop(1, '#140d08'); c.fillStyle = gr; c.fillRect(0, 0, w, h); c.strokeStyle = '#c9a45c'; c.lineWidth = 3; c.strokeRect(24, 24, w - 48, h - 48); c.fillStyle = '#e8cf94'; c.textAlign = 'center'; c.font = '150px Georgia'; c.fillText(it.initials || '·', w / 2, h / 2 + 40); c.font = 'italic 22px Georgia'; c.fillStyle = '#c9a45c'; c.fillText(it.img ? (it.escudo ? 'escudo familiar' : 'cargando retrato…') : 'Retrato en búsqueda', w / 2, h - 60); });
      var canvasMat = new T.MeshStandardMaterial({ map: mono, roughness: .8 });
      var pic = new T.Mesh(new T.PlaneGeometry(pw, ph), canvasMat); pic.position.z = .02; g.add(pic);
      if (it.img) loader.load(it.img, function (tx) { tx.encoding = T.sRGBEncoding; var im = tx.image, r = im.width / im.height, tr = pw / ph; tx.repeat.set(r > tr ? tr / r : 1, r > tr ? 1 : r / tr); tx.offset.set((1 - tx.repeat.x) / 2, (1 - tx.repeat.y) / 2); canvasMat.map = tx; canvasMat.needsUpdate = true; });
      // cartela
      var lab = canvasTex(512, 220, function (c, w, h) { c.fillStyle = '#f3e7cf'; c.fillRect(0, 0, w, h); c.fillStyle = '#2b1b10'; c.textAlign = 'center'; c.font = 'bold 34px Georgia'; wrapText(c, it.name, w / 2, 50, w - 30, 38, 2); c.fillStyle = '#6e5220'; c.font = '26px Georgia'; c.fillText(it.lifespan || '', w / 2, 140); c.font = 'italic 22px Georgia'; c.fillStyle = '#3a2516'; wrapText(c, it.subtitle || '', w / 2, 178, w - 30, 24, 2); });
      var plate = new T.Mesh(new T.PlaneGeometry(1.1, .47), new T.MeshStandardMaterial({ map: lab })); plate.position.set(0, -ph / 2 - .55, .01); g.add(plate);
      // glow spot on wall
      var glow = canvasTex(256, 256, function (c, w, h) { var gr = c.createRadialGradient(w / 2, h / 3, 5, w / 2, h / 2, 128); gr.addColorStop(0, 'rgba(255,220,160,.35)'); gr.addColorStop(1, 'rgba(255,220,160,0)'); c.fillStyle = gr; c.fillRect(0, 0, w, h); });
      var gm = new T.Mesh(new T.PlaneGeometry(2.6, 3.4), new T.MeshBasicMaterial({ map: glow, transparent: true, depthWrite: false })); gm.position.set(0, .1, -.005); g.add(gm);
      g.rotation.y = -p.side * Math.PI / 2; g.position.set(p.side * (W / 2 - .03), fy, p.z); scene.add(g);
      pic.userData = it; plate.userData = it; hits.push(pic, plate); g.children.forEach(function (ch) { if (ch.geometry && ch.geometry.type === 'BoxGeometry') { ch.userData = it; hits.push(ch); } });
      p.group = g;
    });
    if (!place.length) { var et = canvasTex(1024, 256, function (c, w, h) { c.fillStyle = '#e8cf94'; c.textAlign = 'center'; c.font = 'italic 56px Georgia'; c.fillText('Sala en preparación', w / 2, 140); }); var em = new T.Mesh(new T.PlaneGeometry(3, .75), new T.MeshBasicMaterial({ map: et, transparent: true })); em.position.set(0, 2, -5); scene.add(em); }

    // Navigation state
    var minZ = -LEN + 6.5, maxZ = 3, posZ = 3, tgtZ = 3, yaw = 0, tgtYaw = 0, pitch = 0, tgtPitch = 0;
    function clampZ(z) { return Math.max(minZ, Math.min(maxZ, z)); }
    function step(d) { tgtZ = clampZ(tgtZ - d * GAP); }
    document.getElementById('m3d-fwd').onclick = function () { step(1); };
    document.getElementById('m3d-back').onclick = function () { step(-1); };
    stage.addEventListener('wheel', function (e) { e.preventDefault(); tgtZ = clampZ(tgtZ - e.deltaY * .008); }, { passive: false });
    window.addEventListener('keydown', function (e) { if (/INPUT|TEXTAREA/.test(e.target.tagName)) return;
      if (e.key === 'ArrowUp' || e.key === 'w') { step(1); e.preventDefault(); } else if (e.key === 'ArrowDown' || e.key === 's') { step(-1); e.preventDefault(); }
      else if (e.key === 'ArrowLeft' || e.key === 'a') { tgtYaw = Math.min(1.4, tgtYaw + .35); e.preventDefault(); } else if (e.key === 'ArrowRight' || e.key === 'd') { tgtYaw = Math.max(-1.4, tgtYaw - .35); e.preventDefault(); } });
    var down = null, moved = 0, pointers = {};
    stage.addEventListener('pointerdown', function (e) { stage.setPointerCapture(e.pointerId); down = { x: e.clientX, y: e.clientY, yaw: tgtYaw, pitch: tgtPitch, z: tgtZ, touch: e.pointerType !== 'mouse' }; moved = 0; });
    stage.addEventListener('pointermove', function (e) {
      if (!down) { hover(e); return; }
      var dx = e.clientX - down.x, dy = e.clientY - down.y; moved = Math.max(moved, Math.abs(dx) + Math.abs(dy));
      tgtYaw = Math.max(-1.5, Math.min(1.5, down.yaw + dx * .005));
      if (down.touch) tgtZ = clampZ(down.z + dy * .03); // swipe up = walk forward
      else tgtPitch = Math.max(-.35, Math.min(.35, down.pitch + dy * .002));
    });
    stage.addEventListener('pointerup', function (e) { var d = down; down = null; if (d && moved < 8) click(e); });
    stage.addEventListener('pointercancel', function () { down = null; });
    var ray = new T.Raycaster(), mv = new T.Vector2();
    function pick(e) { var r = stage.getBoundingClientRect(); mv.x = (e.clientX - r.left) / r.width * 2 - 1; mv.y = -(e.clientY - r.top) / r.height * 2 + 1; ray.setFromCamera(mv, cam); var h = ray.intersectObjects(hits)[0]; return h && h.distance < 14 ? h.object.userData : null; }
    function hover(e) { var it = pick(e); stage.style.cursor = it ? 'pointer' : ''; if (it) { tip.innerHTML = ''; var s = document.createElement('strong'); s.textContent = it.name; tip.appendChild(s); tip.appendChild(document.createTextNode((it.lifespan ? it.lifespan + ' · ' : '') + 'Pulse para leer la biografía')); var r = root.getBoundingClientRect(); tip.style.left = (e.clientX - r.left + 14) + 'px'; tip.style.top = (e.clientY - r.top + 14) + 'px'; tip.style.opacity = 1; } else tip.style.opacity = 0; }
    function click(e) { var it = pick(e); if (it && it.href) { root.style.transition = 'opacity .35s'; root.style.opacity = .3; location.href = it.href; } }

    function resize() { var w = stage.clientWidth, h = stage.clientHeight; renderer.setSize(w, h, false); cam.aspect = w / h; cam.fov = w < 700 ? 75 : 62; cam.updateProjectionMatrix(); }
    window.addEventListener('resize', resize); resize();
    var t0 = performance.now();
    (function loop(t) {
      posZ += (tgtZ - posZ) * .08; yaw += (tgtYaw - yaw) * .1; pitch += (tgtPitch - pitch) * .1;
      var bob = Math.sin(t / 900) * .015;
      cam.position.set(0, 1.75 + bob, posZ); cam.rotation.set(pitch, yaw, 0, 'YXZ');
      camLight.position.set(0, 2.6, posZ - 1.5);
      renderer.render(scene, cam);
      requestAnimationFrame(loop);
    })(t0);
    // entrance: glide in
    tgtZ = clampZ(-0.5);
    setTimeout(function () { loading.classList.add('done'); }, 350);
  }
})();
