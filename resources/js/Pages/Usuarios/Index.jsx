import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useAppDialog } from '@/Components/AppDialog';
import { Head, router } from '@inertiajs/react';

export default function Index({ usuarios, roles, permisos }) {
    const dialog = useAppDialog();

    const handleAsignarRol = async (userId, role) => {
        const confirmado = await dialog.confirm({
            type: 'info',
            title: 'Asignar rol',
            message: `Se asignara el rol ${role} al usuario.`,
            confirmText: 'Asignar',
        });

        if (confirmado) {
            router.post(route('usuarios.asignar-rol', userId), { role });
        }
    };

    const handleQuitarRol = async (userId, role) => {
        const confirmado = await dialog.confirm({
            type: 'warning',
            title: 'Quitar rol',
            message: `Se quitara el rol ${role} del usuario.`,
            confirmText: 'Quitar',
        });

        if (confirmado) {
            router.post(route('usuarios.quitar-rol', userId), { role });
        }
    };

    const handleAsignarPermiso = async (userId, permission) => {
        const confirmado = await dialog.confirm({
            type: 'info',
            title: 'Asignar permiso',
            message: `Se asignara el permiso ${permission} al usuario.`,
            confirmText: 'Asignar',
        });

        if (confirmado) {
            router.post(route('usuarios.asignar-permiso', userId), { permission });
        }
    };

    const handleQuitarPermiso = async (userId, permission) => {
        const confirmado = await dialog.confirm({
            type: 'warning',
            title: 'Quitar permiso',
            message: `Se quitara el permiso ${permission} del usuario.`,
            confirmText: 'Quitar',
        });

        if (confirmado) {
            router.post(route('usuarios.quitar-permiso', userId), { permission });
        }
    };

    const renderChips = (items, bgClass, textClass, emptyText) => {
        if (!items || items.length === 0) {
            return <span className="text-xs text-gray-400">{emptyText}</span>;
        }

        return items.map((item) => (
            <span
                key={item.id}
                className={`inline-block ${bgClass} ${textClass} text-xs px-2 py-1 rounded mr-2 mb-2`}
            >
                {item.name}
            </span>
        ));
    };

    const renderPermissionList = (items) => {
        if (!items || items.length === 0) {
            return <span className="text-xs text-gray-400">Sin permisos asignados</span>;
        }

        return (
            <ul className="space-y-1">
                {items.map((name) => (
                    <li key={name} className="text-sm text-gray-700">
                        - {name}
                    </li>
                ))}
            </ul>
        );
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Gestion de Usuarios</h2>}>
            <Head title="Usuarios" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow sm:rounded-lg overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles actuales</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permisos asignados</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gestion de roles</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gestion de permisos</th>
                                    </tr>
                                </thead>

                                <tbody className="bg-white divide-y divide-gray-200">
                                    {usuarios.map((user) => {
                                        const permisosDirectos = new Set((user.permissions ?? []).map((p) => p.name));
                                        const permisosEfectivos = new Set(user.all_permissions ?? []);

                                        return (
                                            <tr key={user.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">{user.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{user.email}</td>
                                                <td className="px-6 py-4">
                                                    {renderChips(user.roles, 'bg-indigo-100', 'text-indigo-800', 'Sin roles')}
                                                </td>
                                                <td className="px-6 py-4">
                                                    {renderPermissionList(user.all_permissions)}
                                                </td>
                                                <td className="px-6 py-4 text-right text-sm">
                                                    {roles.map((role) => {
                                                        const tieneRol = user.roles.some((r) => r.name === role.name);

                                                        return (
                                                            <div key={role.id} className="inline-block mr-3 mb-2">
                                                                {tieneRol ? (
                                                                    <button
                                                                        onClick={() => handleQuitarRol(user.id, role.name)}
                                                                        className="text-red-600 hover:text-red-900"
                                                                    >
                                                                        Quitar {role.name}
                                                                    </button>
                                                                ) : (
                                                                    <button
                                                                        onClick={() => handleAsignarRol(user.id, role.name)}
                                                                        className="text-green-600 hover:text-green-900"
                                                                    >
                                                                        Asignar {role.name}
                                                                    </button>
                                                                )}
                                                            </div>
                                                        );
                                                    })}
                                                </td>
                                                <td className="px-6 py-4 text-sm">
                                                    <ul className="space-y-2">
                                                        {permisos.map((permiso) => {
                                                            const tieneDirecto = permisosDirectos.has(permiso.name);
                                                            const tienePorRol = !tieneDirecto && permisosEfectivos.has(permiso.name);

                                                            return (
                                                                <li key={permiso.id} className="flex items-center justify-between gap-3">
                                                                    <span className="text-gray-700">{permiso.name}</span>
                                                                    {tieneDirecto ? (
                                                                        <button
                                                                            onClick={() => handleQuitarPermiso(user.id, permiso.name)}
                                                                            className="text-red-600 hover:text-red-900"
                                                                        >
                                                                            Quitar
                                                                        </button>
                                                                    ) : tienePorRol ? (
                                                                        <span className="text-xs px-2 py-1 rounded bg-blue-100 text-blue-700">
                                                                            Por rol
                                                                        </span>
                                                                    ) : (
                                                                        <button
                                                                            onClick={() => handleAsignarPermiso(user.id, permiso.name)}
                                                                            className="text-green-600 hover:text-green-900"
                                                                        >
                                                                            Asignar
                                                                        </button>
                                                                    )}
                                                                </li>
                                                            );
                                                        })}
                                                    </ul>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}