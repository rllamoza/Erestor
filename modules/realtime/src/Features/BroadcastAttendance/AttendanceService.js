class AttendanceService {
    static broadcast(io, data) {
        // Enforce Screaming Architecture: This service knows what an "Attendance" record looks like
        console.log(`📢 Broadcasting NEW ATTENDANCE: ${data.usuario_nombre} at ${data.evento_nombre}`);

        // We can transform data here if needed before broadcasting
        io.emit('attendance_update', {
            id: data.id,
            usuario: data.usuario_nombre,
            evento: data.evento_nombre,
            fecha: new Date().toISOString(),
            puntos: data.puntos_obtenidos
        });
    }
}

module.exports = AttendanceService;
