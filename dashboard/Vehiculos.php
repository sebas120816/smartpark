<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $clientes = sp_clientes($con);
  $totalClientes = mysqli_num_rows($clientes);
  $vehiculos = sp_vehiculos($con);
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
      <div class="row">
        <div class="col-md-4 grid-margin"><div class="card"><div class="card-body">
          <h4 class="card-title">Registrar vehículo</h4>
          <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger">Revisa que hayas seleccionado cliente y tipo de vehículo.</div>
          <?php } ?>
          <?php if ($totalClientes === 0) { ?>
            <div class="alert alert-warning">Primero registra al menos un cliente.</div>
            <a class="btn btn-outline-primary btn-block mb-3" href="Clientes.php">Crear cliente</a>
          <?php } ?>
          <form method="post" autocomplete="off">
            <input type="hidden" name="accion" value="crearVehiculo">
            <label>Cliente</label>
            <select class="form-control mb-3" name="id_cliente" required>
              <option value="" selected disabled>Seleccione un cliente</option>
              <?php while ($c = mysqli_fetch_assoc($clientes)) { ?><option value="<?php echo $c['id_cliente']; ?>"><?php echo sp_escape($c['cedula'] . ' - ' . $c['nombres']); ?></option><?php } ?>
            </select>
            <input class="form-control mb-3" name="placa" placeholder="Placa" required>
            <input class="form-control mb-3" name="marca" placeholder="Marca" required>
            <input class="form-control mb-3" name="modelo" placeholder="Modelo" required>
            <input class="form-control mb-3" name="color" placeholder="Color" required>
            <label>Tipo de vehículo</label>
            <select class="form-control mb-3" name="tipo" required>
              <option value="" selected disabled>Seleccione un tipo</option>
              <option value="auto">Auto</option>
              <option value="moto">Moto</option>
            </select>
            <button class="btn btn-primary btn-block" <?php echo $totalClientes === 0 ? 'disabled' : ''; ?>>Guardar vehículo</button>
          </form>
        </div></div></div>
        <div class="col-md-8 grid-margin"><div class="card"><div class="card-body">
          <h4 class="card-title">Vehículos registrados</h4>
          <div class="table-responsive"><table class="table table-hover dataTableSimple">
            <thead><tr><th>Placa</th><th>Cliente</th><th>Tipo</th><th>Marca</th><th>Modelo</th><th>Color</th></tr></thead>
            <tbody><?php while ($v = mysqli_fetch_assoc($vehiculos)) { ?><tr><td><?php echo sp_escape($v['placa']); ?></td><td><?php echo sp_escape($v['nombres']); ?></td><td><?php echo sp_escape($v['tipo']); ?></td><td><?php echo sp_escape($v['marca']); ?></td><td><?php echo sp_escape($v['modelo']); ?></td><td><?php echo sp_escape($v['color']); ?></td></tr><?php } ?></tbody>
          </table></div>
        </div></div></div>
      </div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
<script>$('.dataTableSimple').DataTable({language:{url:'//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'}});</script>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
