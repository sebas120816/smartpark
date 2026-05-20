<?php
session_start();
if (isset($_SESSION['emailUser']) != "" && $_SESSION['rol'] == 'Administrador') {
  $IdUser = $_SESSION['IdUser'];
  $rolUser = $_SESSION['rol'];
  $email = $_SESSION['emailUser'];
  include('smartpark_funciones.php');
  $insights = sp_ia_insights($con);
  $prediccion = sp_ia_prediccion_ocupacion($con);
  $franjas = sp_ia_prediccion_franjas($con, 8);
  $riesgos = sp_ia_riesgo_no_show($con);
  $recomendacion = sp_ia_recomendacion_cupos($con);
  $pregunta = isset($_POST['pregunta']) ? trim($_POST['pregunta']) : '';
  $respuesta = $pregunta !== '' ? sp_ia_asistente($con, $pregunta) : '';
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
          <h2 class="font-weight-bold">IA Operativa SMARTPARK</h2>
          <p class="text-muted mb-0">Analítica local, predicción básica y recomendaciones operativas sin depender de servicios externos.</p>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 grid-margin stretch-card">
          <div class="card"><div class="card-body">
            <p class="text-muted mb-2">Ocupación actual</p>
            <h3><?php echo sp_escape($prediccion['ocupacion_actual']); ?>%</h3>
            <p class="mb-0"><?php echo sp_escape($prediccion['mensaje']); ?></p>
          </div></div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
          <div class="card"><div class="card-body">
            <p class="text-muted mb-2">Proyección próxima hora</p>
            <h3><?php echo sp_escape($prediccion['ocupacion_proyectada']); ?>%</h3>
            <p class="mb-0">Promedio histórico franja <?php echo sp_escape($prediccion['hora']); ?>:00: <?php echo sp_escape($prediccion['promedio_historico']); ?> ingreso(s).</p>
          </div></div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
          <div class="card"><div class="card-body">
            <p class="text-muted mb-2">Política sugerida</p>
            <h3><?php echo sp_escape($recomendacion['politica']); ?></h3>
            <p class="mb-0">Cupos recomendados para reserva: <?php echo sp_escape($recomendacion['cupos_reserva']); ?></p>
          </div></div>
        </div>
      </div>

      <div class="row">
        <?php foreach ($insights as $item) { ?>
          <div class="col-md-4 grid-margin">
            <div class="card h-100"><div class="card-body">
              <h4><?php echo sp_escape($item[0]); ?></h4>
              <p class="mb-0"><?php echo sp_escape($item[1]); ?></p>
            </div></div>
          </div>
        <?php } ?>
      </div>

      <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
          <div class="card sp-insight">
            <div class="card-body">
              <div class="d-md-flex justify-content-between align-items-start mb-4">
                <div>
                  <h4 class="card-title mb-1">Predicción por franja horaria</h4>
                  <p class="text-muted mb-0">Proyección de ocupación para las próximas 8 horas usando histórico local por día y hora.</p>
                </div>
                <span class="sp-pill mt-3 mt-md-0"><i class="bi bi-graph-up-arrow"></i> IA local</span>
              </div>
              <?php foreach ($franjas as $franja) { ?>
                <div class="sp-forecast-row">
                  <div class="sp-forecast-hour"><?php echo sp_escape($franja['hora']); ?></div>
                  <div>
                    <div class="sp-forecast-bar">
                      <div class="sp-forecast-fill <?php echo sp_escape($franja['class']); ?>" style="width: <?php echo sp_escape($franja['ocupacion']); ?>%;"></div>
                    </div>
                    <small class="text-muted">
                      Histórico: <?php echo sp_escape($franja['promedio']); ?> ingreso(s) · Esperados: <?php echo sp_escape($franja['demanda_esperada']); ?>
                    </small>
                  </div>
                  <div><strong><?php echo sp_escape($franja['ocupacion']); ?>%</strong></div>
                  <div class="sp-forecast-risk text-<?php echo sp_escape($franja['class']); ?>"><?php echo sp_escape($franja['riesgo']); ?></div>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 grid-margin">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Riesgo de no-show por tipo de usuario</h4>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead><tr><th>Tipo</th><th>Reservas</th><th>Vencidas</th><th>Riesgo</th></tr></thead>
                <tbody>
                  <?php while ($r = mysqli_fetch_assoc($riesgos)) { ?>
                    <tr>
                      <td><?php echo sp_escape($r['tipo_cliente']); ?></td>
                      <td><?php echo sp_escape($r['reservas']); ?></td>
                      <td><?php echo sp_escape($r['vencidas']); ?></td>
                      <td><?php echo sp_escape($r['riesgo']); ?>%</td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div></div>
        </div>
        <div class="col-md-6 grid-margin">
          <div class="card"><div class="card-body">
            <h4 class="card-title">Asistente de administración</h4>
            <form method="post">
              <input class="form-control mb-3" name="pregunta" value="<?php echo sp_escape($pregunta); ?>" placeholder="Ej: ¿cuánto se recaudó hoy?">
              <button class="btn btn-primary btn-block">Preguntar</button>
            </form>
            <?php if ($respuesta !== '') { ?>
              <div class="alert alert-info mt-3 mb-0"><?php echo sp_escape($respuesta); ?></div>
            <?php } ?>
            <p class="text-muted mt-3 mb-0">Puedes preguntar por recaudo, ocupación, espacios libres, reservas vencidas y uso por auto/moto.</p>
          </div></div>
        </div>
      </div>

      <div class="card mt-2">
        <div class="card-body">
          <h4 class="card-title">Recomendación ejecutiva</h4>
          <p class="mb-0"><?php echo sp_escape($recomendacion['mensaje']); ?></p>
        </div>
      </div>
    </div></div>
  </div>
</div>
<?php include 'bases/PageJs.html'; ?>
</body></html>
<?php } else { ?><script>location.href="../acciones/login/exit.php";</script><?php } ?>
