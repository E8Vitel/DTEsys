import { usePage } from '@inertiajs/react';

export default function useAuthorization() {
    const { auth } = usePage().props;
    const permissions = auth?.permissions ?? [];
    const roles = auth?.roles ?? [];
    const isAdmin = roles.includes('admin');

    const can = (permission) => isAdmin || permissions.includes(permission);

    return {
        permissions,
        roles,
        isAdmin,
        can,
    };
}
