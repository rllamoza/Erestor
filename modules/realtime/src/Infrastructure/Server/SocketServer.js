const { Server } = require('socket.io');

class SocketServer {
    static init(server) {
        const io = new Server(server, {
            cors: {
                origin: "*",
                methods: ["GET", "POST"]
            }
        });

        io.on('connection', (socket) => {
            console.log(`🔌 New client connected: ${socket.id}`);

            socket.on('disconnect', () => {
                console.log(`❌ Client disconnected: ${socket.id}`);
            });
        });

        return io;
    }
}

module.exports = SocketServer;
