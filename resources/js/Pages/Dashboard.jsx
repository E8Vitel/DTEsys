import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { FileText, Users, DollarSign, Plus, BarChart3 } from 'lucide-react';

export default function Dashboard({ stats, ultimosDtes }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Dashboard DTEsys
                    </h2>
                    <Link
                        href={route('dtes.create')}
                        className="bg-indigo-600 text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-indigo-700"
                    >
                        <Plus size={18} /> Nuevo DTE
                    </Link>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white p-6 rounded-xl shadow">
                            <div className="flex items-center gap-4">
                                <FileText className="w-10 h-10 text-blue-600" />
                                <div>
                                    <p className="text-sm text-gray-600">DTEs Emitidos</p>
                                    <p className="text-3xl font-bold">{stats.total_dtes}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white p-6 rounded-xl shadow">
                            <div className="flex items-center gap-4">
                                <DollarSign className="w-10 h-10 text-green-600" />
                                <div>
                                    <p className="text-sm text-gray-600">Monto Total</p>
                                    <p className="text-3xl font-bold">${Number(stats.monto_total).toLocaleString('es-CL')}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white p-6 rounded-xl shadow">
                            <div className="flex items-center gap-4">
                                <BarChart3 className="w-10 h-10 text-purple-600" />
                                <div>
                                    <p className="text-sm text-gray-600">Firmados</p>
                                    <p className="text-3xl font-bold">{stats.firmados}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white p-6 rounded-xl shadow">
                            <div className="flex items-center gap-4">
                                <Users className="w-10 h-10 text-orange-600" />
                                <div>
                                    <p className="text-sm text-gray-600">Usuarios</p>
                                    <p className="text-3xl font-bold">{stats.usuarios}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white shadow sm:rounded-lg p-6 mb-8">
                        <h3 className="text-lg font-medium mb-4">Últimos 5 DTEs</h3>
                        {ultimosDtes.length === 0 ? (
                            <p className="text-gray-500">Aún no hay documentos.</p>
                        ) : (
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th className="px-6 py-3 text-right"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {ultimosDtes.map(dte => (
                                        <tr key={dte.id}>
                                            <td className="px-6 py-4 whitespace-nowrap">{dte.folio}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">{dte.tipo_dte?.nombre ?? 'Sin tipo'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right">${Number(dte.monto_total).toLocaleString('es-CL')}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 py-1 rounded text-xs ${
                                                    dte.estado_actual === 'Firmada' ? 'bg-yellow-100 text-yellow-800' :
                                                    dte.estado_actual === 'Enviada' ? 'bg-green-100 text-green-800' :
                                                    dte.estado_actual === 'Anulada' ? 'bg-red-100 text-red-800' :
                                                    'bg-gray-100 text-gray-800'
                                                }`}>
                                                    {dte.estado_actual}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right">
                                                <Link href={route('dtes.show', dte.id)} className="text-indigo-600 hover:text-indigo-900">Ver</Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>

                    <div className="flex justify-end">
                        <Link href={route('reportes.general')} className="bg-purple-600 text-white px-6 py-3 rounded hover:bg-purple-700">
                            Ver Reporte General (PDF)
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}