<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $espacios = sp_espacios($con);
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
        <h4 class="card-title">Estado de espacios</h4>
        <div class="row">
          <?php while ($e = mysqli_fetch_assoc($espacios)) {
            $estadoClass = $e['estado'] === 'libre' ? 'success' : ($e['estado'] === 'reservado' ? 'warning' : 'danger');
          ?>
            <div class="col-6 col-md-2 mb-3">
              <div class="border rounded p-3 text-center js-spacio-card border-<?php echo $estadoClass; ?>" data-id="<?php echo (int)$e['id_espacio']; ?>">
                <strong><?php echo sp_escape($e['codigo']); ?></strong>
                <div>Piso <?php echo sp_escape($e['piso']); ?></div>
                <div><?php echo sp_escape($e['tipo_vehiculo']); ?></div>
                <span class="badge badge-<?php echo $estadoClass; ?> js-spacio-estado"><?php echo sp_escape($e['estado']); ?></span>
              </div>
            </div>
          <?php } ?>
        </div>
      </div></div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
<script>
(function () {
  const endpoint = '../acciones/servicio_api/api_disponibilidad_smartpark.php';
  const estadoClass = { libre: 'success', ocupado: 'danger', reservado: 'warning' };

  function pintarEspacio(espacio) {
    const card = document.querySelector('.js-spacio-card[data-id="' + espacio.id_espacio + '"]');
    if (!card) return;
    const badge = card.querySelector('.js-spacio-estado');
    const cls = estadoClass[espacio.estado] || 'secondary';
    card.classList.remove('border-success', 'border-danger', 'border-warning', 'border-secondary');
    card.classList.add('border-' + cls);
    if (badge) {
      badge.className = 'badge badge-' + cls + ' js-spacio-estado';
      badge.textContent = espacio.estado;
    }
  }

  function actualizarDisponibilidad() {
    fetch(endpoint, { cache: 'no-store' })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        if (!data.ok) return;
        data.espacios.forEach(pintarEspacio);
      })
      .catch(function () {});
  }

  actualizarDisponibilidad();
  setInterval(actualizarDisponibilidad, 5000);
})();
</script>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
