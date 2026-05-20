<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('config/config.php');
date_default_timezone_set("America/Bogota");

function public_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function public_fail($message)
{
    header("Location: ReservarParqueadero.php?error=" . urlencode($message));
    exit;
}

function public_config()
{
    $defaults = ['tiempo_reserva_minutos' => 15, 'nombre_parqueadero' => 'Zona Segura ECCI'];
    $path = __DIR__ . '/config/smartpark_institucional.json';
    if (!file_exists($path)) {
        return $defaults;
    }
    $data = json_decode((string)file_get_contents($path), true);
    return is_array($data) ? array_merge($defaults, $data) : $defaults;
}

$configSmartpark = public_config();
$minutosReserva = max(5, min(60, (int)$configSmartpark['tiempo_reserva_minutos']));

$expirarSql = "UPDATE tbl_parqueos p
    INNER JOIN tbl_espacios e ON e.id_espacio=p.id_espacio
    SET p.estado='vencido', e.estado='libre'
    WHERE p.estado='reservado' AND p.reserva_expira_en < NOW()";
mysqli_query($con, $expirarSql);

$stats = ['total' => 0, 'libres' => 0, 'reservados' => 0, 'ocupados' => 0];
$resultStats = mysqli_query($con, "SELECT COUNT(*) total, SUM(estado='libre') libres, SUM(estado='reservado') reservados, SUM(estado='ocupado') ocupados FROM tbl_espacios");
if ($resultStats) {
    $stats = array_merge($stats, mysqli_fetch_assoc($resultStats) ?: []);
}
$autoLibres = 0;
$motoLibres = 0;
$resultTipos = mysqli_query($con, "SELECT tipo_vehiculo, SUM(estado='libre') libres FROM tbl_espacios GROUP BY tipo_vehiculo");
if ($resultTipos) {
    while ($tipoRow = mysqli_fetch_assoc($resultTipos)) {
        if ($tipoRow['tipo_vehiculo'] === 'auto') {
            $autoLibres = (int)$tipoRow['libres'];
        }
        if ($tipoRow['tipo_vehiculo'] === 'moto') {
            $motoLibres = (int)$tipoRow['libres'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $nombres = trim($_POST['nombres'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $tipoCliente = $_POST['tipo_cliente'] ?? '';
    $placa = strtoupper(preg_replace('/\s+/', '', trim($_POST['placa'] ?? '')));
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $tipoVehiculo = $_POST['tipo'] ?? '';
    $aceptaPolitica = isset($_POST['acepta_politica']);

    $tiposClientePermitidos = ['estudiante', 'docente', 'funcionario', 'visitante'];
    $tiposVehiculoPermitidos = ['auto', 'moto'];
    if ($cedula === '' || $nombres === '' || $telefono === '' || $placa === '' || $marca === '' || $modelo === '' || $color === '') {
        public_fail("Completa todos los campos obligatorios antes de reservar.");
    }
    if (!in_array($tipoCliente, $tiposClientePermitidos, true)) {
        public_fail("Selecciona un tipo de usuario valido.");
    }
    if (!in_array($tipoVehiculo, $tiposVehiculoPermitidos, true)) {
        public_fail("Selecciona un tipo de vehiculo valido.");
    }
    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        public_fail("Ingresa un correo valido.");
    }
    if (!preg_match('/^[A-Z0-9]{5,7}$/', $placa)) {
        public_fail("La placa debe tener entre 5 y 7 caracteres alfanumericos.");
    }
    if (!$aceptaPolitica) {
        public_fail("Debes aceptar la politica de reserva de 15 minutos.");
    }

    mysqli_begin_transaction($con);
    try {
        $stmtCliente = mysqli_prepare($con, "SELECT id_cliente FROM tbl_clientes WHERE cedula=? LIMIT 1");
        mysqli_stmt_bind_param($stmtCliente, "s", $cedula);
        mysqli_stmt_execute($stmtCliente);
        $cliente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCliente));

        if ($cliente) {
            $idCliente = (int)$cliente['id_cliente'];
            $stmtUpdateCliente = mysqli_prepare($con, "UPDATE tbl_clientes SET nombres=?, telefono=?, correo=?, tipo_cliente=? WHERE id_cliente=?");
            mysqli_stmt_bind_param($stmtUpdateCliente, "ssssi", $nombres, $telefono, $correo, $tipoCliente, $idCliente);
            mysqli_stmt_execute($stmtUpdateCliente);
        } else {
            $stmtInsertCliente = mysqli_prepare($con, "INSERT INTO tbl_clientes (cedula, nombres, telefono, correo, tipo_cliente) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmtInsertCliente, "sssss", $cedula, $nombres, $telefono, $correo, $tipoCliente);
            mysqli_stmt_execute($stmtInsertCliente);
            $idCliente = mysqli_insert_id($con);
        }

        $stmtVehiculo = mysqli_prepare($con, "SELECT id_vehiculo, id_cliente FROM tbl_vehiculos WHERE placa=? LIMIT 1");
        mysqli_stmt_bind_param($stmtVehiculo, "s", $placa);
        mysqli_stmt_execute($stmtVehiculo);
        $vehiculo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtVehiculo));

        if ($vehiculo && (int)$vehiculo['id_cliente'] !== $idCliente) {
            throw new Exception("La placa ya pertenece a otro cliente.");
        }

        if ($vehiculo) {
            $idVehiculo = (int)$vehiculo['id_vehiculo'];
            $stmtUpdateVehiculo = mysqli_prepare($con, "UPDATE tbl_vehiculos SET marca=?, modelo=?, color=?, tipo=? WHERE id_vehiculo=?");
            mysqli_stmt_bind_param($stmtUpdateVehiculo, "ssssi", $marca, $modelo, $color, $tipoVehiculo, $idVehiculo);
            mysqli_stmt_execute($stmtUpdateVehiculo);
        } else {
            $stmtInsertVehiculo = mysqli_prepare($con, "INSERT INTO tbl_vehiculos (id_cliente, placa, marca, modelo, color, tipo) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmtInsertVehiculo, "isssss", $idCliente, $placa, $marca, $modelo, $color, $tipoVehiculo);
            mysqli_stmt_execute($stmtInsertVehiculo);
            $idVehiculo = mysqli_insert_id($con);
        }

        $stmtBloqueoVehiculo = mysqli_prepare($con, "SELECT id_parqueo FROM tbl_parqueos WHERE id_vehiculo=? AND estado IN ('espera','reservado','activo') LIMIT 1 FOR UPDATE");
        mysqli_stmt_bind_param($stmtBloqueoVehiculo, "i", $idVehiculo);
        mysqli_stmt_execute($stmtBloqueoVehiculo);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmtBloqueoVehiculo))) {
            throw new Exception("Este vehículo ya tiene una solicitud, reserva o parqueo activo.");
        }

        $stmtTarifa = mysqli_prepare($con, "SELECT id_tarifa, valor_hora FROM tbl_tarifas WHERE tipo_vehiculo=? AND estado=1 LIMIT 1");
        mysqli_stmt_bind_param($stmtTarifa, "s", $tipoVehiculo);
        mysqli_stmt_execute($stmtTarifa);
        $tarifa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTarifa));
        if (!$tarifa) {
            throw new Exception("No hay tarifa activa para ese tipo de vehículo.");
        }

        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $expira = date('Y-m-d H:i:s', time() + ($minutosReserva * 60));

        $stmtEspacio = mysqli_prepare($con, "SELECT id_espacio, codigo FROM tbl_espacios WHERE estado='libre' AND tipo_vehiculo=? ORDER BY piso ASC, codigo ASC LIMIT 1 FOR UPDATE");
        mysqli_stmt_bind_param($stmtEspacio, "s", $tipoVehiculo);
        mysqli_stmt_execute($stmtEspacio);
        $espacio = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtEspacio));

        if ($espacio) {
            $codigoReserva = 'RS-' . date('YmdHis') . '-' . random_int(100, 999);
            $tokenPublico = bin2hex(random_bytes(24));
            $stmtReserva = mysqli_prepare($con, "INSERT INTO tbl_parqueos (id_cliente, id_vehiculo, id_espacio, id_tarifa, fecha_ingreso, hora_ingreso, tarifa_hora_aplicada, reserva_expira_en, codigo_reserva, token_publico, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'reservado')");
            mysqli_stmt_bind_param($stmtReserva, "iiiissdsss", $idCliente, $idVehiculo, $espacio['id_espacio'], $tarifa['id_tarifa'], $fecha, $hora, $tarifa['valor_hora'], $expira, $codigoReserva, $tokenPublico);
            mysqli_stmt_execute($stmtReserva);

            $stmtReservarEspacio = mysqli_prepare($con, "UPDATE tbl_espacios SET estado='reservado' WHERE id_espacio=? AND estado='libre'");
            mysqli_stmt_bind_param($stmtReservarEspacio, "i", $espacio['id_espacio']);
            mysqli_stmt_execute($stmtReservarEspacio);

            mysqli_commit($con);
            header("Location: ReservarParqueadero.php?ok=1&codigo=" . urlencode($codigoReserva) . "&token=" . urlencode($tokenPublico) . "&espacio=" . urlencode($espacio['codigo']) . "&expira=" . urlencode($expira));
            exit;
        }

        $codigoEspera = 'LE-' . date('YmdHis') . '-' . random_int(100, 999);
        $tokenEspera = bin2hex(random_bytes(24));
        $stmtEspera = mysqli_prepare($con, "INSERT INTO tbl_parqueos (id_cliente, id_vehiculo, id_espacio, id_tarifa, fecha_ingreso, hora_ingreso, tarifa_hora_aplicada, codigo_reserva, token_publico, estado) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, 'espera')");
        mysqli_stmt_bind_param($stmtEspera, "iiissdss", $idCliente, $idVehiculo, $tarifa['id_tarifa'], $fecha, $hora, $tarifa['valor_hora'], $codigoEspera, $tokenEspera);
        mysqli_stmt_execute($stmtEspera);

        mysqli_commit($con);
        header("Location: ReservarParqueadero.php?espera=1&codigo=" . urlencode($codigoEspera) . "&token=" . urlencode($tokenEspera));
        exit;
    } catch (Throwable $e) {
        mysqli_rollback($con);
        public_fail($e->getMessage());
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
        <a class="sp-proposal-brand" href="./">
          <img src="assets/custom/imgs/logo.png" alt="SMARTPARK">
          <span>SMARTPARK</span>
        </a>
        <div>
          <a class="btn btn-outline-primary btn-sm mr-2" href="Disponibilidad.php">Disponibilidad</a>
          <a class="btn btn-outline-primary btn-sm mr-2" href="ConsultarReserva.php">Consultar QR</a>
          <a class="btn btn-outline-primary btn-sm mr-2" href="PortalUsuario.php">Mi portal</a>
          <a class="btn btn-primary btn-sm" href="./">Ingreso admin</a>
        </div>
      </div>
    </div>

    <main class="container py-5">
      <div class="row align-items-start">
        <div class="col-lg-8 grid-margin">
          <span class="sp-pill mb-3"><i class="bi bi-calendar-check"></i> Reserva digital</span>
          <h1 class="font-weight-bold mb-2">Reserva tu cupo SMARTPARK</h1>
          <p class="text-muted mb-4">Completa tus datos, recibe un código QR y llega en máximo 15 minutos para confirmar el ingreso en portería.</p>

          <div class="sp-reservation-steps mb-4">
            <div class="sp-reservation-step active"><span>1</span><strong>Datos</strong></div>
            <div class="sp-reservation-step"><span>2</span><strong>Asignación</strong></div>
            <div class="sp-reservation-step"><span>3</span><strong>QR</strong></div>
            <div class="sp-reservation-step"><span>4</span><strong>Ingreso</strong></div>
          </div>

          <div class="card sp-reservation-card">
            <div class="card-body p-4">
              <?php if (isset($_GET['ok'])) { ?>
                <?php
                  $consultaValor = $_GET['token'] ?? $_GET['codigo'];
                  $consultaUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/ConsultarReserva.php?codigo=' . urlencode($consultaValor);
                  $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($consultaUrl);
                ?>
                <div class="sp-result-card success mb-4">
                  <div class="row align-items-center">
                    <div class="col-md-8">
                      <h4 class="font-weight-bold text-success">Reserva creada correctamente</h4>
                      <p class="mb-1">Código: <strong><?php echo public_escape($_GET['codigo']); ?></strong></p>
                      <p class="mb-1">Espacio asignado: <strong><?php echo public_escape($_GET['espacio']); ?></strong></p>
                      <p class="mb-3">Expira: <strong><?php echo public_escape($_GET['expira']); ?></strong></p>
                      <a class="btn btn-outline-primary btn-sm" href="<?php echo public_escape($consultaUrl); ?>">Consultar estado</a>
                      <a class="btn btn-outline-danger btn-sm ml-2" href="PortalUsuario.php?consulta=<?php echo urlencode($consultaValor); ?>">Mi portal</a>
                    </div>
                    <div class="col-md-4 text-center mt-3 mt-md-0">
                      <span class="sp-qr-frame"><img src="<?php echo public_escape($qrUrl); ?>" alt="QR de reserva" width="150" height="150"></span>
                    </div>
                  </div>
                </div>
              <?php } ?>
              <?php if (isset($_GET['espera'])) { ?>
                <div class="sp-result-card waiting mb-4">
                  <h4 class="font-weight-bold text-warning">Estás en lista de espera</h4>
                  <p class="mb-1">Código: <strong><?php echo public_escape($_GET['codigo']); ?></strong></p>
                  <p class="mb-0">Cuando se libere un espacio compatible, SMARTPARK lo asignará automáticamente por orden de llegada.</p>
                  <?php if (isset($_GET['token'])) { ?>
                    <a class="btn btn-outline-primary btn-sm mt-3" href="PortalUsuario.php?consulta=<?php echo urlencode($_GET['token']); ?>">Abrir mi portal</a>
                  <?php } ?>
                </div>
              <?php } ?>
              <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-danger"><?php echo public_escape($_GET['error']); ?></div>
              <?php } ?>
              <form method="post" autocomplete="off">
                <div class="sp-form-section pt-0">
                  <div class="sp-form-section-title"><i class="bi bi-person-badge"></i> Datos del usuario</div>
                  <div class="row">
                    <div class="col-md-6">
                      <input class="form-control mb-3" name="cedula" id="cedulaInput" data-preview="cedula" placeholder="Cédula / código institucional" required>
                      <small class="text-muted">Si ya has reservado antes, ingresa tu cédula para autocompletar tus datos</small>
                    </div>
                    <div class="col-md-6">
                      <select class="form-control mb-3" name="tipo_cliente" id="tipoClienteSelect" required>
                        <option value="" selected disabled>Tipo de usuario</option>
                        <option value="estudiante">Estudiante</option>
                        <option value="docente">Docente</option>
                        <option value="funcionario">Funcionario</option>
                        <option value="visitante">Visitante</option>
                      </select>
                    </div>
                  </div>
                  <div id="clienteStatus" class="mb-3" style="display: none;"></div>
                  <input class="form-control mb-3" name="nombres" id="nombresInput" data-preview="nombres" placeholder="Nombres completos" required>
                  <div class="row">
                    <div class="col-md-6"><input class="form-control mb-3" name="telefono" id="telefonoInput" placeholder="Teléfono" required></div>
                    <div class="col-md-6"><input type="email" class="form-control mb-3" name="correo" id="correoInput" placeholder="Correo institucional o personal"></div>
                  </div>
                </div>

                <div class="sp-form-section">
                  <div class="sp-form-section-title"><i class="bi bi-car-front"></i> Datos del vehículo</div>
                  <div class="row">
                    <div class="col-md-6"><input class="form-control mb-3 text-uppercase" name="placa" data-preview="placa" maxlength="7" placeholder="Placa" required></div>
                    <div class="col-md-6">
                      <select class="form-control mb-3" name="tipo" id="tipoVehiculoReserva" required>
                        <option value="" selected disabled>Tipo de vehículo</option>
                        <option value="auto" data-disponibles="<?php echo public_escape($autoLibres); ?>">Auto</option>
                        <option value="moto" data-disponibles="<?php echo public_escape($motoLibres); ?>">Moto</option>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4"><input class="form-control mb-3" name="marca" placeholder="Marca" required></div>
                    <div class="col-md-4"><input class="form-control mb-3" name="modelo" placeholder="Modelo" required></div>
                    <div class="col-md-4"><input class="form-control mb-3" name="color" placeholder="Color" required></div>
                  </div>
                </div>

                <div class="sp-form-section pb-0">
                  <label class="sp-consent-check">
                    <input type="checkbox" name="acepta_politica" required>
                    <span>Acepto que la reserva dura 15 minutos y que, si no llego a tiempo, el cupo se libera automáticamente.</span>
                  </label>
                  <button class="btn btn-primary btn-block btn-lg"><i class="bi bi-qr-code"></i> Reservar y generar QR</button>
                  <a class="btn sp-whatsapp-btn btn-block mt-3" target="_blank" href="WhatsAppReserva.php?origen=formulario_reserva">
                    <i class="bi bi-whatsapp"></i> Prefiero reservar por WhatsApp
                  </a>
                  <div class="text-center mt-4"><a href="./">Volver al ingreso administrativo</a></div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-4 grid-margin">
          <div class="sp-reservation-side">
            <div class="sp-public-image-card mb-4">
              <img src="assets/custom/imgs/smartpark-reserva-confirmada.png" alt="Reserva SMARTPARK confirmada en celular">
              <div class="sp-public-image-caption">
                <strong>Reserva confirmada</strong>
                <span>Tu cupo queda bloqueado y listo para validar en portería.</span>
              </div>
            </div>
            <div class="card sp-insight mb-4">
              <div class="card-body">
                <h4 class="card-title">Disponibilidad ahora</h4>
                <div class="row">
                  <div class="col-6 mb-3"><p class="sp-kpi-label mb-1">Autos</p><h3 class="sp-kpi-value text-success mb-0"><?php echo public_escape($autoLibres); ?></h3></div>
                  <div class="col-6 mb-3"><p class="sp-kpi-label mb-1">Motos</p><h3 class="sp-kpi-value text-success mb-0"><?php echo public_escape($motoLibres); ?></h3></div>
                </div>
                <div class="sp-availability-meter mb-3">
                  <?php $libres = max(0, (int)$stats['libres']); $total = max(1, (int)$stats['total']); $porcentajeLibres = min(100, round(($libres / $total) * 100)); ?>
                  <span style="width: <?php echo public_escape($porcentajeLibres); ?>%"></span>
                </div>
                <p class="text-muted mb-0">Si no hay cupo, quedarás en lista de espera inteligente.</p>
              </div>
            </div>
            <div class="card sp-reservation-preview mb-4">
              <div class="card-body">
                <h4 class="card-title">Vista previa</h4>
                <div class="sp-preview-row"><span>Usuario</span><strong id="previewNombres">Sin diligenciar</strong></div>
                <div class="sp-preview-row"><span>Documento</span><strong id="previewCedula">-</strong></div>
                <div class="sp-preview-row"><span>Placa</span><strong id="previewPlaca">-</strong></div>
                <div class="sp-preview-row"><span>Cupos del tipo</span><strong id="previewCupos">Selecciona vehículo</strong></div>
              </div>
            </div>
            <div class="card">
              <div class="card-body">
                <h4 class="card-title">Cómo funciona</h4>
                <div class="sp-rule-item"><i class="bi bi-clock"></i><div><strong>15 minutos</strong><p class="text-muted mb-0">La reserva vence si no llegas a tiempo.</p></div></div>
                <div class="sp-rule-item"><i class="bi bi-qr-code-scan"></i><div><strong>QR de acceso</strong><p class="text-muted mb-0">Preséntalo en portería para confirmar llegada.</p></div></div>
                <div class="sp-rule-item"><i class="bi bi-hourglass-split"></i><div><strong>Lista de espera</strong><p class="text-muted mb-0">Asignación automática cuando se libere un cupo.</p></div></div>
                <div class="sp-rule-item"><i class="bi bi-shield-check"></i><div><strong>Sin doble reserva</strong><p class="text-muted mb-0">Un vehículo no puede tener dos reservas activas.</p></div></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <a class="sp-whatsapp-floating" target="_blank" href="WhatsAppReserva.php?origen=boton_flotante_reserva" title="Reservar por WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>
  <?php include('basesLogin/brand_footer.php'); ?>
  <?php include('basesLogin/footerJS.html'); ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var inputNombres = document.querySelector('[data-preview="nombres"]');
      var inputCedula = document.querySelector('[data-preview="cedula"]');
      var inputPlaca = document.querySelector('[data-preview="placa"]');
      var tipoVehiculo = document.getElementById('tipoVehiculoReserva');
      var previewNombres = document.getElementById('previewNombres');
      var previewCedula = document.getElementById('previewCedula');
      var previewPlaca = document.getElementById('previewPlaca');
      var previewCupos = document.getElementById('previewCupos');

      // Nuevos elementos para autocompletado
      var cedulaInput = document.getElementById('cedulaInput');
      var nombresInput = document.getElementById('nombresInput');
      var telefonoInput = document.getElementById('telefonoInput');
      var correoInput = document.getElementById('correoInput');
      var tipoClienteSelect = document.getElementById('tipoClienteSelect');
      var clienteStatus = document.getElementById('clienteStatus');

      var ultimoClienteBuscado = '';

      function resetCamposCliente() {
        nombresInput.value = '';
        telefonoInput.value = '';
        correoInput.value = '';
        tipoClienteSelect.value = '';
        document.querySelector('[name="placa"]').value = '';
        document.querySelector('[name="marca"]').value = '';
        document.querySelector('[name="modelo"]').value = '';
        document.querySelector('[name="color"]').value = '';
        document.getElementById('tipoVehiculoReserva').value = '';
        clienteStatus.style.display = 'none';
        ultimoClienteBuscado = '';
        refreshPreview();
      }

      function valueOrFallback(input, fallback) {
        return input && input.value.trim() ? input.value.trim() : fallback;
      }

      function refreshPreview() {
        previewNombres.textContent = valueOrFallback(inputNombres, 'Sin diligenciar');
        previewCedula.textContent = valueOrFallback(inputCedula, '-');
        previewPlaca.textContent = valueOrFallback(inputPlaca, '-').toUpperCase();
        if (tipoVehiculo && tipoVehiculo.selectedOptions.length) {
          var option = tipoVehiculo.selectedOptions[0];
          var disponibles = option.getAttribute('data-disponibles');
          previewCupos.textContent = disponibles !== null ? disponibles + ' disponibles' : 'Selecciona vehículo';
        }
      }

      // Función para buscar cliente por cédula
      function buscarCliente(cedula) {
        if (cedula.length < 3 || cedula === ultimoClienteBuscado) return;

        clienteStatus.style.display = 'block';
        clienteStatus.className = 'alert alert-info';
        clienteStatus.innerHTML = '<i class="bi bi-search"></i> Buscando cliente...';

        fetch('acciones/buscar_cliente.php?cedula=' + encodeURIComponent(cedula))
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Cliente encontrado - autocompletar
              nombresInput.value = data.cliente.nombres;
              telefonoInput.value = data.cliente.telefono;
              correoInput.value = data.cliente.correo;
              tipoClienteSelect.value = data.cliente.tipo_cliente;

              if (data.vehiculo) {
                // Si tiene vehículo registrado, autocompletar también
                document.querySelector('[name="placa"]').value = data.vehiculo.placa;
                document.querySelector('[name="marca"]').value = data.vehiculo.marca;
                document.querySelector('[name="modelo"]').value = data.vehiculo.modelo;
                document.querySelector('[name="color"]').value = data.vehiculo.color;
                document.getElementById('tipoVehiculoReserva').value = data.vehiculo.tipo;
              }

              clienteStatus.className = 'alert alert-success';
              clienteStatus.innerHTML = '<i class="bi bi-check-circle"></i> ¡Cliente frecuente encontrado! Datos autocompletados.';
              ultimoClienteBuscado = cedula;
              refreshPreview();
            } else {
              // Cliente no encontrado - mostrar mensaje
              clienteStatus.className = 'alert alert-warning';
              clienteStatus.innerHTML = '<i class="bi bi-info-circle"></i> Cliente no registrado. Completa tus datos para crear tu perfil.';
              ultimoClienteBuscado = cedula;
            }
          })
          .catch(error => {
            clienteStatus.className = 'alert alert-danger';
            clienteStatus.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error al buscar cliente. Continúa completando manualmente.';
            console.error('Error:', error);
          });
      }

      // Evento para buscar cliente al salir del campo cédula
      cedulaInput.addEventListener('blur', function() {
        var cedula = this.value.trim();
        if (cedula) {
          buscarCliente(cedula);
        } else {
          resetCamposCliente();
        }
      });

      // Evento para limpiar búsqueda si se modifica la cédula
      cedulaInput.addEventListener('input', function() {
        var cedula = this.value.trim();
        if (cedula !== ultimoClienteBuscado && cedula.length < 3) {
          resetCamposCliente();
        }
      });

      [inputNombres, inputCedula, inputPlaca, tipoVehiculo].forEach(function(element) {
        if (element) {
          element.addEventListener('input', refreshPreview);
          element.addEventListener('change', refreshPreview);
        }
      });
      if (inputPlaca) {
        inputPlaca.addEventListener('input', function() {
          inputPlaca.value = inputPlaca.value.replace(/\s+/g, '').toUpperCase();
        });
      }
      refreshPreview();
    });
  </script>
</body>
</html>
