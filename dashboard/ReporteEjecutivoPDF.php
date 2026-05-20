<?php
session_start();
if (!isset($_SESSION['emailUser']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: ../acciones/login/exit.php');
    exit;
}

require_once('../tcpdf/tcpdf.php');
include('smartpark_funciones.php');

$stats = sp_stats($con);
$ambiental = sp_panel_ambiental($con);
$prediccion = sp_ia_prediccion_ocupacion($con);
$recomendacion = sp_ia_recomendacion_cupos($con);
$alertas = sp_alertas_operativas($con);
$horas = sp_ranking_horas_pico($con);
$ingresos = sp_reporte_ingresos($con);

if (ob_get_length()) {
    ob_end_clean();
}

$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('SMARTPARK');
$pdf->SetAuthor('SMARTPARK Zona Segura ECCI');
$pdf->SetTitle('Reporte Ejecutivo SMARTPARK');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(14, 14, 14);
$pdf->AddPage();

$fecha = date('d/m/Y h:i A');
$html = '
<style>
  h1 { color:#0f766e; font-size:24px; }
  h2 { color:#172033; font-size:16px; margin-top:16px; }
  .muted { color:#64748b; }
  .kpi { border:1px solid #dfe7ea; padding:8px; font-size:12px; }
  .value { font-size:18px; font-weight:bold; color:#172033; }
  table { border-collapse:collapse; width:100%; }
  th { background:#0f766e; color:#fff; font-weight:bold; }
  td, th { border:1px solid #dfe7ea; padding:6px; font-size:11px; }
</style>
<h1>Reporte Ejecutivo SMARTPARK</h1>
<p class="muted">Parqueadero Zona Segura ECCI · Generado: ' . sp_escape($fecha) . '</p>

<h2>Indicadores operativos</h2>
<table>
  <tr>
    <td class="kpi"><strong>Capacidad</strong><br><span class="value">' . sp_escape($stats['espacios']) . '</span></td>
    <td class="kpi"><strong>Libres</strong><br><span class="value">' . sp_escape($stats['libres']) . '</span></td>
    <td class="kpi"><strong>Reservados</strong><br><span class="value">' . sp_escape($stats['reservados']) . '</span></td>
    <td class="kpi"><strong>Lista de espera</strong><br><span class="value">' . sp_escape($stats['espera']) . '</span></td>
  </tr>
</table>

<h2>IA operativa</h2>
<p><strong>Ocupación actual:</strong> ' . sp_escape($prediccion['ocupacion_actual']) . '% · <strong>Proyección próxima hora:</strong> ' . sp_escape($prediccion['ocupacion_proyectada']) . '%</p>
<p>' . sp_escape($prediccion['mensaje']) . '</p>
<p><strong>Política sugerida:</strong> ' . sp_escape($recomendacion['politica']) . '. ' . sp_escape($recomendacion['mensaje']) . '</p>

<h2>Impacto ambiental estimado</h2>
<table>
  <tr>
    <td><strong>CO2 evitado</strong><br>' . sp_escape($ambiental['co2_evitado']) . ' kg</td>
    <td><strong>Km evitados</strong><br>' . sp_escape($ambiental['km_evitados']) . '</td>
    <td><strong>Tiempo evitado</strong><br>' . sp_escape($ambiental['minutos_evitados']) . ' min</td>
    <td><strong>Árboles equivalentes</strong><br>' . sp_escape($ambiental['arboles_equivalentes']) . '</td>
  </tr>
</table>
<p class="muted">' . sp_escape($ambiental['nota']) . '</p>

<h2>Alertas operativas</h2>
<table><tr><th>Nivel</th><th>Alerta</th><th>Detalle</th></tr>';
foreach ($alertas as $alerta) {
    $html .= '<tr><td>' . sp_escape($alerta['nivel']) . '</td><td>' . sp_escape($alerta['titulo']) . '</td><td>' . sp_escape($alerta['mensaje']) . '</td></tr>';
}
$html .= '</table>

<h2>Horas pico</h2>
<table><tr><th>Hora</th><th>Movimientos</th><th>Reservas</th><th>Finalizados</th></tr>';
while ($h = mysqli_fetch_assoc($horas)) {
    $html .= '<tr><td>' . str_pad(sp_escape($h['hora']), 2, '0', STR_PAD_LEFT) . ':00</td><td>' . sp_escape($h['movimientos']) . '</td><td>' . sp_escape($h['reservas']) . '</td><td>' . sp_escape($h['finalizados']) . '</td></tr>';
}
$html .= '</table>

<h2>Ingresos recientes</h2>
<table><tr><th>Fecha</th><th>Pagos</th><th>Total</th></tr>';
$contador = 0;
while ($r = mysqli_fetch_assoc($ingresos)) {
    if ($contador >= 8) {
        break;
    }
    $html .= '<tr><td>' . sp_escape($r['fecha']) . '</td><td>' . sp_escape($r['pagos']) . '</td><td>' . sp_format_money($r['total']) . '</td></tr>';
    $contador++;
}
$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('reporte_ejecutivo_smartpark.pdf', 'I');
