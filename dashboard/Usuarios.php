<?php
session_start();
if (isset($_SESSION['emailUser']) != "" && $_SESSION['rol'] == 'Administrador') {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $usuarios = sp_usuarios($con);
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
          <h4 class="card-title">Crear usuario</h4>
          <form method="post">
            <input type="hidden" name="accion" value="crearUsuario">
            <input class="form-control mb-3" name="nombre" placeholder="Nombre completo" required>
            <input type="email" class="form-control mb-3" name="email" placeholder="Correo" required>
            <input type="password" class="form-control mb-3" name="password" placeholder="Contraseña" required>
            <select class="form-control mb-3" name="rol" required>
              <option value="">Rol</option>
              <option value="Administrador">Administrador</option>
              <option value="Caja">Caja</option>
              <option value="Control">Control</option>
            </select>
            <button class="btn btn-primary btn-block">Guardar usuario</button>
          </form>
        </div></div></div>
        <div class="col-md-8 grid-margin"><div class="card"><div class="card-body">
          <h4 class="card-title">Usuarios del sistema</h4>
          <div class="table-responsive"><table class="table table-hover dataTableSimple">
            <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Última sesión</th></tr></thead>
            <tbody><?php while ($u = mysqli_fetch_assoc($usuarios)) { ?><tr><td><?php echo sp_escape($u['nombre']); ?></td><td><?php echo sp_escape($u['email']); ?></td><td><?php echo sp_escape($u['rol']); ?></td><td><?php echo $u['estado'] ? 'Activo' : 'Inactivo'; ?></td><td><?php echo sp_escape($u['ultima_sesion']); ?></td></tr><?php } ?></tbody>
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
