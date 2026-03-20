export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            className={
                `inline-flex items-center rounded-2xl border border-amber-400 bg-gradient-to-r from-amber-500 to-yellow-400 px-5 py-3 text-xs font-black uppercase tracking-[0.22em] text-white shadow-[0_16px_34px_rgba(245,158,11,0.24)] transition duration-150 ease-in-out hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 active:brightness-95 ${
                    disabled && 'opacity-25'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
