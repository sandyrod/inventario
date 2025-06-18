<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Family;
use App\Models\Product;
use App\Models\Client;
use App\Models\Provider;
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
            'CLIENTES' => new ClientsSheetImport(),
            'PROVEEDORES' => new ProvidersSheetImport(),
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

                

                // 3. Procesar Producto (si existe dato en columna A)
                if (!empty($row[0])) {
                    $Family = Family::where('familycode',$this->getCalculatedValue($row[3] ?? null))->first();
                    $familyId = $Family->id;
                    $familyStockmin = $Family->stockmin;
                    $Brand = Brand::where('code',$this->getCalculatedValue($row[6] ?? null))->first();
                    $brandId = $Brand->id;
                    logger()->info("familia", [
                        'familia id: ' => $familyId,
                        'stockmin' => $Family->stockmin,
                        'familia' => $Family
                    ]);
                    $productData = [
                        'reference' => $this->getCalculatedValue($row[1] ?? null),
                        'description' => $this->getCalculatedValue($row[2] ?? null),
                        'family_id' => $familyId,
                        'unit_id' => 1, // Valor fijo
                        'brand_id' => $brandId,
                        'cost' => $this->parseNumber($row[8] ?? 0),
                        'price' => $this->parseNumber($row[9] ?? 0),
                        'stock' => $this->parseNumber($row[10] ?? 0),
                        'stockmin' => $familyStockmin
                    ];

                    Product::updateOrCreate(
                        ['productcode' => $this->getCalculatedValue($row[0])],
                        $productData
                    );

                    // logger()->info("Producto procesado", [
                    //     'fila' => $rowIndex + 2, // +2 porque startRow es 2
                    //     'codigo' => $this->getCalculatedValue($row[0]),
                    //     'data' => $productData
                    // ]);
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
                $ua = strtoupper($this->getCalculatedValue($row[2]));
                $matrix = trim(strtoupper($this->getCalculatedValue($row[3]))) == '' ? 'NO' : 'SI';
                $stockmin = $this->parseNumber($row[4] ?? 0);

                if (empty($code)) {
                    continue;
                }

                Family::updateOrCreate(
                    ['familycode' => $code],
                    [
                        'familyname' => $name ?? 'Sin nombre',
                        'UA' => $ua,
                        'matrix' => $matrix,
                        'stockmin' => $stockmin
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
    protected function parseNumber($value)
    {
        $value = $this->getCalculatedValue($value);
        return is_numeric($value) ? $value : 0;
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
                logger()->error("Error procesando marca : ".$e->getMessage());
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

class ClientsSheetImport implements ToCollection, WithStartRow, WithCalculatedFormulas
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
                 logger()->info("Producto procesado", [
                         'codigo' => $this->getCalculatedValue($row[0]),
                     ]);
                // Obtener valores calculados
                $clientCode = $this->getCalculatedValue($row[0]);
                $clientName = $this->getCalculatedValue($row[1] ?? null);
                $clientAddress = $this->getCalculatedValue($row[2] ?? null);
                $clientPhone = $this->getCalculatedValue($row[3] ?? null);
                $clientEmail = $this->getCalculatedValue($row[4] ?? null);

                // Validar que el código no sea nulo
                if (empty($clientCode)) {
                    continue;
                }

                // Buscar o crear la marca
                Client::updateOrCreate(
                    ['code' => $clientCode], // Campo de búsqueda
                    [ // Datos a actualizar/crear
                        'name' => $clientName ?? 'Sin nombre',
                        'address' => $clientAddress ,
                        'phone' => $clientPhone,
                        'email' =>  $clientEmail,
                        
                    ]
                );

            } catch (\Exception $e) {
                logger()->error("Error procesando Cliente: ".$e->getMessage());
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

class ProvidersSheetImport implements ToCollection, WithStartRow, WithCalculatedFormulas
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
                 logger()->info("Producto procesado", [
                         'codigo' => $this->getCalculatedValue($row[0]),
                     ]);
                // Obtener valores calculados
                $clientCode = $this->getCalculatedValue($row[0]);
                $clientName = $this->getCalculatedValue($row[1] ?? null);
                $clientAddress = $this->getCalculatedValue($row[2] ?? null);
                $clientPhone = $this->getCalculatedValue($row[3] ?? null);
                $clientEmail = $this->getCalculatedValue($row[4] ?? null);

                // Validar que el código no sea nulo
                if (empty($clientCode)) {
                    continue;
                }

                // Buscar o crear la marca
                Provider::updateOrCreate(
                    ['code' => $clientCode], // Campo de búsqueda
                    [ // Datos a actualizar/crear
                        'name' => $clientName ?? 'Sin nombre',
                        'address' => $clientAddress ,
                        'phone' => $clientPhone,
                        'email' =>  $clientEmail,
                        
                    ]
                );

            } catch (\Exception $e) {
                logger()->error("Error procesando Proveedor: ".$e->getMessage());
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