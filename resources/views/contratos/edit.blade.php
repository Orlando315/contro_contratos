@extends( 'layouts.app' )
@section( 'title', 'Editar - '.config( 'app.name' ) )
@section( 'header','Editar' )
@section( 'breadcrumb' )
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-home" aria-hidden="true"></i> Inicio</a></li>
    <li><a href="{{ route('contratos.index') }}">Contratos</a></li>
    <li class="active">Editar</li>
  </ol>
@endsection
@section('content')
  <!-- Formulario -->
  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      <form class="" action="{{ route('contratos.update', ['id' => $contrato->id]) }}" method="POST">

        {{ method_field('PATCH') }}
        {{ csrf_field() }}

        <h4>Editar contrato</h4>

        <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
          <label class="control-label" for="nombre">Nombre: *</label>
          <input id="nombre" class="form-control" type="text" name="nombre" maxlength="50" value="{{ old('nombre') ? old('nombre') : $contrato->nombre }}" placeholder="Nombre" required>
        </div>

        <div class="form-group {{ $errors->has('inicio') ? 'has-error' : '' }}">
          <label class="control-label" for="inicio">Inicio:</label>
          <input id="inicio" class="form-control" type="text" name="inicio" value="{{ old('inicio') ? old('inicio') : $contrato->inicio }}" placeholder="dd-mm-yyyy">
        </div>

        <div class="form-group {{ $errors->has('fin') ? 'has-error' : '' }}">
          <label class="control-label" for="fin">Fin:</label>
          <input id="fin" class="form-control" type="text" name="fin" value="{{ old('fin') ? old('fin') : $contrato->fin }}" placeholder="dd-mm-yyyy">
        </div>

        <div class="form-group {{ $errors->has('valor') ? 'has-error' : '' }}">
          <label class="control-label" for="valor">Valor: *</label>
          <input id="valor" class="form-control" type="number" step="1" min="1" max="9999999999999" name="valor" value="{{ old('valor') ? old('valor') : $contrato->valor }}" placeholder="Valor" required>
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
          <a class="btn btn-flat btn-default" href="{{ route('contratos.show', [$contrato->id] ) }}"><i class="fa fa-reply"></i> Atras</a>
          <button class="btn btn-flat btn-primary" type="submit"><i class="fa fa-send"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('scripts')
<script type="text/javascript">
  $(document).ready( function(){
    $('#inicio, #fin').datepicker({
      format: 'dd-mm-yyyy',
      language: 'es',
      keyboardNavigation: false,
      autoclose: true
    });
  });
</script>
@endsection