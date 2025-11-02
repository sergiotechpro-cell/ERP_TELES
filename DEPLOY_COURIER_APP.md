# 🚀 Guía de Deployment - App para Choferes

## 📋 Estado del Proyecto

✅ **App Completa y Funcional**
- Backend API: ✅ Lista
- Frontend Next.js: ✅ Lista
- Autenticación: ✅ Funcional
- Integración Google Maps: ✅ Funcional
- Estados de pago: ✅ Automatizados

## 🚢 Deployment en Vercel

### Opción 1: Deployment Automático desde GitHub

1. **Crear repo en GitHub**
```bash
cd C:\Users\User\Desktop\teleserp\courier-app
git init
git add .
git commit -m "Initial commit - Courier App"
git branch -M main
git remote add origin https://github.com/tu-usuario/courier-app.git
git push -u origin main
```

2. **Conectar a Vercel**
   - Ve a [vercel.com](https://vercel.com)
   - Click en "Add New Project"
   - Importa tu repo de GitHub
   - Vercel detectará automáticamente que es Next.js

3. **Configurar Variables de Entorno**
   En Vercel, ve a Settings > Environment Variables:
   
   ```
   NEXT_PUBLIC_API_URL=https://tu-api-laravel.com/api
   NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=tu_google_maps_api_key
   ```

4. **Deploy**
   - Click en "Deploy"
   - ¡Listo! Tendrás una URL como: `https://courier-app.vercel.app`

### Opción 2: Deployment Manual con CLI de Vercel

```bash
# Instalar Vercel CLI
npm install -g vercel

# Login
vercel login

# Deploy
cd courier-app
vercel

# Deploy a producción
vercel --prod
```

## 🗄️ Deployment del Backend

El backend Laravel debe estar deployado primero. Opciones:

### Opción A: Railway

1. Conecta tu repo a Railway
2. Configura variables de entorno (PostgreSQL, etc.)
3. Railway detectará Laravel automáticamente
4. Obtén la URL de tu API

### Opción B: Render

1. Conecta tu repo a Render
2. Crear PostgreSQL database
3. Configurar build y start commands:
   - Build: `composer install --optimize-autoloader --no-dev && php artisan key:generate && php artisan migrate --force`
   - Start: `php artisan serve --host=0.0.0.0 --port=$PORT`
4. Configurar variables de entorno
5. Deploy

### Opción C: Heroku

```bash
heroku create tu-api-name
heroku addons:create heroku-postgresql
git push heroku main
heroku run php artisan migrate
```

## 🔗 Conexión Backend-Frontend

Una vez tengas ambos deployados:

1. **Actualiza la variable de entorno en Vercel:**
   ```
   NEXT_PUBLIC_API_URL=https://tu-api.railway.app/api
   ```

2. **Actualiza CORS en Laravel** (si es necesario):
   En `config/cors.php`, agrega tu dominio de Vercel:
   ```php
   'allowed_origins' => [
       'https://courier-app.vercel.app',
       'https://tu-dominio.vercel.app'
   ],
   ```

3. **Re-deploy la app de Vercel** para que tome las nuevas variables

## 🧪 Testing Post-Deployment

1. Abre la URL de Vercel
2. Intenta login con credenciales de un chofer
3. Verifica que cargue los pedidos
4. Verifica que el mapa funcione (si tienes API Key)
5. Prueba iniciar y completar una entrega

## 📊 Monitoreo

### Vercel Analytics
- Automáticamente disponible en tu dashboard de Vercel
- Muestra visits, performance, errores

### Logs
```bash
# Ver logs de Vercel
vercel logs [URL]

# Ver logs de Railway
railway logs
```

## 🔐 Seguridad Post-Deployment

- ✅ HTTPS automático en Vercel
- ✅ Variables de entorno seguras
- ✅ CORS configurado
- ✅ Tokens en localStorage (considera migrar a httpOnly cookies en futuro)

## 🐛 Troubleshooting

### "API URL not configured"
- Verifica `NEXT_PUBLIC_API_URL` en variables de entorno de Vercel
- Re-deploy la app

### "CORS error"
- Verifica que tu backend permita requests desde tu dominio de Vercel
- Agrega el dominio a `allowed_origins` en Laravel

### "Google Maps not loading"
- Verifica `NEXT_PUBLIC_GOOGLE_MAPS_API_KEY`
- La app funciona sin API Key, solo no muestra el mapa

### "Authentication failed"
- Verifica que el backend esté funcionando
- Verifica que las credenciales del chofer existan en la BD

## 📝 Comandos Útiles

```bash
# Ver variables de entorno en Vercel
vercel env pull

# Deploy específico de producción
vercel --prod

# Rollback
vercel rollback

# Domains personalizado
vercel domains add tu-dominio.com
```

## ✅ Checklist de Deployment

- [ ] Backend deployado y funcionando
- [ ] Variables de entorno configuradas en Vercel
- [ ] CORS configurado en Laravel
- [ ] Google Maps API Key configurada (opcional)
- [ ] Pruebas de login exitosas
- [ ] Pruebas de carga de pedidos exitosas
- [ ] Pruebas de iniciar/completar entrega exitosas
- [ ] Dominio personalizado configurado (opcional)
- [ ] Monitoreo de logs configurado
- [ ] Backup de base de datos configurado

## 🎉 ¡Listo!

Tu app estará disponible en `https://tu-app.vercel.app`

Los choferes podrán:
1. Acceder desde su móvil
2. Login con sus credenciales
3. Ver sus pedidos asignados
4. Iniciar ruta
5. Completar entrega

## 📞 Soporte

Para problemas, revisa:
- Logs de Vercel
- Logs del backend
- Console del navegador
- Network tab para ver peticiones

