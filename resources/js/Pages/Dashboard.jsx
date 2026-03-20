import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useAuthorization from '@/Hooks/useAuthorization';
import { Head, Link } from '@inertiajs/react';
import { FileText, Users, DollarSign, Plus, BarChart3, ArrowRight } from 'lucide-react';

// Convierte las estadisticas del backend en tarjetas presentables para el dashboard.
const metricCards = (stats) => [
    {
        label: 'DTEs emitidos',
        value: stats.total_dtes,
        icon: FileText,
    },
    {
        label: 'Monto total',
        value: `$${Number(stats.monto_total).toLocaleString('es-CL')}`,
        icon: DollarSign,
    },
    {
        label: 'Documentos firmados',
        value: stats.firmados,
        icon: BarChart3,
    },
    {
        label: 'Usuarios activos',
        value: stats.usuarios,
        icon: Users,
    },
];

export default function Dashboard({ stats, ultimosDtes }) {
    // Se consultan permisos para mostrar solo acciones validas para el perfil actual.
    const { can } = useAuthorization();
    const canViewDtes = can('ver-dtes');
    const canCreateDtes = can('crear-dte');
    const canViewReports = can('ver-reportes');

    return (
        <AuthenticatedLayout
            header={
                <div className="grid gap-6 lg:grid-cols-[1.25fr_0.75fr] lg:items-end">
                    <div>
                        <div className="inline-flex rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-black uppercase tracking-[0.3em] text-amber-500">
                            Panel ejecutivo
                        </div>
                        <h2 className="mt-5 max-w-3xl text-4xl font-black leading-tight text-amber-700">
                            Visualiza el estado operativo de tu flujo DTE en una sola lectura.
                        </h2>
                        <p className="mt-4 max-w-2xl text-base leading-7 text-amber-600">
                            Sigue la emision, el volumen documental y la actividad del sistema con
                            una interfaz pensada para priorizar informacion clave.
                        </p>
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        {canCreateDtes && (
                            <Link
                                href={route('dtes.create')}
                                className="flex items-center justify-between rounded-[1.75rem] bg-gradient-to-r from-amber-500 to-yellow-400 px-6 py-5 text-white shadow-[0_24px_55px_rgba(245,158,11,0.22)] transition hover:brightness-105"
                            >
                                <div>
                                    <p className="text-xs font-black uppercase tracking-[0.25em] text-white/80">
                                        Accion principal
                                    </p>
                                    <p className="mt-2 text-xl font-black">Crear nuevo DTE</p>
                                </div>
                                <Plus size={22} />
                            </Link>
                        )}

                        {/* Tabla resumida con los ultimos documentos visibles. */}
                        {canViewDtes && (
                            <Link
                                href={route('dtes.index')}
                                className="flex items-center justify-between rounded-[1.75rem] border border-amber-200 bg-white px-6 py-5 text-amber-700 shadow-[0_18px_45px_rgba(245,158,11,0.12)] transition hover:bg-amber-50"
                            >
                                <div>
                                    <p className="text-xs font-black uppercase tracking-[0.25em] text-amber-400">
                                        Consulta
                                    </p>
                                    <p className="mt-2 text-xl font-black">Ver DTEs</p>
                                </div>
                                <ArrowRight size={22} />
                            </Link>
                        )}
                    </div>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                    <section className="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        {metricCards(stats).map(({ label, value, icon: Icon }) => (
                            <article
                                key={label}
                                className="overflow-hidden rounded-[1.9rem] border border-amber-200 bg-white shadow-[0_22px_55px_rgba(245,158,11,0.12)]"
                            >
                                <div className="h-2 bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500" />
                                <div className="p-6">
                                    <div className="flex items-start justify-between gap-4">
                                        <div>
                                            <p className="text-xs font-black uppercase tracking-[0.22em] text-amber-400">
                                                {label}
                                            </p>
                                            <p className="mt-4 text-3xl font-black leading-none text-amber-700">
                                                {value}
                                            </p>
                                        </div>
                                        <div className="rounded-2xl bg-amber-100 p-4 text-amber-600">
                                            <Icon size={24} />
                                        </div>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </section>

                    <section className="grid gap-8 xl:grid-cols-[1.3fr_0.7fr]">
                        {canViewDtes && (
                            <div className="overflow-hidden rounded-[2rem] border border-amber-200 bg-white shadow-[0_24px_60px_rgba(245,158,11,0.12)]">
                            <div className="flex items-center justify-between border-b border-amber-200 px-6 py-5">
                                <div>
                                    <p className="text-xs font-black uppercase tracking-[0.28em] text-amber-400">
                                        Actividad reciente
                                    </p>
                                    <h3 className="mt-2 text-2xl font-black text-amber-700">
                                        Ultimos 5 DTEs
                                    </h3>
                                </div>
                                {canViewDtes && (
                                    <Link
                                        href={route('dtes.index')}
                                        className="rounded-full bg-amber-50 px-4 py-2 text-sm font-black uppercase tracking-[0.16em] text-amber-600 hover:bg-amber-100"
                                    >
                                        Ver todos
                                    </Link>
                                )}
                            </div>

                            <div className="p-6">
                                {ultimosDtes.length === 0 ? (
                                    <div className="rounded-[1.6rem] border border-dashed border-amber-300 bg-amber-50 px-6 py-12 text-center text-base font-bold text-amber-600">
                                        Aun no hay documentos registrados.
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
                                                    <th className="px-4 py-3 text-right text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                                        Total
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                                        Estado
                                                    </th>
                                                    <th className="px-4 py-3 text-right text-xs font-black uppercase tracking-[0.2em] text-amber-400">
                                                        Detalle
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-amber-100">
                                                {ultimosDtes.map((dte) => (
                                                    <tr key={dte.id} className="hover:bg-amber-50">
                                                        <td className="whitespace-nowrap px-4 py-4 text-sm font-black text-amber-700">
                                                            {dte.folio}
                                                        </td>
                                                        <td className="whitespace-nowrap px-4 py-4 text-sm font-bold text-amber-600">
                                                            {dte.tipo_dte?.nombre ?? 'Sin tipo'}
                                                        </td>
                                                        <td className="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-amber-700">
                                                            ${Number(dte.monto_total).toLocaleString('es-CL')}
                                                        </td>
                                                        <td className="whitespace-nowrap px-4 py-4">
                                                            <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-black uppercase tracking-[0.12em] text-amber-700">
                                                                {dte.estado_actual}
                                                            </span>
                                                        </td>
                                                        <td className="whitespace-nowrap px-4 py-4 text-right">
                                                            {canViewDtes && (
                                                                <Link
                                                                    href={route('dtes.show', dte.id)}
                                                                    className="text-sm font-black uppercase tracking-[0.12em] text-amber-600 hover:text-amber-800"
                                                                >
                                                                    Ver
                                                                </Link>
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>
                            </div>
                        )}

                        <div className="space-y-6">
                            <div className="rounded-[2rem] border border-amber-200 bg-gradient-to-br from-amber-500 via-yellow-400 to-amber-500 p-7 text-white shadow-[0_28px_70px_rgba(245,158,11,0.22)]">
                                <p className="text-xs font-black uppercase tracking-[0.28em] text-white/80">
                                    Resumen del sistema
                                </p>
                                <h3 className="mt-3 text-3xl font-black leading-tight">
                                    {stats.total_dtes} documentos procesados.
                                </h3>
                                <p className="mt-4 text-sm leading-7 text-white/88">
                                    Manten visibilidad sobre volumen, firmas y trazabilidad desde el
                                    panel principal.
                                </p>
                            </div>

                            {canViewReports && (
                                <div className="rounded-[2rem] border border-amber-200 bg-white p-7 shadow-[0_20px_50px_rgba(245,158,11,0.1)]">
                                    <p className="text-xs font-black uppercase tracking-[0.28em] text-amber-400">
                                        Reportes
                                    </p>
                                    <h3 className="mt-3 text-2xl font-black text-amber-700">
                                        Exporta un reporte general del sistema.
                                    </h3>
                                    <p className="mt-4 text-sm leading-7 text-amber-600">
                                        Genera una salida consolidada en PDF para seguimiento operativo o
                                        revision administrativa.
                                    </p>
                                    <Link
                                        href={route('reportes.general')}
                                        className="mt-6 inline-flex items-center gap-2 rounded-2xl border border-amber-300 bg-amber-50 px-5 py-3 text-sm font-black uppercase tracking-[0.14em] text-amber-700 transition hover:bg-white"
                                    >
                                        Abrir reporte
                                        <ArrowRight size={16} />
                                    </Link>
                                </div>
                            )}
                        </div>
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
