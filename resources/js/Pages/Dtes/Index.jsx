import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { FileSearch, Plus } from 'lucide-react';

export default function Index({ dtes, tipos, estados, filters }) {
    const [formFilters, setFormFilters] = useState({
        search: filters?.search ?? '',
        tipo: filters?.tipo ?? '',
        estado: filters?.estado ?? '',
    });

    const handleChange = (event) => {
        const { name, value } = event.target;
        setFormFilters((previous) => ({
            ...previous,
            [name]: value,
        }));
    };

    const handleSearch = (event) => {
        event.preventDefault();

        router.get(route('dtes.index'), formFilters, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        const cleanFilters = {
            search: '',
            tipo: '',
            estado: '',
        };

        setFormFilters(cleanFilters);
        router.get(route('dtes.index'), cleanFilters, {
            preserveState: true,
            replace: true,
        });
    };

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
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <form onSubmit={handleSearch} className="p-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                            <div className="md:col-span-2">
                                <label htmlFor="search" className="block text-sm font-medium text-gray-700 mb-1">Búsqueda</label>
                                <input
                                    id="search"
                                    name="search"
                                    type="text"
                                    value={formFilters.search}
                                    onChange={handleChange}
                                    placeholder="Folio, RUT o razón social"
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <div>
                                <label htmlFor="tipo" className="block text-sm font-medium text-gray-700 mb-1">Tipo DTE</label>
                                <select
                                    id="tipo"
                                    name="tipo"
                                    value={formFilters.tipo}
                                    onChange={handleChange}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Todos</option>
                                    {tipos.map((tipo) => (
                                        <option key={tipo.id} value={tipo.id}>{tipo.nombre}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label htmlFor="estado" className="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select
                                    id="estado"
                                    name="estado"
                                    value={formFilters.estado}
                                    onChange={handleChange}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Todos</option>
                                    {estados.map((estado) => (
                                        <option key={estado} value={estado}>{estado}</option>
                                    ))}
                                </select>
                            </div>

                            <div className="md:col-span-4 flex flex-wrap items-center gap-2">
                                <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded inline-flex items-center gap-2">
                                    <FileSearch size={16} /> Buscar
                                </button>
                                <button type="button" onClick={clearFilters} className="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">
                                    Limpiar filtros
                                </button>
                            </div>
                        </form>
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {dtes.length === 0 ? (
                                <p className="text-center text-gray-500">No hay documentos para los filtros seleccionados.</p>
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
                                        {dtes.map((dte) => (
                                            <tr key={dte.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">{dte.folio}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{dte.tipo_dte?.nombre}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">${Number(dte.monto_total).toLocaleString()}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`px-2 py-1 rounded text-xs ${dte.estado_actual !== 'Borrador' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                                        {dte.estado_actual}
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
