/**
 * public_html/scripts/living-book-graph.js
 *
 * Интерактивный граф «Живой Книги» (vis-network).
 * Изначально показываются только книги; клик по книге раскрывает/скрывает её отклики.
 * Работает через addEventListener, чтобы соответствовать CSP (script-src 'self').
 */
(function () {
    'use strict';

    // Цвета синхронизированы с variables.css и легендой в living-book-graph.css
    var THEME = {
        bg: '#0a0a0a',
        text: '#e0e0e0',
        accent: '#4cc9f0',
        font: "'Courier New', monospace",
        book: {
            background: '#9c27b0',
            border: '#ce93d8',
            highlight: '#ba68c8'
        },
        entry: {
            background: '#12303d',
            border: '#2196f3',
            hover: '#173f50',
            highlight: '#1b4a5e'
        },
        edge: '#6b6b6b'
    };

    var LABEL_MAX_BOOK = 40;
    var LABEL_MAX_ENTRY = 60;
    var PLACEHOLDER = 'Выберите книгу или отклик на графе.';
    var HINT = 'Нажмите на книгу, чтобы показать или скрыть её отклики.';

    function text(value) {
        return typeof value === 'string' ? value : '';
    }

    // Сжимаем пробелы/переносы и обрезаем длинную подпись (полный текст — в «Деталях узла»)
    function truncate(value, max) {
        var clean = value.replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '');
        if (clean.length <= max) return clean;
        return clean.slice(0, max - 1).replace(/\s+$/, '') + '…';
    }

    // «2026-10-26 14:30:00» → «26.10.2026 14:30»; нераспознанный формат возвращаем как есть
    function formatDate(value) {
        var m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(value);
        return m ? m[3] + '.' + m[2] + '.' + m[1] + ' ' + m[4] + ':' + m[5] : value;
    }

    /**
     * Определяет, к какой книге относится каждый отклик:
     *   1) по совпадению details.book_number;
     *   2) по рёбрам — всё, что достижимо от книги через узлы-отклики.
     * Возвращает { bookId: [entryId, ...] }. Отклики без книги в результат не попадают.
     */
    function groupEntriesByBook(nodes, edges, nodesById) {
        var owner = Object.create(null);
        var bookByNumber = Object.create(null);
        var adjacency = Object.create(null);

        nodes.forEach(function (node) {
            var number = text(node.details.book_number);
            if (node.group === 'books' && number !== '' && !bookByNumber[number]) {
                bookByNumber[number] = node.id;
            }
        });

        nodes.forEach(function (node) {
            var number = text(node.details.book_number);
            if (node.group === 'entries' && number !== '' && bookByNumber[number]) {
                owner[node.id] = bookByNumber[number];
            }
        });

        edges.forEach(function (edge) {
            (adjacency[edge.from] = adjacency[edge.from] || []).push(edge.to);
            (adjacency[edge.to] = adjacency[edge.to] || []).push(edge.from);
        });

        nodes.forEach(function (book) {
            if (book.group !== 'books') return;

            var visited = Object.create(null);
            var queue = [book.id];
            visited[book.id] = true;

            while (queue.length) {
                var neighbors = adjacency[queue.shift()] || [];

                for (var i = 0; i < neighbors.length; i++) {
                    var neighborId = neighbors[i];
                    var neighbor = nodesById[neighborId];

                    if (visited[neighborId] || !neighbor || neighbor.group !== 'entries') continue;
                    // Отклик уже привязан к другой книге — не перехватываем
                    if (owner[neighborId] && owner[neighborId] !== book.id) continue;

                    visited[neighborId] = true;
                    owner[neighborId] = book.id;
                    queue.push(neighborId);
                }
            }
        });

        var result = Object.create(null);
        Object.keys(owner).forEach(function (entryId) {
            var bookId = owner[entryId];
            (result[bookId] = result[bookId] || []).push(entryId);
        });

        return result;
    }

    function init() {
        var container = document.querySelector('[data-living-book-graph]');
        if (!container) return;

        var status = document.querySelector('[data-graph-status]');
        var details = document.querySelector('[data-node-details-content]');

        function setStatus(message) {
            if (status) status.textContent = message;
        }

        function appendParagraph(className, message) {
            var p = document.createElement('p');
            p.className = className;
            p.textContent = message;
            details.appendChild(p);
        }

        function showPlaceholder(message) {
            if (!details) return;
            details.textContent = '';
            appendParagraph('living-book-node-details-empty', message);
        }

        /**
         * @param {object} node
         * @param {{count: number, expanded: boolean}} [bookMeta] — только для книг
         */
        function showDetails(node, bookMeta) {
            if (!details || !node) return;

            var data = node.details || {};
            var rows;

            if (node.group === 'books') {
                rows = [['Экземпляр', text(data.book_number) || node.baseLabel]];
                if (bookMeta) rows.push(['Откликов', String(bookMeta.count)]);
            } else {
                rows = [
                    ['Номер экземпляра', text(data.book_number)],
                    ['Автор', text(data.username) || 'Аноним'],
                    ['Дата', formatDate(text(data.created_at))]
                ];
            }

            details.textContent = '';

            var list = document.createElement('dl');
            list.className = 'living-book-node-details-list';

            rows.forEach(function (row) {
                var dt = document.createElement('dt');
                var dd = document.createElement('dd');
                dt.textContent = row[0];
                dd.textContent = row[1] || '—';
                list.appendChild(dt);
                list.appendChild(dd);
            });

            details.appendChild(list);

            if (node.group === 'books' && bookMeta) {
                appendParagraph(
                    'living-book-node-details-hint',
                    bookMeta.count === 0
                        ? 'У этого экземпляра пока нет откликов.'
                        : bookMeta.expanded
                            ? 'Отклики показаны на графе. Нажмите на книгу ещё раз, чтобы скрыть их.'
                            : 'Отклики скрыты. Нажмите на книгу, чтобы показать их.'
                );
            } else if (node.group !== 'books') {
                var quote = document.createElement('blockquote');
                quote.className = 'living-book-node-details-text';
                quote.textContent = text(data.text_response) || '—';
                details.appendChild(quote);
            }
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

                // --- Узлы ---
                var nodes = [];
                var nodesById = Object.create(null);

                payload.nodes.forEach(function (node) {
                    if (!node || (typeof node.id !== 'string' && typeof node.id !== 'number')) return;
                    var id = String(node.id);
                    if (nodesById[id]) return;

                    var isBook = node.group === 'books';
                    var label = truncate(text(node.label), isBook ? LABEL_MAX_BOOK : LABEL_MAX_ENTRY);

                    var item = {
                        id: id,
                        // Форма и цвета задаются группой — так граф всегда совпадает с легендой
                        group: isBook ? 'books' : 'entries',
                        label: label,
                        baseLabel: label,
                        details: node.details && typeof node.details === 'object' ? node.details : {}
                    };

                    nodesById[id] = item;
                    nodes.push(item);
                });

                // --- Рёбра ---
                var edges = [];
                var edgeKeys = Object.create(null);

                payload.edges.forEach(function (edge) {
                    if (!edge) return;
                    var from = String(edge.from);
                    var to = String(edge.to);
                    var key = from + '>' + to;

                    if (!nodesById[from] || !nodesById[to] || from === to || edgeKeys[key]) return;
                    edgeKeys[key] = true;
                    edges.push({ id: key, from: from, to: to });
                });

                if (nodes.length === 0) {
                    setStatus('Граф пока пуст: нет книг и откликов.');
                    return;
                }

                // --- Раскрытие/скрытие откликов ---
                var entriesByBook = groupEntriesByBook(nodes, edges, nodesById);
                var ownerOf = Object.create(null);
                var expanded = Object.create(null);

                Object.keys(entriesByBook).forEach(function (bookId) {
                    entriesByBook[bookId].forEach(function (entryId) {
                        ownerOf[entryId] = bookId;
                    });
                });

                function countEntries(bookId) {
                    return (entriesByBook[bookId] || []).length;
                }

                // Книги и «бесхозные» отклики видны всегда, остальные — только у раскрытой книги
                function isVisible(id) {
                    var bookId = ownerOf[id];
                    return !bookId || expanded[bookId] === true;
                }

                function bookLabel(bookId) {
                    var count = countEntries(bookId);
                    var base = nodesById[bookId].baseLabel;
                    if (count === 0) return base;
                    return (expanded[bookId] ? '− ' : '+ ') + base + ' (' + count + ')';
                }

                function edgeHidden(edge) {
                    return !(isVisible(edge.from) && isVisible(edge.to));
                }

                nodes.forEach(function (node) {
                    if (node.group === 'books') node.label = bookLabel(node.id);
                    node.hidden = !isVisible(node.id);
                });

                edges.forEach(function (edge) {
                    edge.hidden = edgeHidden(edge);
                });

                var nodesData = new window.vis.DataSet(nodes);
                var edgesData = new window.vis.DataSet(edges);

                function toggleBook(bookId) {
                    var entryIds = entriesByBook[bookId] || [];
                    if (entryIds.length === 0) return;

                    expanded[bookId] = !expanded[bookId];

                    nodesData.update(
                        entryIds
                            .map(function (id) {
                                return { id: id, hidden: !expanded[bookId] };
                            })
                            .concat([{ id: bookId, label: bookLabel(bookId) }])
                    );

                    edgesData.update(edges.map(function (edge) {
                        return { id: edge.id, hidden: edgeHidden(edge) };
                    }));
                }

                setStatus('Граф строится…');

                var options = {
                    autoResize: true,
                    interaction: {
                        hover: true,
                        navigationButtons: true,
                        // Клавиши работают только когда граф в фокусе, а не на всей странице
                        keyboard: { enabled: true, bindToWindow: false },
                        multiselect: false
                    },
                    physics: {
                        solver: 'barnesHut',
                        barnesHut: { springLength: 120, avoidOverlap: 0.2 },
                        stabilization: { enabled: true, iterations: 200, fit: true }
                    },
                    nodes: {
                        borderWidth: 1,
                        borderWidthSelected: 2,
                        font: { color: THEME.text, face: THEME.font, size: 13 }
                    },
                    edges: {
                        width: 1.5,
                        selectionWidth: 1,
                        hoverWidth: 0.5,
                        color: {
                            color: THEME.edge,
                            highlight: THEME.accent,
                            hover: THEME.accent,
                            inherit: false
                        },
                        arrows: { to: { enabled: true, scaleFactor: 0.6 } },
                        smooth: { enabled: true, type: 'continuous', roundness: 0.4 }
                    },
                    groups: {
                        books: {
                            shape: 'hexagon',
                            size: 18,
                            color: {
                                background: THEME.book.background,
                                border: THEME.book.border,
                                highlight: { background: THEME.book.highlight, border: THEME.accent },
                                hover: { background: THEME.book.highlight, border: THEME.accent }
                            },
                            // Подпись под шестиугольником — с тёмной обводкой, чтобы читалась поверх рёбер
                            font: {
                                color: THEME.text,
                                face: THEME.font,
                                size: 14,
                                strokeWidth: 4,
                                strokeColor: THEME.bg
                            }
                        },
                        entries: {
                            shape: 'box',
                            margin: 10,
                            widthConstraint: { maximum: 200 },
                            shapeProperties: { borderRadius: 4 },
                            color: {
                                background: THEME.entry.background,
                                border: THEME.entry.border,
                                highlight: { background: THEME.entry.highlight, border: THEME.accent },
                                hover: { background: THEME.entry.hover, border: THEME.accent }
                            },
                            font: {
                                color: THEME.text,
                                face: THEME.font,
                                size: 13,
                                strokeWidth: 0
                            }
                        }
                    }
                };

                var network = new window.vis.Network(
                    container,
                    { nodes: nodesData, edges: edgesData },
                    options
                );

                // После раскладки выключаем физику и подгоняем масштаб под видимые узлы
                network.once('stabilizationIterationsDone', function () {
                    network.setOptions({ physics: false });

                    var visibleIds = nodes
                        .filter(function (node) { return isVisible(node.id); })
                        .map(function (node) { return node.id; });

                    if (visibleIds.length) network.fit({ nodes: visibleIds });

                    setStatus(HINT);
                });

                network.on('hoverNode', function () {
                    container.style.cursor = 'pointer';
                });

                network.on('blurNode', function () {
                    container.style.cursor = '';
                });

                // 'click' (а не 'selectNode') — срабатывает и при повторном клике по уже выбранной книге
                network.on('click', function (event) {
                    if (!event.nodes.length) {
                        showPlaceholder(PLACEHOLDER);
                        return;
                    }

                    var id = String(event.nodes[0]);
                    var node = nodesById[id];
                    if (!node) return;

                    if (node.group === 'books') {
                        toggleBook(id);
                        showDetails(node, {
                            count: countEntries(id),
                            expanded: expanded[id] === true
                        });
                    } else {
                        showDetails(node);
                    }
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