import { Link } from '@inertiajs/react'; 
 
interface Profesor{
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
 
interface Props { 
    materias: Materia[]; 
} 
 
export default function Index({ materias }: Props) { 
    return ( 
        <div> 
            <h1>Materias</h1> 
        
            <table> 
                <thead> 
                    <tr> 
                        <th>Código</th> 
                        <th>Nombre</th> 
                        <th>Profesores</th> 
                    </tr> 
                </thead> 
 
                <tbody> 
                    {materias.map((materia) => ( 
                        <tr key={materia.id}> 
                            <td>{materia.codigo}</td> 
                            <td>{materia.nombre}</td> 
                            <td>
                                {materia.profesores && materia.profesores.length > 0
                                ? materia.profesores.map((p) => `${p.apellido} (${p.dni})`).join(', ') : 'Sin profesores'}
                            </td> 
                        </tr> 
                    ))} 
                </tbody> 
            </table> 
        </div> 
    ); 
}