<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComprasExport implements FromCollection, WithHeadings
{
    protected $compras;

    public function __construct($compras)
    {
        $this->compras = $compras;
    }

    public function collection()
    {
        return $this->compras;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'N° Comprobante',
            'Proveedor',
            'Usuario',
            'Tipo Comp.',
            'Estado',
            'Total'
        ];
    }
}
