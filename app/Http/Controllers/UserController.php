<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Persona;
use App\Sucursal;
use App\Exports\UserExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        $query = User::join('personas', 'users.id', '=', 'personas.id')
            ->join('roles', 'users.idrol', '=', 'roles.id')
            ->join('sucursales', 'users.idsucursal', '=', 'sucursales.id')
            ->select(
                'personas.id', 'personas.nombre', 'personas.tipo_documento', 
                'personas.num_documento', 'personas.telefono', 
                'personas.fotografia', 'users.usuario', 
                'users.password', 'users.condicion', 'users.idrol', 
                'roles.nombre as rol', 'users.idsucursal', 'sucursales.nombre as sucursal'
            );
        
        if (!empty($buscar)) {
            $query->where('personas.' . $criterio, 'like', '%' . $buscar . '%');
        }
            
        $personas = $query->orderBy('personas.id', 'desc')->paginate(6);

        return [
            'pagination' => [
                'total' => $personas->total(),
                'current_page' => $personas->currentPage(),
                'per_page' => $personas->perPage(),
                'last_page' => $personas->lastPage(),
                'from' => $personas->firstItem(),
                'to' => $personas->lastItem(),
            ],
            'personas' => $personas
        ];
    }
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
    
        try {
            DB::beginTransaction();
    
            $errors = [];
    
            // Verificar si el nombre ya existe (si lo necesitas)
            $nombreExistente = Persona::where('nombre', $request->nombre)->first();
            if ($nombreExistente) {
                $errors['nombre'] = 'El nombre ya está registrado en el sistema';
            }
    
            // Verificar si el número de documento ya existe
            $docExistente = Persona::where('num_documento', $request->num_documento)->first();
            if ($docExistente) {
                $errors['num_documento'] = 'El número de documento ya está registrado';
            }
    
            // Verificar si el usuario ya existe
            $userExistente = User::where('usuario', $request->usuario)->first();
            if ($userExistente) {
                $errors['usuario'] = 'El nombre de usuario ya está en uso';
            }
    
            // Validar campos obligatorios
            if (empty($request->nombre)) {
                $errors['nombre'] = 'El nombre es obligatorio';
            }
            if (empty($request->tipo_documento)) {
                $errors['tipo_documento'] = 'El tipo de documento es obligatorio';
            }
            if (empty($request->num_documento)) {
                $errors['num_documento'] = 'El número de documento es obligatorio';
            }
            if (empty($request->telefono)) {
                $errors['telefono'] = 'El teléfono es obligatorio';
            }
            if (empty($request->idrol)) {
                $errors['idrol'] = 'El rol es obligatorio';
            }
            if (empty($request->idsucursal)) {
                $errors['idsucursal'] = 'La sucursal es obligatoria';
            }
            if (empty($request->password)) {
                $errors['password'] = 'La contraseña es obligatoria';
            } elseif (strlen($request->password) < 6) {
                $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
            }
    
            if (!empty($errors)) {
                return response()->json([
                    'error' => true,
                    'message' => 'Error de validación',
                    'errors' => $errors
                ], 422);
            }
    
            // Resto del código de registro...
            $persona = new Persona();
            $persona->nombre = $request->nombre;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento = $request->num_documento;
            $persona->telefono = $request->telefono;
            $persona->usuario = Auth::id();
    
            if ($request->hasFile('fotografia')) {
                $imagen = $request->file('fotografia');
                $nombreImagen = time().'_'.Str::slug($request->nombre).'.'.$imagen->getClientOriginalExtension();
                $imagen->move(public_path('img/usuarios/'), $nombreImagen);
                $persona->fotografia = $nombreImagen;
            }
    
            $persona->save();
    
            $user = new User();
            $user->id = $persona->id;
            $user->usuario = $request->usuario;
            $user->password = bcrypt($request->password);
            $user->idrol = $request->idrol;
            $user->idsucursal = $request->idsucursal;
            $user->idpuntoventa = 1;
            $user->condicion = 1;
            $user->save();
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado con éxito'
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error en el servidor: '.$e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
    
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:personas,id',
            'nombre' => 'required|string|max:100',
            'tipo_documento' => 'required',
            'num_documento' => 'required|string|max:20|unique:personas,num_documento,'.$request->id,
            'telefono' => 'required|string|max:20',
            'usuario' => 'required|string|min:5|unique:users,usuario,'.$request->id,
            'password' => 'nullable|string|min:6',
            'idrol' => 'required|integer|exists:roles,id',
            'idsucursal' => 'required|integer|exists:sucursales,id',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            $message = '';
            
            if ($errors->has('num_documento')) {
                $message = 'El número de documento ya está registrado en el sistema.';
            } 
            if ($errors->has('usuario')) {
                if (!empty($message)) $message .= ' ';
                $message .= 'El nombre de usuario ya está en uso.';
            }
            
            if (empty($message)) {
                $message = 'Por favor complete todos los campos requeridos correctamente.';
            }
    
            return response()->json([
                'error' => true,
                'message' => $message,
                'error_type' => 'duplicate' // Agregamos un tipo de error para identificar en el frontend
            ], 422);
        }
    
        // Resto del método update permanece igual...
        try {
            DB::beginTransaction();
    
            $user = User::findOrFail($request->id);
            $persona = Persona::findOrFail($user->id);
            
            $persona->nombre = $request->nombre;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento = $request->num_documento;
            $persona->telefono = $request->telefono;
    
            if ($request->hasFile('fotografia')) {
                if ($persona->fotografia && file_exists(public_path("img/usuarios/{$persona->fotografia}"))) {
                    unlink(public_path("img/usuarios/{$persona->fotografia}"));
                }
    
                $imagen = $request->file("fotografia");
                $nombreimagen = Str::slug($request->nombre) . "." . $imagen->guessExtension();
                $ruta = public_path("img/usuarios/");
    
                copy($imagen->getRealPath(), $ruta . $nombreimagen);
                $persona->fotografia = $nombreimagen;
            }
    
            $persona->save();
    
            $user->usuario = $request->usuario;
            
            if (!empty($request->password)) {
                $user->password = bcrypt($request->password);
            }
            
            $user->idrol = $request->idrol;
            $user->idsucursal = $request->idsucursal;
            $user->save();
    
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado con éxito'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar usuario: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }
    public function desactivar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
            
        try {
            $user = User::findOrFail($request->id);
            $user->condicion = '0';
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario desactivado con éxito'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al desactivar usuario'
            ], 500);
        }
    }

    public function activar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
            
        try {
            $user = User::findOrFail($request->id);
            $user->condicion = '1';
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario activado con éxito'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al activar usuario'
            ], 500);
        }
    }

    public function listarReporteUsuariosExcel()
    {
        return Excel::download(new UserExport, 'usuarios.xlsx');
    }

    public function editarPersona(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        try {
            $persona = User::join('personas', 'users.id', '=', 'personas.id')
                ->join('roles', 'users.idrol', '=', 'roles.id')
                ->join('sucursales', 'users.idsucursal', '=', 'sucursales.id')
                ->select(
                    'personas.id', 'personas.nombre', 'personas.tipo_documento', 
                    'personas.num_documento', 'personas.direccion', 'personas.telefono', 
                    'personas.email', 'personas.fotografia', 'users.usuario', 
                    'users.password', 'users.condicion', 'users.idrol', 
                    'roles.nombre as rol', 'users.idsucursal', 'sucursales.nombre as sucursal'
                )
                ->where('personas.id', $request->id)
                ->first();

            if (!$persona) {
                return response()->json([
                    'error' => true,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return ['persona' => $persona];
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al obtener datos del usuario'
            ], 500);
        }
    }

    public function selectUsuarios(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $filtro = $request->filtro;

        $usuarios = User::join('personas', 'users.id', '=', 'personas.id')
            ->where('personas.nombre', 'like', '%' . $filtro . '%')
            ->select('users.id', 'personas.nombre as nombre')
            ->orderBy('personas.nombre', 'asc')
            ->get();

        return ['usuarios' => $usuarios];
    }

    public function selectUsuariosPorRol(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $filtro = $request->filtro;

        $usuarios = User::join('personas', 'users.id', '=', 'personas.id')
            ->where('users.idrol', '=', $filtro)
            ->select('personas.nombre as nombre', 'personas.id', 'users.condicion')
            ->orderBy('personas.nombre', 'asc')
            ->get();

        return ['usuarios' => $usuarios];
    }
}