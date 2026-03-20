import { Link } from '@inertiajs/react';

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Link
            {...props}
            className={
                'inline-flex items-center rounded-full px-4 py-2 text-sm font-bold leading-5 transition duration-150 ease-in-out focus:outline-none ' +
                (active
                    ? 'bg-gradient-to-r from-amber-500 to-yellow-400 text-white shadow-[0_12px_24px_rgba(245,158,11,0.22)]'
                    : 'bg-white text-amber-700 hover:bg-amber-100 hover:text-amber-800') +
                ` ${className}`
            }
        >
            {children}
        </Link>
    );
}
