<!DOCTYPE html> 
<html lang="es"> 
<head> 
    <meta charset="UTF-8">
    <style> 
    body { 
        font-family: Arial, sans-serif; 
        font-size: 12px; 
    } 
 
    h1 { 
        text-align: center; 
    } 
 
    table { 
        width: 100%; 
        border-collapse: collapse; 
    } 
 
    th, td { 
        border: 1px solid #000; 
        padding: 6px; 
        text-align: left; 
    }
    @page {
        size: A4 landscape;
        margin: 20mm;
    }
    </style> 
    <title>Plan de estudio</title> 
</head> 
<body> 
 
    <h1>Plan de estudio</h1> 
    <h2>{{ $carrera->nombre }}</h2> 
 
    <table> 
        <thead> 
            <tr> 
                <th>Materia</th> 
                <th>Horas</th> 
            </tr> 
        </thead> 
 
        <tbody> 
            @foreach ($carrera->materias as $materia) 
                <tr> 
                    <td>{{ $materia->nombre }}</td> 
                    <td>{{ $materia->horas }}</td> 
                </tr> 
            @endforeach 
        </tbody> 
    </table> 
    <div style="margin-top: 20px;">
        <p><strong>Total de materias:</strong> {{ $carrera->materias->count() }}</p>
        <p><strong>Total de horas:</strong> {{ $carrera->materias->sum('horas') }}</p>
    </div>
    <p style="text-align: right; font-size: 10px;">
        Fecha de generación: {{ date('d/m/Y') }}
    </p>
 
</body> 
</html> 