(function () {
    'use strict';

    const containers = document.querySelectorAll('.global-search-container');
    if (!containers.length) return;

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    containers.forEach((container) => {
        const input = container.querySelector('.global-search-input');
        const panel = container.querySelector('.global-search-results');
        if (!input || !panel) return;

        let timer = null;
        let controller = null;
        let activeIndex = -1;
        let items = [];

        const closePanel = () => {
            panel.hidden = true;
            panel.innerHTML = '';
            items = [];
            activeIndex = -1;
        };

        const selectItem = (index) => {
            items.forEach((item, itemIndex) => {
                item.classList.toggle('is-active', itemIndex === index);
            });
            activeIndex = index;
            if (items[index]) items[index].scrollIntoView({ block: 'nearest' });
        };

        const render = (results, keyword) => {
            if (!results.length) {
                panel.innerHTML = `<div class="global-search-empty">Không tìm thấy kết quả cho “${escapeHtml(keyword)}”</div>`;
                panel.hidden = false;
                items = [];
                return;
            }

            panel.innerHTML = results.map((result) => {
                const icon = result.type === 'project' ? '📁' : '✓';
                return `
                    <a class="global-search-result" href="${escapeHtml(result.url)}">
                        <span class="global-search-result-icon ${escapeHtml(result.type)}">${icon}</span>
                        <span class="global-search-result-content">
                            <strong>${escapeHtml(result.title)}</strong>
                            <small>${escapeHtml(result.subtitle)}</small>
                        </span>
                    </a>`;
            }).join('');

            panel.hidden = false;
            items = Array.from(panel.querySelectorAll('.global-search-result'));
            activeIndex = -1;
        };

        const search = async () => {
            const keyword = input.value.trim();
            if (keyword.length < 2) {
                closePanel();
                return;
            }

            if (controller) controller.abort();
            controller = new AbortController();
            panel.hidden = false;
            panel.innerHTML = '<div class="global-search-loading">Đang tìm kiếm...</div>';

            try {
                const response = await fetch(`index.php?action=globalSearch&q=${encodeURIComponent(keyword)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    signal: controller.signal
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Không thể tìm kiếm');
                }
                render(Array.isArray(data.results) ? data.results : [], keyword);
            } catch (error) {
                if (error.name === 'AbortError') return;
                panel.innerHTML = `<div class="global-search-empty">${escapeHtml(error.message || 'Tìm kiếm thất bại')}</div>`;
                panel.hidden = false;
            }
        };

        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(search, 250);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePanel();
                input.blur();
                return;
            }
            if (!items.length) return;
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                selectItem((activeIndex + 1) % items.length);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                selectItem((activeIndex - 1 + items.length) % items.length);
            } else if (event.key === 'Enter') {
                event.preventDefault();
                const selected = items[activeIndex >= 0 ? activeIndex : 0];
                if (selected) window.location.href = selected.href;
            }
        });

        input.addEventListener('focus', () => {
            if (panel.innerHTML.trim()) panel.hidden = false;
        });

        document.addEventListener('click', (event) => {
            if (!container.contains(event.target)) closePanel();
        });
    });
})();
