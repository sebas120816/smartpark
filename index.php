<?php
session_start();
if (isset($_SESSION['emailUser']) != "") {
  include('config/config.php');
  $IdUser     = $_SESSION['IdUser'];
  $rolUser     = $_SESSION['rol'];
  $email      = $_SESSION['emailUser'];
  header('location: dashboard/');
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include('basesLogin/head.php'); ?>
<style>
  body {
    background-color: #f5f7ff !important;
  }
</style>
<link rel="stylesheet" href="assets/custom/css/custom_css.css" />

<body>
  <?php
  include('msjs.php');
  include("dashboard/bases/loader.html");
  ?>

  <div class="container-scroller sp-login-shell">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0" style="background: transparent;">
        <div class="row w-100 mx-0 align-items-center">
          <div class="col-lg-7 d-none d-lg-block pl-5">
            <div class="sp-login-copy">
              <span class="sp-pill bg-white text-primary mb-4"><i class="bi bi-shield-check"></i> Zona Segura ECCI</span>
              <h1>SMARTPARK para gestionar parqueaderos universitarios con control real.</h1>
              <p>Reservas QR, control de ingreso y salida, cobro automático, reportes e IA operativa sobre una base de datos relacional normalizada.</p>
              <div class="sp-feature-list">
                <span><i class="bi bi-calendar-check"></i> Reservas por 15 minutos</span>
                <span><i class="bi bi-qr-code-scan"></i> Consulta por QR</span>
                <span><i class="bi bi-bar-chart-line"></i> Reportes de ingresos</span>
                <span><i class="bi bi-database-check"></i> Modelo 3FN</span>
              </div>
              <div class="sp-login-visual mt-4">
                <img src="assets/custom/imgs/smartpark-login-estudiantes.png" alt="Estudiantes universitarios gestionando una reserva desde el celular">
                <div class="sp-login-visual-badge">
                  <i class="bi bi-phone"></i>
                  <div><strong>Reserva móvil</strong><span>QR activo por 15 minutos</span></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4">
              <div class="brand-logo text-center mb-4">
                <a href="./">
                  <img src="assets/custom/imgs/logo.png" alt="SMARTPARK" />
                </a>
              </div>
              <h2 class="text-center mb-1">SMARTPARK</h2>
              <p class="text-center text-muted mb-4">Parqueadero Zona Segura ECCI</p>
              <form action="acciones/login/acciones_login.php" method="post" class="pt-3" autocomplete="off">
                <div class="form-group">
                  <input type="email" name="emailUser" class="form-control form-control-lg" required placeholder="Email" />
                </div>
                <div class="form-group">
                  <input type="password" name="passwordUser" class="form-control form-control-lg" placeholder="Clave" required />
                </div>
                <div class="mt-3">
                  <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                    Iniciar sesión
                  </button>
                </div>
                <p class="text-center mt-4 text-muted mb-2">Ingreso exclusivo para usuarios autorizados.</p>
                <div class="text-center">
                  <a href="Disponibilidad.php" class="text-primary">Ver disponibilidad</a>
                  <span class="mx-2">|</span>
                  <a href="ReservarParqueadero.php" class="text-primary">Reservar</a>
                  <span class="mx-2">|</span>
                  <a href="PortalUsuario.php" class="text-primary">Mi portal</a>
                  <span class="mx-2">|</span>
                  <a href="Presentacion.php" class="text-primary">Propuesta</a>
                </div>
                <a class="btn sp-whatsapp-btn btn-block mt-4" target="_blank" href="WhatsAppReserva.php?origen=login">
                  <i class="bi bi-whatsapp"></i> Reservar por WhatsApp
                </a>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <a class="sp-whatsapp-floating" target="_blank" href="WhatsAppReserva.php?origen=boton_flotante_login" title="Reservar por WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>

  <?php include('basesLogin/brand_footer.php'); ?>
  <?php include('basesLogin/footerJS.html'); ?>
</body>

</html>
