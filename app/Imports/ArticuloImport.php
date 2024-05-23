<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Articulo;
use App\Categoria;
use App\Grupo;
use App\Proveedor;
use App\Persona;
use App\Medida;
use App\Marca;
use App\Industria;
use Illuminate\Support\Facades\Log;
use Exception; // Importa la clase Exception

class ArticuloImport implements ToCollection
{
    private $categoriaMapping;
    private $grupoMapping;
    private $proveedorMapping;
    private $medidaMapping;
    private $marcaMapping;
    private $industriaMapping;
    private $personaMapping;
    private $errors = [];

    public function __construct()
    {
        $this->categoriaMapping = $this->createCategoriaMapping();
        $this->grupoMapping = $this->createGrupoMapping();
        $this->proveedorMapping = $this->createProveedorMapping();
        $this->medidaMapping = $this->createMedidaMapping();
        $this->marcaMapping = $this->createMarcaMapping();
        $this->industriaMapping = $this->createIndustriaMapping();
        $this->personaMapping = $this->createPersonaMapping();
    }

    private function createCategoriaMapping()
    {
        return Categoria::pluck('nombre', 'id')->toArray();
    }

    private function createPersonaMapping()
    {
        return Persona::pluck('nombre', 'id')->toArray();
    }

    private function createGrupoMapping()
    {
        return Grupo::pluck('nombre_grupo', 'id')->toArray();
    }

    private function createProveedorMapping()
    {
        return Proveedor::pluck('contacto', 'id')->toArray();
    }

    private function createMedidaMapping()
    {
        return Medida::pluck('descripcion_medida', 'id')->toArray();
    }

    private function createMarcaMapping()
    {
        return Marca::pluck('nombre', 'id')->toArray();
    }

    private function createIndustriaMapping()
    {
        return Industria::pluck('nombre', 'id')->toArray();
    }

    public function collection(Collection $rows)
    {
        $rowNumber = 1;
        $importacionExitosa = true;

        try {
            \DB::beginTransaction();

            foreach ($rows as $row) {
                $rowErrors = [];

                $idCategoria = $this->getCategoriaId($row[16]);
                $idGrupo = $this->getGrupoId($row[17]);
                $idProveedor = $this->getProveedorId($row[18]);
                $idMedida = $this->getMedidaId($row[19]);
                $idMarca = $this->getMarcaId($row[20]);
                $idIndustria = $this->getIndustriaId($row[21]);

                if (!$idCategoria) {
                    $rowErrors[] = "Error fila $rowNumber: No existe 'Linea $row[16]'";
                }
                if (!$idGrupo) {
                    $rowErrors[] = "Error fila $rowNumber: No existe 'Grupo $row[17]'";
                }
                if (!$idProveedor) {
                    $rowErrors[] = "Error fila $rowNumber: El proveedor '$row[18]' no está registrado";
                }
                if (!$idMedida) {
                    $rowErrors[] = "Error fila $rowNumber: La medida '$row[19]' no está registrada en la base de datos";
                }
                if (!$idMarca) {
                    $rowErrors[] = "Error fila $rowNumber: No existe 'Marca $row[20]'";
                }
                if (!$idIndustria) {
                    $rowErrors[] = "Error fila $rowNumber: No existe 'Industria $row[21]'";
                }

                try {
                    Articulo::create([
                        'codigo' => $row[0],
                        'nombre' => $row[1],
                        'nombre_generico' => $row[2],
                        'descripcion' => $row[3],
                        'unidad_envase' => $row[4],
                        'precio_list_unid' => $row[5],
                        'precio_costo_unid' => $row[6],
                        'precio_costo_paq' => $row[7],
                        'precio_venta' => $row[8],
                        'precio_uno' => $row[9],
                        'precio_dos' => $row[10],
                        'precio_tres' => $row[11],
                        'precio_cuatro' => $row[12],
                        'costo_compra' => $row[13],
                        'stock' => $row[14],
                        'condicion' => $row[15],
                        'fotografia' => null,
                        'idcategoria' => $idCategoria,
                        'idgrupo' => $idGrupo,
                        'idproveedor' => $idProveedor,
                        'idmedida' => $idMedida,
                        'idmarca' => $idMarca,
                        'idindustria' => $idIndustria,
                    ]);
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), "Integrity constraint violation: 1062") !== false) {
                        $rowErrors[] = "Error fila $rowNumber: El producto '$row[1]' ya existe";
                    } else {
                        $rowErrors[] = "Error al procesar fila: " . $e->getMessage();
                    }

                    $importacionExitosa = false;
                }

                if (!empty($rowErrors)) {
                    $this->errors = array_merge($this->errors, $rowErrors);
                }

                $rowNumber++;
            }

            if ($importacionExitosa) {
                \DB::commit(); // Confirmar la transacción si no hay errores
            } else {
                \DB::rollBack(); // Revertir la transacción en caso de error
            }
        } catch (Exception $e) {
            \DB::rollBack(); // Revertir la transacción en caso de error
            $importacionExitosa = false;
            $this->errors[] = "Error al procesar fila: " . $e->getMessage();
        }

        return $this->getErrorsResponse($importacionExitosa);
    }

    public function getErrors()
    {
        return $this->errors ?? [];
    }

    private function getErrorsResponse($importacionExitosa)
    {
        if (!$importacionExitosa) {
            return response()->json(['errors' => $this->errors], 422);
        } else {
            return response()->json(['mensaje' => 'Importación exitosa'], 200);
        }
    }

    private function getCategoriaId($nombreCategoria)
    {
        $idCategoria = array_search($nombreCategoria, $this->categoriaMapping);
        return $idCategoria !== false ? $idCategoria : null;
    }

    private function getGrupoId($nombreGrupo)
    {
        $idGrupo = array_search($nombreGrupo, $this->grupoMapping);
        return $idGrupo !== false ? $idGrupo : null;
    }

    private function getProveedorId($nombreProveedor)
    {
        $idProveedor = array_search($nombreProveedor, $this->personaMapping);
        return $idProveedor !== false ? $idProveedor : null;
    }

    private function getMedidaId($descripcionMedida)
    {
        $idMedida = array_search($descripcionMedida, $this->medidaMapping);
        return $idMedida !== false ? $idMedida : null;
    }

    private function getMarcaId($nombreMarca)
    {
        $idMarca = array_search($nombreMarca, $this->marcaMapping);
        return $idMarca !== false ? $idMarca : null;
    }

    private function getIndustriaId($nombreIndustria)
    {
        $idIndustria = array_search($nombreIndustria, $this->industriaMapping);
        return $idIndustria !== false ? $idIndustria : null;
    }
}
