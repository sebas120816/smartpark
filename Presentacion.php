<!DOCTYPE html>
<html lang="es">
<?php include('basesLogin/head.php'); ?>
<link rel="stylesheet" href="assets/custom/css/custom_css.css" />
<body>
  <nav class="sp-proposal-nav">
    <div class="container">
      <a class="sp-proposal-brand" href="Presentacion.php">
        <img src="assets/custom/imgs/logo.png" alt="SMARTPARK">
        <span>SMARTPARK</span>
      </a>
      <div class="sp-proposal-links">
        <a href="#problema">Problema</a>
        <a href="#solucion">Solución</a>
        <a href="#arquitectura">Arquitectura</a>
        <a href="#demo">Demo</a>
        <a href="Disponibilidad.php">Disponibilidad</a>
        <a href="PortalUsuario.php">Mi portal</a>
        <a class="btn btn-primary btn-sm" href="./">Entrar</a>
      </div>
    </div>
  </nav>

  <div class="sp-hero">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <span class="sp-pill mb-4"><i class="bi bi-building-check"></i> Propuesta institucional para ECCI</span>
          <h1 class="font-weight-bold">SMARTPARK para Zona Segura ECCI</h1>
          <p>Sistema web inteligente para reservas, control de espacios, QR, cobros, reportes ejecutivos, sostenibilidad e IA operativa del parqueadero universitario.</p>
          <div class="sp-hero-actions">
            <a class="sp-hero-action sp-hero-action-primary" href="ReservarParqueadero.php"><i class="bi bi-calendar-plus"></i> Probar reserva</a>
            <a class="sp-hero-action sp-hero-action-whatsapp" target="_blank" href="WhatsAppReserva.php?origen=presentacion"><i class="bi bi-whatsapp"></i> WhatsApp</a>
            <a class="sp-hero-action sp-hero-action-secondary" href="Disponibilidad.php"><i class="bi bi-broadcast"></i> Ver disponibilidad</a>
            <a class="sp-hero-action sp-hero-action-secondary" href="./"><i class="bi bi-box-arrow-in-right"></i> Ingresar al sistema</a>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="sp-hero-visual">
            <img class="sp-hero-visual-image" src="assets/custom/imgs/smartpark-reserva-confirmada.png" alt="Estudiante confirmando una reserva SMARTPARK desde su celular">
            <div class="sp-hero-dashboard">
              <div class="d-flex justify-content-between align-items-center">
                <strong>Operación en vivo</strong>
                <span class="badge badge-success">Online</span>
              </div>
              <div class="sp-hero-mini-grid">
                <div class="sp-hero-mini-card"><span>Cupos</span><strong>50</strong></div>
                <div class="sp-hero-mini-card"><span>Reserva</span><strong>15m</strong></div>
                <div class="sp-hero-mini-card"><span>Tablas</span><strong>7</strong></div>
                <div class="sp-hero-mini-card"><span>IA</span><strong>Local</strong></div>
              </div>
            </div>
            <div class="sp-hero-floating-note">
              <strong>QR + Portería</strong><br>
              <small>Check-in rápido, lista de espera y alertas operativas.</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <main class="content-wrapper px-4">
    <section class="sp-section" id="problema">
      <div class="container">
        <div class="sp-section-title">
          <span class="sp-pill mb-3"><i class="bi bi-exclamation-octagon"></i> Problema actual</span>
          <h2>La operación manual limita el control, la trazabilidad y la calidad del servicio.</h2>
          <p>SMARTPARK nace para resolver problemas reales de parqueaderos pequeños y medianos: información dispersa, cálculos manuales, espacios mal asignados y falta de reportes confiables.</p>
        </div>
        <div class="row">
          <div class="col-md-4 grid-margin"><div class="card h-100"><div class="card-body">
            <h4>Pérdida de información</h4>
            <ul class="sp-problem-list">
              <li><i class="bi bi-x-circle"></i><span>Libretas físicas vulnerables a daño o extravío.</span></li>
              <li><i class="bi bi-x-circle"></i><span>Archivos de Excel sin estructura relacional.</span></li>
              <li><i class="bi bi-x-circle"></i><span>Historial difícil de consultar por cliente o placa.</span></li>
            </ul>
          </div></div></div>
          <div class="col-md-4 grid-margin"><div class="card h-100"><div class="card-body">
            <h4>Errores operativos</h4>
            <ul class="sp-problem-list">
              <li><i class="bi bi-x-circle"></i><span>Cálculo manual de tarifas y reclamos de clientes.</span></li>
              <li><i class="bi bi-x-circle"></i><span>Asignación visual de cupos sin trazabilidad.</span></li>
              <li><i class="bi bi-x-circle"></i><span>Reservas sin control de vencimiento.</span></li>
            </ul>
          </div></div></div>
          <div class="col-md-4 grid-margin"><div class="card h-100"><div class="card-body">
            <h4>Falta de dirección</h4>
            <ul class="sp-problem-list">
              <li><i class="bi bi-x-circle"></i><span>Sin indicadores de ocupación ni horas pico.</span></li>
              <li><i class="bi bi-x-circle"></i><span>Sin reporte ejecutivo para administración.</span></li>
              <li><i class="bi bi-x-circle"></i><span>Sin evidencia para toma de decisiones.</span></li>
            </ul>
          </div></div></div>
        </div>
      </div>
    </section>

    <section class="sp-section sp-dark-band mx-n4 px-4" id="solucion">
      <div class="container">
        <div class="sp-section-title">
          <span class="sp-pill mb-3"><i class="bi bi-lightning-charge"></i> Solución SMARTPARK</span>
          <h2 class="text-white">Un ecosistema completo para gestionar el parqueadero universitario.</h2>
          <p>La plataforma conecta comunidad, portería, caja y administración en un solo flujo digital.</p>
        </div>
        <div class="row">
          <div class="col-md-3 grid-margin"><div class="card h-100 sp-feature"><div class="card-body"><i class="bi bi-qr-code-scan"></i><h4 class="mt-3">Reservas QR</h4><p class="text-muted">Reserva por 15 minutos, consulta estado y valida llegada desde portería.</p></div></div></div>
          <div class="col-md-3 grid-margin"><div class="card h-100 sp-feature"><div class="card-body"><i class="bi bi-hourglass-split"></i><h4 class="mt-3">Lista de espera</h4><p class="text-muted">Si no hay cupos, el sistema asigna automáticamente cuando se libera uno compatible.</p></div></div></div>
          <div class="col-md-3 grid-margin"><div class="card h-100 sp-feature"><div class="card-body"><i class="bi bi-stars"></i><h4 class="mt-3">IA operativa</h4><p class="text-muted">Predicción por hora, riesgo de no-show, alertas y recomendaciones de cupos.</p></div></div></div>
          <div class="col-md-3 grid-margin"><div class="card h-100 sp-feature"><div class="card-body"><i class="bi bi-file-earmark-pdf"></i><h4 class="mt-3">Reporte ejecutivo</h4><p class="text-muted">Indicadores financieros, ambientales, operativos y de demanda listos para dirección.</p></div></div></div>
        </div>
      </div>
    </section>

    <section class="sp-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-5 grid-margin">
            <div class="sp-section-title mb-0">
              <span class="sp-pill mb-3"><i class="bi bi-phone-flip"></i> Experiencia interactiva</span>
              <h2>Una experiencia pública pensada como app universitaria.</h2>
              <p>Reserva desde el celular, consulta disponibilidad, confirma QR y recibe asistencia por WhatsApp. La propuesta visual ayuda a que la universidad vea el sistema como un producto listo para usuarios reales.</p>
              <a class="btn btn-primary mt-3" href="ReservarParqueadero.php">Probar experiencia</a>
            </div>
          </div>
          <div class="col-lg-7 grid-margin">
            <img class="sp-experience-image" src="assets/custom/imgs/smartpark-experiencia-collage.png" alt="Experiencia visual SMARTPARK para reservas universitarias">
          </div>
        </div>
      </div>
    </section>

    <section class="sp-section">
      <div class="container">
        <div class="sp-section-title">
          <span class="sp-pill mb-3"><i class="bi bi-arrow-left-right"></i> Antes vs SMARTPARK</span>
          <h2>El cambio no es solo digitalizar: es convertir operación en inteligencia.</h2>
        </div>
        <div class="sp-before-after">
          <div class="card h-100"><div class="card-body">
            <h4>Antes</h4>
            <ul class="sp-problem-list">
              <li><i class="bi bi-x-circle"></i><span>Libretas y registros manuales.</span></li>
              <li><i class="bi bi-x-circle"></i><span>Espacios asignados por observación.</span></li>
              <li><i class="bi bi-x-circle"></i><span>Cobros calculados a mano.</span></li>
              <li><i class="bi bi-x-circle"></i><span>Sin trazabilidad ni reportes ejecutivos.</span></li>
            </ul>
          </div></div>
          <div class="card h-100 sp-insight"><div class="card-body">
            <h4>Con SMARTPARK</h4>
            <ul class="sp-problem-list sp-value-list">
              <li><i class="bi bi-check-circle"></i><span>Reservas QR con vencimiento automático.</span></li>
              <li><i class="bi bi-check-circle"></i><span>Mapa inteligente y recomendación de cupo.</span></li>
              <li><i class="bi bi-check-circle"></i><span>Cálculo automático de tarifa y recibo.</span></li>
              <li><i class="bi bi-check-circle"></i><span>IA, alertas, CO₂, horas pico y PDF ejecutivo.</span></li>
            </ul>
          </div></div>
        </div>
      </div>
    </section>

    <section class="sp-section" id="arquitectura">
      <div class="container">
        <div class="sp-section-title">
          <span class="sp-pill mb-3"><i class="bi bi-cpu"></i> Arquitectura de producto</span>
          <h2>Una plataforma modular lista para crecer.</h2>
          <p>Cada módulo cumple una función clara y se conecta con el modelo relacional normalizado.</p>
        </div>
        <div class="sp-architecture">
          <div class="sp-architecture-node"><i class="bi bi-phone"></i><strong>Comunidad</strong><span>Reserva web, QR y WhatsApp</span></div>
          <div class="sp-architecture-node"><i class="bi bi-qr-code-scan"></i><strong>Portería</strong><span>Check-in QR y control de llegada</span></div>
          <div class="sp-architecture-node"><i class="bi bi-database"></i><strong>Base de datos</strong><span>7 tablas normalizadas, FK y triggers</span></div>
          <div class="sp-architecture-node"><i class="bi bi-stars"></i><strong>IA local</strong><span>Predicción, no-show y alertas</span></div>
          <div class="sp-architecture-node"><i class="bi bi-file-earmark-bar-graph"></i><strong>Dirección</strong><span>Reportes, CO₂ y PDF ejecutivo</span></div>
        </div>
      </div>
    </section>

    <section class="sp-section" id="demo">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-5 grid-margin">
            <div class="sp-section-title mb-0">
              <span class="sp-pill mb-3"><i class="bi bi-play-circle"></i> Recorrido demo</span>
              <h2>Una demostración guiada en menos de cinco minutos.</h2>
              <p>Estos accesos permiten mostrar el sistema desde la experiencia pública hasta el panel administrativo.</p>
            </div>
          </div>
          <div class="col-lg-7 grid-margin">
            <div class="card"><div class="card-body">
              <a class="sp-demo-link" href="Disponibilidad.php"><span><i class="bi bi-broadcast mr-2"></i> Ver disponibilidad pública en vivo</span><i class="bi bi-arrow-right"></i></a>
              <a class="sp-demo-link" href="ReservarParqueadero.php"><span><i class="bi bi-calendar-plus mr-2"></i> Crear reserva QR de 15 minutos</span><i class="bi bi-arrow-right"></i></a>
              <a class="sp-demo-link" href="dashboard/Porteria.php"><span><i class="bi bi-qr-code-scan mr-2"></i> Validar reserva en portería</span><i class="bi bi-arrow-right"></i></a>
              <a class="sp-demo-link" href="dashboard/IAInsights.php"><span><i class="bi bi-stars mr-2"></i> Revisar IA operativa</span><i class="bi bi-arrow-right"></i></a>
              <a class="sp-demo-link" href="dashboard/Reportes.php"><span><i class="bi bi-file-earmark-pdf mr-2"></i> Descargar reporte ejecutivo</span><i class="bi bi-arrow-right"></i></a>
            </div></div>
          </div>
        </div>
      </div>
    </section>

    <section class="sp-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-5 grid-margin">
            <div class="sp-section-title mb-0">
              <span class="sp-pill mb-3"><i class="bi bi-diagram-3"></i> Flujo operativo</span>
              <h2>Desde la reserva hasta el recibo, todo queda trazado.</h2>
              <p>SMARTPARK digitaliza el ciclo completo del servicio, evitando doble asignación y dejando evidencia en base de datos.</p>
            </div>
          </div>
          <div class="col-lg-7 grid-margin">
            <div class="card"><div class="card-body">
              <div class="sp-step"><span class="sp-step-number">1</span><div><h5>Reserva web o WhatsApp</h5><p class="text-muted mb-0">El usuario solicita cupo y el sistema registra cliente, vehículo y tipo de usuario.</p></div></div>
              <div class="sp-step"><span class="sp-step-number">2</span><div><h5>Asignación inteligente</h5><p class="text-muted mb-0">Se asigna un espacio libre compatible o se envía a lista de espera automática.</p></div></div>
              <div class="sp-step"><span class="sp-step-number">3</span><div><h5>Check-in QR en portería</h5><p class="text-muted mb-0">Portería escanea el QR, confirma llegada y convierte la reserva en parqueo activo.</p></div></div>
              <div class="sp-step"><span class="sp-step-number">4</span><div><h5>Salida, pago y recibo</h5><p class="text-muted mb-0">El sistema calcula tiempo, tarifa, pago y genera recibo único.</p></div></div>
            </div></div>
          </div>
        </div>
      </div>
    </section>

    <section class="sp-section">
      <div class="container">
        <div class="sp-section-title">
          <span class="sp-pill mb-3"><i class="bi bi-graph-up-arrow"></i> Indicadores de valor</span>
          <h2>Diseñado para operación diaria y decisión administrativa.</h2>
        </div>
        <div class="row">
          <div class="col-md-3 grid-margin stretch-card"><div class="card sp-kpi-card"><div class="card-body"><span class="sp-kpi-icon"><i class="bi bi-grid"></i></span><p class="sp-kpi-label">Capacidad</p><h3 class="sp-kpi-value">50</h3><p class="sp-kpi-note mb-0">Espacios en 2 pisos</p></div></div></div>
          <div class="col-md-3 grid-margin stretch-card"><div class="card sp-kpi-card"><div class="card-body"><span class="sp-kpi-icon"><i class="bi bi-database-check"></i></span><p class="sp-kpi-label">Base de datos</p><h3 class="sp-kpi-value">7</h3><p class="sp-kpi-note mb-0">Tablas normalizadas</p></div></div></div>
          <div class="col-md-3 grid-margin stretch-card"><div class="card sp-kpi-card"><div class="card-body"><span class="sp-kpi-icon"><i class="bi bi-clock"></i></span><p class="sp-kpi-label">Reserva</p><h3 class="sp-kpi-value">15</h3><p class="sp-kpi-note mb-0">Minutos de vigencia</p></div></div></div>
          <div class="col-md-3 grid-margin stretch-card"><div class="card sp-kpi-card"><div class="card-body"><span class="sp-kpi-icon"><i class="bi bi-leaf"></i></span><p class="sp-kpi-label">Impacto</p><h3 class="sp-kpi-value">CO₂</h3><p class="sp-kpi-note mb-0">Estimación ambiental</p></div></div></div>
        </div>
      </div>
    </section>

    <section class="sp-section sp-dark-band mx-n4 px-4">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 grid-margin">
            <span class="sp-pill mb-3"><i class="bi bi-database"></i> Bases de Datos 2</span>
            <h2 class="text-white font-weight-bold">Sólido para sustentación académica.</h2>
            <p class="text-muted">El modelo conserva 7 tablas de dominio y aplica normalización, llaves primarias, foráneas, índices, restricciones, vistas, triggers y consultas de sustentación.</p>
          </div>
          <div class="col-lg-6 grid-margin">
            <div class="card"><div class="card-body">
              <ul class="sp-problem-list sp-value-list">
                <li><i class="bi bi-check-circle"></i><span>1FN, 2FN y 3FN documentadas.</span></li>
                <li><i class="bi bi-check-circle"></i><span>Integridad referencial con FK y restricciones CHECK.</span></li>
                <li><i class="bi bi-check-circle"></i><span>Triggers para consistencia de espacios y pagos.</span></li>
                <li><i class="bi bi-check-circle"></i><span>Lista de espera sin crear tabla adicional.</span></li>
              </ul>
            </div></div>
          </div>
        </div>
      </div>
    </section>

    <section class="sp-section">
      <div class="container">
        <div class="card sp-insight">
          <div class="card-body p-4">
            <div class="row align-items-center">
              <div class="col-lg-8">
                <h2 class="font-weight-bold mb-2">SMARTPARK está listo para demostración institucional.</h2>
                <p class="text-muted mb-lg-0">Explora disponibilidad, crea una reserva, consulta por QR o entra al panel administrativo para ver IA, reportes y operación.</p>
              </div>
              <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                <a class="btn btn-primary mr-2" href="Disponibilidad.php">Ver disponibilidad</a>
                <a class="btn sp-whatsapp-btn" target="_blank" href="WhatsAppReserva.php?origen=cta_presentacion"><i class="bi bi-whatsapp"></i> WhatsApp</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <a class="sp-whatsapp-floating" target="_blank" href="WhatsAppReserva.php?origen=boton_flotante_presentacion" title="Reservar por WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>
  <?php include('basesLogin/brand_footer.php'); ?>
  <?php include('basesLogin/footerJS.html'); ?>
</body>
</html>
