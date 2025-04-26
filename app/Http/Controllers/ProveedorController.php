<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use App\Proveedor;
use App\Persona;
use App\Imports\ProvedorImport;
use Exception;
use Maatwebsite\Excel\Facades\Excel;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
    
        $buscar = $request->buscar;
        $por_pagina = $request->por_pagina;

        if ($por_pagina == null) {
            $paginacion = 10;
        } else {
            $paginacion = $por_pagina;
        }
    
        $personas = Proveedor::join('personas', 'proveedores.id', '=', 'personas.id')
            ->select(
                'personas.id',
                'personas.nombre',
                'personas.tipo_documento',
                'personas.num_documento',
                'personas.direccion',
                'personas.telefono',
                'personas.email',
                'proveedores.contacto',
                'proveedores.telefono_contacto'
            )
            ->where(function ($query) use ($buscar) {
                $query->where('personas.nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('personas.num_documento', 'like', '%' . $buscar . '%')
                    ->orWhere('personas.telefono', 'like', '%' . $buscar . '%');
            })
            ->distinct()
            ->orderBy('personas.id', 'desc')
            ->paginate($paginacion);
        
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

    public function index2(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $buscar = $request->buscar;
            $personas = Proveedor::join('personas', 'proveedores.id', '=', 'personas.id')
            ->select(
                'personas.id',
                'personas.nombre',
                'personas.tipo_documento',
                'personas.num_documento',
                'personas.direccion',
                'personas.telefono',
                'personas.email',
                'proveedores.contacto',
                'proveedores.telefono_contacto'
            )
            ->where(function ($query) use ($buscar) {
                $query->where('personas.nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('personas.num_documento', 'like', '%' . $buscar . '%')
                    ->orWhere('personas.telefono', 'like', '%' . $buscar . '%');
            })
            ->distinct()
            ->orderBy('personas.id', 'desc');
        $personas = $personas->get();
        return ['personas' => $personas];
    }
    public function selectProveedor(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $filtro = $request->filtro;
        $proveedores = Proveedor::join('personas', 'proveedores.id', '=', 'personas.id')
            ->where('personas.nombre', 'like', '%' . $filtro . '%')
            ->orWhere('personas.num_documento', 'like', '%' . $filtro . '%')
            ->select('personas.id', 'personas.nombre', 'personas.num_documento')
            ->orderBy('personas.nombre', 'asc')->get();

        return ['proveedores' => $proveedores];
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
    
        // Validar si el proveedor ya existe
        $proveedorExistente = Persona::where('nombre', $request->nombre)->first();
        if ($proveedorExistente) {
            return response()->json([
                'error' => true,
                'message' => 'El proveedor ya existe en el sistema'
            ], 422);
        }
    
        try {
            DB::beginTransaction();
            $persona = new Persona();
            $persona->nombre = $request->nombre;
            $persona->usuario = Auth::user()->id;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento = $request->num_documento;
            $persona->direccion = $request->direccion;
            $persona->telefono = $request->telefono ?? null; // Hacer opcional
            $persona->email = $request->email ?? null; // Hacer opcional
            $persona->save();
    
            $proveedor = new Proveedor();
            $proveedor->contacto = $request->contacto;
            $proveedor->telefono_contacto = $request->telefono_contacto;
            $proveedor->id = $persona->id;
            $proveedor->save();
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Proveedor registrado con éxito'
            ]);
    
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error al registrar proveedor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
    
        // Validar si el proveedor ya existe (excepto el actual)
        $proveedorExistente = Persona::where('nombre', $request->nombre)
                                    ->where('id', '!=', $request->id)
                                    ->first();
        if ($proveedorExistente) {
            return response()->json([
                'error' => true,
                'message' => 'El proveedor ya existe en el sistema'
            ], 422);
        }
    
        try {
            DB::beginTransaction();
    
            $proveedor = Proveedor::findOrFail($request->id);
            $persona = Persona::findOrFail($proveedor->id);
    
            $persona->nombre = $request->nombre;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento = $request->num_documento;
            $persona->direccion = $request->direccion;
            $persona->telefono = $request->telefono ?? null; // Hacer opcional
            $persona->email = $request->email ?? null; // Hacer opcional
            $persona->save();
    
            $proveedor->contacto = $request->contacto;
            $proveedor->telefono_contacto = $request->telefono_contacto;
            $proveedor->save();
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado con éxito'
            ]);
    
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error al actualizar proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importar(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|mimes:csv,txt',
            ]);

            $archivo = $request->file('archivo');

            $import = new ProvedorImport();
            Excel::import($import, $archivo);

            $errors = $import->getErrors();

            if (!empty($errors)) {
                return response()->json(['errors' => $errors], 422);
            } else {
                return response()->json(['mensaje' => 'Importación exitosa'], 200);
            }
        } catch (Exception $e) {
            Log::error('Error en la importación: ' . $e->getMessage());

            return response()->json(['error' => 'Error en la importación', 'mensaje' => $e->getMessage()], 500);
        }
    }
    public function eliminarProveedor($id)
    {
        if (!request()->ajax()) return redirect('/');

        try {
            DB::beginTransaction();

            $proveedor = Proveedor::findOrFail($id);
            $proveedor->delete();

            $persona = Persona::findOrFail($id);
            $persona->delete();

            DB::commit();
            return response()->json(['mensaje' => 'Proveedor eliminado correctamente'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el proveedor'], 500);
        }
    }

}