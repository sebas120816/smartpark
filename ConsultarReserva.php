<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('config/config.php');
date_default_timezone_set("America/Bogota");

function qr_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';
$reserva = null;
$mensajeOk = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cancelar_publica') {
    $token = trim($_POST['token_publico'] ?? '');
    $motivo = trim($_POST['motivo_cancelacion'] ?? 'Cancelación solicitada por usuario');
    if ($token !== '') {
        $stmtCancel = mysqli_prepare($con, "UPDATE tbl_parqueos p
            LEFT JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
            SET p.estado='cancelado',
                p.cancelado_en=NOW(),
                p.motivo_cancelacion=?,
                e.estado=IF(e.id_espacio IS NULL, e.estado, 'libre')
            WHERE p.token_publico=? AND p.estado IN ('reservado','espera')");
        mysqli_stmt_bind_param($stmtCancel, "ss", $motivo, $token);
        mysqli_stmt_execute($stmtCancel);
        header("Location: ConsultarReserva.php?codigo=" . urlencode($token) . "&cancelada=1");
        exit;
    }
}

$expirarSql = "UPDATE tbl_parqueos p
    INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
    SET p.estado='vencido', e.estado='libre'
    WHERE p.estado='reservado' AND p.reserva_expira_en < NOW()";
mysqli_query($con, $expirarSql);

if ($codigo !== '') {
    $sql = "SELECT p.*, c.cedula, c.nombres, c.tipo_cliente, v.placa, v.marca, v.modelo, v.color, v.tipo, e.codigo AS espacio, e.piso
        FROM tbl_parqueos p
        INNER JOIN tbl_clientes c ON c.id_cliente=p.id_cliente
        INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
        LEFT JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
        WHERE p.codigo_reserva=? OR p.token_publico=? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $codigo, $codigo);
    mysqli_stmt_execute($stmt);
    $reserva = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include('basesLogin/head.php'); ?>
