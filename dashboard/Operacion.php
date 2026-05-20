<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $vehiculos = sp_vehiculos_disponibles($con);
  $activos = sp_parqueos_activos($con);
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
        <div class="col-md-4 grid-margin">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Registrar ingreso</h4>
            <form method="post">
              <input type="hidden" name="accion" value="registrarIngreso">
              <select name="id_vehiculo" class="form-control mb-3" required>
                <option value="">Vehículo</option>
                <?php while ($v = mysqli_fetch_assoc($vehiculos)) { ?>
                  <option value="<?php echo $v['id_vehiculo']; ?>"><?php echo sp_escape($v['placa'] . ' - ' . $v['tipo'] . ' - ' . $v['nombres']); ?></option>
                <?php } ?>
              </select>
              <button class="btn btn-primary btn-block">Asignar espacio libre</button>
            </form>
            <?php if (isset($_GET['reserva_activa'])) { ?><div class="alert alert-success mt-3">Reserva confirmada como ingreso activo.</div><?php } ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_espacio') { ?><div class="alert alert-danger mt-3">No hay espacios libres para ese tipo de vehículo.</div><?php } ?>
          </div></div>
        </div>
        <div class="col-md-8 grid-margin">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Registrar salida y pago</h4>
            <div class="table-responsive">
              <table class="table table-hover dataTableSimple">
                <thead><tr><th>Placa</th><th>Cliente</th><th>Espacio</th><th>Ingreso</th><th>Pago</th></tr></thead>
                <tbody>
                  <?php while ($p = mysqli_fetch_assoc($activos)) { ?>
                    <tr>
                      <td><?php echo sp_escape($p['placa']); ?></td>
                      <td><?php echo sp_escape($p['nombres']); ?></td>
                      <td><?php echo sp_escape($p['codigo']); ?></td>
                      <td><?php echo sp_escape($p['fecha_ingreso'] . ' ' . $p['hora_ingreso']); ?></td>
                      <td>
                        <form method="post" class="d-flex confirm-form" data-confirm="¿Cerrar parqueo y registrar pago?">
                          <input type="hidden" name="accion" value="registrarSalidaPago">
                          <input type="hidden" name="id_parqueo" value="<?php echo $p['id_parqueo']; ?>">
                          <select name="metodo_pago" class="form-control mr-2" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="nequi">Nequi</option>
                            <option value="daviplata">Daviplata</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="pasarela">Pasarela</option>
                          </select>
                          <button class="btn btn-success btn-sm">Cerrar</button>
                        </form>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div></div>
        </div>
      </div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
<script src="../assets/custom/js/reservas_countdown.js"></script>
<script>$('.dataTableSimple').DataTable({language:{url:'//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'}});</script>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
