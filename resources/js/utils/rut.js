export function sanitizeRutChileno(value) {
    return (value ?? '').replace(/[^0-9kK]/g, '').toUpperCase();
}

export function formatRutChileno(value) {
    const cleanRut = sanitizeRutChileno(value);

    if (!cleanRut) {
        return '';
    }

    if (cleanRut.length === 1) {
        return cleanRut;
    }

    const cuerpo = cleanRut.slice(0, -1);
    const dv = cleanRut.slice(-1);
    const cuerpoFormateado = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return `${cuerpoFormateado}-${dv}`;
}

export function isRutChilenoValid(value) {
    const cleanRut = sanitizeRutChileno(value);

    if (cleanRut.length < 2) {
        return false;
    }

    const cuerpo = cleanRut.slice(0, -1);
    const dvIngresado = cleanRut.slice(-1);

    if (!/^\d+$/.test(cuerpo)) {
        return false;
    }

    let suma = 0;
    let multiplicador = 2;

    for (let index = cuerpo.length - 1; index >= 0; index -= 1) {
        suma += Number(cuerpo[index]) * multiplicador;
        multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
    }

    const resto = 11 - (suma % 11);
    const dvEsperado = resto === 11 ? '0' : resto === 10 ? 'K' : String(resto);

    return dvIngresado === dvEsperado;
}
