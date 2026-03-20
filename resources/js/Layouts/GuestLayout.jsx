import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen bg-white">
            <div className="grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
                <section className="relative hidden overflow-hidden bg-gradient-to-br from-amber-400 via-yellow-400 to-amber-500 lg:flex">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.45),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(255,255,255,0.22),_transparent_34%)]" />
                    <div className="relative flex w-full flex-col justify-between p-12 text-white xl:p-16">
                        <div className="flex items-center gap-4">
                            <div className="rounded-3xl bg-white/18 p-4 backdrop-blur-sm">
                                <ApplicationLogo className="h-14 w-14 fill-current text-white" />
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.35em] text-white/85">
                                    Plataforma DTE
                                </p>
                                <h1 className="mt-2 text-4xl font-black tracking-tight xl:text-5xl">
                                    DTEsys
                                </h1>
                            </div>
                        </div>

                        <div className="max-w-xl">
                            <p className="mb-4 text-sm font-semibold uppercase tracking-[0.3em] text-white/80">
                                Control documental
                            </p>
                            <h2 className="text-4xl font-black leading-tight xl:text-6xl">
                                Gestion profesional de DTEs en una sola vista.
                            </h2>
                            <p className="mt-6 max-w-lg text-lg leading-8 text-white/88">
                                Emision, seguimiento y administracion operativa con una interfaz clara,
                                compacta y enfocada en el trabajo diario.
                            </p>
                        </div>

                        <div className="grid max-w-2xl grid-cols-3 gap-4">
                            <div className="rounded-3xl border border-white/25 bg-white/12 p-5 backdrop-blur-sm">
                                <p className="text-xs uppercase tracking-[0.28em] text-white/75">Emision</p>
                                <p className="mt-3 text-2xl font-black">DTE</p>
                            </div>
                            <div className="rounded-3xl border border-white/25 bg-white/12 p-5 backdrop-blur-sm">
                                <p className="text-xs uppercase tracking-[0.28em] text-white/75">Control</p>
                                <p className="mt-3 text-2xl font-black">Estados</p>
                            </div>
                            <div className="rounded-3xl border border-white/25 bg-white/12 p-5 backdrop-blur-sm">
                                <p className="text-xs uppercase tracking-[0.28em] text-white/75">Acceso</p>
                                <p className="mt-3 text-2xl font-black">Seguro</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="flex min-h-screen items-center justify-center bg-white px-4 py-8 sm:px-8">
                    <div className="w-full max-w-xl">
                        <div className="mb-8 flex items-center justify-center lg:hidden">
                            <Link
                                href="/"
                                className="flex items-center gap-3 rounded-2xl border border-amber-200 bg-white px-5 py-4 shadow-[0_20px_45px_rgba(245,158,11,0.16)]"
                            >
                                <ApplicationLogo className="h-10 w-10 fill-current text-amber-500" />
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-[0.28em] text-amber-500">
                                        Plataforma
                                    </p>
                                    <p className="text-2xl font-black tracking-tight text-amber-700">
                                        DTEsys
                                    </p>
                                </div>
                            </Link>
                        </div>

                        <div className="overflow-hidden rounded-[2rem] border border-amber-200 bg-white shadow-[0_28px_80px_rgba(245,158,11,0.16)]">
                            {children}
                        </div>
                    </div>
                </section>
            </div>
        </div>
    );
}
