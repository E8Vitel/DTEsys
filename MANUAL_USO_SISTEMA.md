# Manual de Uso de DTEsys

## 1. Que es DTEsys

DTEsys es un sistema web para gestionar Documentos Tributarios Electronicos (DTE) dentro de un flujo controlado. Permite:

- iniciar sesion con usuarios y permisos;
- crear DTE;
- revisar el historial y estado de cada documento;
- firmar documentos;
- simular el envio al SII;
- simular el envio al receptor;
- registrar el acuse de recibo del receptor;
- anular documentos;
- descargar XML y PDF;
- consultar reportes;
- administrar usuarios, roles y permisos.

## 2. Que son los DTE y para que se usan

Los DTE son **Documentos Tributarios Electronicos**. En Chile se usan para registrar operaciones comerciales de manera digital y cumplir obligaciones tributarias ante el SII.

Sirven, entre otras cosas, para:

- respaldar ventas de productos o servicios;
- documentar traslados de mercaderia;
- dejar constancia de cargos o abonos;
- enviar informacion tributaria de forma estructurada;
- mantener trazabilidad del documento desde su emision hasta su aceptacion, rechazo o anulacion.

En este sistema se trabajan los siguientes tipos:

- **33 - Factura Electronica**: documenta ventas afectas a IVA entre contribuyentes.
- **39 - Boleta Electronica**: documenta ventas o servicios al consumidor final.
- **52 - Guia de Despacho Electronica**: respalda traslado o entrega de bienes.
- **56 - Nota de Debito**: aumenta o corrige montos de un documento anterior.
- **61 - Nota de Credito**: rebaja o corrige montos de un documento anterior.

## 3. Importante sobre este proyecto

Este sistema implementa un flujo **simulado** para varias etapas operativas. Eso significa que:

- la firma es una firma digital simulada;
- el envio al SII es una validacion simulada;
- el envio al receptor se realiza como simulacion por correo;
- el timbre electronico generado por el sistema es simulado.

Por lo tanto, el proyecto es util para demostracion, aprendizaje, pruebas internas o desarrollo de flujo, pero no debe asumirse como integracion productiva real con el SII.

## 4. Tecnologias principales

El sistema esta construido con:

- **Laravel** en backend;
- **React + Inertia** en frontend;
- **Spatie Permission** para roles y permisos;
- **DomPDF** para generacion de PDF;
- exportacion CSV para reportes tipo Excel.

## 5. Como ingresar y usar el sistema

### 5.1 Acceso

Al entrar a la raiz del sistema:

- si el usuario ya esta autenticado, es redirigido al `dashboard`;
- si no ha iniciado sesion, es redirigido al login.

### 5.2 Menu principal

Segun los permisos del usuario, el menu puede mostrar:

- **Dashboard**
- **DTEs**
- **Usuarios**
- **Perfil**
- **Cerrar sesion**

## 6. Que hace cada modulo

### 6.1 Dashboard

Es la vista principal del sistema. Muestra:

- cantidad total de DTE emitidos;
- monto total acumulado;
- cantidad de documentos firmados;
- cantidad de usuarios visibles;
- lista de los ultimos 5 DTE;
- acceso rapido para crear DTE;
- acceso a reporte general si el usuario tiene permiso.

### 6.2 Modulo DTEs

Es el centro operativo del sistema.

Desde aqui se puede:

- consultar documentos existentes;
- filtrar por folio, RUT, razon social, tipo y estado;
- crear un nuevo DTE;
- abrir el detalle de un documento;
- descargar XML y PDF;
- continuar el flujo del documento segun su estado.

### 6.3 Crear DTE

En el formulario de emision se ingresan:

- tipo de DTE;
- datos del receptor;
- region y comuna;
- direccion;
- detalle de items;
- montos calculados automaticamente;
- datos de transporte si corresponde a una Guia de Despacho.

El sistema calcula automaticamente:

- monto neto;
- IVA del 19%;
- monto total.

Al guardar:

- se crea el documento;
- se generan sus detalles;
- se asigna un folio interno;
- se genera un timbre electronico simulado;
- se registra el estado inicial **Borrador** en el historial.

### 6.4 Reglas especiales para Guia de Despacho

Si el tipo de DTE es **52 - Guia de Despacho Electronica**, el sistema maneja campos de transporte:

- direccion de destino;
- patente;
- RUT del chofer;
- nombre del chofer.

Desde la fecha **1 de mayo de 2026**, esos campos pasan a ser obligatorios para ese tipo de documento dentro de este sistema.

### 6.5 Detalle de un DTE

La vista de detalle permite revisar:

- datos del emisor;
- datos del receptor;
- ubicacion del receptor;
- datos de transporte si aplica;
- montos;
- estado actual;
- estado SII;
- track ID;
- respuesta SII;
- acuse del receptor;
- detalle de items;
- firma aplicada;
- timbre electronico;
- historial de estados.

### 6.6 Firma de DTE

Si el documento esta en estado **Borrador** y el usuario tiene permiso, puede firmarlo de dos formas:

- firma por texto;
- firma dibujada en canvas.

Al firmar:

