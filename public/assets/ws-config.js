// Настройки подключения к WebSocket-серверу для получения и отправки статистики.
// Укажите домен и путь до вашего WebSocket API.
// Пример: domain: 'ws.example.com', path: '/quiz-stream'
window.__WS_CONFIG__ = {
    domain: window.location.host,
    path: '/api/1',
};
