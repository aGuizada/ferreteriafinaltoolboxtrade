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
        <img class="portada" src="img/toolbox trade CC.png" alt="Daccar Logo">
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
<style>
/* Estilo General */
.content {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color:rgba(255, 255, 255, 0);
  color: #fff;
}

/* Contenedor principal */
.container-login {
  display: flex;
  width: 80%;
  max-width: 1200px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(221, 137, 137, 0.5);
}

/* Sección izquierda */
.left {
  flex: 1;
  background-color:rgb(238, 236, 236);
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.container-title h1 {
  margin-bottom: 10px;
  font-size: 2.5em;
  color: #0dcaf0;
}

.container-title p {
  font-size: 1.2em;
  color: #aaa;
}

.input-texto-arriba {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  font-size: 1em;
  border: none;
  border-bottom: 2px solid #555;
  background: transparent;
  color: #fff;
}

.input-texto-arriba::placeholder {
  color: #777;
}

.input-texto-arriba:focus {
  border-bottom: 2px solid #0dcaf0;
  outline: none;
}

.btn-ingresar {
  width: 100%;
  padding: 15px;
  background-color: #0dcaf0;
  color: #000;
  font-size: 1em;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-ingresar:hover {
  background-color: #0b95c6;
}

/* Sección derecha */
.right {
  flex: 1;
  background: linear-gradient(135deg, #0d47a1, #1976d2);
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}

.logo-container {
  text-align: center;
}

.portada {
  max-width: 60%;
  height: auto;
  filter: drop-shadow(0 0 20px #0dcaf0);
}
</style>