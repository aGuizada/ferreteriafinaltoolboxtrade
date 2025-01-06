<?php

namespace App\Http\Controllers;

use App\CreditoVenta;

use Illuminate\Http\Request;
use App\Persona;
use App\Exports\ClientExport;
use App\Imports\ClienteImport;
use App\User;
use App\Venta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ClienteController extends Controller
{
   
    public function index(Request $request)
    {
        Log::info('Data', [
            'buscar' => $request->buscar,
            'criterio' => $request->criterio,
        ]);

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $usuarioid = $request->usuarioid;
        $perPage = $request->input('per_page', 10); // Default to 10 if not specified

        // Consulta para obtener personas que no son usuarios
        $usuarios = Persona::whereNotIn('id', function ($query) {
            $query->select('id')->from('users');
        });
        $usuarios = $usuarios->whereNull('direccion')->where('usuario', '>', 0);
        
        /*if (!empty($usuarioid)) {
            $usuarios = $usuarios->where('personas.usuario', '=', $usuarioid);
        }*/

        // Aplicar filtros de búsqueda si están presentes
        if (!empty($buscar)) {
            $usuarios->where(function ($query) use ($criterio, $buscar) {
                $query->where($criterio, 'like', '%' . $buscar . '%');
            });
        }

        // Ordenar y paginar los resultados
        $usuarios = $usuarios->orderBy('id', 'desc')->paginate($perPage);

        return [
            'pagination' => [
                'total' => $usuarios->total(),
                'current_page' => $usuarios->currentPage(),
                'per_page' => $usuarios->perPage(),
                'last_page' => $usuarios->lastPage(),
                'from' => $usuarios->firstItem(),
                'to' => $usuarios->lastItem(),
            ],
            'usuarios' => $usuarios
        ];
    }

    public function selectCliente(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $filtro = $request->filtro;
        $clientes = Persona::where('nombre', 'like', '%' . $filtro . '%')
            ->whereNull('direccion')
            ->where('usuario', '>', 0)
            ->select('id', 'nombre', 'tipo_documento',  'num_documento','complemento_id', 'email', 'telefono')
            ->orderBy('nombre', 'asc')
            ->take(5)
            ->get();

        $clientesConCreditos = $clientes->map(function ($cliente) {
            $cantidadCreditos = CreditoVenta::where('idcliente', $cliente->id)
                ->where('estado', '!=', 'Completado')
                ->count();
            $cliente->cantidad_creditos = $cantidadCreditos;
            return $cliente;
        });

        return ['clientes' => $clientesConCreditos];
    }


    public function seleccionarClientePorNumero(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $filtro = $request->numero;
        $clientes = Persona::where('num_documento', 'like', '%'. $filtro .'%')
            ->whereNull('direccion')
            ->where('usuario', '>', 0)
            ->select('id', 'nombre', 'tipo_documento', 'num_documento', 'email', 'telefono')
            ->orderBy('tipo_documento', 'desc')
            ->take(5)
            ->get();
        $clientesConCreditos = $clientes->map(function ($cliente) {
            $cantidadCreditos = CreditoVenta::where('idcliente', $cliente->id)
                ->where('estado', '!=', 'Completado')
                ->count();

            $cliente->cantidad_creditos = $cantidadCreditos;
            return $cliente;
        });

        return ['clientes' => $clientesConCreditos];

        //return ['clientes' => $clientes];
    }

public function store(Request $request)
{
    if (!$request->ajax()) return redirect('/');

    // Verificar si ya existe un cliente con este número de documento
    $clienteExistente = Persona::where('num_documento', $request->num_documento)->first();

    if ($clienteExistente) {
        // Si ya existe, devolver ese ID
        return ['id' => $clienteExistente->id];
    }

    // Si no existe, crear uno nuevo
    $persona = new Persona();
    $persona->nombre = $request->nombre;
    $persona->usuario = Auth::user()->iduse;
    $persona->tipo_documento = $request->tipo_documento;
    $persona->num_documento = $request->num_documento;
    $persona->complemento_id = $request->complemento;
    $persona->save();

    return ['id' => $persona->id];
}

    public function update(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $persona = Persona::findOrFail($request->id);
        $persona->nombre = $request->nombre;
        $persona->usuario = $request->usuariodos_id;
        $persona->tipo_documento = $request->tipo_documento;
        $persona->num_documento = $request->num_documento;
        $persona->complemento_id = $request->complemento;
        $persona->save();
        Log::info('DAtOS ACTU8ALIZAR!!:', [
            'DATOS' => $persona,
        ]);
    }

    public function listarReporteClienteExcel()
    {
        return Excel::download(new ClientExport, 'clientes.xlsx');
    }

    //---seleccionar usuario vendedor--
    public function selectUsuarioVendedor(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $filtro = $request->filtro;
        $clientes = User::join('personas', 'users.id', '=', 'personas.id')
            ->select(
                'personas.id as ID',
                'personas.nombre',
                'users.idrol',
                'users.iduse as ID_use'
            )->where('users.idrol', '=', 2)
            ->orWhere('personas.nombre', 'like', '%' . $filtro . '%')
            ->orderBy('personas.nombre', 'asc')
            //->toSql();
            ->get();

        return ['clientes' => $clientes];
    }
    //---listado por id lo que se pidio de Personana--
    public function indexUsuario(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $idusuario = $request->idusuario;
        $usuario = User::join('personas', 'users.id', '=', 'personas.id')
            //->join('roles', 'users.idrol', '=', 'roles.id')
            ->select(
                'personas.id as ID',
                'personas.nombre',
                'personas.usuario',
                //'users.iduse as ID_use'
            )
            //->where('personas.usuario', '=', $idusuario)->get();
            ->where('users.iduse', '=', $idusuario)->get();
        return ['usuario' => $usuario];
    }
    //---listado por id lo que se pidio de Personana--
    public function indexUsuarioFiltro(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        //$idusuario = $request->idusuario;
        $filtro = $request->filtro;
        $usuariodos = User::join('personas', 'users.id', '=', 'personas.id')
            //->join('roles', 'users.idrol', '=', 'roles.id')
            ->select(
                'personas.nombre',
                'personas.usuario',
                'users.iduse'
            )
            //->where('personas.usuario', '=', $idusuario)->get();
            ->where('users.idrol', '=', 2)
            ->orWhere('personas.nombre', 'like', '%' . $filtro . '%')
            ->orderBy('personas.nombre', 'asc')
            //->toSql();
            ->get();

        return ['usuariodos' => $usuariodos];
    }

    public function getUserInfo()
    {
        $user = Auth::user();
        return response()->json(['user' => $user]);
    }
   
    //     ];
    // }

    public function importarCliente(Request $request){
        $path = $request->file('select_users_file')->getRealPath();
        Excel::import(new ClienteImport, $path);
    }

    public function verificarExistencia(Request $request)
    {
        $documento = $request->query('documento');
        $cliente = Persona::where('num_documento', $documento)->first();

        if ($cliente) {
            return response()->json(['existe' => true, 'cliente' => $cliente]);
        } else {
            return response()->json(['existe' => false]);
        }
    }

    public function buscarPorDocumento(Request $request) {
        $documento = $request->query('documento');
        $cliente = Persona::where('num_documento', $documento)->first();
    
        if ($cliente) {
            return response()->json($cliente);
        } else {
            return response()->json(null, 404);
        }
    }
    public function eliminarCliente($id)
    {
        if (!request()->ajax()) return redirect('/');

        DB::beginTransaction();

        try {
            $cliente = Persona::findOrFail($id);
            
            // Verificar si el cliente tiene ventas asociadas
            $ventasAsociadas = Venta::where('idcliente', $id)->exists();

            if ($ventasAsociadas) {
                DB::rollBack();
                return response()->json([
                    'error' => 'No se puede eliminar el cliente porque tiene ventas asociadas.',
                    'tipo' => 'integridad'
                ], 422);
            }

            $cliente->delete();
            
            DB::commit();
            return response()->json(['mensaje' => 'Cliente eliminado correctamente'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al eliminar el cliente: ' . $e->getMessage(),
                'tipo' => 'excepcion'
            ], 500);
        }
    }
    
}
