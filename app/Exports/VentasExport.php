<?php
namespace App\Exports;

use App\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VentasExport implements FromCollection, WithHeadings
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $usuarioIds;

    public function __construct($fechaInicio, $fechaFin, $usuarioIds)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->usuarioIds = $usuarioIds;
    }

    public function collection()
    {
        $query = Venta::with(['cliente', 'usuario'])
            ->orderBy('fecha_hora', 'asc');

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('fecha_hora', [$this->fechaInicio . ' 00:00:00', $this->fechaFin . ' 23:59:59']);
        }
        if (!empty($this->usuarioIds)) {
            $query->whereIn('idusuario', $this->usuarioIds);
        }

        $ventas = $query->get();
        \Log::info('Ventas exportadas', ['ventas' => $ventas]); // Para debug

        return $ventas->map(function ($venta) {
            return [
                $venta->id,
                $venta->fecha_hora,
                $venta->cliente->nombre ?? '',
                $venta->tipo_comprobante . ' ' . $venta->serie_comprobante . '-' . $venta->num_comprobante,
                $venta->usuario->usuario ?? '',
                $venta->total,
                $venta->estado,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Cliente',
            'Comprobante',
            'Vendedor',
            'Total',
            'Estado',
        ];
    }
}