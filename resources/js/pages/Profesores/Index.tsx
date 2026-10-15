import { Head, Link, router } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Users, CheckCircle2, XCircle, BookOpen } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface Profesor {
    id: number;
    dni: string;
    apellido: string;
    nombre: string;
    email: string;
    estado: boolean;
    foto_url?: string; // 1. Agregado el campo de la URL
    materias?: Materia[];
}

interface Materia {
    id: number;
    codigo: string;
    nombre: string;
}

interface Props {
    profesores: Profesor[];
    flash?: {
        success?: string;
    };
}

export default function Index({ profesores, flash }: Props) {
    const eliminar = (id: number) => {
        if (confirm('¿Está seguro de eliminar este profesor?')) {
            router.delete(`/profesores/${id}`);
        }
    };

    return (
        <>
            <Head title="Cuerpo Docente" />

            <div className="flex-1 space-y-6 p-8 pt-6">
                {/* Header de la vista */}
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">Profesores</h2>
                        <p className="text-muted-foreground">
                            Gestión y administración del cuerpo docente universitario.
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/profesores/create" className="flex items-center gap-2">
                            <Plus className="h-4 w-4" />
                            Nuevo Profesor
                        </Link>
                    </Button>
                </div>

                {/* Mensaje Flash de éxito */}
                {flash?.success && (
                    <div className="flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400">
                        <CheckCircle2 className="h-4 w-4" />
                        <span>{flash.success}</span>
                    </div>
                )}

                {/* Tabla de Profesores */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <Users className="h-5 w-5 text-muted-foreground" />
                                    Docentes Registrados
                                </CardTitle>
                                <CardDescription>
                                    Listado general de profesores habilitados en el sistema
                                </CardDescription>
                            </div>
                            <Badge variant="secondary">Total: {profesores.length}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    {/* 2. Cabecera para la foto */}
                                    <TableHead className="w-[80px]">Foto</TableHead>
                                    <TableHead className="w-[140px]">DNI</TableHead>
                                    <TableHead>Apellido y Nombre</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead className="w-[120px]">Estado</TableHead>
                                    <TableHead className="w-[140px] text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {profesores.map((profesor) => (
                                    <TableRow key={profesor.id}>
                                        {/* 3. Renderizado de la foto o iniciales */}
                                        <TableCell>
                                            {profesor.foto_url ? (
                                                <img 
                                                    src={profesor.foto_url} 
                                                    alt={profesor.nombre} 
                                                    className="w-10 h-10 rounded-full object-cover border border-zinc-700" 
                                                />
                                            ) : (
                                                <div className="w-10 h-10 rounded-full bg-zinc-800 text-zinc-300 border border-zinc-700 flex items-center justify-center font-bold text-xs uppercase">
                                                    {profesor.nombre?.[0] ?? ''}{profesor.apellido?.[0] ?? ''}
                                                </div>
                                            )}
                                        </TableCell>

                                        <TableCell className="font-mono text-xs">{profesor.dni}</TableCell>
                                        <TableCell className="font-medium">
                                            {profesor.apellido}, {profesor.nombre}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground text-sm">
                                            {profesor.email}
                                        </TableCell>
                                        <TableCell>
                                            {profesor.estado ? (
                                                <Badge className="bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 border-emerald-500/20">
                                                    Activo
                                                </Badge>
                                            ) : (
                                                <Badge variant="outline" className="text-zinc-500 border-zinc-700">
                                                    Inactivo
                                                </Badge>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end items-center gap-1">
                                                <Button variant="ghost" size="icon" className="h-8 w-8 text-zinc-400 hover:text-white" asChild>
                                                    <Link href={`/profesores/${profesor.id}/edit`}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="h-8 w-8 text-red-400 hover:text-red-300 hover:bg-red-500/10"
                                                    onClick={() => eliminar(profesor.id)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>

                                        {/* Materias */}
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1 max-w-xs">
                                                {profesor.materias && profesor.materias.length > 0 ? (
                                                    profesor.materias.map((m) => (
                                                        <Badge key={m.id} variant="outline" className="text-xs bg-zinc-900/50 flex items-center gap-1">
                                                            <BookOpen className="w-3 h-3 text-blue-400" />
                                                            <span className="font-semibold">{m.codigo}</span>: {m.nombre}
                                                        </Badge>
                                                    ))
                                                ) : (
                                                    <span className="text-xs text-muted-foreground italic">Sin materias</span>
                                                )}
                                            </div>
                                        </TableCell>
                                        
                                    </TableRow>
                                ))}

                                {profesores.length === 0 && (
                                    <TableRow>
                                        {/* 4. colSpan ajustado a 6 */}
                                        <TableCell colSpan={6} className="h-32 text-center text-muted-foreground">
                                            No hay profesores registrados en el sistema.
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}