import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useAppDialog } from '@/Components/AppDialog';
import { Head, router } from '@inertiajs/react';
import SignatureCanvas from 'react-signature-canvas';
import { Ban, CheckCircle2, Download, Send, XCircle } from 'lucide-react';

export default function Show({ dte, tipoDte, detallesDte }) {
    const dialog = useAppDialog();
    const [firmaTipo, setFirmaTipo] = useState('texto');
    const [firmaTexto, setFirmaTexto] = useState('');
    const sigCanvas = React.useRef(null);
    const [procFirma, setProcFirma] = useState(false);

    const cambiarFirmaTipo = (tipo) => {
        setFirmaTipo(tipo);
    };

    const generarFirmaTextoPng = (texto) => {
        const contenido = (texto || '').trim();
        if (!contenido) {
            return '';
        }

        const canvas = document.createElement('canvas');
        canvas.width = 700;
        canvas.height = 220;
        const ctx = canvas.getContext('2d');

        if (!ctx) {
            return '';
        }

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#111827';
        ctx.font = '48px cursive';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(contenido, canvas.width / 2, canvas.height / 2);

        return canvas.toDataURL('image/png');
    };

    const firmar = async (e) => {
        e.preventDefault();

        const payload = {
            tipo_firma: firmaTipo,
            firma_texto: firmaTipo === 'texto' ? firmaTexto : '',
            firma_canvas: '',
        };

        if (firmaTipo === 'canvas') {
            if (!sigCanvas.current || sigCanvas.current.isEmpty()) {
                await dialog.alert({
                    type: 'warning',
                    title: 'Firma requerida',
                    message: 'Debes dibujar la firma en el canvas.',
                });
                return;
            }

            payload.firma_canvas = sigCanvas.current.toDataURL('image/png');
        } else {
            if (!firmaTexto.trim()) {
                await dialog.alert({
                    type: 'warning',
                    title: 'Firma requerida',
                    message: 'Debes ingresar la firma en texto.',
                });
                return;
            }

            payload.firma_canvas = generarFirmaTextoPng(firmaTexto);
        }

        setProcFirma(true);

        router.post(route('dtes.firmar', dte.id), payload, {
            onSuccess: async () => {
                setFirmaTexto('');
                setFirmaTipo('texto');
                if (sigCanvas.current) {
                    sigCanvas.current.clear();
                }

                await dialog.alert({
                    type: 'success',
                    title: 'Firma registrada',
                    message: 'El DTE fue firmado exitosamente.',
                });
            },
            onFinish: () => setProcFirma(false),
        });
    };

    const enviarSii = async () => {
        const confirmado = await dialog.confirm({
            type: 'info',
            title: 'Enviar al SII',
            message: 'Se enviara el DTE al SII para validacion (simulado).',
            confirmText: 'Enviar',
        });

        if (confirmado) {
            router.post('/dtes/' + dte.id + '/enviar-sii');
        }
    };

    const enviarReceptor = async () => {
        const confirmado = await dialog.confirm({
            type: 'info',
            title: 'Enviar al receptor',
            message: 'Se enviara XML y PDF al receptor (simulado).',
            confirmText: 'Enviar',
        });

        if (confirmado) {
            router.post('/dtes/' + dte.id + '/enviar-receptor');
        }
    };

    const registrarAcuse = async (respuesta) => {
        let comentario = '';

        if (respuesta !== 'aceptado') {
            const valor = await dialog.prompt({
                type: 'warning',
                title: 'Comentario requerido',
                message: 'Debes indicar motivo para rechazo o reparo.',
                inputLabel: 'Comentario',
                inputRequired: true,
                confirmText: 'Registrar',
            });

            if (valor === null) {
                return;
            }

            comentario = valor.trim();
        }

        router.post('/dtes/' + dte.id + '/acuse-receptor', {
            respuesta,
            comentario,
        });
    };

    const anular = async () => {
        const comentario = await dialog.prompt({
            type: 'warning',
            title: 'Anular DTE',
            message: 'Ingresa un comentario para registrar la anulacion.',
            inputLabel: 'Comentario',
            inputPlaceholder: 'Motivo de la anulacion',
            inputRequired: true,
            confirmText: 'Anular',
        });

        if (comentario !== null) {
            router.post(route('dtes.anular', dte.id), { comentario: comentario.trim() });
        }
    };

    const estadoActual = dte.historial?.length > 0 ? dte.historial[dte.historial.length - 1].estado_nuevo : 'Borrador';
    const yaFirmado = !!dte.firma;

    const puedeEnviarSii = estadoActual === 'Firmada' || estadoActual === 'Rechazada SII';
    const puedeEnviarReceptor = estadoActual === 'Aceptada SII';
    const puedeRegistrarAcuse = estadoActual === 'Enviada Receptor';

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Detalle DTE: {dte.folio}</h2>}>
            <Head title={`DTE ${dte.folio}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p><strong>Tipo:</strong> {tipoDte?.nombre} ({tipoDte?.codigo})</p>
                                <p><strong>Fecha Emision:</strong> {new Date(dte.fecha_emision).toLocaleDateString()}</p>
                                <p><strong>Emisor:</strong> {dte.razon_social_emisor} ({dte.rut_emisor})</p>
                                <p><strong>Receptor:</strong> {dte.razon_social_receptor} ({dte.rut_receptor})</p>
                                <p><strong>Giro:</strong> {dte.giro_receptor || 'No informado'}</p>
                                <p><strong>Direccion:</strong> {dte.direccion_receptor || 'No informada'}</p>
                                <p><strong>Region:</strong> {dte.region?.nombre || 'No informada'}</p>
                                <p><strong>Comuna:</strong> {dte.comuna?.nombre || 'No informada'}</p>
                                {tipoDte?.codigo === '52' && (
                                    <>
                                        <p><strong>Destino:</strong> {dte.direccion_destino || 'No informado'}</p>
                                        <p><strong>Patente:</strong> {dte.transporte_patente || 'No informada'}</p>
                                        <p><strong>Chofer:</strong> {(dte.transporte_nombre_chofer || 'No informado')} ({dte.transporte_rut_chofer || 'Sin RUT'})</p>
                                    </>
                                )}
                            </div>
                            <div className="text-right">
                                <p className="text-3xl font-bold">${Number(dte.monto_total).toLocaleString()}</p>
                                <p className="text-sm">Neto: ${Number(dte.monto_neto).toLocaleString()} | IVA: ${Number(dte.iva).toLocaleString()}</p>
                                <p className="mt-4">
                                    <strong>Estado:</strong> <span className="px-3 py-1 rounded bg-blue-100 text-blue-800">{estadoActual}</span>
                                </p>
                                <div className="mt-3 text-sm text-gray-700 space-y-1">
                                    <p><strong>SII:</strong> {dte.estado_sii || 'No enviado'}</p>
                                    <p><strong>Track ID:</strong> {dte.track_id || 'No asignado'}</p>
                                    <p><strong>Respuesta SII:</strong> {dte.codigo_respuesta_sii || '-'} {dte.mensaje_respuesta_sii ? `- ${dte.mensaje_respuesta_sii}` : ''}</p>
                                    <p><strong>Acuse receptor:</strong> {dte.estado_receptor || 'Sin acuse'}</p>
                                </div>
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
                                        <td className="px-4 py-2 text-right">${Number(det.precio_unitario).toLocaleString()}</td>
                                        <td className="px-4 py-2 text-right">${Number(det.subtotal).toLocaleString()}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {!yaFirmado && estadoActual === 'Borrador' && (
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
                            <p className="font-bold mb-4">Firmado por: {dte.firma.user?.name ?? 'Usuario desconocido'}</p>
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                                <div>
                                    <p className="text-sm text-gray-600 mb-2">Firma</p>
                                    <img
                                        src={dte.firma.firma_canvas || generarFirmaTextoPng(dte.firma.firma_texto)}
                                        alt="Firma"
                                        className="max-h-32 border bg-white p-2 rounded"
                                    />
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 mb-2">Timbre electronico</p>
                                    {dte.timbre?.startsWith('data:image') ? (
                                        <img
                                            src={dte.timbre}
                                            alt="Timbre Electronico SII"
                                            className="w-full max-w-[320px] border rounded bg-white p-2"
                                        />
                                    ) : (
                                        <p className="text-sm text-gray-600 break-all">{dte.timbre || 'No disponible'}</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="flex flex-wrap gap-4">
                        <a href={route('dtes.download-xml', dte.id)} className="bg-slate-700 text-white px-6 py-2 rounded flex items-center gap-2">
                            <Download size={18} /> Descargar XML
                        </a>
                        <a href={route('dtes.download-pdf', dte.id)} className="bg-slate-900 text-white px-6 py-2 rounded flex items-center gap-2">
                            <Download size={18} /> Descargar PDF
                        </a>

                        {puedeEnviarSii && (
                            <button onClick={enviarSii} className="bg-blue-600 text-white px-6 py-2 rounded flex items-center gap-2">
                                <Send size={18} /> Enviar al SII
                            </button>
                        )}

                        {puedeEnviarReceptor && (
                            <button onClick={enviarReceptor} className="bg-cyan-700 text-white px-6 py-2 rounded flex items-center gap-2">
                                <Send size={18} /> Enviar al Receptor
                            </button>
                        )}

                        {puedeRegistrarAcuse && (
                            <>
                                <button onClick={() => registrarAcuse('aceptado')} className="bg-emerald-600 text-white px-6 py-2 rounded flex items-center gap-2">
                                    <CheckCircle2 size={18} /> Acuse Aceptado
                                </button>
                                <button onClick={() => registrarAcuse('reparo')} className="bg-amber-600 text-white px-6 py-2 rounded flex items-center gap-2">
                                    <XCircle size={18} /> Acuse con Reparos
                                </button>
                                <button onClick={() => registrarAcuse('rechazado')} className="bg-rose-700 text-white px-6 py-2 rounded flex items-center gap-2">
                                    <XCircle size={18} /> Acuse Rechazado
                                </button>
                            </>
                        )}

                        {estadoActual !== 'Anulada' && (
                            <button onClick={anular} className="bg-red-600 text-white px-6 py-2 rounded flex items-center gap-2">
                                <Ban size={18} /> Anular
                            </button>
                        )}
                    </div>

                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium mb-4">Historial de Estados</h3>
                        <ul className="space-y-3">
                            {dte.historial?.map((h) => (
                                <li key={h.id} className="border-l-4 border-indigo-500 pl-4">
                                    <p><strong>{h.estado_nuevo}</strong> {new Date(h.created_at).toLocaleString()}</p>
                                    <p className="text-sm text-gray-600">Por: {h.user?.name ?? 'Usuario desconocido'} {h.comentario && `- ${h.comentario}`}</p>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
