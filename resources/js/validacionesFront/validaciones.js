const TEXTO_SEGURO = /^[\p{L}\p{M}\p{N}\s.,;:¿?¡!()#@'"/_-]+$/u;
const TAMANO_MAXIMO_IMAGEN = 5 * 1024 * 1024;
const LADO_MAXIMO_IMAGEN = 2560;
const PIXELES_MAXIMOS = 8_000_000;

export function validarTexto(valor, { requerido = false, maximo = 255 } = {}) {
    const texto = String(valor ?? '').trim();
    if (requerido && texto.length === 0) return 'Este campo es obligatorio.';
    if (texto.length > maximo) return `Usa ${maximo} caracteres o menos.`;
    if (texto && !TEXTO_SEGURO.test(texto)) return 'Usa letras, números y puntuación común.';
    return '';
}

export function validarNumero(valor, { requerido = false, minimo = null, maximo = null, entero = false } = {}) {
    const texto = String(valor ?? '').trim();
    if (!texto) return requerido ? 'Este campo es obligatorio.' : '';
    const numero = Number(texto);
    if (!Number.isFinite(numero)) return 'Ingresa un número válido.';
    if (entero && !Number.isInteger(numero)) return 'Ingresa un número entero.';
    if (minimo !== null && numero < minimo) return `El valor mínimo es ${minimo}.`;
    if (maximo !== null && numero > maximo) return `El valor máximo es ${maximo}.`;
    return '';
}

export function validarCorreo(valor) {
    const correo = String(valor ?? '').trim();
    return !correo || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo) ? '' : 'Ingresa un correo electrónico válido.';
}

export async function validarImagen(archivo) {
    if (!archivo) return '';
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(archivo.type)) return 'El archivo debe ser una imagen JPEG, PNG o WebP.';
    if (archivo.size > TAMANO_MAXIMO_IMAGEN) return 'La imagen no puede superar 5 MB.';

    try {
        const dimensiones = await new Promise((resolve, reject) => {
            const imagen = new Image();
            const url = URL.createObjectURL(archivo);
            imagen.onload = () => { URL.revokeObjectURL(url); resolve([imagen.naturalWidth, imagen.naturalHeight]); };
            imagen.onerror = () => { URL.revokeObjectURL(url); reject(new Error('invalid-image')); };
            imagen.src = url;
        });
        const [ancho, alto] = dimensiones;
        if (ancho > LADO_MAXIMO_IMAGEN || alto > LADO_MAXIMO_IMAGEN || ancho * alto > PIXELES_MAXIMOS) return 'La imagen debe medir como máximo 2560 × 2560 píxeles.';
    } catch {
        return 'El archivo no contiene una imagen válida.';
    }
    return '';
}

function mensajePara(campo) {
    const tipo = campo.dataset.validation;
    if (tipo === 'text') return validarTexto(campo.value, { requerido: campo.required, maximo: Number(campo.maxLength) > 0 ? Number(campo.maxLength) : 255 });
    if (tipo === 'number') return validarNumero(campo.value, { requerido: campo.required, minimo: campo.min === '' ? null : Number(campo.min), maximo: campo.max === '' ? null : Number(campo.max), entero: campo.dataset.integer === 'true' });
    if (campo.type === 'email') return validarCorreo(campo.value);
    return '';
}

function marcar(campo, mensaje) {
    campo.setCustomValidity(mensaje);
    campo.classList.toggle('input-invalid', Boolean(mensaje));
}

document.addEventListener('input', (event) => {
    const campo = event.target;
    if (campo instanceof HTMLInputElement || campo instanceof HTMLTextAreaElement) marcar(campo, mensajePara(campo));
});

document.addEventListener('change', async (event) => {
    const campo = event.target;
    if (campo instanceof HTMLSelectElement && campo.matches('[data-fine-home]')) {
        const residentSelect = campo.form?.querySelector('[data-fine-resident]');
        if (residentSelect instanceof HTMLSelectElement) {
            residentSelect.value = '';
            [...residentSelect.options].forEach((option) => {
                option.hidden = Boolean(option.dataset.vivienda && option.dataset.vivienda !== campo.value);
                option.disabled = option.hidden;
            });
        }
    }
    if (campo instanceof HTMLSelectElement && campo.matches('[data-fine-reason]')) {
        const selected = campo.selectedOptions[0];
        const help = campo.form?.querySelector('[data-fine-price]');
        if (help && selected?.dataset.base) help.textContent = `Monto base: $${Number(selected.dataset.base).toFixed(2)} MXN · incremento por reincidencia: ${selected.dataset.incremento}%`;
    }
    if (!(campo instanceof HTMLInputElement) || campo.type !== 'file' || !campo.matches('[data-validation="image"]')) return;
    marcar(campo, await validarImagen(campo.files?.[0]));
});

document.addEventListener('submit', async (event) => {
    const formulario = event.target;
    if (!(formulario instanceof HTMLFormElement)) return;
    if (formulario.dataset.validationResolved === 'true') {
        delete formulario.dataset.validationResolved;
        return;
    }
    event.preventDefault();
    const campos = [...formulario.querySelectorAll('[data-validation]')];
    const archivos = campos.filter((campo) => campo instanceof HTMLInputElement && campo.type === 'file' && campo.dataset.validation === 'image');
    const resultados = await Promise.all(archivos.map(async (campo) => [campo, await validarImagen(campo.files?.[0])]));
    resultados.forEach(([campo, mensaje]) => marcar(campo, mensaje));
    campos.filter((campo) => !(campo instanceof HTMLInputElement && campo.type === 'file')).forEach((campo) => marcar(campo, mensajePara(campo)));
    const invalido = [...formulario.elements].find((campo) => typeof campo.reportValidity === 'function' && !campo.checkValidity());
    if (invalido) {
        invalido.reportValidity();
        return;
    }
    formulario.dataset.validationResolved = 'true';
    formulario.requestSubmit(event.submitter instanceof HTMLElement ? event.submitter : undefined);
}, true);

export const validaciones = { validarTexto, validarNumero, validarCorreo, validarImagen };
