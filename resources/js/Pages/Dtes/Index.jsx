import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useAuthorization from '@/Hooks/useAuthorization';
import { Head, Link, router } from '@inertiajs/react';
import { FileSearch, Plus, RotateCcw } from 'lucide-react';

export default function Index({ dtes, tipos, estados, filters }) {
    // El permiso de creacion controla si se muestra la accion principal.
    const { can } = useAuthorization();
    const canCreateDtes = can('crear-dte');

    // Estado local del formulario de filtros para no recargar en cada tecla.
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

    // Ejecuta la busqueda enviando filtros por GET.
    const handleSearch = (event) => {
        event.preventDefault();

        router.get(route('dtes.index'), formFilters, {
            preserveState: true,
            replace: true,
        });
    };

    // Restablece todos los filtros a su estado inicial.
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
                <div className="grid gap-6 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
                    <div>
                        <div className="inline-flex rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-black uppercase tracking-[0.3em] text-amber-500">
                            Centro documental
                        </div>
                        <h2 className="mt-5 text-4xl font-black leading-tight text-amber-700">
                            Consulta y administra tus documentos DTE con filtros operativos.
                        </h2>
                        <p className="mt-4 max-w-2xl text-base leading-7 text-amber-600">
                            Revisa folios, tipos, estados y montos en una vista preparada para
                            busqueda y seguimiento continuo.
                        </p>
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        <div className="rounded-[1.75rem] border border-amber-200 bg-white px-6 py-5 shadow-[0_18px_45px_rgba(245,158,11,0.1)]">
                            <p className="text-xs font-black uppercase tracking-[0.28em] text-amber-400">
                                Resultados
                            </p>
                            <p className="mt-3 text-4xl font-black text-amber-700">{dtes.length}</p>
                            <p className="mt-2 text-sm font-bold text-amber-500">
                                documentos visibles en esta consulta.
                            </p>
                        </div>

                        {canCreateDtes && (
                            <Link
                                href={route('dtes.create')}
                                className="flex items-center justify-between rounded-[1.75rem] bg-gradient-to-r from-amber-500 to-yellow-400 px-6 py-5 text-white shadow-[0_24px_55px_rgba(245,158,11,0.22)] transition hover:brightness-105"
                            >
                                <div>
                                    <p className="text-xs font-black uppercase tracking-[0.24em] text-white/80">
                                        Accion
                                    </p>
                                    <p className="mt-2 text-xl font-black">Nuevo DTE</p>
                                </div>
                                <Plus size={20} />
                            </Link>
                        )}
                    </div>
                </div>
            }
        >
            <Head title="DTEs" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                    <section className="overflow-hidden rounded-[2rem] border border-amber-200 bg-white shadow-[0_24px_60px_rgba(245,158,11,0.12)]">
                        <div className="border-b border-amber-200 bg-gradient-to-r from-white via-amber-50 to-white px-6 py-5">
                            <p className="text-xs font-black uppercase tracking-[0.28em] text-amber-400">
                                Busqueda avanzada
                            </p>
                            <h3 className="mt-2 text-2xl font-black text-amber-700">
                                Filtra por folio, tipo y estado
                            </h3>
                        </div>

                        <form
                            onSubmit={handleSearch}
                            className="grid grid-cols-1 gap-5 p-6 lg:grid-cols-[1.2fr_0.8fr_0.8fr_auto]"
                        >
                            <div>
                                <label htmlFor="search" className="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-amber-500">
                                    Busqueda
                                </label>
                                <input
                                    id="search"
                                    name="search"
                                    type="text"
                                    value={formFilters.search}
                                    onChange={handleChange}
                                    placeholder="Folio, RUT o razon social"
                                    className="w-full rounded-2xl border border-amber-300 bg-white px-4 py-3 text-amber-800 shadow-sm placeholder:text-amber-300 focus:border-amber-500 focus:ring-amber-300"
                                />
                            </div>

                            <div>
                                <label htmlFor="tipo" className="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-amber-500">
                                    Tipo DTE
                                </label>
                                <select
                                    id="tipo"
                                    name="tipo"
                                    value={formFilters.tipo}
                                    onChange={handleChange}
                                    className="w-full rounded-2xl border border-amber-300 bg-white px-4 py-3 text-amber-800 shadow-sm focus:border-amber-500 focus:ring-amber-300"
                                >
                                    <option value="">Todos</option>
                                    {tipos.map((tipo) => (
                                        <option key={tipo.id} value={tipo.id}>
                                            {tipo.nombre}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label htmlFor="estado" className="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-amber-500">
                                    Estado
                                </label>
                                <select
                                    id="estado"
                                    name="estado"
                                    value={formFilters.estado}
                                    onChange={handleChange}
                                    className="w-full rounded-2xl border border-amber-300 bg-white px-4 py-3 text-amber-800 shadow-sm focus:border-amber-500 focus:ring-amber-300"
                                >
                                    <option value="">Todos</option>
                                    {estados.map((estado) => (
                                        <option key={estado} value={estado}>
                                            {estado}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="flex flex-col justify-end gap-3">
                                <button
                                    type="submit"
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-yellow-400 px-5 py-3 text-sm font-black uppercase tracking-[0.14em] text-white shadow-[0_18px_40px_rgba(245,158,11,0.2)] transition hover:brightness-105"
                                >
                                    <FileSearch size={16} /> Buscar
                                </button>
                                <button
                                    type="button"
                                    onClick={clearFilters}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-300 bg-amber-50 px-5 py-3 text-sm font-black uppercase tracking-[0.14em] text-amber-700 transition hover:bg-white"
                                >
                                    <RotateCcw size={16} /> Limpiar
                                </button>
                            </div>
                        </form>
                    </section>

                    <section className="overflow-hidden rounded-[2rem] border border-amber-200 bg-white shadow-[0_24px_60px_rgba(245,158,11,0.12)]">
                        <div className="flex items-center justify-between border-b border-amber-200 px-6 py-5">
                            <div>
                                <p className="text-xs font-black uppercase tracking-[0.28em] text-amber-400">
                                    Listado
                                </p>
                                <h3 className="mt-2 text-2xl font-black text-amber-700">
                                    Documentos disponibles
                                </h3>
                            </div>
                            <div className="rounded-full bg-amber-50 px-4 py-2 text-sm font-black uppercase tracking-[0.16em] text-amber-600">
                                {dtes.length} registros
                            </div>
                        </div>

                        <div className="p-6">
                            {dtes.length === 0 ? (
                                <div className="rounded-[1.6rem] border border-dashed border-amber-300 bg-amber-50 px-6 py-12 text-center text-base font-bold text-amber-600">
                                    No hay documentos para los filtros seleccionados.
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-amber-200">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                                    Folio
                                                </th>
                                                <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                                    Tipo
                                                </th>
                                                <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                                    Total
                                                </th>
                                                <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                                    Estado
                                                </th>
                                                <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                                    Acciones
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-amber-100">
                                            {dtes.map((dte) => (
                                                <tr key={dte.id} className="hover:bg-amber-50">
                                                    <td className="whitespace-nowrap px-4 py-4 text-sm font-black text-amber-700">
                                                        {dte.folio}
                                                    </td>
                                                    <td className="whitespace-nowrap px-4 py-4 text-sm font-bold text-amber-600">
                                                        {dte.tipo_dte?.nombre}
                                                    </td>
                                                    <td className="whitespace-nowrap px-4 py-4 text-sm font-bold text-amber-700">
                                                        ${Number(dte.monto_total).toLocaleString()}
                                                    </td>
                                                    <td className="whitespace-nowrap px-4 py-4">
                                                        <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-black uppercase tracking-[0.12em] text-amber-700">
                                                            {dte.estado_actual}
                                                        </span>
                                                    </td>
                                                    <td className="whitespace-nowrap px-4 py-4">
                                                        <Link
                                                            href={route('dtes.show', dte.id)}
                                                            className="text-sm font-black uppercase tracking-[0.12em] text-amber-600 hover:text-amber-800"
                                                        >
                                                            Ver documento
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
