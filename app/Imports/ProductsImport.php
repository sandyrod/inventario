<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Family;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ProductsImport implements WithMultipleSheets, WithCalculatedFormulas
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

class InventorySheetImport implements ToCollection, WithStartRow, WithCalculatedFormulas
{
    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $rowIndex => $row) {
            try {
                // Validación más flexible para permitir procesar productos
                if (empty($row[0]) && empty($row[3]) && empty($row[6])) {
                    continue; // Saltar filas completamente vacías
                }

                $familyId = null;
                $brandId = null;

                // 1. Procesar Familia (si existe dato en columna D)
                if (!empty($row[3])) {
                    $familyCode = $this->getCalculatedValue($row[3]);
                    $familyName = $this->getCalculatedValue($row[4] ?? null);
                    $uaValue = $this->getCalculatedValue($row[5] ?? null) ?? 'Si';
                    $matrixValue = $this->getCalculatedValue($row[6] ?? null) ?? 'No';

                    if (!empty($familyCode)) {
                        $family = Family::updateOrCreate(
                            ['familycode' => $familyCode],
                            [
                                'familyname' => $familyName ?? 'Sin nombre',
                                'UA' => strtoupper($uaValue) === 'SI' ? 'Si' : 'No',
                                'matrix' => strtoupper($matrixValue) === 'SI' ? 'Si' : 'No',
                                'active' => true
                            ]
                        );
                        $familyId = $family->id;
                    }
                }

                // 2. Procesar Marca (si existe dato en columna G)
                if (!empty($row[6])) {
                    $brandCode = $this->getCalculatedValue($row[6]);
                    $brandName = $this->getCalculatedValue($row[7] ?? null);

                    if (!empty($brandCode)) {
                        $brand = Brand::updateOrCreate(
                            ['code' => $brandCode],
                            ['description' => $brandName ?? 'Sin nombre']
                        );
                        $brandId = $brand->id;
                    }
                }

                // 3. Procesar Producto (si existe dato en columna A)
                if (!empty($row[0])) {
                    $productData = [
                        'reference' => $this->getCalculatedValue($row[1] ?? null),
                        'description' => $this->getCalculatedValue($row[2] ?? null),
                        'family_id' => $familyId,
                        'unit_id' => 1, // Valor fijo
                        'brand_id' => $brandId,
                        'cost' => $this->parseNumber($row[8] ?? 0),
                        'price' => $this->parseNumber($row[9] ?? 0),
                        'stock' => $this->parseNumber($row[10] ?? 0),
                        'stonkmin' => 0
                    ];

                    Product::updateOrCreate(
                        ['productcode' => $this->getCalculatedValue($row[0])],
                        $productData
                    );

                    logger()->info("Producto procesado", [
                        'fila' => $rowIndex + 2, // +2 porque startRow es 2
                        'codigo' => $this->getCalculatedValue($row[0]),
                        'data' => $productData
                    ]);
                }

            } catch (\Exception $e) {
                logger()->error("Error en fila ".($rowIndex + 2).": ".$e->getMessage());
                continue;
            }
        }
    }

    protected function getCalculatedValue($cell)
    {
        if (is_object($cell) && method_exists($cell, 'getCalculatedValue')) {
            return $cell->getCalculatedValue();
        }
        return $cell;
    }

    protected function parseNumber($value)
    {
        $value = $this->getCalculatedValue($value);
        return is_numeric($value) ? $value : 0;
    }
}

class FamiliesSheetImport implements ToCollection, WithStartRow, WithCalculatedFormulas
{
    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                if (empty($row[0])) {
                    continue;
                }

                $code = $this->getCalculatedValue($row[0]);
                $name = $this->getCalculatedValue($row[1] ?? null);
                $active = isset($row[2]) ? strtoupper($this->getCalculatedValue($row[2])) === 'SI' : 'Si';
                $matrix = isset($row[3]) ? strtoupper($this->getCalculatedValue($row[3])) === 'SI' : 'No';
                $ua = isset($row[4]) ? strtoupper($this->getCalculatedValue($row[4])) === 'SI' ? 'Si' : 'No' : 'Si';

                if (empty($code)) {
                    continue;
                }

                Family::updateOrCreate(
                    ['familycode' => $code],
                    [
                        'familyname' => $name ?? 'Sin nombre',
                        'active' => $active,
                        'matrix' => $matrix == 1 ? 'Si' : 'No',
                        'UA' => $ua
                    ]
                );

            } catch (\Exception $e) {
                logger()->error("Error procesando familia: ".$e->getMessage());
                continue;
            }
        }
    }

    protected function getCalculatedValue($cell)
    {
        if (is_object($cell) && method_exists($cell, 'getCalculatedValue')) {
            return $cell->getCalculatedValue();
        }
        return $cell;
    }
}

class BrandsSheetImport implements ToCollection, WithStartRow, WithCalculatedFormulas
{
    public function startRow(): int
    {
        return 2; // Primera fila es encabezado, datos empiezan en fila 2
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Validación básica - verifica que tenga código
                if (empty($row[0])) {
                    continue;
                }

                // Obtener valores calculados
                $brandCode = $this->getCalculatedValue($row[0]);
                $brandName = $this->getCalculatedValue($row[1] ?? null);
                
                // Validar que el código no sea nulo
                if (empty($brandCode)) {
                    continue;
                }

                // Buscar o crear la marca
                Brand::updateOrCreate(
                    ['code' => $brandCode], // Campo de búsqueda
                    [ // Datos a actualizar/crear
                        'description' => $brandName ?? 'Sin nombre',
                        'active' => true // Agrega este campo si tu modelo lo requiere
                    ]
                );

            } catch (\Exception $e) {
                logger()->error("Error procesando marca (Fila: ".$row->getIndex()."): ".$e->getMessage());
                continue;
            }
        }
    }

    protected function getCalculatedValue($cell)
    {
        if (is_object($cell) && method_exists($cell, 'getCalculatedValue')) {
            return $cell->getCalculatedValue();
        }
        return $cell;
    }
}