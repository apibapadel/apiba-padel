<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

/**
 * Torneos - Admin (v3)
 * - Estado: mini vertical (3 opciones) con AJAX
 * - Imprimir: SOLO habilitado si Finalizado (sin target blank)
 * - VER WEB: único botón que abre en nueva pestaña
 * - Editar: bloqueado si Estado = En Curso
 * - Puntos renombrado a Resultados
 * - Botones con mismo diseño "pill" gris (contenedor redondeado)
 * - Cabeceras y celdas centradas
 */

function normalizar_estado($e): string {
  $e = trim((string)$e);
  if ($e === '') return 'Abierto';
  $low = mb_strtolower($e, 'UTF-8');
  if ($low === 'abierto') return 'Abierto';
  if ($low === 'en curso' || $low === 'encurso') return 'En Curso';
  if ($low === 'finalizado' || $low === 'cerrado') return 'Finalizado';
  if ($low === 'cancelado') return 'Cancelado';
  return $e;
}
function es_finalizado($estado): bool { return normalizar_estado($estado) === 'Finalizado'; }
function es_en_curso($estado): bool { return normalizar_estado($estado) === 'En Curso'; }
function formatear_fecha($fi): string {
  $fi = trim((string)$fi);
  if ($fi === '') return '';
  $ts = strtotime($fi);
  if (!$ts) return '';
  return date('d/m/Y', $ts);
}

// ==============================
// AJAX endpoint (sin recargar)
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_estado') {
  header('Content-Type: application/json; charset=utf-8');

  $id = (int)($_POST['torneo_id'] ?? 0);
  $estado = trim((string)($_POST['nuevo_estado'] ?? ''));

  $permitidos = ['Abierto', 'En Curso', 'Finalizado', 'Cancelado'];
  if ($id <= 0 || !in_array($estado, $permitidos, true)) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
    exit;
  }

  try {
    $stmt = $pdo->prepare("UPDATE torneos SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);
    echo json_encode([
      'ok' => true,
      'estado' => $estado,
      'finalizado' => ($estado === 'Finalizado'),
      'en_curso' => ($estado === 'En Curso')
    ]);
  } catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el estado.']);
  }
  exit;
}

// Fallback (si JS está deshabilitado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['torneo_id'], $_POST['nuevo_estado'])) {
  $id = (int)$_POST['torneo_id'];
  $estado = trim((string)$_POST['nuevo_estado']);
  $permitidos = ['Abierto', 'En Curso', 'Finalizado', 'Cancelado'];
  if ($id > 0 && in_array($estado, $permitidos, true)) {
    $stmt = $pdo->prepare("UPDATE torneos SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);
  }
  header("Location: torneos.php");
  exit;
}

include '_header.php';

