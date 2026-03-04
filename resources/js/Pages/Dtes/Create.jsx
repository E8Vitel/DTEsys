import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';

export default function Create({ tipos }) {
    const { data, setData, post, processing, errors } = useForm({
        tipo_dte_id: '',
        rut_receptor: '',
        razon_social_receptor: '',
        detalles: [{ cantidad: 1, descripcion: '', precio_unitario: 0 }],
    });

    const addDetalle = () => {
        setData('detalles', [...data.detalles, { cantidad: 1, descripcion: '', precio_unitario: 0 }]);
    };

    const removeDetalle = (index) => {
        setData('detalles', data.detalles.filter((_, i) => i !== index));
    };

    const handleDetalleChange = (index, field, value) => {
        const newDetalles = [...data.detalles];
        newDetalles[index][field] = value;
        setData('detalles', newDetalles);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('dtes.store'));
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold">Crear Nuevo DTE</h2>}
        >
            <Head title="Crear DTE" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <form onSubmit={submit}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label className="block text-sm font-medium">Tipo de DTE</label>
                                    <select
                                        value={data.tipo_dte_id}
                                        onChange={e => setData('tipo_dte_id', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    >
                                        <option value="">Selecciona...</option>
                                        {tipos.map(t => (
                                            <option key={t.id} value={t.id}>{t.nombre} ({t.codigo})</option>
                                        ))}
                                    </select>
                                    {errors.tipo_dte_id && <p className="text-red-600 text-sm">{errors.tipo_dte_id}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium">RUT Receptor</label>
                                    <input
                                        type="text"
                                        value={data.rut_receptor}
                                        onChange={e => setData('rut_receptor', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    />
                                    {errors.rut_receptor && <p className="text-red-600 text-sm">{errors.rut_receptor}</p>}
                                </div>

                                <div className="md:col-span-2">
                                    <label className="block text-sm font-medium">Razón Social Receptor</label>
                                    <input
                                        type="text"
                                        value={data.razon_social_receptor}
                                        onChange={e => setData('razon_social_receptor', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    />
                                    {errors.razon_social_receptor && <p className="text-red-600 text-sm">{errors.razon_social_receptor}</p>}
                                </div>
                            </div>

                            <div className="mb-6">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="text-lg font-medium">Detalles / Ítems</h3>
                                    <button type="button" onClick={addDetalle} className="bg-green-600 text-white px-3 py-1 rounded flex items-center gap-1">
                                        <Plus size={16} /> Agregar ítem
                                    </button>
                                </div>

                                {data.detalles.map((det, index) => (
                                    <div key={index} className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 border-b pb-4">
                                        <div>
                                            <label className="block text-sm">Cantidad</label>
                                            <input
                                                type="number"
                                                value={det.cantidad}
                                                onChange={e => handleDetalleChange(index, 'cantidad', e.target.value)}
                                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                                min="1"
                                            />
                                        </div>
                                        <div className="md:col-span-2">
                                            <label className="block text-sm">Descripción</label>
                                            <input
                                                type="text"
                                                value={det.descripcion}
                                                onChange={e => handleDetalleChange(index, 'descripcion', e.target.value)}
                                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            />
                                        </div>
                                        <div className="flex items-end gap-2">
                                            <div className="flex-1">
                                                <label className="block text-sm">Precio Unit.</label>
                                                <input
                                                    type="number"
                                                    value={det.precio_unitario}
                                                    onChange={e => handleDetalleChange(index, 'precio_unitario', e.target.value)}
                                                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                                    min="0"
                                                    step="0.01"
                                                />
                                            </div>
                                            <button type="button" onClick={() => removeDetalle(index)} className="text-red-600 hover:text-red-800">
                                                <Trash2 size={20} />
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <button type="submit" disabled={processing} className="bg-indigo-600 text-white px-6 py-2 rounded">
                                {processing ? 'Guardando...' : 'Crear DTE'}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}