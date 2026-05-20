# Roadmap de IA para SMARTPARK

## Objetivo

Usar IA para mejorar la planeación del parqueadero, anticipar congestión y ayudar a la administración a tomar decisiones.

## Fase 1 - IA Operativa Basada en Reglas

Ya implementada en el módulo `IA Operativa`.

- Alertas por ocupación alta.
- Alertas por reservas próximas a vencer.
- Detección de muchas reservas vencidas en el día.
- Recomendaciones operativas para portería.
- Predicción básica de ocupación para la próxima hora usando histórico por franja.
- Riesgo de no-show por tipo de usuario.
- Recomendación automática de cupos disponibles para reserva.
- Asistente administrativo con preguntas rápidas sobre recaudo, ocupación y reservas.

Esta fase no usa servicios externos. Su ventaja es que funciona localmente, no genera costos y permite demostrar valor inmediato con los datos de la base.

## Fase 2 - Predicción

Con datos históricos suficientes:

- Predicción de ocupación por hora.
- Identificación de horas pico.
- Estimación de probabilidad de no presentación.
- Recomendación de cupos disponibles para reserva.
- Predicción de reservas vencidas por tipo de usuario.
- Modelo de clasificación para priorizar solicitudes de reserva.

## Fase 3 - Asistente Conversacional

Un asistente para administración que responda preguntas como:

- ¿Cuánto se recaudó esta semana?
- ¿Qué día hubo más reservas vencidas?
- ¿Cuál es el porcentaje de ocupación promedio?
- ¿Qué tipo de usuario usa más el parqueadero?

## Fase 3B - WhatsApp Institucional

Primera versión implementada:

- Botón público "Reservar por WhatsApp".
- Mensaje prellenado con los campos requeridos para reservar.
- Accesos desde login, disponibilidad, presentación y formulario de reserva.
- Archivo central: `WhatsAppReserva.php`.

Escalamiento empresarial con WhatsApp Business Cloud API:

- Crear cuenta de Meta Business.
- Activar WhatsApp Business Platform.
- Configurar número oficial de la universidad.
- Crear webhook público HTTPS para recibir mensajes.
- Procesar conversación paso a paso: cédula, nombre, placa, tipo de vehículo.
- Crear reserva automáticamente en `tbl_parqueos`.
- Responder por WhatsApp con código, espacio, vencimiento y enlace QR.
- Consultar estado de reserva enviando el código por WhatsApp.

## Fase 4 - Optimización

- Recomendación automática de distribución de espacios para autos y motos.
- Priorización de reservas para estudiantes, docentes o funcionarios según política institucional.
- Alertas predictivas para reforzar personal de portería.

## Datos Necesarios

- Historial de parqueos.
- Reservas creadas, confirmadas, vencidas y canceladas.
- Hora de ingreso y salida.
- Tipo de usuario.
- Tipo de vehículo.
- Día de la semana y franja horaria.

## Integración Técnica Futura

- API de IA para generar explicaciones de reportes.
- Modelo predictivo entrenado con datos históricos.
- Panel de recomendaciones dentro del dashboard.
- Vectorización de reportes para consultas en lenguaje natural.
- Entrenamiento con histórico anonimizado para proteger datos personales.
