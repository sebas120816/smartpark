# Funcionalidad de Cliente Frecuente - SMARTPARK

## Descripción
La funcionalidad de **Cliente Frecuente** permite que los usuarios registrados anteriormente puedan autocompletar sus datos personales y de vehículo al realizar una nueva reserva, simplemente ingresando su número de cédula.

## Cómo funciona

### Para usuarios registrados
1. **Ingresa tu cédula**: En el campo "Cédula / código institucional" del formulario de reserva
2. **Autocompletado automático**: Al salir del campo (evento `blur`), el sistema busca automáticamente tus datos
3. **Datos completados**: Si eres cliente frecuente, se autocompletarán:
   - Nombres completos
   - Teléfono
   - Correo electrónico
   - Tipo de cliente (estudiante, docente, etc.)
   - **Datos del vehículo** (si tienes uno registrado):
     - Placa
     - Marca
     - Modelo
     - Color
     - Tipo (auto/moto)

### Para usuarios nuevos
- Si la cédula no está registrada, aparecerá un mensaje informativo
- Debes completar manualmente todos los campos
- Al enviar la reserva, se creará tu perfil de cliente frecuente para futuras reservas

## Beneficios
- **Ahorro de tiempo**: No necesitas recordar y escribir todos tus datos cada vez
- **Reducción de errores**: Los datos se cargan automáticamente desde la base de datos
- **Experiencia mejorada**: Proceso de reserva más rápido y fluido
- **Perfil único**: Tu información se mantiene actualizada en cada reserva

## Implementación técnica

### Endpoint AJAX
- **Archivo**: `acciones/buscar_cliente.php`
- **Método**: GET
- **Parámetro**: `cedula` (string)
- **Respuesta**: JSON con datos del cliente y vehículo (si existe)

### JavaScript
- **Evento**: `blur` en campo cédula
- **Validación**: Mínimo 3 caracteres
- **Estados visuales**:
  - 🔍 Buscando...
  - ✅ Cliente encontrado
  - ⚠️ Cliente no registrado
  - ❌ Error de conexión

### Seguridad
- **Prepared statements** para prevenir SQL injection
- **Validación de entrada** en servidor
- **JSON responses** seguras
- **Manejo de errores** sin exponer información sensible

## Casos de uso
1. **Estudiante universitario**: Reserva semanal para clases
2. **Docente**: Reservas frecuentes para eventos académicos
3. **Funcionario**: Uso diario del parqueadero
4. **Visitante recurrente**: Viajes periódicos a la institución

## Notas importantes
- La búsqueda es **case-sensitive** para la cédula
- Si cambias la cédula después del autocompletado, los campos se resetean
- Los datos del vehículo se toman del **último vehículo registrado**
- Si tienes múltiples vehículos, solo se autocompleta el más reciente

## Testing
Para probar la funcionalidad:
1. Registra un cliente con datos demo
2. Ve a `ReservarParqueadero.php`
3. Ingresa la cédula registrada
4. Verifica que los campos se autocompleten

¡Esta funcionalidad hace que SMARTPARK sea mucho más conveniente para usuarios frecuentes! 🚗✨