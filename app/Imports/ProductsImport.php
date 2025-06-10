<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Family;
use App\Models\Product;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductsImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'TInventario' => new InventorySheetImport(),
            'FAMILIAS' => new FamiliesSheetImport(),
            'MARCAS' => new BrandsSheetImport(),
        ];
    }
}

class InventorySheetImport implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 2; // Saltar la fila de encabezados
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Validar que la fila tenga datos mínimos
            if (empty($row[0]) {
                continue;
            }

            // Buscar o crear la familia
            $family = Family::firstOrCreate(
                ['code' => $row[3]],
                ['name' => $row[4]]
            );

            // Buscar o crear la marca
            $brand = Brand::firstOrCreate(
                ['code' => $row[6]],
                ['name' => $row[7]]
            );

            // Crear o actualizar el producto
            Product::updateOrCreate(
                ['code' => $row[0]],
                [
                    'reference' => $row[1],
                    'description' => $row[2],
                    'family_id' => $family->id,
                    'measurement_unit' => $row[5],
                    'brand_id' => $brand->id,
                    'cost' => $row[8],
                    'price' => $row[9],
                    'stock' => $row[10],
                ]
            );
        }
    }
}

class FamiliesSheetImport implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 2; // Saltar la fila de encabezados
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row[0])) {
                continue;
            }

            Family::updateOrCreate(
                ['code' => $row[0]],
                [
                    'name' => $row[1],
                    'active' => strtoupper($row[2]) === 'SI',
                    'matrix' => strtoupper($row[3]) === 'SI',
                ]
            );
        }
    }
}

class BrandsSheetImport implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 1; // Primera fila es encabezado, datos empiezan en fila 2
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row[0])) {
                continue;
            }

            Brand::updateOrCreate(
                ['code' => $row[0]],
                ['name' => $row[1]]
            );
        }
    }
}