- se guarda la firma asociada al documento;
- se actualiza el historial;
- el estado cambia a **Firmada**.

### 6.7 Envio al SII

Solo se puede enviar al SII cuando el DTE:

- ya fue firmado; y
- esta en estado **Firmada** o **Rechazada SII**.

Al enviarlo:

- se genera el XML del documento;
- se asigna un `track_id`;
- se marca el envio;
- se ejecuta una validacion simulada;
- el documento puede quedar en **Aceptada SII** o **Rechazada SII**.

La validacion simulada revisa, entre otras cosas:

- formato basico de RUT emisor y receptor;
- cuadre entre neto, IVA y total;
- datos obligatorios de Guia de Despacho cuando aplican.

### 6.8 Envio al receptor

Solo se puede realizar cuando el DTE esta en estado **Aceptada SII**.

En esta etapa el sistema:

- usa o genera el XML;
- genera el PDF;
- guarda el PDF en almacenamiento;
- simula el envio al receptor por correo;
- cambia el estado a **Enviada Receptor**.

### 6.9 Acuse del receptor

Despues del envio al receptor se puede registrar una respuesta:

- **Acuse Aceptado**
- **Acuse Rechazado**
- **Acuse con Reparos**

Si la respuesta es rechazo o reparo, el sistema exige comentario.

### 6.10 Anulacion

Si el usuario tiene permiso, puede anular un DTE ingresando un comentario obligatorio.

Al hacerlo:

- el documento queda en estado **Anulada**;
- se registra el evento en el historial.

### 6.11 Descargas

Cada DTE permite:

- descargar su **XML**;
- descargar su **PDF**.

### 6.12 Reportes

El sistema incluye reportes para usuarios autorizados:

- **Reporte general PDF**
- **Exportacion CSV** compatible con analisis en Excel

Los reportes pueden filtrar por:

- estado;
- rango de fechas.

### 6.13 Usuarios

El modulo de usuarios esta disponible para quienes tienen permiso de gestion.

Permite:

- ver usuarios;
- revisar roles;
- revisar permisos vigentes;
- asignar roles;
- quitar roles;
- asignar permisos directos;
- quitar permisos directos.

## 7. Roles y permisos

El sistema define estos roles base:

- **admin**
- **contador**
- **emisor**

Permisos registrados:

- `ver-dtes`
- `crear-dte`
- `editar-dte`
- `eliminar-dte`
- `firmar-dte`
- `enviar-dte`
- `anular-dte`
- `ver-reportes`
- `gestionar-usuarios`

Distribucion base:

- **admin**: acceso total.
- **contador**: ver, crear, firmar, enviar, anular y ver reportes.
- **emisor**: ver, crear, firmar y enviar.

## 8. Flujo completo del documento

El flujo funcional del DTE en este proyecto es:

1. Crear documento.
2. Queda en **Borrador**.
3. Firmar documento.
4. Cambia a **Firmada**.
5. Enviar al SII.
6. Resultado: **Aceptada SII** o **Rechazada SII**.
7. Si fue aceptada, enviar al receptor.
8. Cambia a **Enviada Receptor**.
9. Registrar acuse: **Aceptado**, **Rechazado** o **Con reparos**.
10. Opcionalmente, anular si corresponde.

## 9. Estados que maneja el sistema

Estados considerados en la aplicacion:

- **Borrador**
- **Firmada**
- **Enviada SII**
- **Aceptada SII**
- **Rechazada SII**
- **Enviada Receptor**
- **Acuse Aceptado**
- **Acuse Rechazado**
- **Acuse con Reparos**
- **Anulada**

Cada cambio queda registrado en el historial del documento con:

- estado anterior;
- estado nuevo;
- usuario responsable;
- comentario;
- fecha y hora.

## 10. Datos de ejemplo del sistema

Los seeders del proyecto crean usuarios de prueba:

- `admin@dtesys.cl`
- `contador@dtesys.cl`
- `emisor@dtesys.cl`

Contrasena de ejemplo:

- `password123`

Tambien se cargan:

- tipos de DTE;
- roles y permisos;
- documentos de ejemplo.

## 11. Como levantar el sistema localmente

Pasos tipicos en entorno local:

1. Configurar `.env`.
2. Instalar dependencias PHP con Composer.
3. Instalar dependencias JS con npm.
4. Ejecutar migraciones y seeders.
5. Levantar el servidor Laravel.
6. Levantar Vite para el frontend.

Comandos habituales:

```bash
php artisan migrate --seed
php artisan serve
npm run dev
```

## 12. Resumen practico

DTEsys sirve para administrar el ciclo de vida de documentos tributarios electronicos dentro de una plataforma con control de acceso. Su objetivo principal es permitir la emision, seguimiento, firma, validacion simulada, envio y trazabilidad de los DTE, junto con gestion de usuarios y reportes.

Si se quiere evolucionar el proyecto a un entorno real, las areas que normalmente habria que reemplazar o ampliar son:

- firma electronica real;
- integracion real con SII;
- envio real al receptor;
- folios autorizados reales;
- validaciones tributarias formales;
- manejo productivo de certificados y seguridad.
