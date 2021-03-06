<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Usuario;
use App\Empleado;
use App\Contrato;
use App\EmpleadosBanco;
use App\EmpleadosEvento;
use App\EmpleadosContrato;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Carbon\Carbon;

class EmpleadosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Contrato $contrato)
    {
      return view('empleados.create', ['contrato' => $contrato->id]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Contrato $contrato)
    {
      $this->validate($request, [
        'nombres' => 'required|string',
        'apellidos' => 'required|string',
        'sexo' => 'required',
        'fecha_nacimiento' => 'required|date_format:d-m-Y',
        'rut' => 'required|regex:/^(\d{4,9}-[\dk])$/|unique:users,rut',
        'direccion' => 'required|string',
        'telefono' => 'nullable|string',
        'email' => 'nullable|email|unique:users,email',
        'talla_camisa' => 'nullable|string',
        'talla_zapato' => 'nullable|numeric',
        'talla_pantalon' => 'nullable|string',
        'nombre' => 'required|string',
        'tipo_cuenta' => 'required',
        'cuenta' => 'required|string',
        'sueldo' => 'required|numeric',
        'inicio' => 'required|date_format:d-m-Y',
        'inicio_jornada' => 'required|date_format:d-m-Y',
        'fin' => 'nullable|date_format:d-m-Y',
        'jornada' => 'nullable',
      ]);

      if(!$request->jornada){
        $request->merge(['jornada' => Auth::user()->empresa->configuracion->jornada]);
      }

      $empleado = new Empleado($request->all());
      $empleado->empresa_id = Auth::user()->empresa->id;

      if($emplado = $contrato->empleados()->save($empleado)){

        $usuario = new Usuario($request->all());
        $usuario->usuario  = $request->rut;
        $usuario->password = bcrypt($request->rut);
        $usuario->tipo = 5; // Tipo 5 = Empleado
        $usuario->empresa_id = Auth::user()->empresa->id;
        $empleado->usuario()->save($usuario);
        
        $empleado->contratos()->create($request->all());
        $empleado->banco()->create($request->all());

        return redirect('empleados/' . $emplado->id)->with([
          'flash_message' => 'Empleado registrado exitosamente.',
          'flash_class' => 'alert-success'
          ]);
      }else{
        return redirect('empleados/create')->with([
          'flash_message' => 'Ha ocurrido un error.',
          'flash_class' => 'alert-danger',
          'flash_important' => true
          ]);
      }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function show(Empleado $empleado)
    {
      $empleados = Empleado::select('id')
                            ->with('usuario:empleado_id,nombres,apellidos,rut')
                            ->where('contrato_id', $empleado->contrato_id)
                            ->get();

      $contratos = Contrato::all();

      return view('empleados.show', ['empleado' => $empleado, 'empleados' => $empleados, 'contratos' => $contratos]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function edit(Empleado $empleado)
    {
      return view('empleados.edit', ['empleado' => $empleado]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Empleado $empleado)
    {
      $this->validate($request, [
        'nombres' => 'required|string',
        'apellidos' => 'required|string',
        'sexo' => 'required',
        'fecha_nacimiento' => 'required|date_format:d-m-Y',
        'rut' => 'required|regex:/^(\d{4,9}-[\dk])$/|unique:users,rut,' . $empleado->usuario->id . ',id',
        'direccion' => 'required|string',
        'telefono' => 'nullable|string',
        'email' => 'nullable|email|unique:users,email,' . $empleado->usuario->id . ',id',
        'talla_camisa' => 'nullable|string',
        'talla_zapato' => 'nullable|numeric',
        'talla_pantalon' => 'nullable|string',
        'nombre' => 'required|string',
        'tipo_cuenta' => 'required',
        'cuenta' => 'required|string',
        'sueldo' => 'required|numeric',
        'inicio' => 'required|date_format:d-m-Y',
        'fin' => 'nullable|date_format:d-m-Y',
        'inicio_jornada' => 'required|date_format:d-m-Y',
        'jornada' => 'nullable',
        'dias_laborables' => 'nullable',
        'dias_descanso' => 'nullable'
      ]);

      if($empleado->despidoORenuncia() && $request->fin){
        $evento = $empleado->eventos()->where('tipo', 6)->orWhere('tipo', 7)->first();
        $eventoDate = new Carbon($evento->inicio);
        $fin = new Carbon($request->fin);
        if($eventoDate->lessThan($fin)){
          return redirect('empleados/'. $empleado->id .'/edit')
                    ->withErrors('La fecha de fin del contrato no puede ser mayor a la fecha de Renuncia/Despido: '. $evento->inicio)
                    ->withInput();  
        }
      }

      if(!$request->jornada){
        $request->merge(['jornada' => Auth::user()->configuracion->jornada]);
      }

      $empleado->fill($request->all());
      $empleado->contratos->last()->fill($request->all());
      $empleado->banco->fill($request->all());
      $empleado->usuario->fill($request->all());
      $empleado->usuario->usuario = $request->rut;

      if($empleado->push()){
        return redirect('empleados/' . $empleado->id)->with([
          'flash_message' => 'Empleado modificado exitosamente.',
          'flash_class' => 'alert-success'
          ]);
      }else{
        return redirect('empleados/'. $empleado->id .'/edit')->with([
          'flash_message' => 'Ha ocurrido un error.',
          'flash_class' => 'alert-danger',
          'flash_important' => true
          ]);
      }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function destroy(Empleado $empleado)
    {
      if($empleado->delete()){

        Storage::deleteDirectory('Empleado' . $empleado->id);

        return redirect('dashboard')->with([
          'flash_class'   => 'alert-success',
          'flash_message' => 'Empleado eliminado exitosamente.'
        ]);
      }else{
        return redirect('dashboard')->with([
          'flash_class'     => 'alert-danger',
          'flash_message'   => 'Ha ocurrido un error.',
          'flash_important' => true
        ]);
      }
    }

    public function cambio($empleado)
    {
      return view('empleados.cambio', ['empleado'=>$empleado]);
    }

    public function cambioStore(Request $request, Empleado $empleado)
    {
      $this->validate($request, [
        'inicio' => 'required|date_format:d-m-Y',
        'fin' => 'nullable|date_format:d-m-Y',
        'jornada' => 'nullable',
      ]);

      if($empleado->despidoORenuncia()){
        $evento = $empleado->eventos()->where('tipo', 6)->orWhere('tipo', 7)->first();
        $eventoDate = new Carbon($evento->inicio);

        $inicio = new Carbon($request->inicio);
        if($eventoDate->lessThanOrEqualTo($inicio)){
          return redirect('empleados/'. $empleado->id .'/cambio')
                    ->withErrors('La fecha de inicio del contrato no puede ser mayor o igual a la fecha de Renuncia/Despido: '. $evento->inicio)
                    ->withInput();  
        }

        if($request->fin){
          $fin = new Carbon($request->fin);  
          if($eventoDate->lessThan($fin)){
            return redirect('empleados/'. $empleado->id .'/cambio')
                      ->withErrors('La fecha de fin del contrato no puede ser mayor a la fecha de Renuncia/Despido: '. $evento->inicio)
                      ->withInput();  
          }
        }else{
          $request->merge(['fin' => $evento->inicio]);
        }
      }
      
      $request->merge(['inicio_jornada' => $request->inicio]);

      $lastContrato = $empleado->contratos->last();

      if(!$request->jornada){
        $request->merge(['jornada' => Auth::user()->configuracion->jornada]);
      }
      $request->merge(['sueldo' => $lastContrato->sueldo]);

      if($empleado->contratos()->create($request->all())){
        $lastContrato->fin = $request->inicio;
        $lastContrato->save();

        return redirect('empleados/' . $empleado->id)->with([
          'flash_message' => 'Cambio de jornada exitoso.',
          'flash_class' => 'alert-success'
          ]);
      }else{
        return redirect('empleados/' . $empleado->id . '/cambio')->with([
          'flash_message' => 'Ha ocurrido un error.',
          'flash_class' => 'alert-danger',
          'flash_important' => true
          ]);
      }
    }
    
    public function export(Request $request, Empleado $empleado)
    {
      $this->exportExcel($empleado->getDataAsArray($request->inicio, $request->fin), 'Empleado' . $empleado->id);
    }

    protected function exportExcel($data, $nombre)
    {
      $writer = WriterFactory::create(Type::XLSX);
      $writer->openToBrowser("{$nombre}.xlsx");
      $writer->addRows($data);

      $writer->close(); 
    }

    public function getByContrato(Contrato $contrato)
    {
      return $contrato->empleados()->select(['id'])->with(['usuario:id,empleado_id,rut,nombres,apellidos'])->get()->toArray();
    }

    public function toggleTipo(Empleado $empleado)
    {
      $empleado->usuario->tipo = $empleado->usuario->tipo == 4 ? 5 : 4;

      if($empleado->push()){
        return redirect('empleados/' . $empleado->id)->with([
          'flash_message' => 'Empleado actualizado exitosamente.',
          'flash_class' => 'alert-success'
          ]);
      }else{
        return redirect('empleados/' . $empleado->id . '/cambio')->with([
          'flash_message' => 'Ha ocurrido un error.',
          'flash_class' => 'alert-danger',
          'flash_important' => true
          ]);
      }
    }

    public function cambioContrato(Request $request, Empleado $empleado){
      $contrato = Contrato::findOrFail($request->contrato);
      $empleado->contrato_id = $contrato->id;

      if($empleado->save()){
        return redirect('empleados/' . $empleado->id)->with([
          'flash_message' => 'Empleado actualizado exitosamente.',
          'flash_class' => 'alert-success'
          ]);
      }else{
        return redirect('empleados/' . $empleado->id)->with([
          'flash_message' => 'Ha ocurrido un error.',
          'flash_class' => 'alert-danger',
          'flash_important' => true
          ]);
      }

    }

    public function cronjobAsistencias(){
      $empleados = Empleado::withoutGlobalScope(EmpresaScope::class)->get();
      $today = date('Y-m-d');

      foreach($empleados as $empleado){
        if($empleado->isWorkDay()){
          $eventosExists = $empleado->eventsToday()->exists();          
          
          $empleado->eventos()->firstOrCreate([
            'inicio' => $today,
            'tipo' =>  1,
            'jornada' => $empleado->contratos->last()->jornada
          ],[
            'comida' => !$eventosExists,
            'pago' => !$eventosExists
          ]);
        }
      }
    }
}
