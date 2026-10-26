<?php
/** Biografías de personajes ilustres (García / Garza García) — octubre 2026. Idempotente. */
function ilustres_2026_10(): array
{
    $fig = function (string $src, string $alt, string $cap, string $credit, string $creditUrl): string {
        return '<figure class="bio-figure"><img src="' . $src . '" alt="' . htmlspecialchars($alt) . '" loading="lazy">'
            . '<figcaption>' . $cap . ' <span class="credit">Imagen: <a href="' . $creditUrl . '" rel="noopener" target="_blank">' . $credit . '</a></span></figcaption></figure>';
    };
    $src = function (array $links): string {
        $h = '<h2 id="fuentes">Fuentes</h2><ol class="bio-sources">';
        foreach ($links as [$t, $u]) $h .= '<li><a href="' . $u . '" rel="noopener" target="_blank">' . $t . '</a></li>';
        return $h . '</ol>';
    };
    $bios = [];

    // 1) Santos Javier Garza García
    $bios[] = [
        'slug' => 'santos-javier-garza-garcia',
        'family' => 'garza-garcia',
        'old_slugs' => [],
        'full_name' => 'Santos Javier Garza García',
        'subtitle' => 'Presidente municipal de Anáhuac, Nuevo León (2009–2012)',
        'lifespan_label' => 'Anáhuac, Nuevo León',
        'summary' => 'Alcalde de Anáhuac, Nuevo León, durante la administración 2009–2012. Encabezó la evacuación de la cabecera municipal en julio de 2010, cuando el desfogue de la presa Venustiano Carranza obligó a desalojar a unas 18 mil personas.',
        'sort_order' => 1,
        'body' => '<p class="bio-dropcap"><strong>Santos Javier Garza García</strong> es un servidor público de Anáhuac, Nuevo León, municipio fronterizo del norte del estado. Su trayectoria está ligada a la vida política y comunitaria de su tierra, a la que sirvió como presidente municipal en uno de los periodos más difíciles de su historia reciente.</p>'
            . '<h2 id="vida">Vida pública</h2>'
            . '<p>En 2003 participó en los debates municipales organizados por la Comisión Estatal Electoral de Nuevo León como candidato del <strong>Partido Acción Nacional (PAN)</strong> a la alcaldía de Anáhuac. En la elección estatal de 2009 resultó electo presidente municipal con alrededor del 61&nbsp;% de los votos, y encabezó el ayuntamiento durante la administración 2009–2012.</p>'
            . '<h2 id="obra">Cargos y gestión</h2>'
            . '<ul class="bio-timeline">'
            . '<li><span>2003</span> Candidato del PAN a la presidencia municipal de Anáhuac (memoria del proceso electoral de la CEE Nuevo León).</li>'
            . '<li><span>2009</span> Electo presidente municipal de Anáhuac.</li>'
            . '<li><span>2010</span> Ante las lluvias extraordinarias de la temporada de huracanes (huracán Alex) y la apertura de las compuertas de la presa Venustiano Carranza sobre el río Salado, coordinó el desalojo de la cabecera municipal: más de 4 mil familias dejaron sus viviendas para ponerse a salvo.</li>'
            . '<li><span>2010</span> Representó a Anáhuac en la reunión de alcaldes fronterizos de México y Estados Unidos sobre seguridad y cruces internacionales.</li>'
            . '<li><span>2012</span> Como presidente municipal rindió ante el H. Congreso de Nuevo León los informes de avance de gestión financiera del municipio.</li>'
            . '</ul>'
            . $fig('https://upload.wikimedia.org/wikipedia/commons/b/bc/Parroquia_de_Ntra_Se%C3%B1ora_de_Guadalupe.jpg', 'Parroquia de Nuestra Señora de Guadalupe, Anáhuac, Nuevo León', 'Parroquia de Nuestra Señora de Guadalupe, Anáhuac, Nuevo León (imagen de contexto; no es retrato).', 'Daleeex, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Parroquia_de_Ntra_Se%C3%B1ora_de_Guadalupe.jpg')
            . '<h2 id="legado">Legado</h2>'
            . '<p>Su nombre queda asociado a la respuesta civil ante la inundación de 2010, cuando la prioridad fue proteger la vida de los anahuacenses. Pertenece a la familia <strong>Garza García</strong> del noreste mexicano, cuyo tronco común se remonta a los pobladores fundadores del Nuevo Reino de León.</p>'
            . '<p class="bio-note">Nota editorial: la filiación genealógica específica de don Santos Javier con las líneas documentadas en este archivo está en investigación; no se afirma parentesco directo sin acta que lo respalde. No se publica retrato por no contar con una fotografía de uso libre.</p>'
            . $src([
                ['Comisión Estatal Electoral Nuevo León — Memoria del proceso electoral 2003 (debates municipales)', 'https://portalanterior.ieepcnl.mx/memorias/2003/2/t35.htm'],
                ['Wikipedia — Elecciones estatales de Nuevo León de 2009 (resultados Anáhuac)', 'https://es.wikipedia.org/wiki/Elecciones_estatales_de_Nuevo_Le%C3%B3n_de_2009'],
                ['La Jornada (7 jul 2010) — Desfogue de presa obliga a 18 mil a salir del municipio de Anáhuac', 'https://www.jornada.com.mx/2010/07/07/estados/032n1est'],
                ['AFN Tijuana — Seguridad y cruces fronterizos en reunión de alcaldes', 'https://afntijuana.info/informacion_general/7154_seguridad_y_cruces_fronterizos_en_reunion_de_alcaldes'],
                ['H. Congreso de Nuevo León — Sesión ordinaria núm. 330, LXXII Legislatura (oficio 302/2012)', 'https://www.hcnl.gob.mx/trabajo_legislativo/actas-en-sesiones/num-330-sesion-ordinaria-de-la-lxxii-legislatura-del-segundo-periodo-ordinario-de-sesiones-del-terce/'],
            ]),
        'portrait' => null,
    ];

    // 2) Dionisio García Fuentes
    $bios[] = [
        'slug' => 'dionisio-garcia-fuentes',
        'family' => 'garcia-garza',
        'old_slugs' => ['dionisio-garcia-fuente'],
        'full_name' => 'Dr. Dionisio García Fuentes',
        'subtitle' => 'Médico, educador y filósofo positivista · Saltillo, Coahuila',
        'lifespan_label' => '1855 – 1938',
        'summary' => 'Médico cirujano, presidente municipal de Saltillo (1887) y dos veces director del Ateneo Fuente. Conocido en Coahuila como el «Padre del Positivismo»; autor de «La integridad de la conciencia».',
        'sort_order' => 3,
        'body' => '<p class="bio-dropcap"><strong>Dionisio García Fuentes</strong> nació en Saltillo, Coahuila, el 9 de noviembre de 1855<sup>*</sup> y murió en la misma ciudad el 7 de febrero de 1938. Médico de formación y maestro por vocación, fue una de las inteligencias más influyentes de la vida educativa coahuilense entre los siglos XIX y XX.</p>'
            . '<h2 id="vida">Vida</h2>'
            . '<p>Se tituló de médico cirujano en la Ciudad de México en mayo de 1877. De regreso en su ciudad natal combinó el ejercicio de la medicina con la cátedra y el servicio público: en 1887 fue presidente municipal de Saltillo. En 1905 aspiró a la gubernatura de Coahuila con el respaldo de los clubes democráticos «Benito Juárez», promovidos por Francisco I. Madero.</p>'
            . '<h2 id="obra">Obra y cargos</h2>'
            . '<ul class="bio-timeline">'
            . '<li><span>1877</span> Médico cirujano titulado en la Ciudad de México.</li>'
            . '<li><span>1887</span> Presidente municipal de Saltillo.</li>'
            . '<li><span>1893–1895</span> Director del Ateneo Fuente; bajo su dirección se inauguró la biblioteca del plantel el 16 de septiembre de 1894, hoy biblioteca «José García Rodríguez».</li>'
            . '<li><span>1909–1913</span> Director del Ateneo Fuente por segunda ocasión; participó en la reforma de su plan de estudios.</li>'
            . '<li><span>Puebla</span> Director de la Escuela Normal y presidente del Consejo de Salubridad del estado.</li>'
            . '<li><span>1918–1920</span> Vocal del Consejo Superior de Salubridad y vicepresidente del Departamento de Higiene Escolar del Distrito Federal.</li>'
            . '<li><span>1921–1923</span> Catedrático de Biología en la Escuela Nacional Preparatoria; decano de la Facultad de Medicina.</li>'
            . '</ul>'
            . $fig('https://upload.wikimedia.org/wikipedia/commons/8/8c/Ateneo_Fuente_1895.jpg', 'Antiguo edificio del Ateneo Fuente, Saltillo, c. 1895', 'Antiguo edificio del Ateneo Fuente, Saltillo, hacia 1895, época de la primera dirección del Dr. García Fuentes. Fototeca del Archivo Municipal de Saltillo.', 'Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Ateneo_Fuente_1895.jpg')
            . '<h2 id="legado">Legado</h2>'
            . '<p>Difusor en Coahuila de la filosofía positivista de Comte y Gabino Barreda, es recordado como el <strong>«Padre del Positivismo»</strong> en el estado. Su ensayo <em>La integridad de la conciencia</em> sintetiza ese pensamiento. Descansa en el Panteón de Santiago de Saltillo, donde su tumba ostenta un busto de mármol con una sola palabra por epitafio: «Positivismo». Una escuela primaria de Saltillo lleva su nombre.</p>'
            . '<p class="bio-note">* Algunas fuentes educativas de Coahuila registran el nacimiento en 1854. Retrato de época con leyenda impresa «Doctor Dionisio García Fuentes» (fuente secundaria de aniversario de muerte, 1938). Se agrupa en la familia García Garza por su apellido García; su filiación directa con Lorenzo García y Juana de la Garza está en investigación.</p>'
            . $src([
                ['La Otra Plana — «Lo que no debemos olvidar: Dionisio García Fuentes»', 'https://laotraplana.mx/agenda-politica-130/'],
                ['El Mañana — columna «Positivismo» (busto y epitafio en el Panteón de Santiago)', 'https://www.elmanana.com/opinion/columnas/positivismo-5625923.html'],
                ['Vanguardia — El hombre que dejó su vida en el Ateneo Fuente de Saltillo', 'https://vanguardia.com.mx/coahuila/el-hombre-que-dejo-su-vida-en-el-ateneo-fuente-de-saltillo-YK6084076'],
                ['SEDU Coahuila — Breves de la historia de la educación en Coahuila (marzo 2014)', 'https://aprenderly.com/doc/3473796/marzo'],
            ]),
        'portrait' => 'storage/uploads/bios/portraits/dionisio-garcia-fuentes.jpg',
    ];

    // 3) Antonio García Carrillo
    $bios[] = [
        'slug' => 'antonio-garcia-carrillo',
        'family' => 'garcia-garza',
        'old_slugs' => ['antonio-garcia-garrido'],
        'full_name' => 'Lic. Antonio García Carrillo',
        'subtitle' => 'Abogado, periodista y gobernador de Coahuila (1874–1879)',
        'lifespan_label' => '1842 – 1891',
        'summary' => 'Abogado saltillense, discípulo de Sebastián Lerdo de Tejada, impulsor de la ratificación de la independencia de Coahuila respecto de Nuevo León (1868), cofundador del Ateneo Fuente y gobernador del estado. El Teatro García Carrillo de Saltillo honra su nombre.',
        'sort_order' => 2,
        'body' => '<p class="bio-dropcap"><strong>Antonio García Carrillo</strong> nació en Saltillo el 17 de febrero de 1842, hijo del comerciante y agricultor Antonio García de León y de doña Fernanda Carrillo, en una familia de siete hijos. Murió en su ciudad natal en 1891.</p>'
            . '<h2 id="vida">Vida</h2>'
            . '<p>Con apenas quince años acompañó a su hermano mayor Dionisio a un mitin en la Alameda de Saltillo contra la anexión de Coahuila a Nuevo León decretada por Santiago Vidaurri; su padre los envió por seguridad a San Luis Potosí. Tras pasar por Zacatecas llegó a la Ciudad de México en enero de 1861 e ingresó al Colegio de San Ildefonso bajo la tutoría de <strong>Sebastián Lerdo de Tejada</strong>. Presentó su examen profesional el 6 de marzo de 1866 y, no conforme con un título firmado bajo el Imperio, viajó a Chihuahua para recibir uno firmado por el presidente Benito Juárez.</p>'
            . '<h2 id="obra">Obra y cargos</h2>'
            . '<ul class="bio-timeline">'
            . '<li><span>1867</span> Ministro fiscal del Tribunal Superior de Justicia, secretario de Gobierno, cofundador del Ateneo Fuente y corredactor de sus estatutos y planes de estudio.</li>'
            . '<li><span>1868</span> Como diputado al Congreso de la Unión impulsó la ratificación del decreto juarista que separó a Coahuila de Nuevo León.</li>'
            . '<li><span>1867</span> Redactor en jefe del Periódico Oficial durante el sitio de Querétaro.</li>'
            . '<li><span>1873</span> Autor de la Ley Electoral del Estado; promotor del Casino de la Unión, hoy Casino de Saltillo.</li>'
            . '<li><span>1874</span> Gobernador interino constitucional; trasladó la sede del gobierno al edificio frente a la Plaza de la Independencia.</li>'
            . '<li><span>1875–1879</span> Gobernador constitucional electo; en 1875 decretó la erección de la Villa de Jiménez.</li>'
            . '<li><span>Después</span> Senador y miembro de la comisión para el establecimiento de vías férreas en Coahuila.</li>'
            . '</ul>'
            . $fig('https://upload.wikimedia.org/wikipedia/commons/8/89/Teatro_Garc%C3%ADa_Carrillo_1906.jpg', 'Teatro García Carrillo, Saltillo', 'Teatro García Carrillo de Saltillo, inaugurado el 27 de julio de 1910 en honor del gobernador (imagen de contexto; no es retrato).', 'FernandaLópezCervantes, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Teatro_Garc%C3%ADa_Carrillo_1906.jpg')
            . '<h2 id="legado">Legado</h2>'
            . '<p>La historiografía coahuilense lo recuerda como artífice jurídico de la soberanía del estado. Su nombre perdura en el <strong>Teatro García Carrillo</strong> del centro de Saltillo, descrito en su inauguración como homenaje a «un distinguido abogado, sabio y probo gobernador, además de literato y buen ciudadano». Fue padre de José García Rodríguez, después director del Ateneo Fuente.</p>'
            . '<p class="bio-note">Nota editorial: esta ficha sustituye al borrador «Antonio García Garrido», nombre que no aparece documentado en el archivo. Algunas fuentes dan el 14 de febrero de 1842 como fecha de nacimiento. El retrato que encabeza esta ficha procede de la obra de Martha Durón Jiménez, <em>Orígenes de una fundación… José García Rodríguez 1530–1992</em>, p. 31. Se agrupa en la familia García Garza por su apellido García; su filiación directa con Lorenzo García y Juana de la Garza está en investigación.</p>'
            . $src([
                ['Martha Durón Jiménez, «Orígenes de una fundación… José García Rodríguez 1530–1992», p. 31 (retrato)', '/media/biography/2'],
                ['Jaime García Narro, «El Lic. Antonio García Carrillo y la Independencia de México», Colegio Coahuilense de Investigaciones Históricas, Revista núm. 116', 'https://web.seducoahuila.gob.mx/colegiocoahuilense/assets/img/revistas/revistascontenido/Revista%20116%20PDF.pdf'],
                ['Lucas Martínez, «El Plan de Tuxtepec» (Colegio Coahuilense de Investigaciones Históricas)', 'https://web.seducoahuila.gob.mx/colegiocoahuilense/assets/img/publicaciones/publicacionescontenido/3.-el-plan-de-tuxtepec.pdf'],
                ['Zócalo — Algo que no sabías del Teatro García Carrillo', 'https://www.zocalo.com.mx/algo-que-no-sabias-del-teatro-garcia-carrillo/'],
                ['Conoce Saltillo — Teatro Antonio García Carrillo', 'http://conocesaltillo.blogspot.com/2019/03/teatro-antonio-garcia-carrillo.html'],
                ['Vanguardia — El hombre que dejó su vida en el Ateneo Fuente de Saltillo', 'https://vanguardia.com.mx/coahuila/el-hombre-que-dejo-su-vida-en-el-ateneo-fuente-de-saltillo-YK6084076'],
            ]),
        'portrait' => null,
    ];

    // 4) Praxedis de la Peña García
    $bios[] = [
        'slug' => 'praxedis-de-la-pena-garcia',
        'family' => 'garcia-garza',
        'old_slugs' => [],
        'full_name' => 'Lic. Praxedis de la Peña García',
        'subtitle' => 'Abogado, tres veces gobernador de Coahuila y fundador de Torreón',
        'lifespan_label' => '1847 – 1926',
        'summary' => 'Abogado saltillense, gobernador de Coahuila en 1884, 1909 y 1913–1914, y empresario de La Laguna: fundador de la Compañía de Luz y Fuerza Eléctrica de Saltillo y del Banco de Coahuila, y uno de los fundadores de Torreón.',
        'sort_order' => 4,
        'body' => '<p class="bio-dropcap"><strong>Praxedis de la Peña García</strong> nació en Saltillo, Coahuila, el 8 de noviembre de 1847, hijo de Praxedis de la Peña Berlanga y de María Julia García Sánchez. Murió en Torreón el 15 de enero de 1926 y fue sepultado en Saltillo.</p>'
            . '<h2 id="vida">Vida</h2>'
            . '<p>Cursó la preparatoria en el Colegio Isaac Pitman y se tituló de abogado en 1870. Contrajo matrimonio con Concepción Valdés Elguezábal, con quien tuvo nueve hijos. A partir de 1885 se estableció en La Laguna, donde se dedicó a los negocios, principalmente en el Rancho del Torreón, que después sería villa y ciudad.</p>'
            . '<h2 id="obra">Obra y cargos</h2>'
            . '<ul class="bio-timeline">'
            . '<li><span>1870</span> Abogado; oficial mayor de la Secretaría General de Gobierno de Coahuila.</li>'
            . '<li><span>—</span> Comisionado por el gobierno del estado en el deslinde de límites entre Coahuila y Durango, con resultados favorables para Coahuila.</li>'
            . '<li><span>1884</span> Gobernador de Coahuila por primera vez.</li>'
            . '<li><span>1885</span> Se instala en La Laguna; uno de los fundadores de Torreón y promotor de su desarrollo industrial.</li>'
            . '<li><span>1909</span> Gobernador de Coahuila por segunda vez.</li>'
            . '<li><span>1913–1914</span> Gobernador de Coahuila por tercera vez.</li>'
            . '<li><span>Empresas</span> Fundador de la Compañía de Luz y Fuerza Eléctrica de Saltillo y del Banco de Coahuila; fundador y presidente de empresas industriales y mineras de Torreón.</li>'
            . '</ul>'
            . '<h2 id="legado">Legado</h2>'
            . '<p>Figura puente entre el Saltillo decimonónico y la pujante Comarca Lagunera, su nombre está unido a los orígenes de Torreón y a la modernización económica de Coahuila. Por línea materna —María Julia García Sánchez— lleva el apellido <strong>García</strong>, razón por la que figura en esta galería de los García de Coahuila; su filiación con otras ramas del archivo está pendiente de prueba documental.</p>'
            . '<p class="bio-note">Retrato grabado de época (identificación por pie de foto en fuente secundaria; confianza media). Se publica con atribución justa para uso ilustrativo genealógico.</p>'
            . $src([
                ['Wikipedia — Praxedis de la Peña García', 'https://es.wikipedia.org/wiki/Praxedis_de_la_Pe%C3%B1a_Garc%C3%ADa'],
                ['Berrueto González, Arturo (1999). Diccionario Biográfico de Coahuila. Gobierno del Estado de Coahuila (citado por Wikipedia).', 'https://es.wikipedia.org/wiki/Praxedis_de_la_Pe%C3%B1a_Garc%C3%ADa#Bibliograf%C3%ADa'],
                ['Jaime García Narro, Revista núm. 116, Colegio Coahuilense de Investigaciones Históricas (elección de 1884)', 'https://web.seducoahuila.gob.mx/colegiocoahuilense/assets/img/revistas/revistascontenido/Revista%20116%20PDF.pdf'],
            ]),
        'portrait' => 'storage/uploads/bios/portraits/praxedis-de-la-pena-garcia.jpg',
    ];

    // 5) Lic. Francisco Javier García Gaona
    $bios[] = [
        'slug' => 'francisco-javier-garcia-gaona',
        'family' => 'garcia-garza',
        'old_slugs' => [],
        'full_name' => 'Lic. Francisco Javier García Gaona',
        'subtitle' => 'De los García del Huachichil · Emprendedor, activista e investigador genealógico',
        'lifespan_label' => '1988 –',
        'summary' => 'Licenciado coahuilense de los García del Huachichil, descendiente en 13.ª generación del Capitán Blas de la Garza Falcón por ambas líneas. Fundador de Grupo Empresarial GARGA de México y de la plataforma Soy Divergente.',
        'sort_order' => 5,
        'body' => '<p class="bio-dropcap">El <strong>Lic. Francisco Javier García Gaona</strong> (FamilySearch GL1W-FX2) nació en 1988 en el noreste de México y pertenece a los <strong>García del Huachichil</strong>, rama familiar con raíces en Coahuila y Nuevo León. Su trabajo une la memoria familiar documentada con la acción pública y el emprendimiento.</p>'
            . '<h2 id="vida">Linaje documentado</h2>'
            . '<p>Desciende en línea directa del <strong>Capitán Blas de la Garza Falcón</strong> (1591–1669) y de Beatriz González Hidalgo, poblador principal de Monterrey, por <em>ambas</em> líneas parentales.</p>'
            . '<h3>Línea paterna — los García del Huachichil</h3>'
            . '<p class="bio-lineage">Cap. Blas de la Garza Falcón → Juana de la Garza González (1622–1669), casada con el Alférez Lorenzo García de Ábrego → Joseph Lorenzo García de la Garza → Joseph García Morales → Miguel Joseph García Ramírez → Juan Joseph Luis García Ramos → José Antonio García Guajardo → Jesús García Saucedo → Jesús García Flores → Francisco García de la Peña → Hermilo García García → Jonás García Castilleja → Margarito García Vásquez → <strong>Lic. Francisco Javier García Gaona</strong>.</p>'
            . '<h3>Línea materna</h3>'
            . '<p class="bio-lineage">Cap. Blas de la Garza Falcón → Beatriz González Hidalgo (hija) → … → Ramona Gaona Samaniego → <strong>Lic. Francisco Javier García Gaona</strong>.</p>'
            . '<h2 id="obra">Trayectoria pública</h2>'
            . '<ul class="bio-timeline">'
            . '<li><span>2009</span> Convergencia por la Democracia (hoy Movimiento Ciudadano): campañas locales en Coahuila.</li>'
            . '<li><span>2012</span> Presidente del Comité Municipal del Partido Joven en General Cepeda, Coahuila.</li>'
            . '<li><span>2013–2015</span> Fundador del Movimiento Cardenista Coahuilense.</li>'
            . '<li><span>2016–2017</span> Presidente estatal del Frente Cardenista de Reconstrucción Nacional (FCRN).</li>'
            . '<li><span>2018–2020</span> Representante legal del Frente Cardenista de Coahuila, A.C.</li>'
            . '<li><span>2021</span> Funda <strong>Grupo Empresarial GARGA de México</strong>, S.A.S. de C.V.: marketing político, desarrollo tecnológico y comunicación visual.</li>'
            . '<li><span>2023</span> Crea <strong>Soy Divergente</strong>, plataforma de detección, consejería y comunidad en torno al TDAH y la neurodivergencia, nacida de la experiencia vivida.</li>'
            . '</ul>'
            . '<h2 id="legado">Legado y proyecto</h2>'
            . '<p>Impulsor de <em>Genealogía Sefardí</em>, archivo abierto que reúne actas, testamentos y cadenas de prueba de las familias fundadoras del noreste. Su propósito: que la historia de los García del Huachichil y de sus parientes se cuente con documentos, no con suposiciones.</p>'
            . '<p class="bio-note">Aclaración: no ha militado en el PRI. No confundir con el homónimo dominicano Francisco Javier García Fernández.</p>'
            . $src([
                ['Biografía genealógica y personal (PDF del autor) — Archivo #4', '/archivo/4'],
                ['FamilySearch — Francisco Javier García Gaona (GL1W-FX2)', 'https://www.familysearch.org/tree/person/details/GL1W-FX2'],
                ['FamilySearch — Cap. Blas de la Garza Falcón (L5JN-FZY)', 'https://www.familysearch.org/tree/person/details/L5JN-FZY'],
                ['Ficha del Cap. Blas de la Garza Falcón en este archivo', '/biografias/blas-de-la-garza-falcon'],
            ]),
        'portrait' => 'storage/uploads/bios/francisco-javier-garcia-gaona.jpg',
    ];
    return $bios;
}

