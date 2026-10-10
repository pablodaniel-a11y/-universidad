import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';

export default function FlashMessage() {
    // 1. Obtenemos el mensaje flash compartido desde Laravel/Inertia
    const { flash } = usePage().props as any;

    // 2. Estado local para controlar si se muestra o se cierra en la UI
    const [visible, setVisible] = useState(false);

    // 3. Cada vez que cambie el mensaje flash del backend, volvemos a mostrar el aviso
    useEffect(() => {
        if (flash?.success || flash?.error) {
            setVisible(true);
        }
    }, [flash]);

    // Si no es visible o no hay mensajes, no renderizamos nada
    if (!visible || (!flash?.success && !flash?.error)) return null;

    const isSuccess = !!flash.success;
    const message = flash.success || flash.error;

    return (
        <div className={`p-4 rounded-xl border flex items-start gap-3 shadow-sm ${
            isSuccess ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'
        }`}>
            {/* Icono Dinámico (Éxito o Error) */}
            <div className="flex-shrink-0">
                {isSuccess ? (
                    <svg className="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                ) : (
                    <svg className="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                )}
            </div>

            {/* Contenido del Mensaje */}
            <div className="flex-1 text-sm">
                <p className="font-semibold">
                    {isSuccess ? '¡Operación exitosa!' : 'Hubo un problema'}
                </p>
                <p className="mt-1">{message}</p>
            </div>

            {/* Botón de Cerrar (X) */}
            <button
                type="button"
                onClick={() => setVisible(false)}
                className={`flex-shrink-0 p-1.5 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 ${
                    isSuccess
                        ? 'hover:bg-green-100 text-green-500 focus:ring-green-500 focus:ring-offset-green-50'
                        : 'hover:bg-red-100 text-red-500 focus:ring-red-500 focus:ring-offset-red-50'
                }`}
                aria-label="Cerrar notificación"
            >
                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    );
}