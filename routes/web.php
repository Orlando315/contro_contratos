<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*--- RUTAS DE LOGIN ---*/
Route::get('/', function(){
  return view('login');
})->name('login.view');
Route::get('login', function(){
  return view('login');
});

/* --- Auth ---*/
Route::post('auth', 'LoginController@auth')->name('login.auth');
Route::match(['get', 'post'], '/logout', 'LoginController@logout')->name('login.logout');

/* --- Recuperar contraseña --- */
Route::get('password', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showresetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

/* --- Empresas --- */
Route::get('registro', 'EmpresasController@create')->name('empresas.create');
Route::post('registro', 'EmpresasController@store')->name('empresas.store');

/* --- Cronjob --- */
Route::get('cronjob/asistencias/create', 'EmpleadosController@cronjobAsistencias');

/* --- Solo usuarios autenticados --- */
Route::group(['middleware' => 'auth'], function () {

  /* --- Dashboard --- */
  Route::get('dashboard', 'LoginController@dashboard')->name('dashboard');

  /* --- Perfil --- */
  Route::get('/perfil', 'UsuariosController@perfil')->name('usuarios.perfil');
  Route::get('/perfil/edit', 'UsuariosController@editPerfil' )->name('usuarios.editPerfil');
  Route::patch('/perfil', 'UsuariosController@updatePerfil')->name('usuarios.updatePerfil');
  Route::patch('/perfil/password', 'UsuariosController@password')->name('perfil.password');

  /* --- Sueldos --- */
  Route::get('sueldos/{sueldo}/show', 'EmpleadosSueldosController@show')->name('sueldos.show');
  Route::patch('sueldos/{sueldo}/confirmar', 'EmpleadosSueldosController@recibido')->name('sueldos.confirmar');
  Route::get('sueldos/{sueldo}/download', 'EmpleadosSueldosController@download')->name('sueldos.download');

  /* --- Entregas ---*/
  Route::patch('inventarios/entregas/{entrega}', 'InventariosEntregasController@update')->name('entregas.update');

  /* --- Encuestas --- */
  Route::get('encuesta/{encuesta}', 'EncuestasController@showPublic')->name('encuesta.show');
  
  /* --- Respuestas --- */
  Route::post('encuesta/{encuesta}', 'EncuestasRespuestasController@store')->name('respuestas.store');

  /* --- Ayudas ---*/
  Route::get('help', 'AyudasController@list')->name('ayudas.list');

  /* --- Solo usuarios 1 (Super administrador) --- */
  Route::group(['middleware' => 'checkRole:1'], function(){

    /* --- Ayudas --- */
    Route::resource('ayudas', 'AyudasController');

    /* --- Encuentas --- */
    Route::resource('encuestas', 'EncuestasController');

    /* --- Preguntas --- */
    Route::get('preguntas/create/{encuesta}', 'EncuestasPreguntasController@create')->name('preguntas.create');
    Route::post('preguntas/create/{encuesta}', 'EncuestasPreguntasController@store')->name('preguntas.store');
    Route::resource('preguntas', 'EncuestasPreguntasController')
          ->only([
            'show',
            'edit',
            'update',
            'destroy',
          ]);

    /* --- Opciones --- */
    Route::get('opciones', 'PreguntasOpcionesController@index')->name('opciones.index');
    Route::get('opciones/create/{pregunta}', 'PreguntasOpcionesController@create')->name('opciones.create');
    Route::post('opciones/create/{pregunta}', 'PreguntasOpcionesController@store')->name('opciones.store');
    Route::get('opciones/{opcion}/edit', 'PreguntasOpcionesController@edit')->name('opciones.edit');
    Route::patch('opciones/{opcion}', 'PreguntasOpcionesController@update')->name('opciones.update');
    Route::delete('opciones/{opcion}', 'PreguntasOpcionesController@destroy')->name('opciones.destroy');

    /* --- Respuestas --- */
    Route::get('respuestas/{encuesta}/{usuario}', 'EncuestasRespuestasController@show')->name('respuestas.show');
    Route::delete('respuestas/{encuesta}/{usuario}', 'EncuestasRespuestasController@destroy')->name('respuestas.destroy');
  });

  /* --- Solo usuarios 2 (Empresa) --- */
  Route::group(['middleware' => 'checkRole:2'], function(){
      /* --- Empresas --- */
    Route::get('/empresa/edit', 'EmpresasController@edit')->name('empresas.edit');
    Route::patch('/perfil/empresas', 'EmpresasController@update')->name('empresas.update');

    /* --- Contratos --- */
    Route::resource('contratos', 'ContratosController')->except([
      'index',
      'show'
    ]);

    /* --- Documentos --- */    
    Route::get('documentos/contratos/{contrato}', 'DocumentosController@createContrato')->name('documentos.createContrato');
    Route::post('documentos/contratos/{contrato}', 'DocumentosController@storeContrato')->name('documentos.storeContrato');
  });

  /* --- Solo usuarios 2 (Empresa) y 3 (Administrador) --- */
  Route::group(['middleware' => 'checkRole:3'], function(){

    /* --- Contratos --- */
    Route::resource('contratos', 'ContratosController')->only([
      'index',
      'show'
    ]);
    Route::get('contratos/comida/{contrato}', 'ContratosController@comida')->name('contratos.comidas');
    Route::get('contratos/calendar/{contrato}', 'ContratosController@calendar')->name('contratos.calendar');
    Route::post('contratos/export/{contrato}', 'ContratosController@exportJornadas')->name('contratos.exportJornadas');

    /* --- Usuarios --- */
    Route::resource('usuarios', 'UsuariosController');
    Route::patch('usuarios/password/{usuario}', 'UsuariosController@password')->name('usuarios.password');

    /* --- Empleados --- */
    Route::patch('empleados/{empleado}/contrato', 'EmpleadosController@cambioContrato')->name('empleados.cambioContrato');
    Route::patch('empleados/{empleado}/toggle', 'EmpleadosController@toggleTipo')->name('empleados.toggleTipo');
    Route::post('empleados/contratos/{contrato}', 'EmpleadosController@getByContrato');
    Route::get('empleados/{empleado}/cambio', 'EmpleadosController@cambio')->name('empleados.cambio');
    Route::post('empleados/{empleado}/cambio', 'EmpleadosController@cambioStore')->name('empleados.cambioStore');
    Route::post('empleados/{empleado}/export', 'EmpleadosController@export')->name('empleados.export');
    Route::get('empleados/{contrato}/create', 'EmpleadosController@create')->name('empleados.create');
    Route::post('empleados/{contrato}/create', 'EmpleadosController@store')->name('empleados.store');
    Route::resource('empleados', 'EmpleadosController')->except([
      'create',
      'store'
    ]);

    /* --- Eventos --- */
    Route::get('empleados/eventos/export', 'EmpleadosEventosController@events')->name('eventos.events');
    Route::post('empleados/eventos/export', 'EmpleadosEventosController@exportEventsTotal')->name('eventos.export');
    Route::post('empleados/eventos/events', 'EmpleadosEventosController@getEvents')->name('eventos.getEvents');
    Route::get('empleados/eventos/', 'EmpleadosEventosController@index')->name('eventos.index');
    Route::post('empleados/eventos/{empleado}', 'EmpleadosEventosController@store')->name('eventos.store');
    Route::delete('empleados/eventos/{evento}', 'EmpleadosEventosController@destroy')->name('eventos.destroy');
    Route::patch('empleados/eventos/comida/{evento}', 'EmpleadosEventosController@toggleComida')->name('eventos.toggleComida');

    /* --- Documentos --- */
    Route::resource('documentos', 'DocumentosController')->except([
      'show',
      'create',
      'store'
    ]);
    Route::get('documentos/empleados/{empleado}', 'DocumentosController@createEmpleado')->name('documentos.createEmpleado');
    Route::post('documentos/empleados/{empleado}', 'DocumentosController@storeEmpleado')->name('documentos.storeEmpleado');
    Route::get('documentos/download/{documento}', 'DocumentosController@download')->name('documentos.download');

    /* --- Transportes --- */
    Route::resource('transportes', 'TransportesController')->except([
      'index',
      'show'
    ]);
    Route::post('transportes/{transporte}/add/', 'TransportesController@storeContratos')->name('transportes.storeContratos');
    Route::delete('transportes/{transporte}/delete/{contrato}', 'TransportesController@destroyContratos')->name('transportes.destroyContratos');

    /* --- Anticipos --- */
    Route::resource('anticipos', 'AnticiposController')->except([
      'create'
    ]);
    Route::get('anticipos/create/individual', 'AnticiposController@create')->name('anticipos.individual');
    Route::get('anticipos/create/masivo', 'AnticiposController@masivo')->name('anticipos.masivo');
    Route::post('anticipos/empleados/{contrato}', 'AnticiposController@getEmpleados');
    Route::post('anticipos/create/masivo', 'AnticiposController@storeMasivo')->name('anticipos.storeMasivo');

    /* --- Sueldos --- */
    Route::get('sueldos/{contrato}', 'EmpleadosSueldosController@index')->name('sueldos.index');
    Route::get('sueldos/{contrato}/create', 'EmpleadosSueldosController@create')->name('sueldos.create');
    Route::post('sueldos/{contrato}', 'EmpleadosSueldosController@store')->name('sueldos.store');

    /* --- Facturas --- */
    Route::resource('facturas', 'FacturasController');
    Route::get('facturas/{factura}/download/{adjunto}', 'FacturasController@download')->name('facturas.download');

    /* --- Reportes --- */
    Route::get('reportes/inventarios', 'ReportesController@inventariosIndex')->name('reportes.inventariosIndex');
    Route::post('reportes/inventarios', 'ReportesController@inventariosGet')->name('reportes.inventariosGet');
    Route::get('reportes/facturas', 'ReportesController@facturasIndex')->name('reportes.facturasIndex');
    Route::post('reportes/facturas', 'ReportesController@facturasGet')->name('reportes.facturasGet');
    Route::get('reportes/eventos', 'ReportesController@eventosIndex')->name('reportes.eventosIndex');
    Route::post('reportes/eventos', 'ReportesController@eventosGet')->name('reportes.eventosGet');
    Route::get('reportes/sueldos', 'ReportesController@sueldosIndex')->name('reportes.sueldosIndex');
    Route::post('reportes/sueldos', 'ReportesController@sueldosGet')->name('reportes.sueldosGet');
    Route::get('reportes/anticipos', 'ReportesController@anticiposIndex')->name('reportes.anticiposIndex');
    Route::post('reportes/anticipos', 'ReportesController@anticiposGet')->name('reportes.anticiposGet');
    Route::get('reportes/transportes', 'ReportesController@transportesIndex')->name('reportes.transportesIndex');
    Route::post('reportes/transportes', 'ReportesController@transportesGet')->name('reportes.transportesGet');
    Route::get('reportes/comidas', 'ReportesController@comidasIndex')->name('reportes.comidasIndex');
    Route::post('reportes/comidas', 'ReportesController@comidasGet')->name('reportes.comidasGet');
    Route::get('reportes/reemplazos', 'ReportesController@reemplazosIndex')->name('reportes.reemplazosIndex');
    Route::post('reportes/reemplazos', 'ReportesController@reemplazosGet')->name('reportes.reemplazosGet');
    Route::get('reportes/general', 'ReportesController@generalIndex')->name('reportes.generalIndex');
    Route::post('reportes/general', 'ReportesController@generalGet')->name('reportes.generalGet');
  });

  /* --- Solo usuarios 2 (Empresa), 3 (Administrador) y 4 (Supervisor) --- */
  Route::group(['middleware' => 'checkRole:4'], function(){
    /* --- Transportes --- */
    Route::resource('transportes', 'TransportesController')->only([
      'index',
      'show'
    ]);

    /* --- Transporte consumo --- */
    Route::resource('transportes/consumos', 'TransportesConsumosController')->except([
      'create',
      'store'
    ]);
    Route::get('transportes/consumos/create/{transporte}', 'TransportesConsumosController@create')->name('consumos.create');
    Route::post('transportes/consumos/{transporte}', 'TransportesConsumosController@store')->name('consumos.store');
    Route::get('transportes/consumos/download/{consumo}', 'TransportesConsumosController@download')->name('consumos.download');

    /* --- Inventario ---*/
    Route::resource('inventarios', 'InventariosController');
    Route::get('inventarios/download/{inventario}', 'InventariosController@download')->name('inventarios.download');
    /* --- Inventarios Entregas ---*/
    Route::get('inventarios/entregas/{inventario}', 'InventariosEntregasController@create')->name('entregas.create');
    Route::post('inventarios/entregas/{inventario}', 'InventariosEntregasController@store')->name('entregas.store');
    Route::delete('inventarios/entregas/{inventario}/{entrega}', 'InventariosEntregasController@destroy')->name('entregas.destroy');
    Route::get('inventarios/entregas', 'InventariosEntregasController@index')->name('entregas.index');
  });
});
