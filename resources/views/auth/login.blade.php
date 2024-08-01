@extends('auth.contenido')

@section('login')
<div class="content">
  <div class="container-login">
    <div class="left">
      <form class="formulario" method="POST" action="{{ route('login')}}">
        {{ csrf_field() }}
        <div class="container-title">
          <h1>Bienvenido</h1>
          <p>Inicia sesión para continuar</p>
        </div>
        <div class="container-input">
          <input type="text" value="{{old('usuario')}}" name="usuario" id="usuario" class="input-texto-arriba" placeholder="Usuario">
          <div class="message">
            {!!$errors->first('usuario','<span class="invalid-feedback">El campo Usuario es obligatorio.</span>')!!}
          </div>
        </div>
        <div class="container-input">
          <input type="password" name="password" id="password" class="input-texto-arriba" placeholder="Contraseña">
          <div class="message">
            {!!$errors->first('password','<span class="invalid-feedback">El campo Contraseña es obligatorio</span>')!!}
          </div>
        </div>
        <div class="container-input">
          <button type="submit" class="btn-ingresar">iniciar sesion</button>
        </div>
      </form>
    </div>
    <div class="right">
      <div class="logo-container">
        <img class="portada" src="img/toolboox.png" alt="Daccar Logo">
      </div>
    </div>
  </div>
</div>
<script>
  const inputs = document.querySelectorAll('.input-texto-arriba');

  inputs.forEach(input => {
    input.addEventListener('focus', () => {
      input.classList.add('texto-arriba');
    });

    input.addEventListener('blur', () => {
      if (!input.value) {
        input.classList.remove('texto-arriba');
      }
    });

    if (input.value) {
      input.classList.add('texto-arriba');
    }
  });
</script>
@endsection