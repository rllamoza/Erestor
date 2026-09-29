class RankingService {
    static broadcast(io, data) {
        console.log(`📢 Broadcasting RANKING UPDATE`);

        io.emit('ranking_refresh', {
            last_update: new Date().toISOString(),
            triggered_by: data.user_id || 'system'
        });
    }
}

module.exports = RankingService;
