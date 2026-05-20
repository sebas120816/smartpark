# Propuesta de Valor SMARTPARK para la Universidad

SMARTPARK transforma el control manual del parqueadero en una plataforma web con trazabilidad, control de cupos, reservas temporales y reportes.

## Diferenciadores

- Reserva pública para estudiantes, docentes, funcionarios y visitantes.
- Código único de reserva.
- Consulta de estado por QR.
- Ventana de llegada de 15 minutos.
- Liberación automática del espacio si la reserva vence.
- Panel administrativo con reservas por vencer.
- Control de espacios libres, reservados y ocupados.
- Historial por cliente o vehículo.
- Recibos únicos y reportes de ingresos.
- Modelo de base de datos normalizado y sustentable académicamente.

## Flujo QR

1. El usuario crea una reserva.
2. El sistema genera un código único.
3. Se muestra un QR que apunta a `ConsultarReserva.php`.
4. El usuario o personal de control escanea el QR.
5. La página muestra estado: reservado, activo, finalizado, vencido o cancelado.
6. Si está dentro de los 15 minutos, control confirma llegada en el panel administrativo.

## Beneficios para la Universidad

- Reduce congestión en el ingreso.
- Evita asignación doble de espacios.
- Mejora transparencia del cobro.
- Permite medir demanda real del parqueadero.
- Genera información para decisiones operativas.
- Da trazabilidad a estudiantes, docentes y funcionarios.

## Siguientes mejoras comerciales posibles

- QR generado localmente sin servicio externo.
- Notificación por correo al crear reserva.
- Panel en pantalla grande para entrada del parqueadero.
- Integración con carné institucional.
- Exportación PDF/Excel de reportes.
- Roles más detallados para vigilancia y administración.
- Predicción de ocupación con IA.
- Asistente conversacional para reportes administrativos.

## IA Operativa Incluida

SMARTPARK ya incorpora una primera capa de IA local:

- diagnóstico de ocupación actual;
- proyección simple de la próxima hora;
- riesgo de no-show por tipo de usuario;
- recomendación de cupos para reserva;
- asistente administrativo para preguntas rápidas.

Esta capa permite demostrar inteligencia operativa sin depender de internet ni de costos de APIs externas.
