import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Iniciar sesion" />

            <div className="border-b border-amber-200 bg-gradient-to-r from-white via-amber-50 to-white px-8 py-8">
                <div className="inline-flex rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-black uppercase tracking-[0.28em] text-amber-500">
                    Acceso seguro
                </div>
                <h1 className="mt-5 text-4xl font-black leading-tight text-amber-700">
                    Inicia sesion y entra al centro de operaciones.
                </h1>
                <p className="mt-4 max-w-lg text-base leading-7 text-amber-600">
                    Accede a la emision, consulta y administracion de documentos con una
                    interfaz enfocada en velocidad y control.
                </p>
            </div>

            <div className="px-8 py-8">
                {status && (
                    <div className="mb-6 rounded-3xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm font-bold text-amber-700">
                        {status}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-6">
                        <div className="rounded-[1.75rem] border border-amber-200 bg-white p-5 shadow-[0_16px_40px_rgba(245,158,11,0.08)]">
                            <InputLabel htmlFor="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                className="mt-3 block w-full"
                                autoComplete="username"
                                isFocused={true}
                                onChange={(e) => setData('email', e.target.value)}
                            />
                            <InputError message={errors.email} className="mt-3" />
                        </div>

                        <div className="rounded-[1.75rem] border border-amber-200 bg-white p-5 shadow-[0_16px_40px_rgba(245,158,11,0.08)]">
                            <InputLabel htmlFor="password" value="Password" />
                            <TextInput
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                className="mt-3 block w-full"
                                autoComplete="current-password"
                                onChange={(e) => setData('password', e.target.value)}
                            />
                            <InputError message={errors.password} className="mt-3" />
                        </div>
                    </div>

                    {/* <div className="flex flex-col gap-4 rounded-[1.75rem] border border-amber-200 bg-amber-50 p-5 md:flex-row md:items-center md:justify-between">
                        {/* <label className="flex items-center">
                            <Checkbox
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                            />
                            <span className="ms-3 text-sm font-bold text-amber-700">Recordarme</span>
                        </label>

                        {canResetPassword && (
                            <Link
                                href={route('password.request')}
                                className="text-sm font-black uppercase tracking-[0.14em] text-amber-600 transition hover:text-amber-800"
                            >
                                Recuperar acceso
                            </Link>
                        )}
                    </div> */}

                    <div className="flex items-center justify-end">
                        <PrimaryButton className="w-full justify-center md:w-auto" disabled={processing}>
                            Ingresar al sistema
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </GuestLayout>
    );
}
