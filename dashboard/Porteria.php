<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
  $resultados = $busqueda !== '' ? sp_buscar_porteria($con, strtoupper($busqueda)) : null;
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
        <div class="col-md-12 grid-margin">
          <div class="card"><div class="card-body">
            <h3 class="font-weight-bold">Modo Portería</h3>
            <p class="text-muted">Busca por QR seguro, código de reserva, placa, cédula o nombre. Verde: válido, amarillo: por vencer, rojo: no disponible.</p>
            <form method="get" class="row">
              <div class="col-md-8">
                <input class="form-control form-control-lg" id="porteriaQuery" name="q" value="<?php echo sp_escape($busqueda); ?>" placeholder="Escanea QR o escribe placa/código/cédula" autofocus required>
              </div>
              <div class="col-md-2">
                <button class="btn btn-primary btn-lg btn-block">Buscar</button>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-outline-primary btn-lg btn-block" id="btnQrScan"><i class="bi bi-camera"></i> QR</button>
              </div>
            </form>
            <div id="qrScannerBox" class="mt-4" style="display:none;">
              <div class="alert alert-info mb-3">Apunta la cámara al QR de la reserva. Al detectarlo, SMARTPARK buscará el código automáticamente.</div>
              <video id="qrVideo" style="width:100%; max-height:360px; border-radius:8px; background:#07111f;" muted playsinline></video>
              <button type="button" class="btn btn-outline-danger btn-sm mt-3" id="btnQrStop">Detener cámara</button>
            </div>
          </div></div>
        </div>
      </div>

      <?php if ($resultados) { ?>
      <div class="row">
        <div class="col-md-12">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Resultados</h4>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead><tr><th>Estado</th><th>Código</th><th>Usuario</th><th>Placa</th><th>Espacio</th><th>Riesgo</th><th>Expira/Ingreso</th><th>Acción</th></tr></thead>
                <tbody>
                <?php while ($r = mysqli_fetch_assoc($resultados)) { ?>
                  <tr>
                    <?php
                      $minutosRestantes = $r['estado'] === 'reservado' ? floor((strtotime($r['reserva_expira_en']) - time()) / 60) : null;
                      $semaforo = $r['estado'] === 'reservado' && $minutosRestantes >= 5 ? 'success' : ($r['estado'] === 'reservado' ? 'warning' : ($r['estado'] === 'activo' ? 'danger' : 'secondary'));
                    ?>
                    <td><span class="sp-porteria-light <?php echo sp_escape($semaforo); ?>"></span><?php echo sp_badge_estado($r['estado']); ?></td>
                    <td><?php echo sp_escape($r['codigo_reserva']); ?></td>
                    <td><?php echo sp_escape($r['cedula'] . ' - ' . $r['nombres']); ?><br><small><?php echo sp_escape($r['tipo_cliente']); ?></small></td>
                    <td><?php echo sp_escape($r['placa'] . ' / ' . $r['tipo']); ?></td>
                    <td><?php echo $r['estado'] === 'espera' ? 'Lista de espera' : sp_escape(($r['codigo'] ?? '-') . ' / Piso ' . ($r['piso'] ?? '-')); ?></td>
                    <td>
                      <?php echo sp_badge_riesgo_no_show($r['riesgo_no_show']); ?><br>
                      <small class="text-muted"><?php echo sp_escape($r['reservas_vencidas_cliente']); ?>/<?php echo sp_escape($r['reservas_cliente']); ?> vencidas</small>
                    </td>
                    <td>
                      <?php if ($r['estado'] === 'reservado') { ?>
                        <span class="countdown text-warning font-weight-bold" data-expira="<?php echo sp_escape($r['reserva_expira_en']); ?>"></span>
                      <?php } else { ?>
                        <?php echo sp_escape($r['fecha_ingreso'] . ' ' . $r['hora_ingreso']); ?>
                      <?php } ?>
                    </td>
                    <td>
                      <?php if ($r['estado'] === 'reservado') { ?>
                        <form method="post" class="confirm-form" data-confirm="¿Confirmar llegada y activar parqueo?">
                          <input type="hidden" name="accion" value="confirmarLlegadaReserva">
                          <input type="hidden" name="id_parqueo" value="<?php echo $r['id_parqueo']; ?>">
                          <button class="btn btn-success btn-sm">Confirmar llegada</button>
                        </form>
                      <?php } elseif ($r['estado'] === 'activo') { ?>
                        <a class="btn btn-outline-primary btn-sm" href="Operacion.php">Cerrar parqueo</a>
                      <?php } else { ?>
                        <span class="text-muted">Sin acción</span>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
          </div></div>
        </div>
      </div>
      <?php } ?>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
<script src="../assets/custom/js/reservas_countdown.js"></script>
<script>
  const btnQrScan = document.getElementById('btnQrScan');
  const btnQrStop = document.getElementById('btnQrStop');
  const qrScannerBox = document.getElementById('qrScannerBox');
  const qrVideo = document.getElementById('qrVideo');
  const porteriaQuery = document.getElementById('porteriaQuery');
  let qrStream = null;
  let qrScanning = false;

  function extraerCodigoQR(texto) {
    try {
      const url = new URL(texto);
      return url.searchParams.get('codigo') || texto;
    } catch (e) {
      return texto;
    }
  }

  async function detenerScannerQR() {
    qrScanning = false;
    if (qrStream) {
      qrStream.getTracks().forEach(track => track.stop());
      qrStream = null;
    }
    qrScannerBox.style.display = 'none';
  }

  async function iniciarScannerQR() {
    if (!('BarcodeDetector' in window)) {
      alert('Este navegador no soporta escaneo QR nativo. Puedes pegar o escribir el código manualmente.');
      return;
    }

    const detector = new BarcodeDetector({ formats: ['qr_code'] });
    qrStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
    qrVideo.srcObject = qrStream;
    await qrVideo.play();
    qrScannerBox.style.display = 'block';
    qrScanning = true;

    async function detectar() {
      if (!qrScanning) return;
      try {
        const codigos = await detector.detect(qrVideo);
        if (codigos.length > 0) {
          const codigo = extraerCodigoQR(codigos[0].rawValue);
          porteriaQuery.value = codigo;
          await detenerScannerQR();
          window.location.href = 'Porteria.php?q=' + encodeURIComponent(codigo);
          return;
        }
      } catch (e) {}
      requestAnimationFrame(detectar);
    }
    detectar();
  }

  btnQrScan.addEventListener('click', function () {
    iniciarScannerQR().catch(function () {
      alert('No fue posible abrir la cámara. Revisa permisos del navegador o usa búsqueda manual.');
    });
  });

  btnQrStop.addEventListener('click', detenerScannerQR);
</script>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
