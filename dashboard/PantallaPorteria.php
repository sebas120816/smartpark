<?php
session_start();
include('smartpark_funciones.php');

$stats = sp_stats($con);
$ia = sp_ia_insight_operativo($con);
$pisos = sp_ocupacion_por_piso($con);
$ultimos = sp_ultimos_ingresos($con, 6);
$actualizado = date('d/m/Y h:i A');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include('bases/head.html'); ?>
</head>
<body class="sp-tv-body">
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
      <div>
        <h1 class="font-weight-bold mb-1">SMARTPARK Portería</h1>
        <p class="mb-0 text-white-50">Zona Segura ECCI - Estado de operación en tiempo real</p>
      </div>
      <div class="text-right">
        <p class="mb-1 text-white-50">Actualizado</p>
        <h4 class="mb-0" id="spActualizado"><?php echo sp_escape($actualizado); ?></h4>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 grid-margin">
        <div class="sp-tv-card">
          <p class="text-white-50 mb-2">Cupos libres</p>
          <div class="sp-tv-number text-success" id="spLibres"><?php echo sp_escape($stats['libres']); ?></div>
          <p class="mb-0">de <span id="spTotal"><?php echo sp_escape($stats['espacios']); ?></span> espacios</p>
        </div>
      </div>
      <div class="col-md-4 grid-margin">
        <div class="sp-tv-card">
          <p class="text-white-50 mb-2">Ocupación</p>
          <div class="sp-tv-number text-warning"><span id="spOcupacion"><?php echo sp_escape($ia['ocupacion']); ?></span>%</div>
          <p class="mb-0">Riesgo operativo: <?php echo sp_escape($ia['riesgo']); ?></p>
        </div>
      </div>
      <div class="col-md-4 grid-margin">
        <div class="sp-tv-card">
          <p class="text-white-50 mb-2">Vehículos activos</p>
          <div class="sp-tv-number text-info" id="spActivos"><?php echo sp_escape($stats['activos']); ?></div>
          <p class="mb-0"><?php echo sp_escape($ia['recomendacion']); ?></p>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-5 grid-margin">
        <div class="sp-tv-card h-100">
          <h3 class="mb-4">Ocupación por piso</h3>
          <div id="spPisos">
          <?php while ($piso = mysqli_fetch_assoc($pisos)) {
            $totalPiso = max(1, (int)$piso['total']);
            $usoPiso = round(((int)$piso['ocupados'] / $totalPiso) * 100);
          ?>
            <div class="mb-4">
              <div class="d-flex justify-content-between mb-2">
                <strong>Piso <?php echo sp_escape($piso['piso']); ?></strong>
                <span><?php echo $usoPiso; ?>% ocupado</span>
              </div>
              <div class="progress" style="height: 14px;">
                <div class="progress-bar bg-warning" style="width: <?php echo $usoPiso; ?>%;"></div>
              </div>
              <p class="mt-2 mb-0 text-white-50">
                Libres: <?php echo sp_escape($piso['libres']); ?> · Ocupados: <?php echo sp_escape($piso['ocupados']); ?>
              </p>
            </div>
          <?php } ?>
          </div>
        </div>
      </div>
      <div class="col-lg-7 grid-margin">
        <div class="sp-tv-card h-100">
          <h3 class="mb-4">Últimos movimientos</h3>
          <div class="table-responsive">
            <table class="table text-white">
              <thead>
                <tr><th>Hora</th><th>Placa</th><th>Tipo</th><th>Espacio</th><th>Cliente</th></tr>
              </thead>
              <tbody id="spMovimientos">
                <?php while ($mov = mysqli_fetch_assoc($ultimos)) { ?>
                  <tr>
                    <td><?php echo sp_escape(substr($mov['hora_ingreso'], 0, 5)); ?></td>
                    <td><strong><?php echo sp_escape($mov['placa']); ?></strong></td>
                    <td><?php echo sp_escape(ucfirst($mov['tipo'])); ?></td>
                    <td><?php echo sp_escape($mov['codigo']); ?></td>
                    <td><?php echo sp_escape($mov['nombres']); ?></td>
                  </tr>
                <?php } ?>
                <?php if (mysqli_num_rows($ultimos) === 0) { ?>
                  <tr><td colspan="5" class="text-center text-white-50">Aún no hay movimientos registrados.</td></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
          <p class="text-white-50 mt-3 mb-0">Esta pantalla se actualiza automáticamente cada 5 segundos.</p>
        </div>
      </div>
    </div>
  </div>

  <?php include 'bases/PageJs.html'; ?>
  <script>
  (function () {
    const endpoint = '../acciones/servicio_api/api_disponibilidad_smartpark.php';

    function texto(valor) {
      return String(valor === null || valor === undefined ? '0' : valor);
    }

    function actualizarPantalla() {
      fetch(endpoint, { cache: 'no-store' })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (!data.ok) return;
          document.getElementById('spActualizado').textContent = data.actualizado;
          document.getElementById('spLibres').textContent = texto(data.stats.libres);
          document.getElementById('spTotal').textContent = texto(data.stats.espacios);
          document.getElementById('spOcupacion').textContent = texto(data.stats.ocupacion);
          document.getElementById('spActivos').textContent = texto(data.stats.ocupados);

          document.getElementById('spPisos').innerHTML = data.pisos.map(function (piso) {
            return '<div class="mb-4">' +
              '<div class="d-flex justify-content-between mb-2"><strong>Piso ' + piso.piso + '</strong><span>' + piso.uso + '% ocupado</span></div>' +
              '<div class="progress" style="height: 14px;"><div class="progress-bar bg-warning" style="width: ' + piso.uso + '%;"></div></div>' +
              '<p class="mt-2 mb-0 text-white-50">Libres: ' + piso.libres + ' · Ocupados: ' + piso.ocupados + ' · Reservados: ' + piso.reservados + '</p>' +
            '</div>';
          }).join('');

          document.getElementById('spMovimientos').innerHTML = data.movimientos.length ? data.movimientos.map(function (mov) {
            const hora = mov.hora_ingreso ? mov.hora_ingreso.substring(0, 5) : '';
            return '<tr><td>' + hora + '</td><td><strong>' + mov.placa + '</strong></td><td>' + mov.tipo + '</td><td>' + mov.codigo + '</td><td>' + mov.nombres + '</td></tr>';
          }).join('') : '<tr><td colspan="5" class="text-center text-white-50">Aún no hay movimientos registrados.</td></tr>';
        })
        .catch(function () {});
    }

    actualizarPantalla();
    setInterval(actualizarPantalla, 5000);
  })();
  </script>
</body>
</html>
