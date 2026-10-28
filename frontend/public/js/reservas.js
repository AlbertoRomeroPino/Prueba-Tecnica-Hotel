/**
 * Constantes del DOM
 */
const modal = document.getElementById('modal-reserva');
const btnAbrirModal = document.getElementById('btn-abrir-modal');
const btnCerrarModal = document.getElementById('btn-cerrar-modal');
const btnCancelarModal = document.getElementById('btn-cancelar-modal');
const backdrop = document.getElementById('modal-backdrop');

const fNombre = document.getElementById('filtro-nombre');
const fEstado = document.getElementById('filtro-estado');
const fFechaIn = document.getElementById('filtro-fecha-in');
const fFechaOut = document.getElementById('filtro-fecha-out');

const cFechaIn = document.getElementById('fecha_in');
const cFechaOut = document.getElementById('fecha_out');

const tablaReservas = document.getElementById('tabla-reservas');

/**
 * Control del Modal
 */
function toggleModal(mostrar) {
    if(!modal) return;
    if (mostrar) {
        modal.classList.remove('hidden');
    } else {
        modal.classList.add('hidden');
        document.getElementById('form-crear').reset();
        if(cFechaIn) cFechaIn.max = '';
        if(cFechaOut) cFechaOut.min = '';
    }
}

btnAbrirModal?.addEventListener('click', () => toggleModal(true));
btnCerrarModal?.addEventListener('click', () => toggleModal(false));
btnCancelarModal?.addEventListener('click', () => toggleModal(false));
backdrop?.addEventListener('click', () => toggleModal(false));

/**
 * Validación de fechas
 */
function configurarRestriccionesFechas(inputIn, inputOut) {
    if (!inputIn || !inputOut) return;
    inputIn.addEventListener('change', (e) => inputOut.min = e.target.value);
    inputOut.addEventListener('change', (e) => inputIn.max = e.target.value);
}
configurarRestriccionesFechas(cFechaIn, cFechaOut);
configurarRestriccionesFechas(fFechaIn, fFechaOut);

/**
 * REQUISITO: Feedback visual de carga
 */
function mostrarCargando() {
    if (tablaReservas) {
        tablaReservas.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Cargando reservas...
                    </div>
                </td>
            </tr>`;
    }
}

/**
 * Filtros Combinados a la API
 */
async function aplicarFiltros() {
    mostrarCargando(); 

    const params = new URLSearchParams();
    if (fNombre?.value.trim() !== '') params.append('nombre', fNombre.value.trim());
    if (fEstado?.value !== '') params.append('estado', fEstado.value);
    if (fFechaIn?.value !== '') params.append('fecha_inicio', fFechaIn.value);
    if (fFechaOut?.value !== '') params.append('fecha_fin', fFechaOut.value);

    const queryString = params.toString();
    const url = queryString ? `/?${queryString}` : '/';
    
    try {
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error('Error en el servidor');
        const reservas = await response.json();
        actualizarTablaDOM(reservas);
    } catch (error) {
        if(tablaReservas) tablaReservas.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-red-500">Error al cargar los datos.</td></tr>`;
    }
}

let debounceTimeout;
fNombre?.addEventListener('input', () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(aplicarFiltros, 400);
});
fEstado?.addEventListener('change', aplicarFiltros);
fFechaIn?.addEventListener('change', aplicarFiltros);
fFechaOut?.addEventListener('change', aplicarFiltros);

/**
 * Crear Reserva
 */
document.getElementById('form-crear')?.addEventListener('submit', async (e) => {
    e.preventDefault(); 
    
    const nuevaReserva = {
        nombre_huesped: document.getElementById('nombre').value,
        email_huesped: document.getElementById('email').value,
        nombre_alojamiento: document.getElementById('alojamiento').value,
        fecha_entrada: cFechaIn.value,
        fecha_salida: cFechaOut.value,
        importe: parseFloat(document.getElementById('importe').value)
    };

    if (nuevaReserva.fecha_entrada > nuevaReserva.fecha_salida) {
        alert('La fecha de entrada no puede ser posterior a la fecha de salida.');
        return;
    }

    try {
        const response = await fetch('/?action=crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(nuevaReserva)
        });
        
        const result = await response.json();

        if (response.ok && !result.error) {
            toggleModal(false);
            alert('¡Reserva creada con éxito!');
            aplicarFiltros(); 
        } else {
            alert('Error: ' + (result.mensaje || 'Verifica los campos.'));
        }
    } catch (error) {
        alert('Fallo de red al intentar conectar con el servidor.');
    }
});

/**
 * REQUISITO: JS Organizado (Delegación de eventos en lugar de lógica inline)
 * Captura los cambios de los <select> dentro de la tabla sin usar 'onchange' en el HTML
 */
tablaReservas?.addEventListener('change', async (e) => {
    // Verificamos si lo que cambió fue un selector de estado
    if (e.target.classList.contains('select-estado')) {
        const id = e.target.dataset.id;
        const nuevoEstado = e.target.value;

        if (nuevoEstado === 'cancelada' && !confirm('¿Seguro que deseas cancelar esta reserva?')) {
            aplicarFiltros(); // Deshacer cambio visual
            return;
        }

        try {
            const response = await fetch('/?action=cambiar_estado', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, estado: nuevoEstado })
            });

            if (response.ok) {
                aplicarFiltros();
            } else {
                alert('Error al actualizar el estado en el servidor.');
            }
        } catch (error) {
            alert('Error de conexión al cambiar el estado.');
        }
    }
});

/**
 * Actualizar DOM
 */
function actualizarTablaDOM(reservas) {
    if (!tablaReservas) return;
    tablaReservas.innerHTML = ''; 

    if (!reservas || reservas.length === 0) {
        tablaReservas.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No hay resultados para este filtro.</td></tr>`;
        return;
    }

    reservas.forEach(r => {
        let colorClasses = '';
        if (r.estado === 'pendiente') colorClasses = 'bg-brand-50 text-brand-700 border-brand-200';
        else if (r.estado === 'confirmada') colorClasses = 'bg-green-50 text-green-700 border-green-200';
        else colorClasses = 'bg-red-50 text-red-700 border-red-200';

        const selectHtml = `
            <select data-id="${r.id}" class="select-estado text-xs font-semibold rounded-full px-3 py-1 border cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 transition-colors ${colorClasses}">
                <option value="pendiente" class="bg-white text-gray-900" ${r.estado === 'pendiente' ? 'selected' : ''}>Pendiente</option>
                <option value="confirmada" class="bg-white text-gray-900" ${r.estado === 'confirmada' ? 'selected' : ''}>Confirmada</option>
                <option value="cancelada" class="bg-white text-gray-900" ${r.estado === 'cancelada' ? 'selected' : ''}>Cancelada</option>
            </select>
        `;

        tablaReservas.innerHTML += `
            <tr id="reserva-${r.id}" class="hover:bg-gray-50/50 transition-colors duration-200">
                <td class="px-6 py-4 text-sm text-gray-500">#${r.id}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${r.nombre_huesped}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${r.fecha_entrada} <br><span class="text-xs text-gray-400">al ${r.fecha_salida}</span></td>
                <td class="px-6 py-4">${selectHtml}</td>
                <td class="px-6 py-4 text-right">
                    <a href="/detalle?id=${r.id}" class="text-brand-600 hover:text-brand-700 text-sm font-semibold transition-colors">Ver detalle</a>
                </td>
            </tr>
        `;
    });
}