
<p>Estimad@, {{ $data['name'] }}, se han actualizado los datos de la siguente Carrera </p> 

<p>Carrera {{ $data['carrera_nombre'] }}</p>

<p>Codigo de Carrera {{ $data['carrera_codigo'] }}</p>

<p>Estado de Carrera {{ $data['carrera_estado'] === '1' ? "Activo" : "Desactivado" }}</p>

<p>Email {{ $data['user_email'] }}.</p> 

<p>Si no ha realizado este cambio, contacte al administrador del sistema.</p> 