<?php
session_start();
if (isset($_SESSION['emailUser']) != "" && $_SESSION['rol'] == 'Administrador') {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $reporte = sp_reporte_ingresos($con);
  $metodos = sp_reporte_metodos_pago($con);
  $tipos = sp_reporte_tipo_vehiculo($con);
  $ocupacion = sp_ocupacion_por_piso($con);
  $ambiental = sp_panel_ambiental($con);
  $horasPico = sp_ranking_horas_pico($con);
  $alertas = sp_alertas_operativas($con);
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
    <div class="main-panel"><div class="content-wrapper">
      <div class="row align-items-start">
        <div class="col-md-8 grid-margin">
          <h2 class="font-weight-bold sp-page-title">Reportes ejecutivos SMARTPARK</h2>
          <p class="text-muted mb-0">Indicadores financieros, operativos, ambientales y de demanda para dirección universitaria.</p>
        </div>
        <div class="col-md-4 grid-margin text-md-right">
          <a class="btn btn-primary" target="_blank" href="ReporteEjecutivoPDF.php"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF ejecutivo</a>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card sp-kpi-card"><div class="card-body">
            <span class="sp-kpi-icon"><i class="bi bi-leaf"></i></span>
            <p class="sp-kpi-label">CO2 evitado</p>
            <h3 class="sp-kpi-value text-success"><?php echo sp_escape($ambiental['co2_evitado']); ?> kg</h3>
            <p class="sp-kpi-note mb-0">Estimado operacional</p>
          </div></div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card sp-kpi-card"><div class="card-body">
            <span class="sp-kpi-icon"><i class="bi bi-signpost-2"></i></span>
            <p class="sp-kpi-label">Km evitados</p>
            <h3 class="sp-kpi-value"><?php echo sp_escape($ambiental['km_evitados']); ?></h3>
            <p class="sp-kpi-note mb-0">Vueltas menos buscando cupo</p>
          </div></div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card sp-kpi-card"><div class="card-body">
            <span class="sp-kpi-icon"><i class="bi bi-clock-history"></i></span>
            <p class="sp-kpi-label">Tiempo evitado</p>
            <h3 class="sp-kpi-value"><?php echo sp_escape($ambiental['minutos_evitados']); ?> min</h3>
            <p class="sp-kpi-note mb-0">Búsqueda de cupo</p>
          </div></div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card sp-kpi-card"><div class="card-body">
            <span class="sp-kpi-icon"><i class="bi bi-tree"></i></span>
            <p class="sp-kpi-label">Árboles eq.</p>
            <h3 class="sp-kpi-value text-success"><?php echo sp_escape($ambiental['arboles_equivalentes']); ?></h3>
            <p class="sp-kpi-note mb-0">Absorción anual estimada</p>
          </div></div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
          <div class="card sp-insight"><div class="card-body">
            <h4 class="card-title"><i class="bi bi-exclamation-triangle"></i> Alertas operativas</h4>
            <?php foreach ($alertas as $alerta) { ?>
              <div class="alert alert-<?php echo sp_escape($alerta['nivel']); ?> mb-2">
                <strong><?php echo sp_escape($alerta['titulo']); ?>:</strong> <?php echo sp_escape($alerta['mensaje']); ?>
              </div>
            <?php } ?>
          </div></div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Ranking de horas pico</h4>
            <div class="table-responsive"><table class="table table-hover">
              <thead><tr><th>Hora</th><th>Movimientos</th><th>Reservas</th><th>Finalizados</th></tr></thead>
              <tbody><?php while ($h = mysqli_fetch_assoc($horasPico)) { ?><tr><td><?php echo str_pad(sp_escape($h['hora']), 2, '0', STR_PAD_LEFT); ?>:00</td><td><strong><?php echo sp_escape($h['movimientos']); ?></strong></td><td><?php echo sp_escape($h['reservas']); ?></td><td><?php echo sp_escape($h['finalizados']); ?></td></tr><?php } ?></tbody>
            </table></div>
          </div></div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 grid-margin">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Ingresos diarios</h4>
            <div class="table-responsive"><table class="table table-hover dataTableSimple">
              <thead><tr><th>Fecha</th><th>Pagos</th><th>Total recaudado</th></tr></thead>
              <tbody><?php while ($r = mysqli_fetch_assoc($reporte)) { ?><tr><td><?php echo sp_escape($r['fecha']); ?></td><td><?php echo sp_escape($r['pagos']); ?></td><td><?php echo sp_format_money($r['total']); ?></td></tr><?php } ?></tbody>
            </table></div>
          </div></div>
        </div>
        <div class="col-md-6 grid-margin">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Ingresos por método de pago</h4>
            <div class="table-responsive"><table class="table table-hover">
              <thead><tr><th>Método</th><th>Pagos</th><th>Total</th></tr></thead>
              <tbody><?php while ($m = mysqli_fetch_assoc($metodos)) { ?><tr><td><?php echo sp_escape($m['metodo_pago']); ?></td><td><?php echo sp_escape($m['pagos']); ?></td><td><?php echo sp_format_money($m['total']); ?></td></tr><?php } ?></tbody>
            </table></div>
          </div></div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 grid-margin">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Ingresos por tipo de vehículo</h4>
            <div class="table-responsive"><table class="table table-hover">
              <thead><tr><th>Tipo</th><th>Parqueos</th><th>Total</th></tr></thead>
              <tbody><?php while ($t = mysqli_fetch_assoc($tipos)) { ?><tr><td><?php echo sp_escape($t['tipo']); ?></td><td><?php echo sp_escape($t['parqueos']); ?></td><td><?php echo sp_format_money($t['total']); ?></td></tr><?php } ?></tbody>
            </table></div>
          </div></div>
        </div>
        <div class="col-md-6 grid-margin">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Ocupación por piso</h4>
            <div class="table-responsive"><table class="table table-hover">
              <thead><tr><th>Piso</th><th>Libres</th><th>Reservados</th><th>Ocupados</th><th>Total</th></tr></thead>
              <tbody><?php while ($o = mysqli_fetch_assoc($ocupacion)) { ?><tr><td><?php echo sp_escape($o['piso']); ?></td><td class="text-success"><?php echo sp_escape($o['libres']); ?></td><td class="text-warning"><?php echo sp_escape($o['reservados']); ?></td><td class="text-danger"><?php echo sp_escape($o['ocupados']); ?></td><td><?php echo sp_escape($o['total']); ?></td></tr><?php } ?></tbody>
            </table></div>
          </div></div>
        </div>
      </div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
<script>$('.dataTableSimple').DataTable({language:{url:'//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'}});</script>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
