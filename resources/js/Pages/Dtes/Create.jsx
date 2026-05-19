import React, { useEffect, useMemo } from 'react';
import { useAppDialog } from '@/Components/AppDialog';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { formatRutChileno, isRutChilenoValid, sanitizeRutChileno } from '@/utils/rut';

export default function Create({ tipos, regiones, comunas }) {
    const dialog = useAppDialog();

    // Estado completo del formulario de emision.
    const { data, setData, post, processing, errors } = useForm({
        tipo_dte_id: '',
        rut_receptor: '',
        razon_social_receptor: '',
        giro_receptor: '',
        direccion_receptor: '',
        region_id: '',
        comuna_id: '',
        direccion_destino: '',
        transporte_patente: '',
        transporte_rut_chofer: '',
        transporte_nombre_chofer: '',
        detalles: [{ cantidad: 1, descripcion: '', precio_unitario: 0 }],
    });

    // Permite identificar rapidamente si el formulario corresponde a una Guia.
    const tipoSeleccionado = useMemo(() => {
        return tipos.find((tipo) => String(tipo.id) === String(data.tipo_dte_id));
    }, [tipos, data.tipo_dte_id]);

    const esGuiaDespacho = tipoSeleccionado?.codigo === '52';
    const rutReceptorIngresado = sanitizeRutChileno(data.rut_receptor).length > 0;
    const rutReceptorValido = isRutChilenoValid(data.rut_receptor);
    const rutChoferIngresado = sanitizeRutChileno(data.transporte_rut_chofer).length > 0;
    const rutChoferValido = isRutChilenoValid(data.transporte_rut_chofer);

    // Formateador visual para montos mostrados en pantalla.
    const formatoCLP = (valor) =>
        new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: 'CLP',
            maximumFractionDigits: 0,
        }).format(valor || 0);

    const toNumber = (value) => {
        const number = Number(value);
        return Number.isFinite(number) ? number : 0;
    };

    // Agrega una nueva linea al detalle del documento.
    const addDetalle = () => {
        setData('detalles', [...data.detalles, { cantidad: 1, descripcion: '', precio_unitario: 0 }]);
    };

    // Quita una linea del detalle, manteniendo siempre al menos una.
    const removeDetalle = (index) => {
        if (data.detalles.length === 1) return;
        setData('detalles', data.detalles.filter((_, i) => i !== index));
    };

    // Normaliza y actualiza los datos de cada item.
    const handleDetalleChange = (index, field, value) => {
        const newDetalles = [...data.detalles];
        const parsedValue = field === 'descripcion' ? value : value === '' ? '' : Math.max(0, toNumber(value));
        newDetalles[index][field] = parsedValue;
        setData('detalles', newDetalles);
    };

    const handleRutChange = (field) => (event) => {
        setData(field, formatRutChileno(event.target.value));
    };

    // Recalcula neto, IVA y total cada vez que cambia el detalle.
    const resumen = useMemo(() => {
        const neto = data.detalles.reduce((acum, det) => {
            return acum + (toNumber(det.cantidad) * toNumber(det.precio_unitario));
        }, 0);

        const iva = neto * 0.19;
        const total = neto + iva;

        return { neto, iva, total };
    }, [data.detalles]);

    const tieneItemsValidos = useMemo(() => {
        return data.detalles.some((det) => {
            return String(det.descripcion ?? '').trim() !== ''
                && toNumber(det.cantidad) > 0;
        });
    }, [data.detalles]);

    // Filtra comunas segun la region elegida por el usuario.
    const comunasFiltradas = useMemo(() => {
        if (!data.region_id) return [];
        return comunas.filter((comuna) => String(comuna.region_id) === String(data.region_id));
    }, [comunas, data.region_id]);

    // Si cambia la region, invalida la comuna seleccionada cuando ya no corresponde.
    useEffect(() => {
        if (!data.comuna_id) return;
        const comunaVigente = comunasFiltradas.some((comuna) => String(comuna.id) === String(data.comuna_id));
        if (!comunaVigente) {
            setData('comuna_id', '');
        }
    }, [data.comuna_id, comunasFiltradas, setData]);

    // Envia el formulario al backend para crear el DTE.
    const submit = async (e) => {
        e.preventDefault();

        if (!tieneItemsValidos) {
            await dialog.alert({
                type: 'error',
                title: 'No se pudo crear el DTE',
                message: 'Revise el formulario.',
            });
            return;
        }

        post(route('dtes.store'));
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold">Emision de DTE</h2>}
        >
            <Head title="Crear DTE" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <form onSubmit={submit}>
                            <div className="border-b border-gray-200 px-6 py-5 md:px-8">
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="md:col-span-2">
                                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-500">Documento Tributario Electronico</p>
                                        <h1 className="text-2xl font-bold text-gray-900">Formulario de Emision</h1>
                                        <p className="mt-1 text-sm text-gray-500">Completa los datos del receptor y el detalle del documento.</p>
                                    </div>
                                </div>
                            </div>

                            <div className="px-6 py-6 md:px-8">
                                <div className="grid gap-6 lg:grid-cols-2">
                                    <div className="rounded-lg border border-gray-200 p-4">
                                        <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500">Emisor</h3>
                                        <div className="mt-3 space-y-2 text-sm text-gray-700">
                                            <p><span className="font-semibold">Razon Social:</span> DTEsys SPA</p>
                                            <p><span className="font-semibold">RUT:</span> Segun usuario autenticado</p>
                                            <p><span className="font-semibold">Giro:</span> Servicios tecnologicos</p>
                                        </div>
                                    </div>

                                    <div className="rounded-lg border border-gray-200 p-4">
                                        <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500">Datos del documento</h3>
                                        <div className="mt-3">
                                            <label className="block text-sm font-medium text-gray-700">Tipo de DTE</label>
                                            <select
                                                value={data.tipo_dte_id}
                                                onChange={(e) => setData('tipo_dte_id', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            >
                                                <option value="">Seleccione tipo...</option>
                                                {tipos.map((t) => (
                                                    <option key={t.id} value={t.id}>
                                                        {t.codigo} - {t.nombre}
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.tipo_dte_id && <p className="mt-1 text-sm text-red-600">{errors.tipo_dte_id}</p>}
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-6 rounded-lg border border-gray-200 p-4">
                                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500">Receptor</h3>
                                    <div className="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">RUT Receptor</label>
                                            <input
                                                type="text"
                                                value={data.rut_receptor}
                                                onChange={handleRutChange('rut_receptor')}
                                                className={`mt-1 block w-full rounded-md shadow-sm ${rutReceptorIngresado && !rutReceptorValido ? 'border-red-300 focus:border-red-500 focus:ring-red-300' : 'border-gray-300'}`}
                                                placeholder="76.123.456-7"
                                                inputMode="text"
                                                maxLength={12}
                                            />
                                            {rutReceptorIngresado && !rutReceptorValido && !errors.rut_receptor && (
                                                <p className="mt-1 text-sm text-red-600">Ingresa un RUT chileno valido.</p>
                                            )}
                                            {errors.rut_receptor && <p className="mt-1 text-sm text-red-600">{errors.rut_receptor}</p>}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Razon Social</label>
                                            <input
                                                type="text"
                                                value={data.razon_social_receptor}
                                                onChange={(e) => setData('razon_social_receptor', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                placeholder="Nombre empresa o contribuyente"
                                            />
                                            {errors.razon_social_receptor && <p className="mt-1 text-sm text-red-600">{errors.razon_social_receptor}</p>}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Giro</label>
                                            <input
                                                type="text"
                                                value={data.giro_receptor}
                                                onChange={(e) => setData('giro_receptor', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                placeholder="Actividad economica"
                                            />
                                            {errors.giro_receptor && <p className="mt-1 text-sm text-red-600">{errors.giro_receptor}</p>}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Region</label>
                                            <select
                                                value={data.region_id}
                                                onChange={(e) => setData('region_id', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            >
                                                <option value="">Seleccione region...</option>
                                                {regiones.map((region) => (
                                                    <option key={region.id} value={region.id}>
                                                        {region.nombre}
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.region_id && <p className="mt-1 text-sm text-red-600">{errors.region_id}</p>}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Comuna</label>
                                            <select
                                                value={data.comuna_id}
                                                onChange={(e) => setData('comuna_id', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                disabled={!data.region_id}
                                            >
                                                <option value="">Seleccione comuna...</option>
                                                {comunasFiltradas.map((comuna) => (
                                                    <option key={comuna.id} value={comuna.id}>
                                                        {comuna.nombre}
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.comuna_id && <p className="mt-1 text-sm text-red-600">{errors.comuna_id}</p>}
                                        </div>

                                        <div className="md:col-span-2">
                                            <label className="block text-sm font-medium text-gray-700">Direccion</label>
                                            <input
                                                type="text"
                                                value={data.direccion_receptor}
                                                onChange={(e) => setData('direccion_receptor', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                placeholder="Calle 123, oficina 45"
                                            />
                                            {errors.direccion_receptor && <p className="mt-1 text-sm text-red-600">{errors.direccion_receptor}</p>}
                                        </div>
                                    </div>
                                </div>

                                {esGuiaDespacho && (
                                    <div className="mt-6 rounded-lg border border-amber-300 bg-amber-50 p-4">
                                        <h3 className="text-sm font-semibold uppercase tracking-wide text-amber-800">Datos de transporte (Guia DTE 52)</h3>
                                        <p className="mt-1 text-xs text-amber-700">Estos campos seran obligatorios por Anexo 2.5 desde 2026-05-01.</p>
                                        <div className="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div className="md:col-span-2">
                                                <label className="block text-sm font-medium text-gray-700">Direccion destino</label>
                                                <input
                                                    type="text"
                                                    value={data.direccion_destino}
                                                    onChange={(e) => setData('direccion_destino', e.target.value)}
                                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                />
                                                {errors.direccion_destino && <p className="mt-1 text-sm text-red-600">{errors.direccion_destino}</p>}
                                            </div>

                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Patente</label>
                                                <input
                                                    type="text"
                                                    value={data.transporte_patente}
                                                    onChange={(e) => setData('transporte_patente', e.target.value)}
                                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                    placeholder="ABCD12"
                                                />
                                                {errors.transporte_patente && <p className="mt-1 text-sm text-red-600">{errors.transporte_patente}</p>}
                                            </div>

                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">RUT chofer</label>
                                                <input
                                                    type="text"
                                                    value={data.transporte_rut_chofer}
                                                    onChange={handleRutChange('transporte_rut_chofer')}
                                                    className={`mt-1 block w-full rounded-md shadow-sm ${rutChoferIngresado && !rutChoferValido ? 'border-red-300 focus:border-red-500 focus:ring-red-300' : 'border-gray-300'}`}
                                                    placeholder="12.345.678-9"
                                                    inputMode="text"
                                                    maxLength={12}
                                                />
                                                {rutChoferIngresado && !rutChoferValido && !errors.transporte_rut_chofer && (
                                                    <p className="mt-1 text-sm text-red-600">Ingresa un RUT chileno valido.</p>
                                                )}
                                                {errors.transporte_rut_chofer && <p className="mt-1 text-sm text-red-600">{errors.transporte_rut_chofer}</p>}
                                            </div>

                                            <div className="md:col-span-2">
                                                <label className="block text-sm font-medium text-gray-700">Nombre chofer</label>
                                                <input
                                                    type="text"
                                                    value={data.transporte_nombre_chofer}
                                                    onChange={(e) => setData('transporte_nombre_chofer', e.target.value)}
                                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                />
                                                {errors.transporte_nombre_chofer && <p className="mt-1 text-sm text-red-600">{errors.transporte_nombre_chofer}</p>}
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div className="mt-6 rounded-lg border border-gray-200 p-4">
                                    <div className="mb-4 flex items-center justify-between">
                                        <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500">Detalle de items</h3>
                                        <button
                                            type="button"
                                            onClick={addDetalle}
                                            className="inline-flex items-center gap-2 rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700"
                                        >
                                            <Plus size={16} /> Agregar item
                                        </button>
                                    </div>

                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">#</th>
                                                    <th className="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Cantidad</th>
                                                    <th className="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Descripcion</th>
                                                    <th className="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Precio Unit.</th>
                                                    <th className="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Subtotal</th>
                                                    <th className="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Accion</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-100 bg-white">
                                                {data.detalles.map((det, index) => {
                                                    const subtotal = toNumber(det.cantidad) * toNumber(det.precio_unitario);

                                                    return (
                                                        <tr key={index}>
                                                            <td className="px-3 py-2 text-sm text-gray-700">{index + 1}</td>
                                                            <td className="px-3 py-2">
                                                                <input
                                                                    type="number"
                                                                    value={det.cantidad}
                                                                    onChange={(e) => handleDetalleChange(index, 'cantidad', e.target.value)}
                                                                    className="block w-24 rounded-md border-gray-300 text-sm shadow-sm"
                                                                    min="1"
                                                                    step="1"
                                                                />
                                                            </td>
                                                            <td className="px-3 py-2">
                                                                <input
                                                                    type="text"
                                                                    value={det.descripcion}
                                                                    onChange={(e) => handleDetalleChange(index, 'descripcion', e.target.value)}
                                                                    className="block w-full rounded-md border-gray-300 text-sm shadow-sm"
                                                                    placeholder="Descripcion del bien o servicio"
                                                                />
                                                            </td>
                                                            <td className="px-3 py-2">
                                                                <input
                                                                    type="number"
                                                                    value={det.precio_unitario}
                                                                    onChange={(e) => handleDetalleChange(index, 'precio_unitario', e.target.value)}
                                                                    className="ml-auto block w-36 rounded-md border-gray-300 text-right text-sm shadow-sm"
                                                                    min="0"
                                                                    step="1"
                                                                />
                                                            </td>
                                                            <td className="px-3 py-2 text-right text-sm font-medium text-gray-900">{formatoCLP(subtotal)}</td>
                                                            <td className="px-3 py-2 text-right">
                                                                <button
                                                                    type="button"
                                                                    onClick={() => removeDetalle(index)}
                                                                    className="inline-flex rounded-md p-2 text-red-600 hover:bg-red-50 hover:text-red-700"
                                                                    disabled={data.detalles.length === 1}
                                                                >
                                                                    <Trash2 size={18} />
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>

                                    {errors.detalles && <p className="mt-2 text-sm text-red-600">{errors.detalles}</p>}
                                </div>

                                <div className="mt-6 flex justify-end">
                                    <div className="w-full max-w-sm rounded-lg border border-gray-200 bg-gray-50 p-4">
                                        <div className="flex items-center justify-between border-b border-gray-200 py-1 text-sm">
                                            <span className="text-gray-600">Monto Neto</span>
                                            <span className="font-semibold text-gray-900">{formatoCLP(resumen.neto)}</span>
                                        </div>
                                        <div className="flex items-center justify-between border-b border-gray-200 py-1 text-sm">
                                            <span className="text-gray-600">IVA (19%)</span>
                                            <span className="font-semibold text-gray-900">{formatoCLP(resumen.iva)}</span>
                                        </div>
                                        <div className="flex items-center justify-between py-2">
                                            <span className="text-sm font-semibold uppercase tracking-wide text-gray-700">Total DTE</span>
                                            <span className="text-lg font-bold text-gray-900">{formatoCLP(resumen.total)}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-6 border-t border-gray-200 pt-4">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {processing ? 'Guardando DTE...' : 'Emitir DTE'}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
