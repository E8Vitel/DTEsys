import { Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Link
            {...props}
            className={`flex w-full items-center rounded-2xl px-4 py-3 ${
                active
                    ? 'bg-gradient-to-r from-amber-500 to-yellow-400 text-white shadow-[0_12px_24px_rgba(245,158,11,0.18)]'
                    : 'border border-amber-200 bg-white text-amber-700 hover:bg-amber-50 hover:text-amber-800'
            } text-base font-bold transition duration-150 ease-in-out focus:outline-none ${className}`}
        >
            {children}
        </Link>
    );
}
