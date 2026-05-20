<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $clientes = sp_clientes($con);
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
            <h4 class="card-title">Nuevo cliente</h4>
            <form method="post">
              <input type="hidden" name="accion" value="crearCliente">
              <input class="form-control mb-3" name="cedula" placeholder="Cédula" required>
              <input class="form-control mb-3" name="nombres" placeholder="Nombres completos" required>
              <input class="form-control mb-3" name="telefono" placeholder="Teléfono" required>
              <input type="email" class="form-control mb-3" name="correo" placeholder="Correo">
              <select class="form-control mb-3" name="tipo_cliente" required>
                <option value="visitante">Visitante</option>
                <option value="estudiante">Estudiante</option>
                <option value="docente">Docente</option>
                <option value="funcionario">Funcionario</option>
              </select>
              <button class="btn btn-primary btn-block">Guardar cliente</button>
            </form>
          </div></div>
        </div>
        <div class="col-md-8 grid-margin">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Clientes registrados</h4>
            <div class="table-responsive">
              <table class="table table-hover dataTableSimple">
                <thead><tr><th>Cédula</th><th>Nombres</th><th>Tipo</th><th>Teléfono</th><th>Correo</th></tr></thead>
                <tbody>
                  <?php while ($c = mysqli_fetch_assoc($clientes)) { ?>
                    <tr><td><?php echo sp_escape($c['cedula']); ?></td><td><?php echo sp_escape($c['nombres']); ?></td><td><?php echo sp_escape($c['tipo_cliente']); ?></td><td><?php echo sp_escape($c['telefono']); ?></td><td><?php echo sp_escape($c['correo']); ?></td></tr>
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
<script>$('.dataTableSimple').DataTable({language:{url:'//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'}});</script>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
