CREATE TABLE reserva (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_huesped VARCHAR(100) NOT NULL,
    email_huesped VARCHAR(100) NOT NULL,
    nombre_alojamiento VARCHAR(150) NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    importe DECIMAL(10, 2) NOT NULL,
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reserva_evento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reserva(id) ON DELETE CASCADE
);

INSERT INTO reserva (id, nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas, fecha_creacion) VALUES
(1, 'Carlos García', 'carlos.garcia@email.com', 'Habitación Doble Estándar', '2026-11-01', '2026-11-05', 'confirmada', 450.00, 'Llegada tarde a las 23:00', '2026-10-15 08:30:00'),
(2, 'María López', 'maria.lopez@email.com', 'Suite Vista Mar', '2026-11-10', '2026-11-15', 'pendiente', 1200.50, 'Celebración de aniversario', '2026-10-15 09:15:00'),
(3, 'Juan Martínez', 'juan.martinez@email.com', 'Habitación Individual', '2026-10-20', '2026-10-22', 'confirmada', 180.00, 'Ninguna', '2026-10-15 10:05:00'),
(4, 'Laura Fernández', 'laura.f@email.com', 'Apartamento Familiar', '2026-12-24', '2026-12-31', 'confirmada', 1500.00, 'Necesitan cuna para bebé', '2026-10-15 11:20:00'),
(5, 'Pedro Sánchez', 'psanchez@email.com', 'Habitación Doble Superior', '2026-10-18', '2026-10-20', 'cancelada', 250.00, 'Cancelado por enfermedad', '2026-10-15 12:00:00'),
(6, 'Ana Gómez', 'ana.gomez@email.com', 'Habitación Doble Estándar', '2026-11-05', '2026-11-08', 'pendiente', 300.00, 'Camas separadas por favor', '2026-10-15 12:45:00'),
(7, 'Luis Ramírez', 'l.ramirez@email.com', 'Suite Presidencial', '2026-11-20', '2026-11-25', 'confirmada', 2500.00, 'Transfer desde el aeropuerto', '2026-10-15 13:10:00'),
(8, 'Carmen Torres', 'carmen.torres@email.com', 'Habitación Individual', '2026-10-17', '2026-10-19', 'confirmada', 190.00, 'Alérgico al polvo', '2026-10-15 13:50:00'),
(9, 'Jorge Ruiz', 'jruiz@email.com', 'Apartamento Familiar', '2027-01-05', '2027-01-10', 'pendiente', 950.00, 'Planta baja preferiblemente', '2026-10-15 14:25:00'),
(10, 'Elena Navarro', 'elena.nav@email.com', 'Habitación Doble Superior', '2026-10-25', '2026-10-28', 'confirmada', 420.00, 'Vista a la piscina', '2026-10-15 15:00:00'),
(11, 'Miguel Díaz', 'miguel.diaz@email.com', 'Habitación Doble Estándar', '2026-11-12', '2026-11-14', 'cancelada', 220.00, 'Vuelo cancelado', '2026-10-15 15:30:00'),
(12, 'Lucía Castro', 'lucia.castro@email.com', 'Suite Vista Mar', '2026-12-01', '2026-12-05', 'confirmada', 1100.00, 'Botella de cava en la habitación', '2026-10-15 16:15:00'),
(13, 'Roberto Vargas', 'rvargas@email.com', 'Habitación Individual', '2026-10-22', '2026-10-24', 'pendiente', 200.00, 'Factura a nombre de la empresa', '2026-10-15 16:40:00'),
(14, 'Sofía Ortiz', 'sofia.ortiz@email.com', 'Habitación Doble Superior', '2026-11-08', '2026-11-12', 'confirmada', 580.00, 'Late check-out solicitado', '2026-10-15 17:05:00'),
(15, 'David Molina', 'david.m@email.com', 'Apartamento Familiar', '2026-11-28', '2026-12-02', 'confirmada', 850.00, 'Viajan con mascota', '2026-10-15 17:35:00'),
(16, 'Marta Delgado', 'marta.delgado@email.com', 'Suite Vista Mar', '2026-10-28', '2026-10-31', 'pendiente', 900.00, 'Vegetariana', '2026-10-15 18:10:00'),
(17, 'Pablo Méndez', 'pmendez@email.com', 'Habitación Doble Estándar', '2026-11-15', '2026-11-18', 'confirmada', 330.00, 'Ninguna', '2026-10-15 18:45:00'),
(18, 'Isabel Gil', 'isabel.gil@email.com', 'Habitación Individual', '2026-12-10', '2026-12-12', 'cancelada', 180.00, 'Cambio de planes', '2026-10-15 19:20:00'),
(19, 'Fernando Cruz', 'fcruz@email.com', 'Habitación Doble Superior', '2027-02-14', '2027-02-16', 'pendiente', 400.00, 'San Valentín', '2026-10-15 19:55:00'),
(20, 'Raquel Reyes', 'raquel.reyes@email.com', 'Suite Presidencial', '2026-12-28', '2027-01-02', 'confirmada', 3500.00, 'Cena de nochevieja incluida', '2026-10-15 20:30:00');

INSERT INTO reserva_evento (id, reserva_id, tipo, descripcion, fecha_creacion) VALUES
(1, 1, 'CREACION', 'El cliente creó la reserva vía web', '2026-10-15 08:30:05'),
(2, 1, 'CREACION', 'Pago de señal del 50% recibido con tarjeta', '2026-10-15 08:35:00'),
(3, 2, 'CREACION', 'Reserva pendiente de confirmación de pago', '2026-10-15 09:15:05'),
(4, 3, 'CREACION', 'Reserva rápida desde portal de afiliados', '2026-10-15 10:05:10'),
(5, 3, 'CONFIRMACION', 'Confirmación automática enviada', '2026-10-15 10:05:15'),
(6, 4, 'CREACION', 'Reserva familiar bloqueada telefónicamente', '2026-10-15 11:20:20'),
(7, 4, 'NOTIFICACION', 'Se añadió nota sobre necesidad de cuna', '2026-10-15 11:25:00'),
(8, 5, 'CREACION', 'Reserva inicial creada', '2026-10-15 12:00:05'),
(9, 5, 'CANCELACION', 'El cliente solicitó cancelación telefónica', '2026-10-15 14:00:00'),
(10, 6, 'CREACION', 'Reserva creada, pendiente de pago', '2026-10-15 12:45:10'),
(11, 7, 'CREACION', 'Reserva VIP gestionada por agencia', '2026-10-15 13:10:05'),
(12, 7, 'MODIFICACION', 'Se añadió solicitud de transfer al aeropuerto', '2026-10-15 13:15:00'),
(13, 8, 'CREACION', 'Reserva de habitación individual', '2026-10-15 13:50:05'),
(14, 9, 'CREACION', 'Pre-reserva para el próximo año', '2026-10-15 14:25:10'),
(15, 10, 'CREACION', 'Reserva creada correctamente', '2026-10-15 15:00:05'),
(16, 11, 'CREACION', 'Reserva estándar', '2026-10-15 15:30:05'),
(17, 11, 'CANCELACION', 'Cancelación automática por falta de pago', '2026-10-17 10:00:00'),
(18, 12, 'CREACION', 'Reserva romántica creada', '2026-10-15 16:15:05'),
(19, 13, 'CREACION', 'Reserva de negocios pendiente de datos fiscales', '2026-10-15 16:40:05'),
(20, 14, 'CREACION', 'Reserva creada con solicitud de late check-out', '2026-10-15 17:05:05');