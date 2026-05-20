<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
  $historial = sp_historial($con, $buscar);
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
      <div class="card"><div class="card-body">
        <h4 class="card-title">Historial por cliente o vehículo</h4>
        <form class="row mb-3" method="get">
          <div class="col-md-10"><input class="form-control" name="buscar" value="<?php echo sp_escape($buscar); ?>" placeholder="Buscar por cliente, cédula o placa"></div>
          <div class="col-md-2"><button class="btn btn-primary btn-block">Buscar</button></div>
        </form>
        <div class="table-responsive"><table class="table table-hover dataTableSimple">
          <thead><tr><th>Recibo</th><th>Placa</th><th>Cliente</th><th>Espacio</th><th>Ingreso</th><th>Salida</th><th>Horas</th><th>Total</th><th>Estado</th></tr></thead>
          <tbody><?php while ($p = mysqli_fetch_assoc($historial)) { ?><tr>
            <td><?php echo sp_escape($p['numero_recibo']); ?></td><td><?php echo sp_escape($p['placa']); ?></td><td><?php echo sp_escape($p['nombres']); ?></td><td><?php echo sp_escape($p['codigo']); ?></td>
            <td><?php echo sp_escape($p['fecha_ingreso'] . ' ' . $p['hora_ingreso']); ?></td><td><?php echo sp_escape($p['fecha_salida'] . ' ' . $p['hora_salida']); ?></td><td><?php echo sp_escape($p['total_horas']); ?></td><td><?php echo sp_format_money($p['valor_total']); ?></td><td><?php echo sp_escape($p['estado']); ?></td>
          </tr><?php } ?></tbody>
        </table></div>
      </div></div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
<script>$('.dataTableSimple').DataTable({searching:false,language:{url:'//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'}});</script>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
