// import { Link } from '@inertiajs/react';
import FlashMessage from '@/components/FlashMessage';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Link, router } from '@inertiajs/react';
//import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@radix-ui/react-select';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    ChevronLeft,
    ChevronRight,
    Eye,
    FileSpreadsheet,
    Pencil,
    Plus,
    Search,
    Trash2,
    //Table as TableIcon,
} from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { useAuth } from '@/hooks/use-auth'; //importamos el hook personalizado para verificar permisos

interface Filtros {
    buscar: string;
    materia: string;
    orden: string;
    direccion: string;
    por_pagina: number;
}

interface Profesor {
    id: number;
    dni: string;
    nombre: string;
    apellido: string;
}

interface Materia {
    id: number;
    codigo: string;
    nombre: string;
    profesores?: Profesor[];
}

interface Carrera {
    id: number;
    codigo: string;
    nombre: string;
    estado: boolean;
    materias: Materia[];
}

interface CarrerasPaginadas {
    data: Carrera[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    carreras: CarrerasPaginadas;
    materias: Materia[];
    filtros: Filtros;
    flash?: {
        success?: string;
    };
}

export default function Index({ carreras, materias, filtros, flash }: Props) {
    const [buscar, setBuscar] = useState(filtros?.buscar ?? '');
    const [materia, setMateria] = useState(filtros?.materia ?? '');

    const [orden, setOrden] = useState(filtros.orden ?? 'nombre');

    const [porPagina, setPorPagina] = useState(filtros.por_pagina ?? 5);

    const [direccion, setDireccion] = useState(filtros.direccion ?? 'asc');

    const { can } = useAuth(); //Inicializamos la función 'can' para verificar permisos

    const handleExportExcel = () => {
        // Creamos la query string basándonos en los filtros actuales de la vista
        // Ignoramos 'por_pagina' porque el Excel debe bajar todas las filas filtradas, no solo 5 o 10
        const params = new URLSearchParams({
            buscar: filtros.buscar || '',
            materia: filtros.materia || '',
            orden: filtros.orden || 'nombre',
            direccion: filtros.direccion || 'asc',
        }).toString();

        // Redirección nativa para iniciar la descarga del binario
        window.location.href = `/carreras/export?${params}`;
    };

    /*
    const indicadorOrden = (campo: string) => {
        if (orden !== campo) {
            return '';
        }

        return direccion === 'asc' ? ' ↑' : ' ↓';
    };
    */

    const ordenarPor = (campo: string) => {
        let nuevaDireccion = 'asc';

        if (orden === campo && direccion === 'asc') {
            nuevaDireccion = 'desc';
        }

        setOrden(campo);
        setDireccion(nuevaDireccion);

        router.get('/carreras', {
            buscar,
            materia,
            orden: campo,
            direccion: nuevaDireccion,
            por_pagina: porPagina,
        });
    };

    const aplicarFiltros = (e: FormEvent) => {
        e.preventDefault();

        router.get('/carreras', {
            buscar,
            materia,
            orden,
            direccion,
            por_pagina: porPagina,
        });
    };

    const eliminar = (id: number) => {
        if (confirm('¿Está seguro de eliminar esta carrera?')) {
            router.delete(`/carreras/${id}`);
        }
    };

    return (
        <div className="mx-auto min-h-screen max-w-6xl bg-gray-50 p-6">
            {/* Encabezado Principal */}
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Carreras
                    </h1>
                    <p className="mt-1 text-sm text-gray-500">
                        Gestión y listado de las carreras del sistema.
                    </p>
                </div>

                {/*Ocultar botón de Crear Carrera */} 
                {can('crear carreras') && ( 
                <Button asChild className="bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm"> 
                    <Link href="/carreras/create"> 
                        <Plus className="w-4 h-4 mr-2" /> 
                        Nueva carrera 
                    </Link> 
                </Button>)}
                
                {/*  
                <Button
                    asChild
                    className="bg-indigo-600 text-white shadow-sm hover:bg-indigo-700"
                >
                    <Link href="/carreras/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Nueva carrera
                    </Link>
                </Button>
                */}
            </div>

            {flash?.success && <div>{flash.success}</div>}

            {/* SECCIÓN DE FILTROS */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <form
                    onSubmit={aplicarFiltros}
                    className="flex flex-col items-end gap-4 md:flex-row"
                >
                    {/* Filtro: Texto Libre */}
                    <div className="w-full md:flex-1">
                        <label className="mb-1.5 block text-xs font-semibold tracking-wider text-gray-600 uppercase">
                            Buscar por término
                        </label>
                        <div className="relative">
                            <Search className="pointer-events-none absolute inset-y-0 left-3 my-auto h-4 w-4 text-gray-400" />
                            <Input
                                type="text"
                                value={buscar}
                                onChange={(e) => setBuscar(e.target.value)}
                                placeholder="Escribe el nombre o código..."
                                className="border-gray-300 bg-gray-50 pl-9 text-gray-900 focus-visible:border-indigo-500 focus-visible:ring-indigo-500/20"
                            />
                        </div>
                    </div>

                    {/* Filtro: Selector de Materia */}
                    <div className="w-full md:w-64">
                        <label className="mb-1.5 block text-xs font-semibold tracking-wider text-gray-600 uppercase">
                            Filtrar por Materia
                        </label>
                        <Select
                            value={materia}
                            onValueChange={(value) =>
                                setMateria(value === 'all' ? '' : value)
                            }
                        >
                            <SelectTrigger className="w-full border-gray-300 bg-gray-50 text-gray-900">
                                <SelectValue placeholder="Todas las materias" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Todas las materias
                                </SelectItem>
                                {materias.map((m) => (
                                    <SelectItem key={m.id} value={String(m.id)}>
                                        {m.codigo} - {m.nombre}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {/* Filtro: Selector de Filas por Página */}
                    <div className="w-full md:w-32">
                        <label className="mb-1.5 block text-xs font-semibold tracking-wider text-gray-600 uppercase">
                            Mostrar
                        </label>
                        <Select
                            value={String(porPagina)}
                            onValueChange={(value) =>
                                setPorPagina(Number(value))
                            }
                        >
                            <SelectTrigger className="w-full border-gray-300 bg-gray-50 text-gray-900">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="5">5 filas</SelectItem>
                                <SelectItem value="10">10 filas</SelectItem>
                                <SelectItem value="20">20 filas</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    {/* Botón Exportar */}
                    <div className="flex w-full items-center gap-2 md:w-auto">
                        <Button
                            type="button"
                            onClick={handleExportExcel}
                            className="w-full bg-emerald-600 text-white shadow hover:bg-emerald-500 md:w-auto"
                        >
                            <FileSpreadsheet className="mr-2 h-4 w-4" />
                            Exportar a Excel
                        </Button>
                    </div>
                </form>
            </div>
            {/* Reemplazo estructural de la tabla */}
            {/* Contenedor de la Tabla */}
            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <FlashMessage />
                    <Table>
                        <TableHeader className="bg-gray-50">
                            <TableRow>
                                {/* Columna 1: Código */}
                                <TableHead
                                    onClick={() => ordenarPor('codigo')}
                                    className="cursor-pointer px-6 py-3.5 text-xs font-semibold tracking-wider text-gray-500 uppercase select-none"
                                >
                                    <div className="flex items-center gap-1.5">
                                        <span>Código</span>
                                        {/* Evaluamos qué icono mostrar según el estado actual */}
                                        {orden !== 'codigo' ? (
                                            // Si no se está ordenando por este campo, mostramos el icono neutral
                                            <ArrowUpDown className="h-3.5 w-3.5 text-gray-400" />
                                        ) : direccion === 'asc' ? (
                                            // Si está en ascendente, flecha arriba en un color más oscuro/activo
                                            <ArrowUp className="h-3.5 w-3.5 font-bold text-gray-700" />
                                        ) : (
                                            // Si está en descendente, flecha abajo activa
                                            <ArrowDown className="h-3.5 w-3.5 font-bold text-gray-700" />
                                        )}
                                    </div>
                                </TableHead>

                                {/* Columna 2: Nombre */}
                                <TableHead className="px-6 py-3.5 text-xs font-semibold tracking-wider text-gray-500 uppercase">
                                    Nombre
                                </TableHead>

                                {/* Columna 3: Materias */}
                                <TableHead className="px-6 py-3.5 text-xs font-semibold tracking-wider text-gray-500 uppercase">
                                    Materias
                                </TableHead>

                                {/* Columna 4: Estado */}
                                <TableHead className="px-6 py-3.5 text-xs font-semibold tracking-wider text-gray-500 uppercase">
                                    Estado
                                </TableHead>

                                {/* Columna 5: Acciones */}
                                <TableHead className="px-6 py-3.5 text-right text-xs font-semibold tracking-wider text-gray-500 uppercase">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        {/* ... demás cabeceras ... */}
                        <TableBody>
                            {carreras.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="h-24 text-center text-sm text-muted-foreground"
                                    >
                                        No hay carreras registradas en este
                                        momento.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                carreras.data.map((carrera) => (
                                    <TableRow
                                        key={carrera.id}
                                        className="transition-colors"
                                    >
                                        {/* Código */}
                                        <TableCell className="px-6 py-4 font-mono text-xs text-muted-foreground">
                                            {carrera.codigo}
                                        </TableCell>

                                        {/* Nombre */}
                                        <TableCell className="px-6 py-4 font-medium text-foreground">
                                            {carrera.nombre}
                                        </TableCell>

                                        {/* Materias */}
                                        <TableCell className="max-w-[250px] truncate px-6 py-4 text-sm text-muted-foreground">
                                            {carrera.materias
                                                .map(
                                                    (materia) => materia.codigo,
                                                )
                                                .join(', ')}
                                        </TableCell>

                                        {/* Estado */}
                                        <TableCell className="px-6 py-4">
                                            <Badge
                                                variant={
                                                    carrera.estado
                                                        ? 'outline'
                                                        : 'destructive'
                                                } //outline era antes sucess
                                                className={`gap-1.5 font-medium shadow-none ${carrera.estado ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-50' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-50'}`}
                                            >
                                                <span
                                                    className={`h-1.5 w-1.5 rounded-full ${carrera.estado ? 'bg-emerald-500' : 'bg-red-500'}`}
                                                />
                                                {carrera.estado
                                                    ? 'Activa'
                                                    : 'Inactiva'}
                                            </Badge>
                                        </TableCell>

                                        {/* Acciones */}
                                        <TableCell className="px-6 py-4 text-right whitespace-nowrap">
                                            <div className="flex items-center justify-end gap-1.5">
                                                {/* Botón Ver */}
                                                {/* Ocultar botones de acción por fila */} 
                                                {can('ver carreras') && (    
                                                    <Button 
                                                        asChild 
                                                        variant="ghost" 
                                                        size="sm" 
                                                        className="h-8 text-slate-600 hover:text-slate-900" 
                                                        title="Ver detalles de la carrera" 
                                                    > 
                                                        <Link href={`/carreras/${carrera.id}`}> 
                                                            <Eye className="w-4 h-4 mr-1.5" /> 
                                                            Ver 
                                                        </Link> 
                                                    </Button>
                                                )} 
                                                {/* ...Y aplicamos la misma lógica para el resto de las acciones */}
                                                {/* 
                                                <Button
                                                    asChild
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-8 text-slate-600 hover:text-slate-900"
                                                    title="Ver detalles de la carrera"
                                                >
                                                    <Link
                                                        href={`/carreras/${carrera.id}`}
                                                    >
                                                        <Eye className="mr-1.5 h-4 w-4" />
                                                        Ver
                                                    </Link>
                                                </Button>
                                                */}

                                                {/* Botón Editar */}
                                                <Button
                                                    asChild
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-8 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900"
                                                    title="Editar carrera"
                                                >
                                                    <Link
                                                        href={`/carreras/${carrera.id}/edit`}
                                                    >
                                                        <Pencil className="mr-1.5 h-4 w-4" />
                                                        Editar
                                                    </Link>
                                                </Button>

                                                {/* Botón Eliminar */}
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        eliminar(carrera.id)
                                                    }
                                                    className="h-8 text-red-600 hover:bg-red-50 hover:text-red-900"
                                                    title="Eliminar carrera"
                                                >
                                                    <Trash2 className="mr-1.5 h-4 w-4" />
                                                    Eliminar
                                                </Button>

                                                {/* Agregar PDF */}
                                                <a
                                                    href={`/carreras/${carrera.id}/plan`}
                                                    title="Descargar plan de estudio"
                                                >
                                                    Plan
                                                </a>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>

            {/* PIE DE PÁGINA: CONTROL DE PAGINACIÓN ESTILIZADO */}
            <div className="flex flex-col items-center justify-between gap-4 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row">
                {/* Información de Registros */}
                <p className="text-center text-sm text-muted-foreground sm:text-left">
                    Mostrando{' '}
                    <span className="font-semibold text-foreground">
                        {carreras.from ?? 0}
                    </span>{' '}
                    a{' '}
                    <span className="font-semibold text-foreground">
                        {carreras.to ?? 0}
                    </span>{' '}
                    de{' '}
                    <span className="font-semibold text-foreground">
                        {carreras.total}
                    </span>{' '}
                    carreras
                </p>

                {/* Botones de Navegación */}
                <div className="flex items-center gap-4">
                    {/* Botón Anterior */}
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={!carreras.prev_page_url}
                        onClick={() => router.get(carreras.prev_page_url!)}
                        className="bg-white text-gray-700 shadow-xs hover:bg-gray-50"
                    >
                        <ChevronLeft className="mr-1 h-4 w-4" />
                        Anterior
                    </Button>

                    {/* Indicador de Página Actual */}
                    <span className="rounded-lg border border-gray-200/60 bg-gray-100/80 px-3 py-1.5 text-sm font-medium text-muted-foreground">
                        Página{' '}
                        <span className="font-semibold text-foreground">
                            {carreras.current_page}
                        </span>{' '}
                        de{' '}
                        <span className="font-semibold text-foreground">
                            {carreras.last_page}
                        </span>
                    </span>

                    {/* Botón Siguiente */}
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={!carreras.next_page_url}
                        onClick={() => router.get(carreras.next_page_url!)}
                        className="bg-white text-gray-700 shadow-xs hover:bg-gray-50"
                    >
                        Siguiente
                        <ChevronRight className="ml-1 h-4 w-4" />
                    </Button>
                </div>
            </div>
            {/* Fin contenedor de Página */}
        </div>
    );
}
