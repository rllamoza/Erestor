require('dotenv').config();
const express = require('express');
const http = require('http');
const cors = require('cors');
const SocketServer = require('./src/Infrastructure/Server/SocketServer');
const HttpGateway = require('./src/Infrastructure/Server/HttpGateway');

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);

// Initialize Socket.io
const io = SocketServer.init(server);

// Initialize HTTP Gateway (for PHP triggers)
HttpGateway.init(app, io);

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`🚀 Realtime service (Screaming Architecture) running on port ${PORT}`);
});