$torneos = $pdo->query("
  SELECT id, nombre, categoria, sede, fecha_inicio, estado
  FROM torneos
  ORDER BY id DESC
")->fetchAll();
?>

<style>
  .table-wrap{ overflow-x:auto; }
  .torneos-table th, .torneos-table td { vertical-align: middle; text-align: center; }
  .torneos-table .col-id { width: 1%; white-space: nowrap; font-size: 12px; }
  .torneos-table .col-nombre { max-width: 320px; }
  .torneos-table .col-fecha { width: 1%; white-space: nowrap; }
  .torneos-table td { padding-top: 10px; padding-bottom: 10px; }

  /* Torneo: estilo similar a badges pero negrita */
  /* Fecha más chica */
  .fecha-mini{
    font-size: 12px;
    color: var(--muted);
    white-space: nowrap;
  }

  /* Acciones: SIEMPRE en una sola fila (si no entra, hace scroll horizontal dentro de la celda) */
  .actions{
    display:flex;
    gap:6px;
    flex-wrap: nowrap;
    align-items:center;
    justify-content:center;
    white-space: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    padding: 2px 0;
  }

  /* Evitar movimiento por aparición/desaparición de scrollbar */
  .actions{
    scrollbar-gutter: stable both-edges;
    min-height: 40px;
  }

  .actions::-webkit-scrollbar{ height: 6px; }
  .actions::-webkit-scrollbar-thumb{ background: rgba(0,0,0,.18); border-radius: 999px; }
  .actions::-webkit-scrollbar-track{ background: rgba(0,0,0,.04); border-radius: 999px; }

  /* Fuente/estilo unificado para TODOS los botones */
  .actions .btn{
    font-family: inherit;
    font-weight: 900;
    letter-spacing: .2px;
  }

  /* Estado: mini vertical */
  .state3v{
    display:flex;
    flex-direction: column;
    gap: 4px;
    padding: 6px;
    border-radius: 14px;
    background: rgba(0,0,0,.04);
    border: 1px solid rgba(0,0,0,.10);
    width: 120px;
    margin: 0 auto;
    user-select:none;
  }
  .state3v input{ position:absolute; opacity:0; pointer-events:none; }
  .state3v label{
    display:flex;
    align-items:center;
    justify-content:center;
    height: 22px;
    border-radius: 999px;
    font-size: 11px;
    cursor:pointer;
    color: rgba(0,0,0,.70);
    border: 1px solid rgba(0,0,0,.10);
    background: rgba(255,255,255,.72);
    transition: transform .06s ease, background .18s ease;
    white-space: nowrap;
  }
  .state3v label:active{ transform: scale(.98); }

  .state3v[data-value="Abierto"] label[data-v="Abierto"],
  .state3v[data-value="En Curso"] label[data-v="En Curso"],
  .state3v[data-value="Finalizado"] label[data-v="Finalizado"]{
    background: rgba(255,255,255,.95);
    border-color: rgba(0,0,0,.14);
    font-weight: 800;
  }
  .state3v[data-value="Abierto"] label[data-v="Abierto"]{ color:#0b4; }
  .state3v[data-value="En Curso"] label[data-v="En Curso"]{ color:#0aa; }
  .state3v[data-value="Finalizado"] label[data-v="Finalizado"]{ color:#666; }

  .estado-msg{ text-align:center; }

  /* Evitar "layout shift" al guardar: reservamos espacio fijo para el mensaje */
  .estado-msg{
    display:block !important;
    min-height: 14px;
    line-height: 14px;
    margin-top: 6px;
    opacity: 0;
    transition: opacity .12s ease;
  }
  .estado-msg.is-visible{ opacity: 1; }

  
  /* ==========================
     BOTONES PRO (Admin UI)
     - "soft" background + borde
     - misma altura/typography
     ========================== */
  .actions .btn{
    height: 32px !important;
    padding: 0 12px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    letter-spacing: .2px;
    border-radius: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px;
    box-shadow: 0 1px 0 rgba(0,0,0,.06);
    transition: transform .06s ease, filter .15s ease, background .15s ease, border-color .15s ease;
    text-transform: none;
  }
  .actions .btn:hover{ filter: brightness(1.03); }
  .actions .btn:active{ transform: translateY(1px); }

  /* Paleta PRO */
  .btn-editar-wow{
    background: rgba(39,194,255,.14) !important;
    border: 1px solid rgba(39,194,255,.35) !important;
    color: rgba(0,0,0,.86) !important;
  }
  .btn-insc{
    background: rgba(138,175,229,.18) !important;
    border: 1px solid rgba(138,175,229,.45) !important;
    color: rgba(0,0,0,.84) !important;
  }
  .btn-resultados.btn-danger{
    background: rgba(255,77,77,.14) !important;
    border: 1px solid rgba(255,77,77,.40) !important;
    color: rgba(0,0,0,.86) !important;
  }
  .btn-verweb{
    background: rgba(34,197,94,.14) !important;
    border: 1px solid rgba(34,197,94,.38) !important;
    color: rgba(0,0,0,.86) !important;
  }
  .btn-imprimir{
    background: rgba(0,0,0,.10) !important;
    border: 1px solid rgba(0,0,0,.28) !important;
    color: rgba(0,0,0,.90) !important;
  }

  /* Disabled más limpio */
  .actions .btn.is-disabled{
    opacity: .42;
    filter: grayscale(1);
    box-shadow: none;
  }

  .btn.is-disabled{
    opacity: .45;
    pointer-events: none;
    filter: grayscale(1);
  }

    /* Anchos fijos (evita que se aplasten/superpongan) */
  .torneos-table{
    table-layout: fixed;
    width: 100%;
  }

  .torneos-table th.col-id, .torneos-table td.col-id{ width: 60px; min-width:60px; max-width:60px; }
  .torneos-table th.col-catsede, .torneos-table td.col-catsede{ width: 240px; min-width:240px; max-width:240px; }
  .torneos-table th.col-fecha, .torneos-table td.col-fecha{ width: 100px; min-width:100px; max-width:100px; }
  .torneos-table th.col-estado, .torneos-table td.col-estado{ width: 140px; min-width:140px; max-width:140px; }

  /* Acciones: más ancho + padding a la izquierda para que no "rompa" al activar/desactivar */
  .torneos-table th.col-acciones, .torneos-table td.col-acciones{
    width: 640px;
    min-width: 640px;
    max-width: 640px;
    padding-left: 12px;
    padding-right: 12px;
  }
.torneos-table th:last-child,
  .torneos-table td:last-child{
    width: 520px; /* ancho suficiente para todos los botones */
    min-width: 520px;
    max-width: 520px;
  }

  /* Categoría/Sede más chico */
  .torneos-table td.col-catsede,
  .torneos-table th.col-catsede{
    font-size: 12px;
  }
  .torneos-table td.col-catsede .badge{
    font-size: 11px;
  }

  /* Estado más chico */
  .state3v{ width: 110px; }
  .state3v label{ height: 20px; font-size: 10px; }


  /* Títulos de columnas unificados */
  .torneos-table th{
    font-family: inherit !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    letter-spacing: .2px;
    text-align: center;
  }


  /* Línea inferior premium para títulos */
  .torneos-table thead th{
    position: relative;
  }

  .torneos-table thead th::after{
    content: "";
    position: absolute;
    left: 15%;
    bottom: 0;
    width: 70%;
    height: 2px;
    background: linear-gradient(90deg, 
                rgba(0,0,0,0) 0%, 
                rgba(0,0,0,.15) 25%, 
                rgba(0,0,0,.25) 50%, 
                rgba(0,0,0,.15) 75%, 
                rgba(0,0,0,0) 100%);
    border-radius: 999px;
  }

</style>

<h2>Torneos</h2>

<div class="table-wrap">
  <table class="torneos-table">
    <tr>
      <th class="col-id">ID</th>
      
      <th class="col-catsede">Categoría / Sede</th>
      <th class="col-fecha">Inicio</th>
      <th class="col-estado">Estado</th>
      <th class="col-acciones">Acciones</th>
    </tr>

    <?php foreach($torneos as $t): ?>
      <?php
        $tid = (int)$t['id'];
        $estadoActual = normalizar_estado($t['estado'] ?? '');
        $finalizado = es_finalizado($estadoActual);
        $enCurso = es_en_curso($estadoActual);

        $imprimirHref = "/apiba-padel/admin/generar_torneo.php?id=".$tid."&print=1";
      ?>
      <tr data-torneo="<?= $tid ?>">
        <td class="col-id" style="font-size:12px;"><?= $tid ?></td>

        <td class="col-catsede">
          <span class="badge"><?= htmlspecialchars((string)($t['categoria'] ?? '')) ?></span><br>
          <span class="badge"><?= htmlspecialchars((string)($t['sede'] ?? 'Sin sede')) ?></span>
        </td>

        <td class="col-fecha"><span class="fecha-mini"><?= htmlspecialchars(formatear_fecha($t['fecha_inicio'] ?? '')) ?></span></td>

        <td class="col-estado">
          <div class="state3v" data-value="<?= htmlspecialchars($estadoActual) ?>" data-id="<?= $tid ?>">
            <input type="radio" name="estado_<?= $tid ?>" id="eA_<?= $tid ?>" value="Abierto" <?= $estadoActual==='Abierto'?'checked':'' ?> />
            <label for="eA_<?= $tid ?>" data-v="Abierto">Abierto</label>

            <input type="radio" name="estado_<?= $tid ?>" id="eC_<?= $tid ?>" value="En Curso" <?= $estadoActual==='En Curso'?'checked':'' ?> />
            <label for="eC_<?= $tid ?>" data-v="En Curso">En curso</label>

            <input type="radio" name="estado_<?= $tid ?>" id="eF_<?= $tid ?>" value="Finalizado" <?= $estadoActual==='Finalizado'?'checked':'' ?> />
            <label for="eF_<?= $tid ?>" data-v="Finalizado">Finalizado</label>
          </div>
          <div class="estado-msg"></div>
        </td>

        <td class="col-acciones">
          <div class="actions">
            <a class="btn btn-soft btn-sm action-btn btn-editar btn-editar-wow <?= $enCurso ? 'is-disabled' : '' ?>" data-lock="<?= ($enCurso ? '🔒' : '🔓') ?>"
               href="<?= $enCurso ? 'javascript:void(0)' : 'editar_torneo.php?id='.$tid ?>" data-label="Editar" data-label-disabled="Editar 🔒"
               <?= $enCurso ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
              Editar
            </a>

            <a class="btn btn-soft btn-sm action-btn btn-insc <?= $finalizado ? 'is-disabled' : '' ?>" data-lock="<?= ($finalizado ? '🔒' : '🔓') ?>" href="<?= $finalizado ? 'javascript:void(0)' : 'inscriptos.php?id='.$tid ?>" <?= $finalizado ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Inscriptos</a>

            <a class="btn btn-ok btn-sm action-btn btn-resultados btn-danger <?= $finalizado ? '' : 'is-disabled' ?>" data-lock="<?= ($finalizado ? '🔓' : '🔒') ?>"
               href="<?= $finalizado ? '/apiba-padel/admin/torneo_puntos.php?id='.$tid : 'javascript:void(0)' ?>"
               <?= $finalizado ? '' : 'tabindex="-1" aria-disabled="true"' ?>>
              Resultados
            </a>

            <a class="btn btn-soft btn-sm action-btn btn-verweb" data-lock="🔓" href="/apiba-padel/torneos/ver.php?id=<?= $tid ?>" target="_blank">VER WEB</a>

            <a class="btn btn-ok btn-sm action-btn btn-imprimir <?= $finalizado ? '' : 'is-disabled' ?>" data-lock="<?= ($finalizado ? '🔓' : '🔒') ?>"
               href="<?= $finalizado ? $imprimirHref : 'javascript:void(0)' ?>"
               <?= $finalizado ? '' : 'tabindex="-1" aria-disabled="true"' ?>>
              Imprimir
            </a>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<script>
(function(){
  function postEstado(torneoId, nuevoEstado){
    const fd = new FormData();
    fd.append('action', 'update_estado');
    fd.append('torneo_id', torneoId);
    fd.append('nuevo_estado', nuevoEstado);

    return fetch('torneos.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    }).then(r => r.json());
  }

  function setDisabled(linkEl, disabled, hrefWhenEnabled){
    if (!linkEl) return;
    if (disabled){
      linkEl.classList.add('is-disabled');
      linkEl.setAttribute('aria-disabled','true');
      linkEl.setAttribute('tabindex','-1');
      linkEl.setAttribute('href','javascript:void(0)');
      linkEl.setAttribute('data-lock','🔒');

    if (linkEl.dataset && (linkEl.dataset.label || linkEl.dataset.labelDisabled)){
      const normal = linkEl.dataset.label || linkEl.textContent;
      const dis = linkEl.dataset.labelDisabled || (normal + " 🔒");
      linkEl.textContent = disabled ? dis : normal;
    }

    } else {
      linkEl.classList.remove('is-disabled');
      linkEl.removeAttribute('aria-disabled');
      linkEl.removeAttribute('tabindex');
      if (hrefWhenEnabled) linkEl.setAttribute('href', hrefWhenEnabled);
      linkEl.setAttribute('data-lock','🔓');
    if (ico) ico.textContent = disabled ? "🔒" : "🔓";
    }
  }

  document.querySelectorAll('.state3v').forEach(function(box){
    const torneoId = box.getAttribute('data-id');
    const row = document.querySelector('tr[data-torneo="'+torneoId+'"]');
    const msg = row ? row.querySelector('.estado-msg') : null;

    box.addEventListener('change', function(e){
      const input = e.target;
      if (!input || input.tagName !== 'INPUT') return;

      const nuevo = input.value;

      // UI optimista
      box.setAttribute('data-value', nuevo);
      if (msg) { msg.classList.add('is-visible'); msg.textContent='Guardando...'; }

      postEstado(torneoId, nuevo).then(function(res){
        if (!res || !res.ok){
          if (msg) { msg.classList.add('is-visible'); msg.textContent = (res && res.error) ? res.error : 'Error al guardar.'; }
          return;
        }

        if (msg) { msg.textContent = 'Guardado'; setTimeout(()=>{ msg.classList.remove('is-visible'); }, 700); }

        const finalizado = !!res.finalizado;
        const enCurso = !!res.en_curso;

        // Imprimir (solo finalizado)
        const btnImp = row.querySelector('.btn-imprimir');
        setDisabled(btnImp, !finalizado, "/apiba-padel/admin/generar_torneo.php?id=" + torneoId + "&print=1");

        // Resultados (solo finalizado)
        const btnRes = row.querySelector('.btn-resultados');
        setDisabled(btnRes, !finalizado, "/apiba-padel/admin/torneo_puntos.php?id=" + torneoId);

        // Editar (bloqueado si En Curso)
        const btnEd = row.querySelector('.btn-editar');
        setDisabled(btnEd, enCurso, "editar_torneo.php?id=" + torneoId);
      }).catch(function(){
        if (msg) { msg.classList.add('is-visible'); msg.textContent='Error de red/servidor.'; }
      });
    });
  });
})();
</script>

<?php include '_footer.php'; ?>
