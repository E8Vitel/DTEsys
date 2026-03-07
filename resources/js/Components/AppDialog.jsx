import Modal from '@/Components/Modal';
import { createContext, useContext, useMemo, useRef, useState } from 'react';
import { AlertTriangle, CheckCircle2, CircleAlert, Info } from 'lucide-react';

const DialogContext = createContext(null);

const TYPE_CONFIG = {
    info: {
        Icon: Info,
        iconClass: 'text-blue-600',
        badgeClass: 'bg-blue-100',
    },
    success: {
        Icon: CheckCircle2,
        iconClass: 'text-green-600',
        badgeClass: 'bg-green-100',
    },
    warning: {
        Icon: AlertTriangle,
        iconClass: 'text-amber-600',
        badgeClass: 'bg-amber-100',
    },
    error: {
        Icon: CircleAlert,
        iconClass: 'text-red-600',
        badgeClass: 'bg-red-100',
    },
};

const INITIAL_STATE = {
    show: false,
    mode: 'alert',
    type: 'info',
    title: '',
    message: '',
    confirmText: 'Aceptar',
    cancelText: 'Cancelar',
    inputLabel: '',
    inputPlaceholder: '',
    inputDefaultValue: '',
    inputValue: '',
    inputRequired: false,
};

export function AppDialogProvider({ children }) {
    const resolverRef = useRef(null);
    const [dialog, setDialog] = useState(INITIAL_STATE);

    const closeAndResolve = (value) => {
        setDialog((prev) => ({ ...prev, show: false }));

        if (resolverRef.current) {
            resolverRef.current(value);
            resolverRef.current = null;
        }
    };

    const openDialog = (config) =>
        new Promise((resolve) => {
            resolverRef.current = resolve;
            setDialog({
                ...INITIAL_STATE,
                show: true,
                ...config,
                inputValue: config.inputDefaultValue ?? '',
            });
        });

    const api = useMemo(
        () => ({
            alert: (config) =>
                openDialog({
                    mode: 'alert',
                    type: 'info',
                    confirmText: 'Aceptar',
                    ...config,
                }),
            confirm: (config) =>
                openDialog({
                    mode: 'confirm',
                    type: 'warning',
                    confirmText: 'Confirmar',
                    cancelText: 'Cancelar',
                    ...config,
                }),
            prompt: (config) =>
                openDialog({
                    mode: 'prompt',
                    type: 'info',
                    confirmText: 'Aceptar',
                    cancelText: 'Cancelar',
                    inputLabel: 'Valor',
                    ...config,
                }),
        }),
        [],
    );

    const typeConfig = TYPE_CONFIG[dialog.type] ?? TYPE_CONFIG.info;
    const Icon = typeConfig.Icon;
    const isPrompt = dialog.mode === 'prompt';
    const isAlert = dialog.mode === 'alert';
    const isConfirmDisabled =
        isPrompt && dialog.inputRequired && !dialog.inputValue.trim();

    const handleConfirm = () => {
        if (dialog.mode === 'confirm') {
            closeAndResolve(true);
            return;
        }

        if (dialog.mode === 'prompt') {
            closeAndResolve(dialog.inputValue);
            return;
        }

        closeAndResolve(true);
    };

    const handleCancel = () => {
        if (dialog.mode === 'prompt') {
            closeAndResolve(null);
            return;
        }

        closeAndResolve(false);
    };

    return (
        <DialogContext.Provider value={api}>
            {children}

            <Modal
                show={dialog.show}
                onClose={isAlert ? () => {} : handleCancel}
                closeable={!isAlert}
                maxWidth="md"
            >
                <div className="p-6">
                    <div className="flex items-start gap-4">
                        <div className={`rounded-full p-2 ${typeConfig.badgeClass}`}>
                            <Icon className={`h-6 w-6 ${typeConfig.iconClass}`} />
                        </div>

                        <div className="flex-1">
                            <h3 className="text-lg font-semibold text-gray-900">
                                {dialog.title || 'Mensaje'}
                            </h3>
                            <p className="mt-2 text-sm text-gray-600 whitespace-pre-line">
                                {dialog.message}
                            </p>

                            {isPrompt && (
                                <div className="mt-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">
                                        {dialog.inputLabel}
                                    </label>
                                    <input
                                        type="text"
                                        value={dialog.inputValue}
                                        onChange={(event) =>
                                            setDialog((prev) => ({
                                                ...prev,
                                                inputValue: event.target.value,
                                            }))
                                        }
                                        placeholder={dialog.inputPlaceholder}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                            )}

                            <div className="mt-6 flex justify-end gap-3">
                                {!isAlert && (
                                    <button
                                        type="button"
                                        onClick={handleCancel}
                                        className="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        {dialog.cancelText}
                                    </button>
                                )}
                                <button
                                    type="button"
                                    onClick={handleConfirm}
                                    disabled={isConfirmDisabled}
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {dialog.confirmText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Modal>
        </DialogContext.Provider>
    );
}

export function useAppDialog() {
    const context = useContext(DialogContext);

    if (!context) {
        throw new Error('useAppDialog must be used within AppDialogProvider');
    }

    return context;
}