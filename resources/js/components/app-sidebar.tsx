import { Link } from '@inertiajs/react';
import { BookOpen, FolderGit2, GraduationCap, LayoutGrid, Shield, ShieldHalf, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
import { useAuth } from '@/hooks/use-auth'; //  Importar el hook personalizado

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid, //  Sin la propiedad 'permission', se muestra a todos
    },
    {
        title: 'Usuarios',
        href: '/users',
        icon: Users,
        permission: 'listar usuarios', //  Requiere este permiso 
    },
    {
        title: 'Carreras',
        href: '/carreras',
        icon: GraduationCap,
        permission: 'listar carreras', //  Requiere este permiso
    },
    {
        title: 'Roles',
        href: '/roles',
        icon: Shield,
        permission: 'listar roles', //  Requiere este permiso
    },
    {
        title: 'Permisos',
        href: '/permissions',
        icon: ShieldHalf,
        permission: 'listar permisos', //  Requiere este permiso
    },
    
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {

    const { can } = useAuth(); // Inicializa la función 'can' 
    
    // Filtra los elementos de navegación en tiempo real 
    const filteredNavItems = mainNavItems.filter((item) => { 
        // Si el ítem no tiene la propiedad 'permission', se muestra a todos (ej: Dashboard). 
        // Si la tiene, evalúa si el usuario tiene el permiso asignado en Spatie. 
        return !item.permission || can(item.permission); 
    });

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>
            
            <SidebarContent> 
                {/* Le pasamos el array filtrado en lugar del original estático */} 
                <NavMain items={filteredNavItems} /> 
            </SidebarContent> 

            {/*
            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>
            */}
            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
