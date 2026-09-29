const AttendanceService = require('../../Features/BroadcastAttendance/AttendanceService');
const RankingService = require('../../Features/BroadcastRanking/RankingService');

class HttpGateway {
    static init(app, io) {
        // Auth Middleware
        const validateSecret = (req, res, next) => {
            const secret = req.headers['x-auth-secret'];
            if (secret !== process.env.AUTH_SECRET) {
                return res.status(403).json({ error: 'Unauthorized' });
            }
            next();
        };

        // endpoint for PHP triggers
        app.post('/emit', validateSecret, (req, res) => {
            const { event, data } = req.body;

            if (!event || !data) {
                return res.status(400).json({ error: 'Missing event or data' });
            }

            console.log(`📢 Internal trigger: ${event}`);

            // Delegate to business features
            switch (event) {
                case 'attendance_registered':
                    AttendanceService.broadcast(io, data);
                    break;
                case 'ranking_updated':
                    RankingService.broadcast(io, data);
                    break;
                default:
                    io.emit(event, data);
            }

            res.json({ status: 'emitted' });
        });

        app.get('/health', (req, res) => res.send('OK'));
    }
}

module.exports = HttpGateway;
