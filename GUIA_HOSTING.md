# 🚀 Guía para Subir SMARTPARK a Hosting Gratuito

## 📋 PASOS DETALLADOS PARA DESPLIEGUE

### PASO 1: Elegir Hosting Gratuito
**Recomendación: 000webhost**
- ✅ PHP 8+ compatible
- ✅ MySQL/MariaDB incluido
- ✅ 1GB espacio gratuito
- ✅ Sin publicidad forzada
- ✅ FTP + Panel de control fácil

**Registro:** https://www.000webhost.com/

### PASO 2: Preparar Archivos Locales

#### A) Backup de Base de Datos
```bash
# Desde tu XAMPP local:
cd /Applications/XAMPP/xamppfiles/htdocs/sistema-parking-estacionamiento
/Applications/XAMPP/bin/mysqldump -u root smartpark > smartpark_backup.sql
```

#### B) Comprimir Proyecto
```bash
# Crear ZIP del proyecto completo
cd /Applications/XAMPP/xamppfiles/htdocs
zip -r smartpark_para_hosting.zip sistema-parking-estacionamiento/
```

**Archivos que necesitas subir:**
- ✅ `smartpark_backup.sql` (backup de BD)
- ✅ `smartpark_para_hosting.zip` (todos los archivos)

### PASO 3: Configurar Hosting

#### A) Crear Base de Datos
1. En el panel de 000webhost → **Database Manager**
2. Crear nueva base de datos MySQL
3. **Anotar credenciales:**
   - Nombre de BD: `tu_base_datos`
   - Usuario: `tu_usuario`
   - Contraseña: `tu_password`
   - Host: Generalmente `localhost`

#### B) Subir Archivos via FTP
1. En panel de 000webhost → **File Manager** o **FTP Details**
2. Conectar con FTP (FileZilla recomendado)
3. **Subir `smartpark_para_hosting.zip`**
4. **Extraer el ZIP** en la raíz del hosting (`public_html/`)

**Estructura final en hosting:**
```
public_html/
├── index.php
├── config/
├── dashboard/
├── acciones/
├── assets/
├── emails/
├── PHPMailer/
├── tcpdf/
└── ... (todos los archivos)
```

### PASO 4: Configurar Base de Datos

#### A) Importar Base de Datos
1. En panel de hosting → **phpMyAdmin**
2. Seleccionar tu base de datos
3. Ir a **Importar**
4. Subir `smartpark_backup.sql`
5. **Ejecutar importación**

#### B) Configurar Conexión
1. En tu hosting, editar `config/config.php`
2. **Reemplazar con las credenciales reales:**
```php
$usuario = "tu_usuario_real"; // Del panel de hosting
$password = "tu_password_real"; // Del panel de hosting
$servidor = "localhost"; // Generalmente localhost
$basededatos = "tu_base_datos_real"; // Nombre de tu BD
```

### PASO 5: Configurar Emails (Opcional)

Si quieres que funcionen los emails de confirmación:
1. En `emails/aviso_reserva_email.php` y otros archivos de email
2. Configurar SMTP del hosting o usar servicio externo (Gmail, SendGrid)
3. **Nota:** Muchos hostings gratuitos bloquean envío de emails

### PASO 6: Probar la Aplicación

#### URLs de tu sitio:
- **Inicio:** `https://tusitioweb.000webhostapp.com/`
- **Reserva pública:** `https://tusitioweb.000webhostapp.com/ReservarParqueadero.php`
- **Admin:** `https://tusitioweb.000webhostapp.com/dashboard/`

#### Probar funcionalidades:
1. ✅ Login admin (usuario por defecto del backup)
2. ✅ Reserva pública con cliente frecuente
3. ✅ Dashboard y reportes
4. ✅ QR codes y consultas

### PASO 7: Solución de Problemas Comunes

#### ❌ Error de conexión MySQL
- Verificar credenciales en `config.php`
- Confirmar que la BD existe y está importada
- Revisar permisos de usuario MySQL

#### ❌ Página en blanco
- Activar `display_errors` en `config.php` temporalmente
- Revisar logs de error del hosting
- Verificar PHP version (mínimo 7.4)

#### ❌ Emails no funcionan
- Configurar SMTP en PHPMailer
- Usar servicio de emails (Mailgun, SendGrid free tier)
- O simplemente desactivar envío de emails

#### ❌ Archivos no se suben
- Verificar límite de tamaño de archivos del hosting
- Subir archivos en lotes más pequeños
- Usar FTP en lugar del File Manager

### PASO 8: Mantenimiento

#### Actualizaciones:
- Para actualizar código: Subir archivos modificados via FTP
- Para actualizar BD: Exportar nueva versión y reimportar

#### Backup:
- Descargar BD periódicamente desde phpMyAdmin
- Hacer backup de archivos importantes

### 💡 Consejos para Hosting Gratuito

1. **Límites de uso:** La mayoría tiene límites de CPU/memoria
2. **Sin garantía:** Pueden suspender cuentas inactivas
3. **Actualizaciones:** Revisa compatibilidad cuando actualices PHP
4. **Seguridad:** Cambia contraseñas por defecto inmediatamente

### 🔄 Alternativas si 000webhost no funciona

**InfinityFree:** https://infinityfree.net/
- Similar a 000webhost
- 5GB espacio gratuito

**FreeHostia:** https://www.freehostia.com/
- Más generoso con recursos

---

## 📞 Soporte

Si tienes problemas durante el despliegue:
1. Revisa los logs de error del hosting
2. Verifica credenciales de BD
3. Confirma que todos los archivos se subieron correctamente
4. Prueba con datos demo incluidos

¡Tu SMARTPARK estará online en minutos! 🎉🚀