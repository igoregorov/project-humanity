(function () {
    'use strict';

    function text(value) {
        return typeof value === 'string' ? value : '';
    }

    function init() {
        var container = document.querySelector('[data-living-book-graph]');
        if (!container) return;

        var status = document.querySelector('[data-graph-status]');
        var details = document.querySelector('[data-node-details-content]');

        function setStatus(message) {
            if (status) status.textContent = message;
        }

        function showDetails(node) {
            if (!details) return;

            details.textContent = '';

            var data = node && node.details ? node.details : {};
            var rows = [];

            if (data.kind === 'book') {
                rows = [['Экземпляр', text(data.book_number)]];
            } else if (data.kind === 'entry') {
                rows = [
                    ['Номер экземпляра', text(data.book_number)],
                    ['Автор', text(data.username) || 'Аноним'],
                    ['Дата', text(data.created_at)],
                    ['Текст отклика', text(data.text_response)]
                ];
            } else {
                details.textContent = 'Для этого узла нет дополнительных данных.';
                return;
            }

            rows.forEach(function (row) {
                var line = document.createElement('p');
                var label = document.createElement('strong');
                label.textContent = row[0] + ': ';
                line.appendChild(label);
                line.appendChild(document.createTextNode(row[1] || '—'));
                details.appendChild(line);
            });
        }

        if (!window.vis || !window.vis.Network || !window.vis.DataSet) {
            setStatus('Библиотека графа не загружена.');
            return;
        }

        setStatus('Граф загружается…');

        fetch(container.getAttribute('data-graph-url'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function (payload) {
                if (
                    !payload ||
                    !Array.isArray(payload.nodes) ||
                    !Array.isArray(payload.edges)
                ) {
                    throw new Error('Некорректный формат ответа графа');
                }

                var nodes = [];
                var known = Object.create(null);

                payload.nodes.forEach(function (node) {
                    if (!node || (typeof node.id !== 'string' && typeof node.id !== 'number')) return;
                    var id = String(node.id);
                    if (known[id]) return;
                    known[id] = true;

                    nodes.push({
                        id: id,
                        label: text(node.label).slice(0, 120),
                        group: node.group === 'books' ? 'books' : 'entries',
                        shape: node.shape === 'hexagon' ? 'hexagon' : 'box',
                        color: node.group === 'books' ? '#9c27b0' : '#2196f3',
                        details: node.details && typeof node.details === 'object' ? node.details : {}
                    });
                });

                var edges = [];
                var edgeKeys = Object.create(null);

                payload.edges.forEach(function (edge) {
                    if (!edge) return;
                    var from = String(edge.from);
                    var to = String(edge.to);
                    var key = from + '>' + to;

                    if (!known[from] || !known[to] || from === to || edgeKeys[key]) return;
                    edgeKeys[key] = true;
                    edges.push({ from: from, to: to, arrows: 'to' });
                });

                if (nodes.length === 0) {
                    setStatus('Граф пока пуст: нет книг и откликов.');
                    return;
                }

                setStatus('Выберите узел, чтобы увидеть детали.');

                var network = new window.vis.Network(
                    container,
                    {
                        nodes: new window.vis.DataSet(nodes),
                        edges: new window.vis.DataSet(edges)
                    },
                    {
                        autoResize: true,
                        interaction: { hover: true, navigationButtons: true, keyboard: true },
                        physics: { stabilization: { iterations: 150 } },
                        nodes: { font: { color: '#e0e0e0', face: 'Courier New' } },
                        edges: { color: '#aaaaaa', smooth: { enabled: true, type: 'dynamic' } }
                    }
                );

                network.on('selectNode', function (event) {
                    if (!event.nodes.length) return;
                    var selected = nodes.find(function (node) {
                        return String(node.id) === String(event.nodes[0]);
                    });
                    showDetails(selected);
                });

                network.on('deselectNode', function () {
                    if (details) details.textContent = 'Выберите книгу или отклик на графе.';
                });
            })
            .catch(function (error) {
                console.error('Living Book graph error:', error);
                setStatus('Не удалось загрузить граф. Обновите страницу или попробуйте позже.');
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());