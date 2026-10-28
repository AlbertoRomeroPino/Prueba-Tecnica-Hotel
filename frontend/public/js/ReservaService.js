class ReservaService {
    constructor() {
        this.baseUrl = 'http://localhost/backend/public/index.php/api/';
        this.timeout = 5000; // jQuery usa milisegundos (5 segundos)
    }

    // 1 y 6. Listar y Filtrar reservas
    obtenerReservas(filtros = {}) {
        return $.ajax({
            url: this.baseUrl + 'reservas',
            method: 'GET',
            data: filtros, // jQuery convierte el objeto en parámetros URL automáticamente
            timeout: this.timeout,
            dataType: 'json'
        }).catch(error => {
            console.error('Error en obtenerReservas:', error);
            return []; // Devolvemos array vacío en caso de error, como en PHP
        });
    }

    // 2. Crear una nueva reserva
    crearReserva(datos) {
        return $.ajax({
            url: this.baseUrl + 'reservas',
            method: 'POST',
            data: JSON.stringify(datos),
            contentType: 'application/json', // Importante para enviar JSON puro
            timeout: this.timeout,
            dataType: 'json'
        }).catch(error => {
            console.error('Error en crearReserva:', error);
            return { error: true, mensaje: error.statusText || 'Error de red' };
        });
    }

    // 3. Ver detalle de una reserva específica
    obtenerDetalleReserva(id) {
        return $.ajax({
            url: this.baseUrl + 'reservas/' + id,
            method: 'GET',
            timeout: this.timeout,
            dataType: 'json'
        }).catch(error => {
            console.error('Error en obtenerDetalleReserva:', error);
            return null;
        });
    }

    // 4. Cambiar el estado de la reserva
    cambiarEstado(id, estado) {
        return $.ajax({
            url: this.baseUrl + 'reservas/' + id + '/estado',
            method: 'POST',
            data: JSON.stringify({ estado: estado }),
            contentType: 'application/json',
            timeout: this.timeout,
            dataType: 'json'
        }).catch(error => {
            console.error('Error en cambiarEstado:', error);
            return { error: true, mensaje: error.statusText || 'Error de red' };
        });
    }

    // 5. Historial de eventos de una reserva
    obtenerEventos(id) {
        return $.ajax({
            url: this.baseUrl + 'reservas/' + id + '/eventos',
            method: 'GET',
            timeout: this.timeout,
            dataType: 'json'
        }).catch(error => {
            console.error('Error en obtenerEventos:', error);
            return [];
        });
    }
}