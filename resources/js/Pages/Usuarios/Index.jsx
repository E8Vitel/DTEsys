import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useAppDialog } from '@/Components/AppDialog';
import { Head, router } from '@inertiajs/react';

export default function Index({ usuarios, roles, permisos }) {
    // Dialogos reutilizables para confirmar operaciones sensibles.
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
            message: `Se quitara el rol ${role} al usuario.`,
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
            message: `Se quitara el permiso ${permission} al usuario.`,
            confirmText: 'Quitar',
        });

        if (confirmado) {
            router.post(route('usuarios.quitar-permiso', userId), { permission });
        }
    };

    // Renderiza roles como etiquetas compactas.
    const renderChips = (items, bgClass, textClass, emptyText) => {
        if (!items || items.length === 0) {
            return <span className="text-xs font-bold text-amber-400">{emptyText}</span>;
        }

        return items.map((item) => (
            <span
                key={item.id}
                className={`mr-2 mb-2 inline-block rounded-full px-3 py-1 text-xs font-black uppercase tracking-[0.1em] ${bgClass} ${textClass}`}
            >
                {item.name}
            </span>
        ));
    };

    // Muestra el conjunto efectivo de permisos de cada usuario.
    const renderPermissionList = (items) => {
        if (!items || items.length === 0) {
            return <span className="text-xs font-bold text-amber-400">Sin permisos asignados</span>;
        }

        return (
            <ul className="space-y-2">
                {items.map((name) => (
                    <li key={name} className="text-sm font-bold text-amber-700">
                        {name}
                    </li>
                ))}
            </ul>
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
                    <div>
                        <div className="inline-flex rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-black uppercase tracking-[0.3em] text-amber-500">
                            Administracion
                        </div>
                        <h2 className="mt-5 text-4xl font-black leading-tight text-amber-700">
                            Controla usuarios, roles y permisos desde una sola consola.
                        </h2>
                        <p className="mt-4 max-w-2xl text-base leading-7 text-amber-600">
                            Revisa accesos efectivos y administra permisos directos con una vista
                            compacta y de lectura rapida.
                        </p>
                    </div>
                </div>
            }
        >
            <Head title="Usuarios" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <section className="overflow-hidden rounded-[2rem] border border-amber-200 bg-white shadow-[0_24px_60px_rgba(245,158,11,0.12)]">
                        <div className="flex items-center justify-between border-b border-amber-200 bg-gradient-to-r from-white via-amber-50 to-white px-6 py-5">
                            <div>
                                <p className="text-xs font-black uppercase tracking-[0.28em] text-amber-400">
                                    Matriz de acceso
                                </p>
                                <h3 className="mt-2 text-2xl font-black text-amber-700">
                                    Gestion de usuarios y privilegios
                                </h3>
                            </div>
                            <div className="rounded-full bg-amber-50 px-4 py-2 text-sm font-black uppercase tracking-[0.16em] text-amber-600">
                                {usuarios.length} usuarios
                            </div>
                        </div>

                        <div className="overflow-x-auto p-6">
                            <table className="min-w-full divide-y divide-amber-200">
                                <thead>
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                            Usuario
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                            Roles
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                            Permisos vigentes
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                            Gestion de roles
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                            Gestion de permisos
                                        </th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-amber-100">
                                    {usuarios.map((user) => {
                                        const permisosDirectos = new Set(
                                            (user.permissions ?? []).map((p) => p.name),
                                        );
                                        const permisosEfectivos = new Set(user.all_permissions ?? []);

                                        return (
                                            <tr key={user.id} className="align-top hover:bg-amber-50">
                                                <td className="px-4 py-5">
                                                    <p className="text-base font-black text-amber-700">
                                                        {user.name}
                                                    </p>
                                                    <p className="mt-2 text-sm font-bold text-amber-500">
                                                        {user.email}
                                                    </p>
                                                </td>
                                                <td className="px-4 py-5">
                                                    {renderChips(
                                                        user.roles,
                                                        'bg-amber-100',
                                                        'text-amber-700',
                                                        'Sin roles',
                                                    )}
                                                </td>
                                                <td className="px-4 py-5">
                                                    {renderPermissionList(user.all_permissions)}
                                                </td>
                                                <td className="px-4 py-5 text-right text-sm">
                                                    {roles.map((role) => {
                                                        const tieneRol = user.roles.some(
                                                            (r) => r.name === role.name,
                                                        );

                                                        return (
                                                            <div key={role.id} className="mb-2">
                                                                {tieneRol ? (
                                                                    <button
                                                                        onClick={() =>
                                                                            handleQuitarRol(user.id, role.name)
                                                                        }
                                                                        className="text-sm font-black uppercase tracking-[0.12em] text-amber-500 hover:text-amber-700"
                                                                    >
                                                                        Quitar {role.name}
                                                                    </button>
                                                                ) : (
                                                                    <button
                                                                        onClick={() =>
                                                                            handleAsignarRol(user.id, role.name)
                                                                        }
                                                                        className="text-sm font-black uppercase tracking-[0.12em] text-amber-700 hover:text-amber-500"
                                                                    >
                                                                        Asignar {role.name}
                                                                    </button>
                                                                )}
                                                            </div>
                                                        );
                                                    })}
                                                </td>
                                                <td className="px-4 py-5 text-sm">
                                                    <ul className="space-y-3">
                                                        {permisos.map((permiso) => {
                                                            const tieneDirecto = permisosDirectos.has(permiso.name);
                                                            const tienePorRol =
                                                                !tieneDirecto &&
                                                                permisosEfectivos.has(permiso.name);

                                                            return (
                                                                <li
                                                                    key={permiso.id}
                                                                    className="flex items-center justify-between gap-3 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3"
                                                                >
                                                                    <span className="font-black text-amber-700">
                                                                        {permiso.name}
                                                                    </span>
                                                                    {tieneDirecto ? (
                                                                        <button
                                                                            onClick={() =>
                                                                                handleQuitarPermiso(
                                                                                    user.id,
                                                                                    permiso.name,
                                                                                )
                                                                            }
                                                                            className="text-xs font-black uppercase tracking-[0.12em] text-amber-500 hover:text-amber-700"
                                                                        >
                                                                            Quitar
                                                                        </button>
                                                                    ) : tienePorRol ? (
                                                                        <span className="rounded-full bg-white px-3 py-1 text-xs font-black uppercase tracking-[0.12em] text-amber-700">
                                                                            Por rol
                                                                        </span>
                                                                    ) : (
                                                                        <button
                                                                            onClick={() =>
                                                                                handleAsignarPermiso(
                                                                                    user.id,
                                                                                    permiso.name,
                                                                                )
                                                                            }
                                                                            className="text-xs font-black uppercase tracking-[0.12em] text-amber-700 hover:text-amber-500"
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
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
