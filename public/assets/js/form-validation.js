/**
 * Validación inline de formularios del panel admin.
 * Muestra errores bajo el campo afectado (borde rojo + mensaje).
 * Los formularios con data-inline-validation="off" conservan su propia validación.
 */
document.querySelectorAll('form:not([data-inline-validation="off"])').forEach(form => {
    form.setAttribute('novalidate', '');

    const clearFieldError = field => {
        field.classList.remove('field-invalid', 'border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
        const next = field.nextElementSibling;
        if (next && next.classList.contains('field-error')) next.remove();
    };

    const fieldLabel = field => {
        if (field.id) {
            const label = document.querySelector(`label[for="${field.id}"]`);
            if (label) {
                const text = label.textContent
                    .replace(/\([^)]*\)/g, '')
                    .replace(/\*/g, '')
                    .replace(/\s+/g, ' ')
                    .trim();
                if (text) return text;
            }
        }
        const name = field.name ? field.name.replace(/_/g, ' ') : '';
        return name || 'campo';
    };

    form.addEventListener('submit', e => {
        let hasErrors = false;
        let firstError = null;

        form.querySelectorAll('.field-error').forEach(el => el.remove());
        form.querySelectorAll('.field-invalid').forEach(el => el.classList.remove('field-invalid', 'border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20'));

        form.querySelectorAll('[required]').forEach(field => {
            const isCheckable = field.type === 'checkbox' || field.type === 'radio';
            const empty = isCheckable ? !field.checked : field.value.trim() === '';
            if (empty) {
                hasErrors = true;
                if (!firstError) firstError = field;
                field.classList.add('field-invalid', 'border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
                const p = document.createElement('p');
                p.className = 'field-error mt-2 text-xs text-red-400';
                p.textContent = 'El campo ' + fieldLabel(field) + ' es obligatorio.';
                field.insertAdjacentElement('afterend', p);
            }
        });

        form.querySelectorAll('input[min]').forEach(field => {
            if (field.type !== 'number' && field.type !== 'date' && field.type !== 'time') return;
            const value = field.value.trim();
            if (value === '') return;
            const min = parseFloat(field.getAttribute('min'));
            if (isNaN(min)) return;
            if (parseFloat(value) < min) {
                hasErrors = true;
                if (!firstError) firstError = field;
                field.classList.add('field-invalid', 'border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
                const p = document.createElement('p');
                p.className = 'field-error mt-2 text-xs text-red-400';
                const shownMin = Number.isInteger(min) ? min : min.toFixed(2);
                p.textContent = 'El campo ' + fieldLabel(field) + ' debe ser al menos ' + shownMin + '.';
                field.insertAdjacentElement('afterend', p);
            }
        });

        if (hasErrors) {
            e.preventDefault();
            if (firstError) {
                firstError.scrollIntoView({ block: 'center', behavior: 'smooth' });
                firstError.focus({ preventScroll: true });
            }
        }
    });

    form.addEventListener('input', e => {
        if (e.target.classList.contains('field-invalid')) clearFieldError(e.target);
    });
    form.addEventListener('change', e => {
        if (e.target.classList.contains('field-invalid')) clearFieldError(e.target);
    });
});
