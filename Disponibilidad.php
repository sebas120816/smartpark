<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('config/config.php');
date_default_timezone_set("America/Bogota");

function disponibilidad_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

mysqli_query($con, "UPDATE tbl_parqueos p
    INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
    SET p.estado='vencido', e.estado='libre'
    WHERE p.estado='reservado' AND p.reserva_expira_en < NOW()");

$stats = [
    'total' => 0,
    'libres' => 0,
    'reservados' => 0,
    'ocupados' => 0,
];

$resultStats = mysqli_query($con, "SELECT
    COUNT(*) total,
    SUM(estado='libre') libres,
    SUM(estado='reservado') reservados,
    SUM(estado='ocupado') ocupados
    FROM tbl_espacios");
if ($resultStats) {
    $rowStats = mysqli_fetch_assoc($resultStats);
    $stats = array_merge($stats, $rowStats ?: []);
}

$total = max(1, (int)$stats['total']);
$enUso = (int)$stats['reservados'] + (int)$stats['ocupados'];
$ocupacion = round(($enUso / $total) * 100);

if ($ocupacion >= 85) {
    $estado = 'Alta demanda';
    $mensaje = 'Recomendamos reservar antes de llegar y verificar portería.';
    $clase = 'text-danger';
} elseif ($ocupacion >= 60) {
    $estado = 'Demanda media';
    $mensaje = 'Hay cupos, pero la rotación puede cambiar rápido.';
    $clase = 'text-warning';
} else {
    $estado = 'Disponibilidad favorable';
    $mensaje = 'Buen momento para llegar o reservar un espacio.';
    $clase = 'text-success';
}

$porTipo = mysqli_query($con, "SELECT tipo_vehiculo,
    COUNT(*) total,
    SUM(estado='libre') libres,
    SUM(estado='reservado') reservados,
    SUM(estado='ocupado') ocupados
    FROM tbl_espacios
    GROUP BY tipo_vehiculo
    ORDER BY tipo_vehiculo ASC");

$porPiso = mysqli_query($con, "SELECT piso,
    COUNT(*) total,
    SUM(estado='libre') libres,
    SUM(estado='reservado') reservados,
    SUM(estado='ocupado') ocupados
    FROM tbl_espacios
    GROUP BY piso
    ORDER BY piso ASC");

$actualizado = date('d/m/Y h:i A');
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
          <a class="btn btn-outline-primary btn-sm mr-2" href="ReservarParqueadero.php">Reservar</a>
          <a class="btn btn-outline-primary btn-sm mr-2" href="PortalUsuario.php">Mi portal</a>
          <a class="btn btn-primary btn-sm" href="ConsultarReserva.php">Consultar QR</a>
        </div>
      </div>
    </div>
  <main class="content-wrapper px-4" style="min-height: 100vh; background: transparent;">
    <div class="container py-5">
      <div class="sp-live-hero mb-4">
        <div>
          <span class="sp-pill mb-3"><i class="bi bi-broadcast"></i> Disponibilidad en vivo</span>
          <h1 class="font-weight-bold mb-2">Cupos SMARTPARK Zona Segura ECCI</h1>
          <p class="mb-0">Consulta el estado del parqueadero antes de llegar. La información se actualiza automáticamente cada 30 segundos.</p>
          <div class="sp-live-actions mt-4">
            <a class="btn btn-light" href="ReservarParqueadero.php"><i class="bi bi-calendar-plus"></i> Reservar cupo</a>
            <a class="btn btn-outline-light" href="ConsultarReserva.php"><i class="bi bi-qr-code-scan"></i> Consultar QR</a>
          </div>
        </div>
        <div class="sp-live-status">
          <span>Actualizado</span>
          <strong><?php echo disponibilidad_escape($actualizado); ?></strong>
          <small class="<?php echo $clase; ?>"><?php echo disponibilidad_escape($estado); ?></small>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card sp-kpi-card"><div class="card-body">
            <span class="sp-kpi-icon"><i class="bi bi-check2-circle"></i></span>
            <p class="sp-kpi-label">Libres</p>
            <h3 class="sp-kpi-value text-success"><?php echo disponibilidad_escape($stats['libres']); ?></h3>
            <p class="sp-kpi-note mb-0">Cupos disponibles</p>
          </div></div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card sp-kpi-card"><div class="card-body">
            <span class="sp-kpi-icon"><i class="bi bi-calendar-check"></i></span>
            <p class="sp-kpi-label">Reservados</p>
            <h3 class="sp-kpi-value text-warning"><?php echo disponibilidad_escape($stats['reservados']); ?></h3>
            <p class="sp-kpi-note mb-0">En espera de llegada</p>
          </div></div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card sp-kpi-card"><div class="card-body">
            <span class="sp-kpi-icon"><i class="bi bi-car-front"></i></span>
            <p class="sp-kpi-label">Ocupados</p>
            <h3 class="sp-kpi-value text-danger"><?php echo disponibilidad_escape($stats['ocupados']); ?></h3>
            <p class="sp-kpi-note mb-0">Vehículos activos</p>
          </div></div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card sp-kpi-card"><div class="card-body">
            <span class="sp-kpi-icon"><i class="bi bi-speedometer2"></i></span>
            <p class="sp-kpi-label">Ocupación</p>
            <h3 class="sp-kpi-value <?php echo $clase; ?>"><?php echo $ocupacion; ?>%</h3>
            <p class="sp-kpi-note mb-0"><?php echo disponibilidad_escape($estado); ?></p>
          </div></div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-8 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title mb-0">Mapa operativo por piso</h4>
                <span class="text-muted small"><span class="sp-status-dot sp-dot-free"></span>Libre <span class="sp-status-dot sp-dot-reserved ml-2"></span>Reservado <span class="sp-status-dot sp-dot-occupied ml-2"></span>Ocupado</span>
              </div>
              <?php while ($piso = mysqli_fetch_assoc($porPiso)) {
                $usoPiso = round((((int)$piso['ocupados'] + (int)$piso['reservados']) / max(1, (int)$piso['total'])) * 100);
              ?>
                <div class="sp-floor-card mb-4">
                  <div class="d-flex justify-content-between mb-2">
                    <strong>Piso <?php echo disponibilidad_escape($piso['piso']); ?></strong>
                    <span><?php echo $usoPiso; ?>% en uso</span>
                  </div>
                  <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar" style="width: <?php echo $usoPiso; ?>%;"></div>
                  </div>
                  <div class="sp-mini-slots" aria-label="Representación visual del piso <?php echo disponibilidad_escape($piso['piso']); ?>">
                    <?php
                      $slotIndex = 0;
                      $slotCounts = [
                        'libre' => (int)$piso['libres'],
                        'reservado' => (int)$piso['reservados'],
                        'ocupado' => (int)$piso['ocupados'],
                      ];
                      foreach ($slotCounts as $slotEstado => $cantidad) {
                        for ($i = 0; $i < $cantidad; $i++) {
                          $slotIndex++;
                          echo '<span class="sp-mini-slot ' . disponibilidad_escape($slotEstado) . '" title="' . disponibilidad_escape(ucfirst($slotEstado)) . '"></span>';
                        }
                      }
                    ?>
                  </div>
                  <p class="text-muted mt-2 mb-0">
                    Libres: <?php echo disponibilidad_escape($piso['libres']); ?> · Reservados: <?php echo disponibilidad_escape($piso['reservados']); ?> · Ocupados: <?php echo disponibilidad_escape($piso['ocupados']); ?>
                  </p>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="col-lg-4 grid-margin stretch-card">
          <div class="card sp-insight">
            <div class="card-body">
              <h4 class="card-title"><i class="bi bi-stars"></i> Recomendación</h4>
              <h3 class="<?php echo $clase; ?>"><?php echo disponibilidad_escape($estado); ?></h3>
              <p class="text-muted"><?php echo disponibilidad_escape($mensaje); ?></p>
              <hr>
              <div class="sp-phone-card mb-4">
                <i class="bi bi-phone"></i>
                <div>
                  <strong>Experiencia tipo app</strong>
                  <span>Reserva, consulta QR y WhatsApp desde el mismo flujo.</span>
                </div>
              </div>
              <h5 class="mb-3">Por tipo de vehículo</h5>
              <?php while ($tipo = mysqli_fetch_assoc($porTipo)) { ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                  <span><?php echo disponibilidad_escape(ucfirst($tipo['tipo_vehiculo'])); ?></span>
                  <strong><?php echo disponibilidad_escape($tipo['libres']); ?> libres</strong>
                </div>
              <?php } ?>
              <a href="ReservarParqueadero.php" class="btn btn-primary btn-block mt-4">Reservar ahora</a>
              <a href="WhatsAppReserva.php?origen=disponibilidad" target="_blank" class="btn sp-whatsapp-btn btn-block">
                <i class="bi bi-whatsapp"></i> Reservar por WhatsApp
              </a>
              <a href="./" class="btn btn-outline-primary btn-block">Ingreso administrativo</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  </div>
  <script>
    setTimeout(function () {
      window.location.reload();
    }, 30000);
  </script>
  <a class="sp-whatsapp-floating" target="_blank" href="WhatsAppReserva.php?origen=boton_flotante_disponibilidad" title="Reservar por WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>
  <?php include('basesLogin/brand_footer.php'); ?>
  <?php include('basesLogin/footerJS.html'); ?>
</body>
</html>
