<?php
session_start();
if (isset($_SESSION['emailUser']) != "" && $_SESSION['rol'] == 'Administrador') {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');

  $eventos = sp_auditoria_eventos(150);
  $hoy = date('Y-m-d');
  $totalHoy = 0;
  $acciones = [];

  foreach ($eventos as $evento) {
    if (strpos($evento['fecha'], $hoy) === 0) {
      $totalHoy++;
    }
    $accion = $evento['accion'] !== '' ? $evento['accion'] : 'sin_accion';
    $acciones[$accion] = ($acciones[$accion] ?? 0) + 1;
  }
  arsort($acciones);
?>
<!DOCTYPE html>
<html lang="es">
<?php include('bases/head.html'); ?>
<body>
<?php include('bases/loader.html'); ?>
<div class="container-scroller">
  <?php include 'bases/navbar.php'; ?>
  <div class="container-fluid page-body-wrapper">
    <?php include 'bases/config.html'; include 'bases/nav.php'; ?>
    <div class="main-panel">
      <div class="content-wrapper">
        <div class="row align-items-start">
          <div class="col-md-8 grid-margin">
            <h2 class="font-weight-bold sp-page-title">Auditoría SMARTPARK</h2>
            <p class="text-muted mb-0">Trazabilidad de acciones críticas para operación, sustentación y control administrativo.</p>
          </div>
          <div class="col-md-4 grid-margin text-md-right">
            <span class="sp-pill"><i class="bi bi-shield-check"></i> Solo administrador</span>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 grid-margin stretch-card">
            <div class="card sp-kpi-card"><div class="card-body">
              <span class="sp-kpi-icon"><i class="bi bi-list-check"></i></span>
              <p class="sp-kpi-label">Eventos visibles</p>
              <h3 class="sp-kpi-value"><?php echo count($eventos); ?></h3>
              <p class="sp-kpi-note mb-0">Últimos registros</p>
            </div></div>
          </div>
          <div class="col-md-4 grid-margin stretch-card">
            <div class="card sp-kpi-card"><div class="card-body">
              <span class="sp-kpi-icon"><i class="bi bi-calendar2-day"></i></span>
              <p class="sp-kpi-label">Eventos de hoy</p>
              <h3 class="sp-kpi-value"><?php echo $totalHoy; ?></h3>
              <p class="sp-kpi-note mb-0"><?php echo sp_escape(date('d/m/Y')); ?></p>
            </div></div>
          </div>
          <div class="col-md-4 grid-margin stretch-card">
            <div class="card sp-kpi-card"><div class="card-body">
              <span class="sp-kpi-icon"><i class="bi bi-person-lock"></i></span>
              <p class="sp-kpi-label">Usuario actual</p>
              <h3 class="sp-kpi-value" style="font-size: 20px;"><?php echo sp_escape($email); ?></h3>
              <p class="sp-kpi-note mb-0">Rol Administrador</p>
            </div></div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
              <div class="card-body">
                <h4 class="card-title">Línea de tiempo</h4>
                <?php if (count($eventos) === 0) { ?>
                  <div class="alert alert-info mb-0">Aún no hay eventos de auditoría. Se crearán automáticamente al registrar vehículos, ingresos, reservas y pagos.</div>
                <?php } ?>
                <?php foreach ($eventos as $evento) { ?>
                  <div class="sp-audit-item">
                    <div>
                      <div class="sp-audit-time"><?php echo sp_escape($evento['fecha']); ?></div>
                    </div>
                    <div>
                      <div class="sp-audit-action"><?php echo sp_escape(str_replace('_', ' ', $evento['accion'])); ?></div>
                      <div class="sp-audit-user"><i class="bi bi-person-circle"></i><?php echo sp_escape($evento['usuario']); ?></div>
                      <p class="sp-audit-detail mt-2"><?php echo sp_escape($evento['detalle']); ?></p>
                    </div>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>
          <div class="col-lg-4 grid-margin stretch-card">
            <div class="card sp-insight">
              <div class="card-body">
                <h4 class="card-title">Acciones frecuentes</h4>
                <?php if (count($acciones) === 0) { ?>
                  <p class="text-muted mb-0">Sin acciones registradas todavía.</p>
                <?php } ?>
                <?php foreach (array_slice($acciones, 0, 8, true) as $accion => $cantidad) { ?>
                  <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <span><?php echo sp_escape(str_replace('_', ' ', $accion)); ?></span>
                    <strong><?php echo sp_escape($cantidad); ?></strong>
                  </div>
                <?php } ?>
                <p class="text-muted mt-3 mb-0">Este módulo usa archivo de auditoría para no alterar el modelo normalizado de 7 tablas.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
</body>
</html>
<?php } else { ?>
  <script>location.href="../acciones/login/exit.php";</script>
<?php } ?>
