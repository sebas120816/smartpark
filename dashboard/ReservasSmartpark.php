<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $reservas = sp_reservas_pendientes($con);
  $recientes = sp_reservas_recientes($con);
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
      <div class="card mb-4"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Reservas vigentes</h4>
          <a class="btn btn-outline-primary btn-sm" href="../ReservarParqueadero.php">Crear reserva pública</a>
        </div>
        <?php if (isset($_GET['error'])) { ?><div class="alert alert-danger">La reserva ya venció o no puede confirmarse.</div><?php } ?>
        <div class="table-responsive">
          <table class="table table-hover dataTableSimple">
            <thead><tr><th>Código</th><th>Usuario</th><th>Tipo</th><th>Placa</th><th>Vehículo</th><th>Espacio</th><th>Riesgo no-show</th><th>Tiempo</th><th>QR</th><th>Acción</th></tr></thead>
            <tbody>
              <?php while ($r = mysqli_fetch_assoc($reservas)) { ?>
                <tr>
                  <td><?php echo sp_escape($r['codigo_reserva']); ?></td>
                  <td><?php echo sp_escape($r['cedula'] . ' - ' . $r['nombres']); ?></td>
                  <td><?php echo sp_escape($r['tipo_cliente']); ?></td>
                  <td><?php echo sp_escape($r['placa']); ?></td>
                  <td><?php echo sp_escape($r['tipo'] . ' / ' . $r['marca'] . ' ' . $r['modelo']); ?></td>
                  <td><?php echo sp_escape($r['codigo']); ?></td>
                  <td>
                    <?php echo sp_badge_riesgo_no_show($r['riesgo_no_show']); ?><br>
                    <small class="text-muted"><?php echo sp_escape($r['reservas_vencidas_cliente']); ?> vencida(s) de <?php echo sp_escape($r['reservas_cliente']); ?></small>
                  </td>
                  <td><span class="countdown text-warning font-weight-bold" data-expira="<?php echo sp_escape($r['reserva_expira_en']); ?>"></span></td>
                  <td><a target="_blank" href="../ConsultarReserva.php?codigo=<?php echo urlencode($r['codigo_reserva']); ?>">Ver</a></td>
                  <td class="d-flex">
                    <form method="post" class="mr-2 confirm-form" data-confirm="¿Confirmar llegada y activar el parqueo?">
                      <input type="hidden" name="accion" value="confirmarLlegadaReserva">
                      <input type="hidden" name="id_parqueo" value="<?php echo $r['id_parqueo']; ?>">
                      <button class="btn btn-success btn-sm">Llegó</button>
                    </form>
                    <form method="post" class="confirm-form" data-confirm="¿Cancelar esta reserva y liberar el espacio?">
                      <input type="hidden" name="accion" value="cancelarReserva">
                      <input type="hidden" name="id_parqueo" value="<?php echo $r['id_parqueo']; ?>">
                      <input type="hidden" name="motivo_cancelacion" value="Cancelación desde panel administrativo">
                      <button class="btn btn-outline-danger btn-sm">Cancelar</button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div></div>

      <div class="card"><div class="card-body">
        <h4 class="card-title">Reservas recientes</h4>
        <div class="table-responsive">
          <table class="table table-hover dataTableSimple">
            <thead><tr><th>Código</th><th>Cliente</th><th>Placa</th><th>Espacio</th><th>Expira</th><th>Estado</th><th>Motivo</th></tr></thead>
            <tbody>
              <?php while ($r = mysqli_fetch_assoc($recientes)) { ?>
                <tr>
                  <td><?php echo sp_escape($r['codigo_reserva']); ?></td>
                  <td><?php echo sp_escape($r['cedula'] . ' - ' . $r['nombres']); ?></td>
                  <td><?php echo sp_escape($r['placa']); ?></td>
                  <td><?php echo sp_escape($r['codigo']); ?></td>
                  <td><?php echo sp_escape($r['reserva_expira_en']); ?></td>
                  <td><?php echo sp_badge_estado($r['estado']); ?></td>
                  <td><?php echo sp_escape($r['motivo_cancelacion'] ?? '-'); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div></div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
<script src="../assets/custom/js/reservas_countdown.js"></script>
<script>$('.dataTableSimple').DataTable({language:{url:'//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'}});</script>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
