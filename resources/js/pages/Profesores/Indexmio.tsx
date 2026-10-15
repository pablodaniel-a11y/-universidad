import { Link } from '@inertiajs/react';

interface Profesor {
    id: number;
    dni: string;
    nombre: string;
    apellido: string;
    materias?: Materia[];
}

interface Materia {
    id: number;
    codigo: string;
    nombre: string;
}

interface Props {
    profesores: Profesor[];
}

export default function Index({ profesores }: Props) {
    return (
        <div>
            <h1>Profesores</h1>

            <table>
                <thead>
                    <tr>
                        <th>dni</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Materias</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    {profesores.map((profesor) => (
                        <tr key={profesor.id}>
                            <td>{profesor.dni}</td>
                            <td>{profesor.nombre}</td>
                            <td>{profesor.apellido}</td>
                            <td>
                                {profesor.materias &&
                                profesor.materias.length > 0
                                    ? profesor.materias
                                          .map(
                                              (m) =>
                                                  `${m.codigo} (${m.nombre})`,
                                          )
                                          .join(', ')
                                    : 'Sin Materias Asignadas'}
                            </td>
                            <td>
                                <Link href={`/profesores/${profesor.id}/edit`}>
                                    Editar
                                </Link>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
