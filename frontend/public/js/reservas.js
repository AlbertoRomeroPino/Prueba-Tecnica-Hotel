// URL base de tu API Backend
const API_URL = 'http://localhost/backend/public/index.php/api/reservas';

/**
 * Filtra las reservas por estado.
 * @param {string} estado - El estado por el cual filtrar las reservas.
 */
document.getElementById('filtro-estado')?.addEventListener('change', async (e) => {
    const estado = e.target.value;
    const url = estado ? `${API_URL}?estado=${estado}` : API_URL;
    
    try {
        const response = await fetch(url);
        const reservas = await response.json();
        actualizarTablaDOM(reservas);
    } catch (error) {
        console.error('Error al filtrar:', error);
    }
});

/**
 * Crea una nueva reserva enviando los datos del formulario al backend.
 * @param {Object} reserva - Los datos de la nueva reserva.
 */
document.getElementById('form-crear')?.addEventListener('submit', async (e) => {
    e.preventDefault(); // Evita que la página se recargue
    
    const nuevaReserva = {
        nombre_huesped: document.getElementById('nombre').value,
        email_huesped: document.getElementById('email').value,
        nombre_alojamiento: document.getElementById('alojamiento').value,
        fecha_entrada: document.getElementById('fecha_in').value,
        fecha_salida: document.getElementById('fecha_out').value,
        importe: parseFloat(document.getElementById('importe').value)
    };

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(nuevaReserva)
        });
        
        if(response.ok) {
            alert('Reserva creada con éxito');
            document.getElementById('form-crear').reset();
            // Opcional: Recargar la lista automáticamente llamando a la API de nuevo
            document.getElementById('filtro-estado').dispatchEvent(new Event('change'));
        }
    } catch (error) {
        console.error('Error al crear:', error);
    }
});
/**
 * Cancela una reserva existente.
 * @param {number} id - El ID de la reserva a cancelar.
 */
async function cancelarReserva(id) {
    if(!confirm('¿Estás seguro de cancelar esta reserva?')) return;
    
    try {
        const response = await fetch(`${API_URL}/${id}/estado`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado: 'cancelada' })
        });

        if(response.ok) {
            // Actualizar el estado visualmente en la tabla sin recargar
            const badge = document.querySelector(`#reserva-${id} .badge`);
            badge.className = 'badge bg-danger';
            badge.textContent = 'Cancelada';
            
            // Ocultar el botón de cancelar
            const btn = document.querySelector(`#reserva-${id} button`);
            if(btn) btn.remove();
        }
    } catch (error) {
        console.error('Error al cancelar:', error);
    }
}

/**
 * actualiza la tabla de reservas en el DOM con los datos proporcionados.
 * @param {Array} reservas - El array de reservas a actualizar en el DOM.
 */
 
function actualizarTablaDOM(reservas) {
    const tbody = document.getElementById('tabla-reservas');
    tbody.innerHTML = ''; 

    reservas.forEach(r => {
        const badgeClass = r.estado === 'pendiente' ? 'warning' : (r.estado === 'confirmada' ? 'success' : 'danger');
        const botonCancelar = r.estado !== 'cancelada' 
            ? `<button onclick="cancelarReserva(${r.id})" class="btn btn-sm btn-danger">Cancelar</button>` 
            : '';

        tbody.innerHTML += `
            <tr id="reserva-${r.id}">
                <td>${r.id}</td>
                <td>${r.nombre_huesped}</td>
                <td>${r.nombre_alojamiento}</td>
                <td>${r.fecha_entrada} / ${r.fecha_salida}</td>
                <td><span class="badge bg-${badgeClass}">${r.estado.charAt(0).toUpperCase() + r.estado.slice(1)}</span></td>
                <td>
                    <a href="/detalle?id=${r.id}" class="btn btn-sm btn-info text-white">Ver</a>
                    ${botonCancelar}
                </td>
            </tr>
        `;
    });
}