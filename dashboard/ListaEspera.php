<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  sp_asignar_lista_espera($con);
  $lista = sp_lista_espera($con);
  $stats = sp_stats($con);
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
            <h2 class="font-weight-bold sp-page-title">Lista de espera inteligente</h2>
            <p class="text-muted mb-0">Solicitudes sin cupo asignado. El sistema asigna automáticamente por orden de llegada cuando se libera un espacio compatible.</p>
          </div>
          <div class="col-md-4 grid-margin text-md-right">
            <span class="sp-pill"><i class="bi bi-hourglass-split"></i> En espera: <?php echo sp_escape($stats['espera']); ?></span>
          </div>
        </div>

        <div class="card sp-insight mb-4">
          <div class="card-body">
            <h4 class="card-title mb-2">Regla de asignación</h4>
            <p class="text-muted mb-0">Cuando un cupo queda libre por salida, cancelación o vencimiento, SMARTPARK toma la solicitud más antigua del mismo tipo de vehículo y la convierte en reserva de 15 minutos.</p>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Solicitudes en espera</h4>
            <div class="table-responsive">
              <table class="table table-hover dataTableSimple">
                <thead><tr><th>Código</th><th>Usuario</th><th>Tipo</th><th>Placa</th><th>Vehículo</th><th>Espera</th><th>Estado</th></tr></thead>
                <tbody>
                  <?php while ($r = mysqli_fetch_assoc($lista)) { ?>
                    <tr>
                      <td><?php echo sp_escape($r['codigo_reserva']); ?></td>
                      <td><?php echo sp_escape($r['cedula'] . ' - ' . $r['nombres']); ?><br><small><?php echo sp_escape($r['correo']); ?></small></td>
                      <td><?php echo sp_escape($r['tipo_cliente']); ?></td>
                      <td><?php echo sp_escape($r['placa']); ?></td>
                      <td><?php echo sp_escape($r['tipo'] . ' / ' . $r['marca'] . ' ' . $r['modelo']); ?></td>
                      <td><?php echo sp_escape($r['minutos_espera']); ?> min</td>
                      <td><?php echo sp_badge_estado($r['estado']); ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
<script>$('.dataTableSimple').DataTable({language:{url:'//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'}});</script>
</body>
</html>
<?php } else { ?>
  <script>location.href="../acciones/login/exit.php";</script>
<?php } ?>
