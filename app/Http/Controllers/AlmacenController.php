<?php

namespace App\Http\Controllers;

use App\Almacen;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\User;

class AlmacenController extends Controller
{
    //listas almacen
   public function index(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        $almacenes = Almacen::join('sucursales', 'almacens.sucursal', '=', 'sucursales.id')
            ->select('almacens.*', 'sucursales.nombre as nombre_sucursal')
            ->when($buscar, function ($query) use ($buscar, $criterio) {
                return $query->where('almacens.nombre_almacen', 'like', '%' . $buscar . '%')
                    ->orWhere('sucursales.nombre', 'like', '%' . $buscar . '%');
            })
            ->orderBy('almacens.id', 'desc')
            ->paginate(10);

      foreach ($almacenes as $almacen) {
    $encargadosIds = explode(',', $almacen->encargado);
    // Cambia 'name' a 'usuario' en la siguiente línea
    $encargadosNombres = User::whereIn('id', $encargadosIds)->pluck('usuario')->implode(', ');
    $almacen->encargados_nombres = $encargadosNombres;
}

        return [
            'pagination' => [
                'total' => $almacenes->total(),
                'current_page' => $almacenes->currentPage(),
                'per_page' => $almacenes->perPage(),
                'last_page' => $almacenes->lastPage(),
                'from' => $almacenes->firstItem(),
                'to' => $almacenes->lastItem(),
            ],
            'almacenes' => $almacenes
        ];
    }

    public function buscadorAlmacen(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $nombre = $request->input('buscar');
        $campos = [
            'id',
            'nombre_almacen',
            'condicion'
        ];

        $almacenes = Almacen::where('almacens.nombre_almacen', 'like', '%' . $nombre . '%')
                            ->where('condicion', 1)
                            ->select($campos)
                            ->paginate(3);

        return [
            'pagination' => [
                'total' => $almacenes->total(),
                'current_page' => $almacenes->currentPage(),
                'per_page' => $almacenes->perPage(),
                'last_page' => $almacenes->lastPage(),
                'from' => $almacenes->firstItem(),
                'to' => $almacenes->lastItem(),
            ],
            'almacenes' => $almacenes
        ];
    }
    public function index2(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        $almacenes = Almacen::join('sucursales', 'almacens.sucursal', '=', 'sucursales.id')
            ->select('almacens.*', 'sucursales.nombre as nombre_sucursal')
            ->when($buscar, function ($query) use ($buscar, $criterio) {
                return $query->where('almacens.nombre_almacen', 'like', '%' . $buscar . '%')
                    ->orWhere('sucursales.nombre', 'like', '%' . $buscar . '%');
            })
            ->orderBy('almacens.id', 'desc');   
        $almacenes = $almacenes->get();
      foreach ($almacenes as $almacen) {
    $encargadosIds = explode(',', $almacen->encargado);
    // Cambia 'name' a 'usuario' en la siguiente línea
    $encargadosNombres = User::whereIn('id', $encargadosIds)->pluck('usuario')->implode(', ');
    $almacen->encargados_nombres = $encargadosNombres;
}

        return [ 'almacenes' => $almacenes
        ];
    }
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        try {
            $almacenes = new Almacen();
            $almacenes->nombre_almacen = $request->nombre_almacen;
            $almacenes->ubicacion = $request->ubicacion;
            $almacenes->encargado = $request->encargado;
            $almacenes->telefono = $request->telefono;
            $almacenes->sucursal = $request->sucursal;
            $almacenes->observacion = $request->observacion;
            $almacenes->save();
            
            $encargados = explode(',', $request->encargado);
            $almacenes->encargado = json_encode($encargados);
            
            return response()->json(['success' => true]);
            
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if($errorCode == 1062){
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate entry: El nombre del almacén ya existe'
                ], 422);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el almacén'
            ], 500);
        }
    }
    public function update(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $almacenes = Almacen::findOrFail($request->id);

        $almacenes->nombre_almacen = $request->nombre_almacen;
        $almacenes->ubicacion = $request->ubicacion;
        $almacenes->encargado = $request->encargado;
        $almacenes->telefono = $request->telefono;
        $almacenes->sucursal = $request->sucursal;
        $almacenes->observacion = $request->observacion;
        Log::info('ACTUALIZAR ALMACEN:', [
            'nombre_almacen' => $request->nombre_almacen,
            'ubicacion' => $request->ubicacion,
            'encargado' => $request->encargado,
            'telefono' => $request->telefono,
            'sucursal' => $request->sucursal,
            'observacion' => $request->observacion,
        ]);
        $almacenes->save();
  $encargados = explode(',', $request->encargado);
    $almacenes->encargado = json_encode($encargados);
    }
    public function selectAlmacen(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $almacenes = Almacen::where('condicion', '=', '1')
            ->select('id', 'nombre_almacen')->orderBy('nombre_almacen', 'asc')->get();
        return ['almacenes' => $almacenes];
    }
    public function selectAlmacenDestino(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $almacenes = Almacen::where('condicion', '=', '1')
            ->select('id', 'nombre_almacen')->orderBy('nombre_almacen', 'asc')->get();
        return ['almacenes' => $almacenes];
    }
    public function destroy($id)
{
    if (!request()->ajax()) return redirect('/');
    
    try {
        $almacen = Almacen::findOrFail($id);
        $almacen->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Almacén eliminado correctamente'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'No se pudo eliminar el almacén'
        ], 500);
    }
}
public function verificarNombre(Request $request)
{
    if (!$request->ajax()) return redirect('/');
    
    $nombre = $request->nombre;
    $id = $request->id;
    
    $query = Almacen::where('nombre_almacen', $nombre);
    
    if ($id) {
        $query->where('id', '!=', $id);
    }
    
    $existe = $query->exists();
    
    return response()->json([
        'existe' => $existe
    ]);
}
}