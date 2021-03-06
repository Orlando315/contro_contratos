@extends( 'layouts.app' )
@section( 'title', 'Inventarios - '.config( 'app.name' ) )
@section( 'header','Inventarios' )
@section( 'breadcrumb' )
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-home" aria-hidden="true"></i> Inicio</a></li>
    <li><a href="{{ route('inventarios.index') }}">Inventarios</a></li>
    <li class="active">Agregar</li>
  </ol>
@endsection
@section('content')
  <!-- Formulario -->
  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      <form class="" action="{{ route('inventarios.store') }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}

        <h4>Agregar inventario</h4>
        
        @if(Auth::user()->tipo <= 3)
          <div class="form-group {{ $errors->has('contrato_id') ? 'has-error' : '' }}">
            <label class="control-label" for="contrato_id">Contrato: *</label>
            <select id="contrato_id" class="form-control" name="contrato_id" required>
              <option value="">Seleccione...</option>
              @foreach($contratos as $contrato)
                <option value="{{ $contrato->id }}" {{ old('contrato_id') == $contrato->id ? 'selected':'' }}>{{ $contrato->nombre }}</option>
              @endforeach
            </select>
          </div>
        @else
          <input type="hidden" name="contrato_id" value="{{ Auth::user()->empleado->contrato_id }}">
        @endif


        <div class="form-group {{ $errors->has('tipo') ? 'has-error' : '' }}">
          <label class="control-label" class="form-control" for="tipo">Tipo: *</label>
          <select id="tipo" class="form-control" name="tipo" required>
            <option value="">Seleccione...</option>
            @if(Auth::user()->tipo <= 3)
              <option value="1" {{ old('tipo') == '1' ? 'selected' : '' }}>Insumo</option>
              <option value="2" {{ old('tipo') == '2' ? 'selected' : '' }}>EPP</option>
            @endif
            <option value="3" {{ old('tipo') == '3' ? 'selected' : '' }}>Otro</option>
          </select>
        </div>

        <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
          <label class="control-label" for="nombre">Nombre: *</label>
          <input id="nombre" class="form-control" type="text" name="nombre" maxlength="50" value="{{ old('nombre') ? old('nombre') : '' }}" placeholder="Nombre" required>
        </div>

        <div class="form-group {{ $errors->has('valor') ? 'has-error' : '' }}">
          <label class="control-label" for="valor">Valor: *</label>
          <input id="valor" class="form-control" type="number" step="1" min="1" maxlength="999999999999999" name="valor" value="{{ old('valor') ? old('valor') : '' }}" placeholder="Valor" required>
        </div>

        <div class="form-group {{ $errors->has('fecha') ? 'has-error' : '' }}">
          <label class="control-label" for="fecha">Fecha: *</label>
          <input id="fecha" class="form-control" type="text" name="fecha" value="{{ old('fecha') ? old('fecha') : '' }}" placeholder="dd-mm-yyyy" required>
        </div>

        <div class="form-group {{ $errors->has('cantidad') ? 'has-error' : '' }}">
          <label class="control-label" for="cantidad">Cantidad: *</label>
          <input id="cantidad" class="form-control" type="number" step="1" min="1" maxlength="999999" name="cantidad" value="{{ old('cantidad') ? old('cantidad') : '' }}" placeholder="Cantidad" required>
        </div>

        <div class="form-group {{ $errors->has('adjunto') ? 'has-error' : '' }}">
          <label class="control-label" for="adjunto">Adjunto: </label>
          <input id="adjunto" type="file" name="adjunto" accept="image/jpeg,image/png,application/pdf,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
        </div>

        @if (count($errors) > 0)
        <div class="alert alert-danger alert-important">
          <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>  
        </div>
        @endif

        <div class="form-group text-right">
          <a class="btn btn-flat btn-default" href="{{ route('inventarios.index') }}"><i class="fa fa-reply"></i> Atras</a>
          <button class="btn btn-flat btn-primary" type="submit"><i class="fa fa-send"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('scripts')
<script type="text/javascript">
  $(document).ready( function(){
    $('#fecha').datepicker({
      format: 'dd-mm-yyyy',
      language: 'es',
      keyboardNavigation: false,
      autoclose: true
    });
  });
</script>
@endsection
