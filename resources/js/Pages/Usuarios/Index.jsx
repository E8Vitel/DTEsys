import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Index({ usuarios, roles }) {
    const handleAsignar = (userId, role) => {
        if (confirm(`¿Asignar rol ${role} al usuario?`)) {
            router.post(route('usuarios.asignar-rol', userId), { role });
        }
    };

    const handleQuitar = (userId, role) => {
        if (confirm(`¿Quitar rol ${role} al usuario?`)) {
            router.post(route('usuarios.quitar-rol', userId), { role });
        }
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Gestión de Usuarios</h2>}>
            <Head title="Usuarios" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow sm:rounded-lg overflow-hidden">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {usuarios.map(user => (
                                    <tr key={user.id}>
                                        <td className="px-6 py-4 whitespace-nowrap">{user.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">{user.email}</td>
                                        <td className="px-6 py-4">
                                            {user.roles.map(role => (
                                                <span key={role.id} className="inline-block bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded mr-2">
                                                    {role.name}
                                                </span>
                                            ))}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            {roles.map(role => (
                                                <div key={role.id} className="inline-block mr-3">
                                                    {user.roles.some(r => r.name === role.name) ? (
                                                        <button
                                                            onClick={() => handleQuitar(user.id, role.name)}
                                                            className="text-red-600 hover:text-red-900"
                                                        >
                                                            Quitar {role.name}
                                                        </button>
                                                    ) : (
                                                        <button
                                                            onClick={() => handleAsignar(user.id, role.name)}
                                                            className="text-green-600 hover:text-green-900"
                                                        >
                                                            Asignar {role.name}
                                                        </button>
                                                    )}
                                                </div>
                                            ))}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}