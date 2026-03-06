import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import SignatureCanvas from 'react-signature-canvas';
import { Mail, Ban } from 'lucide-react';

export default function Show({ dte, tipoDte, detallesDte }) {
    const [firmaTipo, setFirmaTipo] = useState('texto');
    const [firmaTexto, setFirmaTexto] = useState('');
    const sigCanvas = React.useRef(null);

    const { setData: setForm, post: postFirma, processing: procFirma } = useForm({
        tipo_firma: 'texto',
        firma_texto: '',
        firma_canvas: '',
    });

    const cambiarFirmaTipo = (tipo) => {
        setFirmaTipo(tipo);
        setForm('tipo_firma', tipo);
    };

    const firmar = (e) => {
        e.preventDefault();

        if (firmaTipo === 'canvas') {
            const dataUrl = sigCanvas.current.getTrimmedCanvas().toDataURL('image/png');
            setForm('firma_canvas', dataUrl);
        } else {
            setForm('firma_texto', firmaTexto);
        }

        postFirma(route('dtes.firmar', dte.id), {
            onSuccess: () => alert('Firmado exitosamente'),
        });
    };

    const enviar = () => {
        if (confirm('Enviar simulado por correo?')) {
            router.post(route('dtes.enviar', dte.id));
        }
    };

    const anular = () => {
        const comentario = prompt('Comentario para anulacion:');
        if (comentario) {
            router.post(route('dtes.anular', dte.id), { comentario });
        }
    };

    const estadoActual = dte.historial?.length > 0 ? dte.historial[dte.historial.length - 1].estado_nuevo : 'Borrador';
    const yaFirmado = !!dte.firma;

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Detalle DTE: {dte.folio}</h2>}>
            <Head title={`DTE ${dte.folio}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p><strong>Tipo:</strong> {tipoDte?.nombre} - ({tipoDte?.codigo})</p>
                                <p><strong>Fecha Emision:</strong> {new Date(dte.fecha_emision).toLocaleDateString()}</p>
                                <p><strong>Emisor:</strong> {dte.razon_social_emisor} ({dte.rut_emisor})</p>
                                <p><strong>Receptor:</strong> {dte.razon_social_receptor} ({dte.rut_receptor})</p>
                                <p><strong>Giro:</strong> {dte.giro_receptor || 'No informado'}</p>
                                <p><strong>Direccion:</strong> {dte.direccion_receptor || 'No informada'}</p>
                                <p><strong>Region:</strong> {dte.region?.nombre || 'No informada'}</p>
                                <p><strong>Comuna:</strong> {dte.comuna?.nombre || 'No informada'}</p>
                            </div>
                            <div className="text-right">
                                <p className="text-3xl font-bold">${dte.monto_total.toLocaleString()}</p>
                                <p className="text-sm">Neto: ${dte.monto_neto.toLocaleString()} | IVA: ${dte.iva.toLocaleString()}</p>
                                <p className="mt-4">
                                    <strong>Estado:</strong> <span className="px-3 py-1 rounded bg-blue-100 text-blue-800">{estadoActual}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium mb-4">Detalles</h3>
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th className="px-4 py-2 text-left">Cant.</th>
                                    <th className="px-4 py-2 text-left">Descripcion</th>
                                    <th className="px-4 py-2 text-right">P. Unit.</th>
                                    <th className="px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                {detallesDte.map((det) => (
                                    <tr key={det.id}>
                                        <td className="px-4 py-2">{det.cantidad}</td>
                                        <td className="px-4 py-2">{det.descripcion}</td>
                                        <td className="px-4 py-2 text-right">${det.precio_unitario.toLocaleString()}</td>
                                        <td className="px-4 py-2 text-right">${det.subtotal.toLocaleString()}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {!yaFirmado && (
                        <div className="bg-white shadow sm:rounded-lg p-6">
                            <h3 className="text-lg font-medium mb-4">Firma Digital Simulada</h3>
                            <div className="flex gap-4 mb-4">
                                <button onClick={() => cambiarFirmaTipo('texto')} className={`px-4 py-2 rounded ${firmaTipo === 'texto' ? 'bg-indigo-600 text-white' : 'bg-gray-200'}`}>
                                    Firma Texto
                                </button>
                                <button onClick={() => cambiarFirmaTipo('canvas')} className={`px-4 py-2 rounded ${firmaTipo === 'canvas' ? 'bg-indigo-600 text-white' : 'bg-gray-200'}`}>
                                    Firma Canvas
                                </button>
                            </div>

                            {firmaTipo === 'texto' ? (
                                <input
                                    type="text"
                                    value={firmaTexto}
                                    onChange={(e) => setFirmaTexto(e.target.value)}
                                    placeholder="Nombre y Cargo"
                                    className="w-full border-gray-300 rounded-md shadow-sm p-2 mb-4"
                                />
                            ) : (
                                <div className="border border-gray-300 rounded p-2 mb-4">
                                    <SignatureCanvas
                                        ref={sigCanvas}
                                        penColor="black"
                                        canvasProps={{ width: 500, height: 200, className: 'border' }}
                                    />
                                    <button type="button" onClick={() => sigCanvas.current.clear()} className="text-red-600 mt-2">
                                        Borrar
                                    </button>
                                </div>
                            )}

                            <button onClick={firmar} disabled={procFirma} className="bg-green-600 text-white px-6 py-2 rounded">
                                {procFirma ? 'Firmando...' : 'Firmar DTE'}
                            </button>
                        </div>
                    )}

                    {yaFirmado && (
                        <div className="bg-green-50 p-6 rounded-lg">
                            <p className="font-bold">Firmado por: {dte.firma.user.name}</p>
                            {dte.firma.tipo_firma === 'texto' ? (
                                <p>{dte.firma.firma_texto}</p>
                            ) : (
                                <img src={dte.firma.firma_canvas} alt="Firma" className="max-h-32" />
                            )}
                        </div>
                    )}

                    <div className="flex gap-4">
                        {estadoActual !== 'Anulada' && estadoActual !== 'Rechazada' && (
                            <>
                                {estadoActual === 'Firmada' && (
                                    <button onClick={enviar} className="bg-blue-600 text-white px-6 py-2 rounded flex items-center gap-2">
                                        <Mail size={18} /> Enviar por Correo (Simulado)
                                    </button>
                                )}
                                <button onClick={anular} className="bg-red-600 text-white px-6 py-2 rounded flex items-center gap-2">
                                    <Ban size={18} /> Anular
                                </button>
                            </>
                        )}
                    </div>

                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium mb-4">Historial de Estados</h3>
                        <ul className="space-y-3">
                            {dte.historial?.map((h) => (
                                <li key={h.id} className="border-l-4 border-indigo-500 pl-4">
                                    <p><strong>{h.estado_nuevo}</strong> {new Date(h.created_at).toLocaleString()}</p>
                                    <p className="text-sm text-gray-600">Por: {h.user.name} {h.comentario && `- ${h.comentario}`}</p>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
