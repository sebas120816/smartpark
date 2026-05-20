<?php
session_start();
if (isset($_SESSION['emailUser']) != "" && $_SESSION['rol'] == 'Administrador') {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $tarifas = sp_tarifas($con);
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
        <h4 class="card-title">Configurar tarifas por hora</h4>
        <div class="table-responsive"><table class="table table-hover">
          <thead><tr><th>Tipo</th><th>Valor hora</th><th>Actualizar</th></tr></thead>
          <tbody>
            <?php while ($t = mysqli_fetch_assoc($tarifas)) { ?>
              <tr>
                <td><?php echo sp_escape($t['tipo_vehiculo']); ?></td>
                <td><?php echo sp_format_money($t['valor_hora']); ?></td>
                <td>
                  <form method="post" class="d-flex">
                    <input type="hidden" name="accion" value="actualizarTarifa">
                    <input type="hidden" name="tipo_vehiculo" value="<?php echo sp_escape($t['tipo_vehiculo']); ?>">
                    <input type="number" min="0" step="100" name="valor_hora" class="form-control mr-2" value="<?php echo sp_escape($t['valor_hora']); ?>" required>
                    <button class="btn btn-primary btn-sm">Guardar</button>
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table></div>
      </div></div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
