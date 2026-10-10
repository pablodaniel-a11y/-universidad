<?php

namespace App\Exports;

use App\Models\Carrera; 
use Illuminate\Database\Eloquent\Builder; 
use Maatwebsite\Excel\Concerns\FromQuery; 
use Maatwebsite\Excel\Concerns\Exportable; 
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithMapping; 

// 1. Importamos la interfaz WithEvents para poder manipular los estilos de la hoja
use Maatwebsite\Excel\Concerns\WithEvents;
// 2. Importamos la clase Event para manejar los eventos del ciclo de vida del archivo Excel
use Maatwebsite\Excel\Events\AfterSheet;
// 3. Importamos la clase Border para aplicar los bordes a las celdas
use PhpOffice\PhpSpreadsheet\Style\Border;

//use Illuminate\Support\Collection;
//use Maatwebsite\Excel\Concerns\FromCollection;

class CarrerasExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    use Exportable; 
    protected $filters;

    // Recibimos los filtros validados desde el controlador
    public function __construct(array $filters) 
    { 
        $this->filters = $filters; 
    }

    public function query() : Builder 
    { 
        // Reutilizamos la lógica de Eloquent sin ejecutar .get() o .paginate() 
        return Carrera::with('materias')
            ->when(($this->filters['buscar'] ?? '') !== '', function ($query) { 
                $query->where('nombre', 'like', "%{$this->filters['buscar']}%"); 
            }) 
            ->when(!empty($this->filters['materia']), function ($query) { 
                $query->whereHas('materias', function ($q) { 
                    $q->where('materias.id', $this->filters['materia']); 
                }); 
            }) 
            ->orderBy($this->filters['orden'], $this->filters['direccion']); 
    }

    // Definimos las columnas que tendrá el Excel 
    public function headings(): array 
    { 
        return [ 
            'Código', 
            'Nombre de la Carrera', 
            'Estado', 
            'Materias Asignadas' 
        ]; 
    } 

    // Mapeamos los datos de cada Carrera. 
    // $carrera ya trae cargadas sus materias gracias al Eager Loading (with) 
    public function map($carrera): array 
    { 
        //  Tarea 1: Traducimos el estado binario/número a texto legible ("Activa" o "Inactiva")
        $estadoTexto = $carrera->estado == 1 ? 'Activa' : 'Inactiva';


        // Transformamos la colección de materias a un string separado por comas 
        $materiasString = $carrera->materias->pluck('nombre')->implode(', '); 
        return [ 
            $carrera->codigo, 
            $carrera->nombre, 
            //$carrera->estado,
            $estadoTexto, // Usamos la variable traducida
            $materiasString, 
        ];
    }

    
    // Método obligatorio para implementar WithEvents: aquí aplicaremos el diseño avanzado
    public function registerEvents(): array
    {
        return[
            AfterSheet::class => function(AfterSheet $event) {
                // Obtenemos la hoja de cálculo actual
                $sheet = $event->sheet->getDelegate();
                
                // Calculamos cuántas filas de datos tenemos consultando la base de datos
                $totalFilas = $this->query()->count();
                // La última fila de datos (sumando la fila 1 que corresponde a los títulos/headings)
                $ultimaFila = $totalFilas + 1;

                // tarea 2: Títulos centrados y en negrita (Fila 1)
                $sheet->getStyle('A1:D1')->applyFromArray([
                    'font' => [
                        'bold' => true, // Ponemos el texto en negrita
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Centrado horizontal
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,   // Centrado vertical
                    ],
                ]);

                // tarea 3: Aplicar bordes a toda la tabla resultante
                $sheet->getStyle("A1:D{$ultimaFila}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN, // Estilo de línea fina
                            'color'       => ['argb' => 'FFCCCCCC'], // Color gris claro para los bordes internos/externos
                        ],
                    ],
                ]);

                // tarea 4: Autoajustar el ancho de las columnas automáticamente
                foreach (range('A', 'D') as $columna) {
                    $sheet->getColumnDimension($columna)->setAutoSize(true);
                }

                // tarea 1: Pintar la fila dependiendo de si está Activa o Inactiva
                // Recorremos fila por fila desde la fila 2 (datos) hasta la última fila
                for ($row = 2; $row <= $ultimaFila; $row++) {
                    // Leemos el valor de la columna Estado (que es la columna 3, es decir, 'C')
                    $estadoValor = $sheet->getCell("C{$row}")->getValue();

                    // Definimos el color según el estado (Códigos de color ARGB en Hexadecimal sin el #)
                    $colorFondo = '';
                    if ($estadoValor === 'Activa') {
                        $colorFondo = 'FFD4EDDA'; // Verde suave (Estilo Bootstrap success)
                    } else {
                        $colorFondo = 'FFF8D7DA'; // Rojo/Rosa suave (Estilo Bootstrap danger)
                    }

                    // Aplicamos el color de fondo a toda la fila actual (de la columna A a la D)
                    $sheet->getStyle("A{$row}:D{$row}")->getFill()->applyFromArray([
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => $colorFondo],
                    ]);
                }
            },
        ];

    }
}
