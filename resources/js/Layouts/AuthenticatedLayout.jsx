import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import useAuthorization from '@/Hooks/useAuthorization';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    // Obtiene datos del usuario autenticado desde las props compartidas por Inertia.
    const { auth } = usePage().props;
    const user = auth.user;
    const { can } = useAuthorization();
    const canManageUsers = can('gestionar-usuarios');
    const canViewDtes = can('ver-dtes');

    // Controla el menu responsive en pantallas pequenas.
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    return (
        <div className="min-h-screen bg-[linear-gradient(180deg,#ffffff_0%,#fff7cc_48%,#ffffff_100%)]">
            <div className="h-2 w-full bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500" />

            <nav className="border-b border-amber-200 bg-white/95 backdrop-blur">
                <div className="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex items-center gap-4">
                            <Link
                                href="/"
                                className="flex items-center gap-3 rounded-2xl border border-amber-200 bg-white px-4 py-3 shadow-[0_16px_40px_rgba(245,158,11,0.12)]"
                            >
                                <div className="rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-400 p-3 text-white">
                                    <ApplicationLogo className="h-8 w-8 fill-current" />
                                </div>
                                <div>
                                    <p className="text-[11px] font-bold uppercase tracking-[0.3em] text-amber-500">
                                        Sistema
                                    </p>
                                    <p className="text-xl font-black tracking-tight text-amber-700">
                                        DTEsys
                                    </p>
                                </div>
                            </Link>

                            <div className="hidden xl:block">
                                <p className="text-[11px] font-bold uppercase tracking-[0.3em] text-amber-400">
                                    Operacion documental
                                </p>
                                <p className="mt-1 text-sm font-semibold text-amber-700">
                                    Emision, consulta y gestion en un flujo centralizado.
                                </p>
                            </div>
                        </div>

                        <div className="hidden items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-2 py-2 sm:flex">
                            <NavLink href={route('dashboard')} active={route().current('dashboard')}>
                                Dashboard
                            </NavLink>

                            {/* El menu se adapta segun permisos del usuario. */}
                            {canViewDtes && (
                                <NavLink href={route('dtes.index')} active={route().current('dtes.*')}>
                                    DTEs
                                </NavLink>
                            )}

                            {canManageUsers && (
                                <NavLink
                                    href={route('usuarios.index')}
                                    active={route().current('usuarios.*')}
                                >
                                    Usuarios
                                </NavLink>
                            )}
                        </div>

                        <div className="hidden items-center gap-3 sm:flex">
                            <div className="rounded-2xl border border-amber-200 bg-white px-4 py-2 text-right">
                                <p className="text-[11px] font-bold uppercase tracking-[0.25em] text-amber-400">
                                    Usuario activo
                                </p>
                                <p className="mt-1 text-sm font-bold text-amber-700">{user.name}</p>
                            </div>

                            <div className="relative">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center gap-2 rounded-2xl border border-amber-300 bg-gradient-to-r from-amber-500 to-yellow-400 px-4 py-3 text-sm font-bold text-white shadow-[0_14px_30px_rgba(245,158,11,0.2)] transition hover:brightness-105 focus:outline-none"
                                            >
                                                Cuenta
                                                <svg
                                                    className="h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')}>Perfil</Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button">
                                            Cerrar sesion
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-me-2 flex items-center sm:hidden">
                            <button
                                onClick={() =>
                                    setShowingNavigationDropdown((previousState) => !previousState)
                                }
                                className="inline-flex items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 p-3 text-amber-600 transition hover:bg-white hover:text-amber-700 focus:outline-none"
                            >
                                <svg
                                    className="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden'}>
                    <div className="space-y-2 border-t border-amber-200 bg-white px-4 py-4">
                        <ResponsiveNavLink
                            href={route('dashboard')}
                            active={route().current('dashboard')}
                        >
                            Dashboard
                        </ResponsiveNavLink>

                        {canViewDtes && (
                            <ResponsiveNavLink
                                href={route('dtes.index')}
                                active={route().current('dtes.*')}
                            >
                                DTEs
                            </ResponsiveNavLink>
                        )}

                        {canManageUsers && (
                            <ResponsiveNavLink
                                href={route('usuarios.index')}
                                active={route().current('usuarios.*')}
                            >
                                Usuarios
                            </ResponsiveNavLink>
                        )}

                        <div className="rounded-3xl border border-amber-200 bg-amber-50 px-4 py-4">
                            <div className="text-sm font-bold text-amber-700">{user.name}</div>
                            <div className="mt-1 text-xs font-medium text-amber-500">{user.email}</div>
                        </div>

                        <ResponsiveNavLink href={route('profile.edit')}>Perfil</ResponsiveNavLink>
                        <ResponsiveNavLink method="post" href={route('logout')} as="button">
                            Cerrar sesion
                        </ResponsiveNavLink>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="mx-auto w-full max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-[2rem] border border-amber-200 bg-white shadow-[0_24px_60px_rgba(245,158,11,0.12)]">
                        <div className="h-2 w-full bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500" />
                        <div className="px-6 py-6 sm:px-8">{header}</div>
                    </div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
