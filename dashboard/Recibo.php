<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $recibo = sp_recibo($con, (int)$_GET['idParqueo']);
?>
<!DOCTYPE html>
<html lang="es">
<?php include('bases/head.html'); ?>
<body>
<?php include('bases/loader.html'); ?>
<style>
@media print {
  .navbar, .sidebar, .btn, .settings-panel { display: none !important; }
  .main-panel { width: 100% !important; margin: 0 !important; }
  .content-wrapper { padding: 0 !important; background: #fff !important; }
  .card { border: none !important; box-shadow: none !important; }
}
</style>
<div class="container-scroller">
  <?php include 'bases/navbar.php'; ?>
  <div class="container-fluid page-body-wrapper">
    <?php include 'bases/config.html'; include 'bases/nav.php'; ?>
    <div class="main-panel"><div class="content-wrapper">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card"><div class="card-body">
            <?php if ($recibo) { ?>
              <?php
                $consultaUrl = '';
                $qrUrl = '';
                if (!empty($recibo['codigo_reserva'])) {
                  $consultaUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/ConsultarReserva.php?codigo=' . urlencode($recibo['codigo_reserva']);
                  $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=' . urlencode($consultaUrl);
                }
              ?>
              <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                  <h2 class="mb-1">SMARTPARK</h2>
                  <p class="text-muted mb-0">Parqueadero Zona Segura ECCI</p>
                  <small>NIT/ID institucional: ECCI-SMARTPARK</small>
                </div>
                <div class="text-right">
                  <h4>Recibo</h4>
                  <strong><?php echo sp_escape($recibo['numero_recibo']); ?></strong>
                </div>
              </div>
              <div class="row">
                <div class="col-md-8">
                  <table class="table table-bordered">
                    <tr><th>Fecha pago</th><td><?php echo sp_escape($recibo['fecha_pago']); ?></td></tr>
                    <tr><th>Cliente</th><td><?php echo sp_escape($recibo['cedula'] . ' - ' . $recibo['nombres']); ?></td></tr>
                    <tr><th>Vehículo</th><td><?php echo sp_escape($recibo['placa'] . ' / ' . $recibo['marca'] . ' ' . $recibo['modelo'] . ' / ' . $recibo['tipo']); ?></td></tr>
                    <tr><th>Espacio</th><td><?php echo sp_escape($recibo['codigo'] . ' / Piso ' . $recibo['piso']); ?></td></tr>
                    <tr><th>Ingreso</th><td><?php echo sp_escape($recibo['fecha_ingreso'] . ' ' . $recibo['hora_ingreso']); ?></td></tr>
                    <tr><th>Salida</th><td><?php echo sp_escape($recibo['fecha_salida'] . ' ' . $recibo['hora_salida']); ?></td></tr>
                    <tr><th>Tiempo cobrado</th><td><?php echo sp_escape($recibo['total_horas']); ?> hora(s)</td></tr>
                    <tr><th>Tarifa aplicada</th><td><?php echo sp_format_money($recibo['tarifa_hora_aplicada']); ?></td></tr>
                    <tr><th>Método</th><td><?php echo sp_escape($recibo['metodo_pago']); ?></td></tr>
                    <tr><th>Total</th><td><strong><?php echo sp_format_money($recibo['valor_pagado']); ?></strong></td></tr>
                  </table>
                </div>
                <div class="col-md-4 text-center">
                  <?php if ($qrUrl) { ?>
                    <img src="<?php echo sp_escape($qrUrl); ?>" alt="QR recibo" width="130" height="130">
                    <p class="mt-2 mb-0"><small>Consulta digital de la reserva</small></p>
                  <?php } ?>
                  <hr>
                  <p class="mb-0">Atendido por</p>
                  <strong><?php echo sp_escape($email); ?></strong>
                </div>
              </div>
              <button onclick="window.print()" class="btn btn-primary btn-block mt-3">Imprimir recibo</button>
            <?php } else { ?>
              <div class="alert alert-danger">No se encontró el recibo solicitado.</div>
            <?php } ?>
          </div></div>
        </div>
      </div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
