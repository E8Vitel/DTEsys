import { usePage } from '@inertiajs/react';

export default function useAuthorization() {
    // Lee del page props los roles y permisos expuestos por Laravel/Inertia.
    const { auth } = usePage().props;
    const permissions = auth?.permissions ?? [];
    const roles = auth?.roles ?? [];
    const isAdmin = roles.includes('admin');

    // Un administrador pasa cualquier validacion.
    // Para otros perfiles se valida contra la lista de permisos disponible.
    const can = (permission) => isAdmin || permissions.includes(permission);

    return {
        permissions,
        roles,
        isAdmin,
        can,
    };
}
