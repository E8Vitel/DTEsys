import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { FileText, Plus } from 'lucide-react';

export default function Index({ dtes }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold">Mis Documentos DTE</h2>
                    <Link href={route('dtes.create')} className="bg-indigo-600 text-white px-4 py-2 rounded flex items-center gap-2">
                        <Plus size={18} /> Nuevo DTE
                    </Link>
                </div>
            }
        >
            <Head title="DTEs" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {dtes.length === 0 ? (
                                <p className="text-center text-gray-500">No hay documentos aún. Crea uno nuevo.</p>
                            ) : (
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado Actual</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {dtes.map(dte => (
                                            <tr key={dte.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">{dte.folio}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{dte.tipo_dte?.nombre}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">${dte.monto_total.toLocaleString()}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`px-2 py-1 rounded text-xs ${dte.historial?.length > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                                        {dte.historial?.length > 0 ? dte.historial[dte.historial.length - 1].estado_nuevo : 'Borrador'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Link href={route('dtes.show', dte.id)} className="text-indigo-600 hover:text-indigo-900">Ver</Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}