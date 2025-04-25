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
                'personas.num_documento', 'personas.direccion', 'personas.telefono', 
                'personas.email', 'personas.fotografia', 'users.usuario', 
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
        if (!$request->ajax())
            return redirect('/');

        // Validate the request
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'tipo_documento' => 'required',
            'num_documento' => 'required|string|max:20',
            'email' => 'required|email|unique:personas,email',
            'usuario' => 'required|string|min:5|unique:users,usuario',
            'password' => 'required|string|min:6',
            'idrol' => 'required|integer|exists:roles,id',
            'idsucursal' => 'required|integer|exists:sucursales,id',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if name already exists
        $existingName = Persona::where('nombre', $request->nombre)->first();
        if ($existingName) {
            return response()->json([
                'error' => true,
                'message' => 'El nombre ya existe en el sistema'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $persona = new Persona();
            $persona->nombre = $request->nombre;
            $persona->usuario = Auth::user()->id;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento = $request->num_documento;
            $persona->direccion = $request->direccion ?? '';
            $persona->telefono = $request->telefono ?? '';
            $persona->email = $request->email;

            if ($request->hasFile('fotografia')) {
                $imagen = $request->file("fotografia");
                $nombreimagen = Str::slug($request->nombre) . "." . $imagen->guessExtension();
                $ruta = public_path("img/usuarios/");

                // Create directory if it doesn't exist
                if (!File::isDirectory($ruta)) {
                    File::makeDirectory($ruta, 0755, true);
                }

                // Copy image to directory
                copy($imagen->getRealPath(), $ruta . $nombreimagen);
                $persona->fotografia = $nombreimagen;
            }

            $persona->save();

            $user = new User();
            $user->id = $persona->id;
            $user->idrol = $request->idrol;
            $user->idsucursal = $request->idsucursal;
            $user->usuario = $request->usuario;
            $user->password = bcrypt($request->password);
            $user->condicion = '1';
            $user->idpuntoventa = '1';
            $user->save();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado con éxito'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar usuario: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error al registrar usuario: ' . $e->getMessage()
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
            'num_documento' => 'required|string|max:20',
            'email' => 'required|email|unique:personas,email,' . $request->id,
            'usuario' => 'required|string|min:5|unique:users,usuario,' . $request->id,
            'password' => 'nullable|string|min:6',
            'idrol' => 'required|integer|exists:roles,id',
            'idsucursal' => 'required|integer|exists:sucursales,id',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if name already exists for another user
        $existingName = Persona::where('nombre', $request->nombre)
                              ->where('id', '!=', $request->id)
                              ->first();
        if ($existingName) {
            return response()->json([
                'error' => true,
                'message' => 'El nombre ya existe en el sistema'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->id);
            $persona = Persona::findOrFail($user->id);
            
            $persona->nombre = $request->nombre;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento = $request->num_documento;
            $persona->direccion = $request->direccion ?? '';
            $persona->telefono = $request->telefono ?? '';
            $persona->email = $request->email;

            if ($request->hasFile('fotografia')) {
                // Delete previous image if it exists
                if ($persona->fotografia && file_exists(public_path("img/usuarios/{$persona->fotografia}"))) {
                    unlink(public_path("img/usuarios/{$persona->fotografia}"));
                }

                $imagen = $request->file("fotografia");
                $nombreimagen = Str::slug($request->nombre) . "." . $imagen->guessExtension();
                $ruta = public_path("img/usuarios/");

                // Copy image to directory
                copy($imagen->getRealPath(), $ruta . $nombreimagen);
                $persona->fotografia = $nombreimagen;
            }

            $persona->save();

            $user->usuario = $request->usuario;
            
            // Only update password if provided
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