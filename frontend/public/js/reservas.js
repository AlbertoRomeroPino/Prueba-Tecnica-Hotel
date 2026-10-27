const modal = document.getElementById('modal-reserva');
const btnAbrirModal = document.getElementById('btn-abrir-modal');
const btnCerrarModal = document.getElementById('btn-cerrar-modal');
const btnCancelarModal = document.getElementById('btn-cancelar-modal');
const backdrop = document.getElementById('modal-backdrop');

function toggleModal(mostrar) {
    if(!modal) return;
    if (mostrar) {
        modal.classList.remove('hidden');
    } else {
        modal.classList.add('hidden');
        document.getElementById('form-crear').reset();
    }
}

btnAbrirModal?.addEventListener('click', () => toggleModal(true));
btnCerrarModal?.addEventListener('click', () => toggleModal(false));
btnCancelarModal?.addEventListener('click', () => toggleModal(false));
backdrop?.addEventListener('click', () => toggleModal(false));


document.getElementById('filtro-estado')?.addEventListener('change', async (e) => {
    const estado = e.target.value;
    const url = estado ? `/?estado=${estado}` : `/`;
    
    try {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const reservas = await response.json();
        actualizarTablaDOM(reservas);
    } catch (error) {
        console.error('Error al filtrar:', error);
    }
});


document.getElementById('form-crear')?.addEventListener('submit', async (e) => {
    e.preventDefault(); 
    
    const nuevaReserva = {
        nombre_huesped: document.getElementById('nombre').value,
        email_huesped: document.getElementById('email').value,
        nombre_alojamiento: document.getElementById('alojamiento').value,
        fecha_entrada: document.getElementById('fecha_in').value,
        fecha_salida: document.getElementById('fecha_out').value,
        importe: parseFloat(document.getElementById('importe').value)
    };

    try {
        const response = await fetch('/?action=crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(nuevaReserva)
        });
        
        const result = await response.json();

        if (response.ok && !result.error) {
            toggleModal(false);
            document.getElementById('filtro-estado').dispatchEvent(new Event('change'));
        } else {
            alert('Error del backend: ' + (result.mensaje || 'Verifica que todos los campos sean correctos.'));
        }
    } catch (error) {
        console.error('Error al crear:', error);
    }
});


// CANCELAR RESERVA
async function cancelarReserva(id) {
    if(!confirm('¿Estás seguro de cancelar esta reserva?')) return;
    
    try {
        const response = await fetch('/?action=cancelar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        if(response.ok) {
            document.getElementById('filtro-estado').dispatchEvent(new Event('change'));
        }
    } catch (error) {
        console.error('Error al cancelar:', error);
    }
}


function actualizarTablaDOM(reservas) {
    const tbody = document.getElementById('tabla-reservas');
    tbody.innerHTML = ''; 

    if (!reservas || reservas.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No hay resultados para este filtro.</td></tr>`;
        return;
    }

    reservas.forEach(r => {
        let badgeHtml = '';
        if (r.estado === 'pendiente') badgeHtml = `<span class="bg-brand-50 text-brand-700 border border-brand-200 rounded-full px-3 py-1 text-xs font-semibold">Pendiente</span>`;
        else if (r.estado === 'confirmada') badgeHtml = `<span class="bg-green-50 text-green-700 border border-green-200 rounded-full px-3 py-1 text-xs font-semibold">Confirmada</span>`;
        else badgeHtml = `<span class="bg-red-50 text-red-700 border border-red-200 rounded-full px-3 py-1 text-xs font-semibold">Cancelada</span>`;

        const botonCancelar = r.estado !== 'cancelada' 
            ? `<button onclick="cancelarReserva(${r.id})" class="text-gray-400 hover:text-red-600 text-sm font-medium transition-colors ml-3">Cancelar</button>` : '';

        tbody.innerHTML += `
            <tr id="reserva-${r.id}" class="hover:bg-gray-50/50 transition-colors duration-200">
                <td class="px-6 py-4 text-sm text-gray-500">#${r.id}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${r.nombre_huesped}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${r.fecha_entrada} <br><span class="text-xs text-gray-400">al ${r.fecha_salida}</span></td>
                <td class="px-6 py-4">${badgeHtml}</td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="/detalle?id=${r.id}" class="text-brand-600 hover:text-brand-700 text-sm font-semibold transition-colors">Ver</a>
                    ${botonCancelar}
                </td>
            </tr>
        `;
    });
}