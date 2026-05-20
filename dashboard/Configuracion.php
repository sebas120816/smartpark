<?php
session_start();
if (isset($_SESSION['emailUser']) != "" && ($_SESSION['rol'] ?? '') === 'Administrador') {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $config = sp_config_institucional();
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
        <div class="col-lg-8 grid-margin stretch-card">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Configuración institucional</h4>
            <p class="text-muted">Personaliza datos visibles en el portal público sin alterar el modelo de 7 tablas.</p>
            <?php if (isset($_GET['success'])) { ?><div class="alert alert-success">Configuración guardada correctamente.</div><?php } ?>
            <form method="post">
              <input type="hidden" name="accion" value="guardarConfiguracion">
              <div class="form-group">
                <label>Nombre del parqueadero</label>
                <input class="form-control" name="nombre_parqueadero" value="<?php echo sp_escape($config['nombre_parqueadero']); ?>" required>
              </div>
              <div class="form-group">
                <label>Universidad</label>
                <input class="form-control" name="universidad" value="<?php echo sp_escape($config['universidad']); ?>" required>
              </div>
              <div class="form-group">
                <label>Tiempo de reserva en minutos</label>
                <input type="number" min="5" max="60" class="form-control" name="tiempo_reserva_minutos" value="<?php echo sp_escape($config['tiempo_reserva_minutos']); ?>" required>
              </div>
              <div class="form-group">
                <label>Horario de atención</label>
                <input class="form-control" name="horario_atencion" value="<?php echo sp_escape($config['horario_atencion']); ?>">
              </div>
              <div class="form-group">
                <label>WhatsApp institucional</label>
                <input class="form-control" name="telefono_whatsapp" value="<?php echo sp_escape($config['telefono_whatsapp']); ?>">
              </div>
              <div class="form-group">
                <label>Mensaje para portal público</label>
                <textarea class="form-control" name="mensaje_portal" rows="3"><?php echo sp_escape($config['mensaje_portal']); ?></textarea>
              </div>
              <button class="btn btn-primary">Guardar configuración</button>
            </form>
          </div></div>
        </div>
        <div class="col-lg-4 grid-margin stretch-card">
          <div class="card sp-insight"><div class="card-body">
            <h4 class="card-title">Vista rápida</h4>
            <div class="sp-preview-row"><span>Parqueadero</span><strong><?php echo sp_escape($config['nombre_parqueadero']); ?></strong></div>
            <div class="sp-preview-row"><span>Institución</span><strong><?php echo sp_escape($config['universidad']); ?></strong></div>
            <div class="sp-preview-row"><span>Reserva</span><strong><?php echo sp_escape($config['tiempo_reserva_minutos']); ?> min</strong></div>
            <div class="sp-preview-row"><span>Horario</span><strong><?php echo sp_escape($config['horario_atencion']); ?></strong></div>
          </div></div>
        </div>
      </div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
