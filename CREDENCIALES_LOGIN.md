# 🔐 Credenciales de Login - ERP Teleserp

## Usuarios de Prueba

### 👨‍💼 Manager (Administrador)

**Acceso completo a todos los módulos**

- **Email:** `admin@teleserp.local`
- **Contraseña:** `Admin#1234`
- **Rol:** Manager

**Módulos accesibles:**
- ✅ Dashboard
- ✅ Inventario
- ✅ Pedidos
- ✅ Finanzas
- ✅ Costos
- ✅ Clientes
- ✅ Empleados
- ✅ Punto de Venta (POS)
- ✅ Bodegas
- ✅ Calendario
- ✅ Rutas y Entregas

**Redirección después del login:** `/dashboard`

---

### 👨‍💼 Vendedor

**Acceso limitado (sin finanzas, costos ni dashboard)**

- **Email:** `vendedor@teleserp.local`
- **Contraseña:** `Vendedor#1234`
- **Rol:** Vendedor

**Módulos accesibles:**
- ❌ Dashboard (sin acceso)
- ✅ Inventario
- ✅ Pedidos
- ❌ Finanzas (sin acceso)
- ❌ Costos (sin acceso)
- ✅ Clientes
- ❌ Empleados (sin acceso)
- ✅ Punto de Venta (POS)
- ✅ Bodegas
- ✅ Calendario
- ✅ Rutas y Entregas

**Redirección después del login:** `/pedidos`

---

## ⚙️ Configuración Inicial

### 1. Crear roles y permisos

Ejecuta el seeder de roles y permisos:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

O ejecuta todos los seeders:

```bash
php artisan db:seed
```

### 2. Verificar que los usuarios existan

Si los usuarios no existen, se crearán automáticamente al ejecutar:

```bash
php artisan db:seed
```

Este comando creará:
- ✅ Roles y permisos
- ✅ Usuario Admin (Manager)
- ✅ Usuario Vendedor

---

## 🔒 Seguridad

⚠️ **IMPORTANTE:** Estas credenciales son solo para desarrollo y pruebas. 

**Antes de desplegar a producción:**
1. Cambia todas las contraseñas
2. Elimina o deshabilita estos usuarios de prueba
3. Usa contraseñas seguras y únicas
4. Considera implementar autenticación de dos factores (2FA)

---

## 📝 Notas

- Los roles y permisos se gestionan a través de Spatie Permission
- El sistema redirige automáticamente según los permisos del usuario
- Si un usuario intenta acceder a una ruta sin permisos, verá un error 403
- El menú lateral se actualiza automáticamente según los permisos del usuario

---

## 🆘 Problemas Comunes

### Error: "There is no role named `Manager`"

**Solución:** Ejecuta primero el seeder de roles:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### No puedo hacer login

**Verifica:**
1. Que el usuario exista en la base de datos
2. Que el usuario tenga un rol asignado
3. Que las credenciales sean correctas (case-sensitive)

### Usuario creado pero sin permisos

**Solución:** Asigna el rol manualmente:
```php
$user = User::where('email', 'admin@teleserp.local')->first();
$user->assignRole('Manager');
```

---

**Última actualización:** 2025

