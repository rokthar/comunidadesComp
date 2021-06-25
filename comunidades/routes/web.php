<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/* USUARIOS */
$router->post('/usuario/registro', 'UsuarioController@RegistrarUsuario');
$router->post('/usuario/login', 'UsuarioController@login');

/* ESTUDIANTES */
$router->post('/estudiante/registro/{external_id}', 'UsuarioController@RegistrarEstudiante');
$router->get('/estudiante/perfil/{external_id}', 'UsuarioController@datosEstudiante');

/* DOCENTE */
$router->post('/docente/registro/{external_id}', 'UsuarioController@RegistrarDocente');
$router->get('/docente/perfil/{external_id}', 'UsuarioController@datosDocente');
$router->get('/docente/lista', 'UsuarioController@listarDocentes');

// COMUNIDAD
$router->post('/comunidad/registro/{external_docente}', 'ComunidadController@RegistrarComuidad');
$router->get('/comunidad/historial/{external_comunidad}', 'ComunidadController@historialComunidad');
$router->post('/comunidad/editar/{external_comunidad}', 'ComunidadController@EditarComunidad');


$router->post('/decano/activar/{external_comunidad}', 'ComunidadController@ActivarComunidad');
$router->post('/secretaria/activar/{external_comunidad}', 'ComunidadController@RevisionInformacion');
$router->post('/gestor/activar/{external_comunidad}', 'ComunidadController@RevisionGestor');

// RECHAZOS
$router->post('/comunidad/rechazar/{external_comunidad}', 'ComunidadController@RechazarComunidad');
$router->post('/actividades/rechazar/{external_actividades}', 'ActividadController@RechazarPlanificacion');
$router->post('/postulacion/rechazar/{external_postulacion}', 'PostulacionController@RechazarPostulacion');
$router->post('/vinculacion/rechazar/{external_vinculacion}', 'VinculacionController@RechazarVinculacion');

// FIN DE LOS RECHAZOS
$router->get('/comunidad/listar/comunidadesactivadas', 'ComunidadController@ListarComunidadesActivadas');
$router->get('/secretaria/listar/comunidades', 'ComunidadController@ListarComunidadesSecretaria');
$router->get('/gestor/listar/comunidades', 'ComunidadController@ListarComunidadesGestor');
$router->get('/decano/listar/comundades', 'ComunidadController@ListarComunidadesDecano');
$router->get('/tutor/buscar/comunidad/{external_docente}', 'ComunidadController@BuscarComunidad');
$router->get('/vinculacion/listar/comunidades/{external_comunidad}', 'ComunidadController@ListarComunidadesVinculacion');
$router->get('/miembro/buscar-comunidad/{external_estudiante}', 'ComunidadController@BuscarComunidadByMiembro');


//ACTIVIDADES
$router->post('/comunidad/planficaractividades/{external_docente}', 'ActividadController@PlanificarActividades');
$router->get('/comunidad/actividadesespera', 'ActividadController@ListarPlanificacionEspera');
$router->get('/comunidad/actividadesactivadas', 'ActividadController@ListarPlanificacionActivada');
$router->post('/comunidad/activaractividad/{external_actividades}', 'ActividadController@ActivarPlanificacion');
$router->post('/comunidad/terminaractividad/{external_comunidad}', 'ActividadController@TermianrPlanificacion');

//DETALLE ACTIVIDADES
$router->post('/comunidad/detalleactividad/{external_actividades}', 'ActividadController@RegistrarDetalleActividad');
$router->get('/comunidad/listar/actividades/{external_comunidad}', 'ActividadController@ListarPlanificacionByComunidad');
$router->get('/comunidad/generar/resultados/{external_comunidad}', 'ActividadController@ListarPlanificacionResultados');


//POSTULACION
$router->post('/estudiante/postulacion/{external_estudiante}/{external_comunidad}', 'PostulacionController@RegistrarPostulacion');
$router->post('/estudiante/detallepostulacion/{external_postulacion}', 'PostulacionController@RegistrarDetallePostulacion');
$router->post('/tutor/activarpostulacion/{external_postulacion}', 'PostulacionController@ActivarPostulacion');
$router->get('/estudiante/listarpostulacionespera', 'PostulacionController@listarPostulacionesEspera');
$router->get('/comunidad/listarpostulacionespera/{external_comunidad}', 'PostulacionController@listarPostulacionesEsperaByComunidad');
$router->get('/estudiante/listarpostulacionaceptadas', 'PostulacionController@listarPostulacionesAceptadas');
$router->get('/estudiante/buscarpostulacion/{external_estudiante}', 'PostulacionController@buscarPostulacion');
$router->post('/estudiante/cancelar-postulacion/{external_estudiante}', 'PostulacionController@CancelarPostulacion');



//MIEMBROS
$router->post('/miembros/registrar/{external_postulacion}', 'MiembroController@AñadirMiembro');
$router->get('/miembros/listar/{id_comunidad}', 'MiembroController@listarMiembrosComunidad');


//VINCULACION
$router->post('/comunidad/vinculacion/{ext_comunidad}/{ext_comunidad_solic}', 'VinculacionController@RegistrarVinculacion');
$router->post('/comunidad/activarvinculacion/{external_vinculacion}', 'VinculacionController@AceptarVinculacion');
$router->get('/comunidad/listarsolicitud/vinculacion/{external_comunidad}', 'VinculacionController@ListarVinculacionComunidad');

$router->post('/comunidad/subirimagen/{external_comunidad}', 'ComunidadController@subirImagenComunidad');


//RESULTADOS
$router->post('/comunidad/resultados/{external_det_actividad}', 'ResultadoController@registrarResultado');
$router->post('/comunidad/subirimagen/resultado/{external_resultado}', 'ResultadoController@subirImagenResultado');
$router->get('/comunidad/listar/resultados', 'ResultadoController@listarResultados');
$router->get('/comunidad/listar/resultadoscomunidad/{external_comunidad}', 'ResultadoController@listarResultadosByComunidad');
$router->get('/ver/resultado/{external_resultado}', 'ResultadoController@listarResultado');
$router->get('/comunidad/presentar/resultado/miembros/{external_estudiante}', 'ResultadoController@listarResultadosByEstudiante');

$router->post('/utilidad/emviar-mail', 'MailController@mailAdjunto');

// configuraciones
$router->post('/configuracion/mail', 'ConfiguracionController@editarMail');
$router->post('/configuracion/clave', 'ConfiguracionController@editarClave');

$router->get('/configuracion/ver', 'ConfiguracionController@listarConf');

$router->get('/comunidad/buscar-comunidad/{external_comunidad}', 'ComunidadController@BuscarComunidadExternal');

// EDITAR
$router->post('/docente/editar/{external_docente}', 'UsuarioController@EditarDocente');
$router->post('/estudiante/editar/{external_estudiante}', 'UsuarioController@EditarEstudiante');
$router->post('/docente/editar-clave/{external_docente}', 'UsuarioController@EditarDocenteClave');
$router->post('/estudiante/editar-clave/{external_estudiante}', 'UsuarioController@EditarEstudianteClave');

$router->get('/docente/listar-docentes', 'UsuarioController@listarDocentesConf');
$router->get('/estudiante/listar-estudiantes', 'UsuarioController@listarEstudiantesConf');
$router->post('/usuarios/activar/{external_usuario}', 'UsuarioController@ActivarUsuario');
$router->post('/usuarios/desactivar/{external_usuario}', 'UsuarioController@DesactivarUsuario');
$router->post('/usuarios/recuperar-clave', 'UsuarioController@recuperarClave');
