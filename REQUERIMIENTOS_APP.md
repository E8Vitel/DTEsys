# Requerimientos de la App

## Requerimientos funcionales

1. El sistema debe permitir que los usuarios inicien sesion y accedan solo a los modulos habilitados segun su rol y permisos.
2. El sistema debe permitir crear DTE registrando tipo de documento, datos del receptor, detalle de items y montos calculados.
3. El sistema debe permitir avanzar el flujo de un DTE, incluyendo firma, validacion simulada ante SII y envio simulado al receptor.
4. El sistema debe permitir consultar el historial y estado de cada DTE, ademas de descargar su XML y PDF.
5. El sistema debe permitir filtrar y buscar DTE por folio, RUT, razon social, tipo de documento y estado.
6. El sistema debe permitir registrar respuestas del receptor, incluyendo aceptacion, rechazo o reparos, con comentario obligatorio cuando corresponda.
7. El sistema debe permitir anular DTE mediante el registro de un motivo o comentario obligatorio.
8. El sistema debe permitir administrar usuarios, roles y permisos para controlar el acceso a las funcionalidades del sistema.
9. El sistema debe permitir generar reportes de documentos emitidos para apoyar el control y seguimiento operativo.
10. El sistema debe permitir manejar reglas especiales para ciertos tipos de DTE, como los datos de transporte en la Guia de Despacho.

## Requerimientos no funcionales

1. La aplicacion debe mantener trazabilidad de las acciones relevantes sobre cada DTE para facilitar auditoria y seguimiento.
2. La interfaz debe ser clara y facil de usar para personal administrativo, permitiendo operar los modulos principales sin capacitacion avanzada.
3. El sistema debe responder en tiempos adecuados en las operaciones comunes, como listar documentos, abrir detalles y generar archivos descargables.
4. La aplicacion debe proteger la informacion mediante autenticacion, control de acceso por permisos y validacion de datos ingresados.
5. El sistema debe mantener consistencia en los datos registrados, evitando estados invalidos o incompletos dentro del flujo de los DTE.
6. La solucion debe ser mantenible, con una estructura que permita agregar nuevos tipos de documento o ajustar reglas de negocio sin rehacer toda la aplicacion.