function run_ilustres_2026_10(PDO $pdo): array
{
    $log = [];
    $cols = $pdo->query("SHOW COLUMNS FROM biographies LIKE 'category'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE biographies ADD COLUMN category VARCHAR(40) NULL AFTER subtitle");
        $log[] = 'columna category añadida';
    }
    if (!$pdo->query("SHOW COLUMNS FROM biographies LIKE 'family_group'")->fetch()) {
        $pdo->exec("ALTER TABLE biographies ADD COLUMN family_group VARCHAR(40) NULL AFTER category");
        $log[] = 'columna family_group añadida';
    }
    $find = $pdo->prepare('SELECT id, slug FROM biographies WHERE slug = ?');
    foreach (ilustres_2026_10() as $b) {
        $id = null;
        foreach (array_merge([$b['slug']], $b['old_slugs']) as $s) {
            $find->execute([$s]);
            if ($r = $find->fetch(PDO::FETCH_ASSOC)) { $id = (int)$r['id']; break; }
        }
        $cit = '«' . $b['full_name'] . '», Genealogía Sefardí — Personas ilustres. https://genealogiasefardi.site/biografias/' . $b['slug'];
        $vals = [$b['slug'], $b['full_name'], $b['subtitle'], $b['lifespan_label'], $b['summary'], $b['body'], $b['portrait'], $cit, $b['sort_order'], $b['family']];
        if ($id) {
            $pdo->prepare("UPDATE biographies SET slug=?, full_name=?, subtitle=?, lifespan_label=?, summary=?, body_html=?, portrait_path=COALESCE(?, portrait_path), preferred_citation=?, sort_order=?, family_group=?, category='ilustre', is_featured=1, published=1 WHERE id=?")
                ->execute(array_merge($vals, [$id]));
            $log[] = "UPDATE #$id {$b['slug']}";
        } else {
            $pdo->prepare("INSERT INTO biographies (slug, full_name, subtitle, lifespan_label, summary, body_html, portrait_path, preferred_citation, sort_order, family_group, category, is_featured, published) VALUES (?,?,?,?,?,?,?,?,?,?,'ilustre',1,1)")
                ->execute($vals);
            $log[] = 'INSERT #' . $pdo->lastInsertId() . " {$b['slug']}";
        }
    }
    return $log;
}
