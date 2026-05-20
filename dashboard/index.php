<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $stats = sp_stats($con);
  $ia = sp_ia_insight_operativo($con);
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
            <div class="sp-page-title">
              <div>
                <h2 class="font-weight-bold">SMARTPARK - Zona Segura ECCI</h2>
                <p class="text-muted mb-0">Panel ejecutivo para controlar espacios, vehículos, cobros y reportes del parqueadero.</p>
              </div>
              <span class="sp-pill"><i class="bi bi-activity"></i> Riesgo operativo: <span id="spRiesgo"><?php echo sp_escape($ia['riesgo']); ?></span></span>
            </div>
            <div class="row">
              <div class="col-md-3 grid-margin stretch-card">
                <div class="card sp-kpi"><div class="card-body"><span class="sp-kpi-icon"><i class="bi bi-grid"></i></span><p class="sp-kpi-label">Capacidad</p><h3 class="sp-kpi-value" id="spTotal"><?php echo $stats['espacios']; ?></h3><p class="text-muted mb-0">Espacios totales</p></div></div>
              </div>
              <div class="col-md-3 grid-margin stretch-card">
                <div class="card sp-kpi"><div class="card-body"><span class="sp-kpi-icon"><i class="bi bi-check2-circle"></i></span><p class="sp-kpi-label">Libres</p><h3 class="sp-kpi-value text-success" id="spLibres"><?php echo $stats['libres']; ?></h3><p class="text-muted mb-0">Disponibles ahora</p></div></div>
              </div>
              <div class="col-md-3 grid-margin stretch-card">
                <div class="card sp-kpi"><div class="card-body"><span class="sp-kpi-icon"><i class="bi bi-car-front"></i></span><p class="sp-kpi-label">Ocupados</p><h3 class="sp-kpi-value text-danger" id="spOcupados"><?php echo $stats['ocupados']; ?></h3><p class="text-muted mb-0"><span id="spOcupacion"><?php echo $ia['ocupacion']; ?></span>% de ocupación</p></div></div>
              </div>
              <div class="col-md-3 grid-margin stretch-card">
                <div class="card sp-kpi"><div class="card-body"><span class="sp-kpi-icon"><i class="bi bi-cash-coin"></i></span><p class="sp-kpi-label">Ingresos hoy</p><h3 class="sp-kpi-value" id="spIngresosHoy"><?php echo sp_format_money($stats['ingresos_hoy']); ?></h3><p class="text-muted mb-0">Pagos registrados</p></div></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-8 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Parqueos activos</h4>
                    <div class="table-responsive">
                      <table class="table table-hover">
                        <thead><tr><th>Placa</th><th>Cliente</th><th>Espacio</th><th>Ingreso</th><th>Estado</th></tr></thead>
                        <tbody id="spParqueosActivos">
                          <?php $activos = sp_parqueos_activos($con); while ($p = mysqli_fetch_assoc($activos)) { ?>
                            <tr>
                              <td><?php echo sp_escape($p['placa']); ?></td>
                              <td><?php echo sp_escape($p['nombres']); ?></td>
                              <td><?php echo sp_escape($p['codigo']); ?></td>
                              <td><?php echo sp_escape($p['fecha_ingreso'] . ' ' . $p['hora_ingreso']); ?></td>
                              <td><span class="badge badge-danger">Ocupado</span></td>
                            </tr>
                          <?php } ?>
                          <?php if (mysqli_num_rows($activos) === 0) { ?>
                            <tr><td colspan="5" class="text-center text-muted">No hay parqueos activos en este momento.</td></tr>
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4 grid-margin stretch-card">
                <div class="card sp-insight">
                  <div class="card-body">
                    <h4 class="card-title"><i class="bi bi-stars"></i> IA operativa</h4>
                    <p class="mb-2"><strong>Ocupación actual:</strong> <span id="spInsightOcupacion"><?php echo $ia['ocupacion']; ?></span>%</p>
                    <p class="mb-2"><strong>Promedio en esta hora:</strong> <?php echo $ia['promedio_hora']; ?> ingresos</p>
                    <p class="text-muted mb-3"><?php echo sp_escape($ia['recomendacion']); ?></p>
                    <a class="btn btn-outline-primary btn-block" href="PantallaPorteria.php">Abrir pantalla de portería</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Accesos rápidos</h4>
                    <a class="btn btn-primary btn-block mb-2" href="Operacion.php">Registrar ingreso o salida</a>
                    <a class="btn btn-outline-primary btn-block mb-2" href="Clientes.php">Crear cliente</a>
                    <a class="btn btn-outline-primary btn-block mb-2" href="Vehiculos.php">Registrar vehículo</a>
                    <a class="btn btn-outline-primary btn-block" href="Espacios.php">Ver espacios</a>
                  </div>
                </div>
              </div>
              <div class="col-md-8 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Ocupación por piso</h4>
                    <div class="table-responsive">
                      <table class="table">
                        <thead><tr><th>Piso</th><th>Total</th><th>Libres</th><th>Ocupados</th><th>Uso</th></tr></thead>
                        <tbody id="spPisosDashboard">
                          <?php $pisos = sp_ocupacion_por_piso($con); while ($piso = mysqli_fetch_assoc($pisos)) {
                            $totalPiso = max(1, (int)$piso['total']);
                            $usoPiso = round(((int)$piso['ocupados'] / $totalPiso) * 100);
                          ?>
                            <tr>
                              <td>Piso <?php echo sp_escape($piso['piso']); ?></td>
                              <td><?php echo sp_escape($piso['total']); ?></td>
                              <td><span class="sp-status-dot sp-status-free"></span><?php echo sp_escape($piso['libres']); ?></td>
                              <td><span class="sp-status-dot sp-status-busy"></span><?php echo sp_escape($piso['ocupados']); ?></td>
                              <td>
                                <div class="progress" style="height: 8px;">
                                  <div class="progress-bar" role="progressbar" style="width: <?php echo $usoPiso; ?>%;" aria-valuenow="<?php echo $usoPiso; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                              </td>
                            </tr>
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include 'bases/PageJs.html'; ?>
    <script>
    (function () {
      const endpoint = '../acciones/servicio_api/api_disponibilidad_smartpark.php';

      function money(value) {
        return '$' + Number(value || 0).toLocaleString('es-CO', { maximumFractionDigits: 0 });
      }

      function actualizarPanel() {
        fetch(endpoint, { cache: 'no-store' })
          .then(function (response) { return response.json(); })
          .then(function (data) {
            if (!data.ok) return;
            document.getElementById('spRiesgo').textContent = data.stats.riesgo;
            document.getElementById('spTotal').textContent = data.stats.espacios;
            document.getElementById('spLibres').textContent = data.stats.libres;
            document.getElementById('spOcupados').textContent = data.stats.ocupados;
            document.getElementById('spOcupacion').textContent = data.stats.ocupacion;
            document.getElementById('spInsightOcupacion').textContent = data.stats.ocupacion;
            document.getElementById('spIngresosHoy').textContent = money(data.stats.ingresos_hoy);

            document.getElementById('spPisosDashboard').innerHTML = data.pisos.map(function (piso) {
              return '<tr>' +
                '<td>Piso ' + piso.piso + '</td>' +
                '<td>' + piso.total + '</td>' +
                '<td><span class="sp-status-dot sp-status-free"></span>' + piso.libres + '</td>' +
                '<td><span class="sp-status-dot sp-status-busy"></span>' + piso.ocupados + '</td>' +
                '<td><div class="progress" style="height: 8px;"><div class="progress-bar" role="progressbar" style="width: ' + piso.uso + '%;" aria-valuenow="' + piso.uso + '" aria-valuemin="0" aria-valuemax="100"></div></div></td>' +
              '</tr>';
            }).join('');

            document.getElementById('spParqueosActivos').innerHTML = data.parqueos_activos.length ? data.parqueos_activos.map(function (mov) {
              const ingreso = (mov.fecha_ingreso || '') + ' ' + (mov.hora_ingreso || '');
              return '<tr>' +
                '<td>' + mov.placa + '</td>' +
                '<td>' + mov.nombres + '</td>' +
                '<td>' + mov.codigo + '</td>' +
                '<td>' + ingreso + '</td>' +
                '<td><span class="badge badge-danger">Ocupado</span></td>' +
              '</tr>';
            }).join('') : '<tr><td colspan="5" class="text-center text-muted">No hay parqueos activos en este momento.</td></tr>';
          })
          .catch(function () {});
      }

      actualizarPanel();
      setInterval(actualizarPanel, 5000);
    })();
    </script>
  </body>
  </html>
<?php } else { ?>
  <script>location.href = "../acciones/login/exit.php";</script>
<?php } ?>
