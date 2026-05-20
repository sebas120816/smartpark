<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">

    <?php
    if (in_array($rolUser, ['Administrador', 'Caja', 'Control'])) { ?>
      <li class="nav-item">
        <a class="nav-link" href="index.php">
          <i class="bi bi-speedometer2 menu-icon"></i>
          <span class="menu-title">Panel</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Porteria.php">
          <i class="bi bi-qr-code-scan menu-icon"></i>
          <span class="menu-title">Portería</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="PantallaPorteria.php" target="_blank">
          <i class="bi bi-display menu-icon"></i>
          <span class="menu-title">Pantalla portería</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Operacion.php">
          <i class="bi bi-box-arrow-in-right menu-icon"></i>
          <span class="menu-title">Ingreso / Salida</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="ReservasSmartpark.php">
          <i class="bi bi-calendar-check menu-icon"></i>
          <span class="menu-title">Reservas</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="ListaEspera.php">
          <i class="bi bi-hourglass-split menu-icon"></i>
          <span class="menu-title">Lista de espera</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Vehiculos.php">
          <img src="../assets/custom/imgs/carro.png" alt="car" style="padding: 0px 10px 0px 0px" />
          <span class="menu-title">Vehículos</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Clientes.php">
          <i class="bi bi-person-fill-add menu-icon"></i>
          <span class="menu-title">Clientes</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Espacios.php">
          <i class="bi bi-grid-3x3-gap menu-icon"></i>
          <span class="menu-title">Espacios</span>
        </a>
      </li>
      <li class="nav-item active">
        <a class="nav-link" href="HistorialParqueos.php">
          <i class="bi bi-clock-history menu-icon"></i>
          <span class="menu-title">Historial</span>
        </a>
      </li>
    <?php }
    if ($rolUser == 'Administrador') { ?>
      <li class="nav-item">
        <a class="nav-link" href="Tarifas.php">
          <i class="bi bi-cash-coin menu-icon"></i>
          <span class="menu-title">Tarifas</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Usuarios.php">
          <i class="bi bi-people-fill menu-icon"></i>
          <span class="menu-title">Usuarios</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Reportes.php">
          <i class="bi bi-bar-chart-line menu-icon"></i>
          <span class="menu-title">Reportes</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="IAInsights.php">
          <i class="bi bi-stars menu-icon"></i>
          <span class="menu-title">IA Operativa</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Auditoria.php">
          <i class="bi bi-shield-lock menu-icon"></i>
          <span class="menu-title">Auditoría</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Configuracion.php">
          <i class="bi bi-sliders menu-icon"></i>
          <span class="menu-title">Configuración</span>
        </a>
      </li>
    <?php } ?>
  </ul>
</nav>
