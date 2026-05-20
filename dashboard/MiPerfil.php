<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $nuevoEmail = $_POST['email'];
    if (!empty($_POST['password'])) {
      $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
      $stmt = sp_prepare($con, "UPDATE tbl_usuarios SET nombre=?, email=?, password=? WHERE id_usuario=?", "No se pudo preparar la actualizacion del perfil.");
      mysqli_stmt_bind_param($stmt, "sssi", $nombre, $nuevoEmail, $password, $IdUser);
    } else {
      $stmt = sp_prepare($con, "UPDATE tbl_usuarios SET nombre=?, email=? WHERE id_usuario=?", "No se pudo preparar la actualizacion del perfil.");
      mysqli_stmt_bind_param($stmt, "ssi", $nombre, $nuevoEmail, $IdUser);
    }
    if (!mysqli_stmt_execute($stmt)) {
      sp_db_error($con, "No se pudo actualizar el perfil.");
    }
    $_SESSION['emailUser'] = $nuevoEmail;
    sp_redirect("MiPerfil.php?success=1");
  }

  $stmtPerfil = sp_prepare($con, "SELECT nombre, email, rol, estado, ultima_sesion, creado_en FROM tbl_usuarios WHERE id_usuario=? LIMIT 1", "No se pudo preparar la consulta del perfil.");
  mysqli_stmt_bind_param($stmtPerfil, "i", $IdUser);
  mysqli_stmt_execute($stmtPerfil);
  $perfil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtPerfil));
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
        <div class="row justify-content-center">
          <div class="col-md-6 grid-margin">
            <div class="card">
              <div class="card-body">
                <h4 class="card-title">Mi perfil</h4>
                <?php if (isset($_GET['success'])) { ?>
                  <div class="alert alert-success">Perfil actualizado correctamente.</div>
                <?php } ?>
                <form method="post">
                  <label>Nombre</label>
                  <input class="form-control mb-3" name="nombre" value="<?php echo sp_escape($perfil['nombre']); ?>" required>
                  <label>Correo</label>
                  <input type="email" class="form-control mb-3" name="email" value="<?php echo sp_escape($perfil['email']); ?>" required>
                  <label>Nueva contraseña</label>
                  <input type="password" class="form-control mb-3" name="password" placeholder="Dejar vacio para conservar la actual">
                  <p class="text-muted mb-1">Rol: <?php echo sp_escape($perfil['rol']); ?></p>
                  <p class="text-muted">Ultima sesion: <?php echo sp_escape($perfil['ultima_sesion']); ?></p>
                  <button class="btn btn-primary btn-block">Guardar cambios</button>
                </form>
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
  <script>location.href = "../acciones/login/exit.php";</script>
<?php } ?>