<link rel="stylesheet" href="assets/custom/css/custom_css.css" />
<body>
  <div class="sp-reservation-shell">
    <div class="sp-public-topbar">
      <div class="container">
        <a class="sp-proposal-brand" href="Presentacion.php">
          <img src="assets/custom/imgs/logo.png" alt="SMARTPARK">
          <span>SMARTPARK</span>
        </a>
        <div>
          <a class="btn btn-outline-primary btn-sm mr-2" href="Disponibilidad.php">Disponibilidad</a>
          <a class="btn btn-outline-primary btn-sm mr-2" href="PortalUsuario.php">Mi portal</a>
          <a class="btn btn-primary btn-sm" href="ReservarParqueadero.php">Reservar</a>
        </div>
      </div>
    </div>

    <main class="container py-5">
      <div class="row align-items-start">
        <div class="col-lg-7 mx-auto grid-margin">
          <span class="sp-pill mb-3"><i class="bi bi-qr-code-scan"></i> Consulta QR</span>
          <h1 class="font-weight-bold mb-2">Estado de tu reserva</h1>
          <p class="text-muted mb-4">Ingresa el código o escanea el QR para verificar si tu cupo está reservado, activo, vencido o en lista de espera.</p>

          <div class="card sp-reservation-card">
            <div class="card-body p-4">
                <form method="get" class="mb-4">
                  <input class="form-control mb-3" name="codigo" value="<?php echo qr_escape($codigo); ?>" placeholder="Código de reserva" required>
                  <button class="btn btn-primary btn-block">Consultar</button>
                </form>

                <?php if ($codigo !== '' && !$reserva) { ?>
                  <div class="alert alert-danger">No encontramos una reserva con ese código.</div>
                <?php } ?>
                <?php if (isset($_GET['cancelada'])) { ?>
                  <div class="alert alert-success">La reserva fue cancelada correctamente y el cupo quedó disponible.</div>
                <?php } ?>

                <?php if ($reserva) { ?>
                  <div class="sp-status-ticket mb-4">
                    <?php
                      $badgeClass = [
                        'reservado' => 'badge-warning',
                        'activo' => 'badge-danger',
                        'finalizado' => 'badge-success',
                        'vencido' => 'badge-secondary',
                        'cancelado' => 'badge-dark',
                        'espera' => 'badge-info',
                      ][$reserva['estado']] ?? 'badge-secondary';
                    ?>
                    <div>
                      <span class="badge <?php echo $badgeClass; ?>" style="font-size: 14px;"><?php echo qr_escape($reserva['estado']); ?></span>
                      <h3 class="mb-0 mt-2"><?php echo qr_escape($reserva['codigo_reserva']); ?></h3>
                    </div>
                    <i class="bi bi-shield-check"></i>
                  </div>
                  <div class="sp-public-detail-grid">
                    <div><span>Código</span><strong><?php echo qr_escape($reserva['codigo_reserva']); ?></strong></div>
                    <div><span>Usuario</span><strong><?php echo qr_escape($reserva['cedula'] . ' - ' . $reserva['nombres']); ?></strong></div>
                    <div><span>Tipo usuario</span><strong><?php echo qr_escape($reserva['tipo_cliente']); ?></strong></div>
                    <div><span>Vehículo</span><strong><?php echo qr_escape($reserva['placa'] . ' / ' . $reserva['marca'] . ' ' . $reserva['modelo']); ?></strong></div>
                    <div><span>Espacio</span><strong><?php echo $reserva['estado'] === 'espera' ? 'Pendiente por asignar' : qr_escape($reserva['espacio'] . ' / Piso ' . $reserva['piso']); ?></strong></div>
                    <div><span>Expira</span><strong><?php echo $reserva['estado'] === 'espera' ? 'Se activará cuando haya cupo' : qr_escape($reserva['reserva_expira_en']); ?></strong></div>
                    <?php if ($reserva['estado'] === 'espera') { ?>
                      <div class="full"><span>Mensaje</span><strong>Estás en lista de espera. SMARTPARK asignará un cupo automáticamente cuando se libere uno compatible con tu vehículo.</strong></div>
                    <?php } ?>
                    <?php if ($reserva['estado'] === 'reservado') { ?>
                      <div class="full"><span>Tiempo restante</span><strong><span class="countdown text-warning font-weight-bold" data-expira="<?php echo qr_escape($reserva['reserva_expira_en']); ?>"></span></strong></div>
                    <?php } ?>
                    <?php if ($reserva['estado'] === 'cancelado' && !empty($reserva['motivo_cancelacion'])) { ?>
                      <div class="full"><span>Motivo cancelación</span><strong><?php echo qr_escape($reserva['motivo_cancelacion']); ?></strong></div>
                    <?php } ?>
                  </div>
                  <?php if (in_array($reserva['estado'], ['reservado', 'espera'], true) && !empty($reserva['token_publico'])) { ?>
                    <form method="post" class="mt-4 confirm-form" data-confirm="¿Cancelar esta reserva? El cupo se liberará inmediatamente.">
                      <input type="hidden" name="accion" value="cancelar_publica">
                      <input type="hidden" name="token_publico" value="<?php echo qr_escape($reserva['token_publico']); ?>">
                      <label class="font-weight-bold">Cancelar reserva</label>
                      <input class="form-control mb-2" name="motivo_cancelacion" maxlength="180" placeholder="Motivo opcional">
                      <button class="btn btn-outline-danger btn-block">Cancelar mi reserva</button>
                    </form>
                  <?php } ?>
                <?php } ?>

                <div class="text-center mt-4">
                  <a class="btn btn-outline-primary btn-block" href="ReservarParqueadero.php">Crear una reserva</a>
                </div>
              </div>
            </div>
          </div>
        <div class="col-lg-4 d-none d-lg-block">
          <div class="sp-reservation-side">
            <div class="sp-public-image-card mb-4">
              <img src="assets/custom/imgs/smartpark-login-estudiantes.png" alt="Estudiantes consultando SMARTPARK desde el celular">
              <div class="sp-public-image-caption">
                <strong>Consulta desde cualquier celular</strong>
                <span>El QR permite validar estado sin entrar al panel administrativo.</span>
              </div>
            </div>
            <div class="card sp-insight">
              <div class="card-body">
                <h4 class="card-title">Acceso rápido</h4>
                <div class="sp-rule-item"><i class="bi bi-qr-code"></i><div><strong>QR público</strong><p class="text-muted mb-0">Consulta desde el celular sin ingresar al panel administrativo.</p></div></div>
                <div class="sp-rule-item"><i class="bi bi-clock-history"></i><div><strong>Tiempo real</strong><p class="text-muted mb-0">Las reservas vencidas se liberan automáticamente.</p></div></div>
                <div class="sp-rule-item"><i class="bi bi-whatsapp"></i><div><strong>WhatsApp</strong><p class="text-muted mb-0">Ideal para atención y reservas asistidas.</p></div></div>
                <a href="WhatsAppReserva.php?origen=consulta_qr" target="_blank" class="btn sp-whatsapp-btn btn-block mt-3"><i class="bi bi-whatsapp"></i> Ayuda por WhatsApp</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <?php include('basesLogin/brand_footer.php'); ?>
  <?php include('basesLogin/footerJS.html'); ?>
  <script src="assets/custom/js/reservas_countdown.js"></script>
</body>
</html>
