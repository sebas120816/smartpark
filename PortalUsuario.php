<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('config/config.php');
date_default_timezone_set("America/Bogota");

function portal_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function portal_badge($estado)
{
    $clases = [
        'reservado' => 'badge-warning',
        'espera' => 'badge-info',
        'activo' => 'badge-danger',
        'finalizado' => 'badge-success',
        'vencido' => 'badge-secondary',
        'cancelado' => 'badge-dark',
    ];
    $class = $clases[$estado] ?? 'badge-secondary';
    return '<span class="badge ' . $class . '">' . portal_escape($estado) . '</span>';
}

mysqli_query($con, "UPDATE tbl_parqueos p
    INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
    SET p.estado='vencido', e.estado='libre'
    WHERE p.estado='reservado' AND p.reserva_expira_en < NOW()");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cancelar') {
    $token = trim($_POST['token_publico'] ?? '');
    $motivo = trim($_POST['motivo_cancelacion'] ?? 'Cancelación solicitada desde portal');
    if ($token !== '') {
        $stmt = mysqli_prepare($con, "UPDATE tbl_parqueos p
            LEFT JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
            SET p.estado='cancelado',
                p.cancelado_en=NOW(),
                p.motivo_cancelacion=?,
                e.estado=IF(e.id_espacio IS NULL, e.estado, 'libre')
            WHERE p.token_publico=? AND p.estado IN ('reservado','espera')");
        mysqli_stmt_bind_param($stmt, "ss", $motivo, $token);
        mysqli_stmt_execute($stmt);
        header("Location: PortalUsuario.php?consulta=" . urlencode($token) . "&cancelada=1");
        exit;
    }
}

$consulta = trim($_GET['consulta'] ?? '');
$cliente = null;
$vehiculos = null;
$reservas = null;

if ($consulta !== '') {
    $stmtCliente = mysqli_prepare($con, "SELECT DISTINCT c.*
        FROM tbl_clientes c
        LEFT JOIN tbl_parqueos p ON p.id_cliente=c.id_cliente
        WHERE c.cedula=? OR p.codigo_reserva=? OR p.token_publico=?
        LIMIT 1");
    mysqli_stmt_bind_param($stmtCliente, "sss", $consulta, $consulta, $consulta);
    mysqli_stmt_execute($stmtCliente);
    $cliente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCliente));

    if ($cliente) {
        $idCliente = (int)$cliente['id_cliente'];
        $stmtVehiculos = mysqli_prepare($con, "SELECT * FROM tbl_vehiculos WHERE id_cliente=? ORDER BY creado_en DESC");
        mysqli_stmt_bind_param($stmtVehiculos, "i", $idCliente);
        mysqli_stmt_execute($stmtVehiculos);
        $vehiculos = mysqli_stmt_get_result($stmtVehiculos);

        $stmtReservas = mysqli_prepare($con, "SELECT p.*, v.placa, v.tipo, v.marca, v.modelo, e.codigo AS espacio, e.piso
            FROM tbl_parqueos p
            INNER JOIN tbl_vehiculos v ON v.id_vehiculo=p.id_vehiculo
            LEFT JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
            WHERE p.id_cliente=?
            ORDER BY FIELD(p.estado,'reservado','espera','activo','vencido','cancelado','finalizado'), p.creado_en DESC
            LIMIT 30");
        mysqli_stmt_bind_param($stmtReservas, "i", $idCliente);
        mysqli_stmt_execute($stmtReservas);
        $reservas = mysqli_stmt_get_result($stmtReservas);
    }
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
          <a class="btn btn-primary btn-sm" href="ReservarParqueadero.php">Reservar</a>
        </div>
      </div>
    </div>

    <main class="container py-5">
      <div class="row align-items-start">
        <div class="col-lg-8 grid-margin">
          <span class="sp-pill mb-3"><i class="bi bi-person-badge"></i> Portal de usuario</span>
          <h1 class="font-weight-bold mb-2">Mi SMARTPARK</h1>
          <p class="text-muted mb-4">Consulta tus reservas, vehículos e historial usando tu cédula, código de reserva o QR seguro.</p>

          <div class="card sp-reservation-card mb-4">
            <div class="card-body p-4">
              <form method="get" class="row">
                <div class="col-md-9">
                  <input class="form-control" name="consulta" value="<?php echo portal_escape($consulta); ?>" placeholder="Cédula, código de reserva o token QR" required>
                </div>
                <div class="col-md-3 mt-3 mt-md-0">
                  <button class="btn btn-primary btn-block">Consultar</button>
                </div>
              </form>
            </div>
          </div>

          <?php if (isset($_GET['cancelada'])) { ?>
            <div class="alert alert-success">Reserva cancelada correctamente.</div>
          <?php } ?>
          <?php if ($consulta !== '' && !$cliente) { ?>
            <div class="alert alert-danger">No encontramos información asociada a esa consulta.</div>
          <?php } ?>

          <?php if ($cliente) { ?>
            <div class="card mb-4"><div class="card-body">
              <div class="d-md-flex justify-content-between align-items-center">
                <div>
                  <h4 class="mb-1"><?php echo portal_escape($cliente['nombres']); ?></h4>
                  <p class="text-muted mb-md-0"><?php echo portal_escape($cliente['cedula']); ?> · <?php echo portal_escape($cliente['tipo_cliente']); ?> · <?php echo portal_escape($cliente['telefono']); ?></p>
                </div>
                <span class="sp-pill mt-3 mt-md-0"><i class="bi bi-shield-check"></i> Perfil verificado</span>
              </div>
            </div></div>

            <div class="card mb-4"><div class="card-body">
              <h4 class="card-title">Reservas e historial</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead><tr><th>Estado</th><th>Código</th><th>Vehículo</th><th>Espacio</th><th>Fecha</th><th>Acción</th></tr></thead>
                  <tbody>
                  <?php while ($r = mysqli_fetch_assoc($reservas)) { ?>
                    <tr>
                      <td><?php echo portal_badge($r['estado']); ?></td>
                      <td><?php echo portal_escape($r['codigo_reserva']); ?></td>
                      <td><?php echo portal_escape($r['placa'] . ' / ' . $r['marca'] . ' ' . $r['modelo']); ?></td>
                      <td><?php echo $r['estado'] === 'espera' ? 'Pendiente' : portal_escape(($r['espacio'] ?? '-') . ' Piso ' . ($r['piso'] ?? '-')); ?></td>
                      <td><?php echo portal_escape($r['fecha_ingreso'] . ' ' . $r['hora_ingreso']); ?></td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="ConsultarReserva.php?codigo=<?php echo urlencode($r['token_publico'] ?: $r['codigo_reserva']); ?>">Ver QR</a>
                        <?php if (in_array($r['estado'], ['reservado','espera'], true) && !empty($r['token_publico'])) { ?>
                          <button class="btn btn-outline-danger btn-sm" type="button" data-toggle="collapse" data-target="#cancelar<?php echo (int)$r['id_parqueo']; ?>">Cancelar</button>
                        <?php } ?>
                      </td>
                    </tr>
                    <?php if (in_array($r['estado'], ['reservado','espera'], true) && !empty($r['token_publico'])) { ?>
                      <tr class="collapse" id="cancelar<?php echo (int)$r['id_parqueo']; ?>">
                        <td colspan="6">
                          <form method="post" class="d-md-flex">
                            <input type="hidden" name="accion" value="cancelar">
                            <input type="hidden" name="token_publico" value="<?php echo portal_escape($r['token_publico']); ?>">
                            <input class="form-control mr-md-2 mb-2 mb-md-0" name="motivo_cancelacion" maxlength="180" placeholder="Motivo de cancelación">
                            <button class="btn btn-outline-danger">Confirmar cancelación</button>
                          </form>
                        </td>
                      </tr>
                    <?php } ?>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
            </div></div>
          <?php } ?>
        </div>

        <div class="col-lg-4 grid-margin">
          <div class="sp-reservation-side">
            <div class="sp-public-image-card mb-4">
              <img src="assets/custom/imgs/smartpark-login-estudiantes.png" alt="Usuarios SMARTPARK">
              <div class="sp-public-image-caption">
                <strong>Control desde el celular</strong>
                <span>Consulta, cancela y valida tu QR en segundos.</span>
              </div>
            </div>
            <?php if ($cliente && $vehiculos) { ?>
              <div class="card"><div class="card-body">
                <h4 class="card-title">Mis vehículos</h4>
                <?php while ($v = mysqli_fetch_assoc($vehiculos)) { ?>
                  <div class="sp-preview-row"><span><?php echo portal_escape(strtoupper($v['tipo'])); ?></span><strong><?php echo portal_escape($v['placa']); ?></strong></div>
                <?php } ?>
              </div></div>
            <?php } else { ?>
              <div class="card sp-insight"><div class="card-body">
                <h4 class="card-title">Qué puedes hacer</h4>
                <div class="sp-rule-item"><i class="bi bi-calendar-check"></i><div><strong>Ver reserva activa</strong><p class="text-muted mb-0">Estado, cupo asignado y tiempo restante.</p></div></div>
                <div class="sp-rule-item"><i class="bi bi-x-circle"></i><div><strong>Cancelar a tiempo</strong><p class="text-muted mb-0">Libera el cupo para otra persona.</p></div></div>
                <div class="sp-rule-item"><i class="bi bi-clock-history"></i><div><strong>Consultar historial</strong><p class="text-muted mb-0">Revisa tus movimientos anteriores.</p></div></div>
              </div></div>
            <?php } ?>
          </div>
        </div>
      </div>
    </main>
  </div>
  <?php include('basesLogin/brand_footer.php'); ?>
  <?php include('basesLogin/footerJS.html'); ?>
</body>
</html>
