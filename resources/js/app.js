/** BTX Desk shared UI behaviours. AdminLTE owns jQuery and Bootstrap. */
(function () {
    'use strict';

    const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'mp4', 'kmz', 'kml', 'zip'];
    const themeStorageKey = 'btx-theme';

    function readTheme() {
        try {
            return window.localStorage.getItem(themeStorageKey) === 'dark' ? 'dark' : 'light';
        } catch (error) {
            return 'light';
        }
    }

    function storeTheme(theme) {
        try {
            window.localStorage.setItem(themeStorageKey, theme);
        } catch (error) {
            // Storage may be unavailable in private or restricted browsing.
        }
    }

    function applyTheme(theme, persist) {
        const dark = theme === 'dark';
        const navbar = document.querySelector('.main-header.navbar');
        const toggle = document.getElementById('btxThemeToggle');

        document.documentElement.dataset.btxTheme = dark ? 'dark' : 'light';
        document.body.classList.toggle('dark-mode', dark);

        if (navbar) {
            navbar.classList.toggle('navbar-dark', dark);
            navbar.classList.toggle('navbar-light', !dark);
            navbar.classList.toggle('navbar-white', !dark);
        }

        if (toggle) {
            const label = dark ? 'Ativar modo claro' : 'Ativar modo escuro';
            const icon = toggle.querySelector('i');
            toggle.setAttribute('aria-label', label);
            toggle.setAttribute('aria-pressed', String(dark));
            toggle.setAttribute('title', label);
            if (icon) icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
        }

        if (persist) storeTheme(dark ? 'dark' : 'light');

        window.dispatchEvent(new CustomEvent('btx:theme-changed', {
            detail: { theme: dark ? 'dark' : 'light' }
        }));
    }

    function initThemeToggle() {
        const toggle = document.getElementById('btxThemeToggle');
        if (!toggle) return;

        applyTheme(readTheme(), false);
        toggle.addEventListener('click', () => {
            applyTheme(document.body.classList.contains('dark-mode') ? 'light' : 'dark', true);
        });
    }

    function initSidebarTransitions() {
        const body = document.body;
        const toggles = document.querySelectorAll('[data-widget="pushmenu"]');
        let expansionTimer;

        toggles.forEach(toggle => toggle.addEventListener('click', () => {
            if (!body.classList.contains('sidebar-collapse')) return;

            window.clearTimeout(expansionTimer);
            body.classList.add('btx-sidebar-expanding');
            expansionTimer = window.setTimeout(() => {
                body.classList.remove('btx-sidebar-expanding');
            }, 320);
        }));
    }

    function chartThemeColors() {
        const styles = getComputedStyle(document.body);
        return {
            text: styles.getPropertyValue('--btx-text').trim() || '#273444',
            muted: styles.getPropertyValue('--btx-text-muted').trim() || '#667085',
            border: styles.getPropertyValue('--btx-border').trim() || '#dfe5ec',
            surface: styles.getPropertyValue('--btx-surface').trim() || '#ffffff'
        };
    }

    function themeChart(chart) {
        if (!chart) return;
        const colors = chartThemeColors();
        const options = chart.options || {};
        const plugins = options.plugins || (options.plugins = {});
        const legend = plugins.legend || (plugins.legend = {});
        legend.labels = Object.assign({}, legend.labels, { color: colors.text });

        Object.values(options.scales || {}).forEach(scale => {
            scale.ticks = Object.assign({}, scale.ticks, { color: colors.muted });
            scale.grid = Object.assign({}, scale.grid, { color: colors.border });
            if (scale.title) scale.title.color = colors.text;
        });

        if (chart.config && chart.config.type === 'doughnut') {
            chart.data.datasets.forEach(dataset => { dataset.borderColor = colors.surface; });
        }

        chart.update();
    }

    window.BtxTheme = {
        current: readTheme,
        registerChart(chart) {
            themeChart(chart);
            window.addEventListener('btx:theme-changed', () => themeChart(chart));
            return chart;
        }
    };

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        return `${(bytes / Math.pow(1024, index)).toFixed(index ? 1 : 0)} ${units[index]}`;
    }

    function extensionOf(file) {
        const parts = file.name.toLowerCase().split('.');
        return parts.length > 1 ? parts.pop() : '';
    }

    function iconFor(extension) {
        if (['jpg', 'jpeg', 'png'].includes(extension)) return 'fa-file-image';
        if (extension === 'pdf') return 'fa-file-pdf';
        if (['doc', 'docx'].includes(extension)) return 'fa-file-word';
        if (['xls', 'xlsx'].includes(extension)) return 'fa-file-excel';
        if (['zip', 'kmz'].includes(extension)) return 'fa-file-archive';
        if (extension === 'mp4') return 'fa-file-video';
        return 'fa-file-alt';
    }

    function initUploader(root) {
        const input = root.querySelector('.btx-uploader__input');
        const dropzone = root.querySelector('.btx-uploader__dropzone');
        const list = root.querySelector('.btx-uploader__files');
        const counter = root.querySelector('.btx-uploader__counter');
        const errors = root.querySelector('.btx-uploader__errors');
        const maxFiles = Number(root.dataset.maxFiles || 5);
        const maxBytes = Number(root.dataset.maxSizeMb || 5) * 1024 * 1024;
        const accepted = (root.dataset.extensions || allowedExtensions.join(',')).split(',').map(value => value.trim().toLowerCase());
        let files = [];
        let previewUrls = [];

        function identity(file) { return `${file.name}:${file.size}:${file.lastModified}`; }
        function clearPreviews() { previewUrls.forEach(url => URL.revokeObjectURL(url)); previewUrls = []; }
        function syncInput() {
            const transfer = new DataTransfer();
            files.forEach(file => transfer.items.add(file));
            input.files = transfer.files;
        }
        function announce(messages) {
            errors.hidden = messages.length === 0;
            errors.innerHTML = '';
            messages.forEach(message => {
                const item = document.createElement('div');
                item.textContent = message;
                errors.appendChild(item);
            });
        }
        function render() {
            clearPreviews();
            list.innerHTML = '';
            files.forEach((file, index) => {
                const extension = extensionOf(file);
                const item = document.createElement('li');
                item.className = 'btx-uploader__file';
                const preview = document.createElement('span');
                preview.className = 'btx-uploader__preview';
                if (['jpg', 'jpeg', 'png'].includes(extension)) {
                    const url = URL.createObjectURL(file);
                    previewUrls.push(url);
                    const image = document.createElement('img');
                    image.src = url;
                    image.alt = '';
                    preview.appendChild(image);
                } else {
                    preview.innerHTML = `<i class="fas ${iconFor(extension)}" aria-hidden="true"></i>`;
                }
                const details = document.createElement('span');
                details.innerHTML = `<span class="btx-uploader__name"></span><span class="btx-uploader__meta"></span>`;
                details.querySelector('.btx-uploader__name').textContent = file.name;
                details.querySelector('.btx-uploader__meta').textContent = `${extension.toUpperCase() || 'ARQUIVO'} · ${formatBytes(file.size)}`;
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'btx-uploader__remove';
                remove.setAttribute('aria-label', `Remover ${file.name}`);
                remove.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';
                remove.addEventListener('click', () => { files.splice(index, 1); syncInput(); render(); announce([]); });
                item.append(preview, details, remove);
                list.appendChild(item);
            });
            counter.textContent = `${files.length} de ${maxFiles} arquivo${files.length === 1 ? '' : 's'}`;
        }
        function addFiles(candidates) {
            const messages = [];
            const existing = new Set(files.map(identity));
            Array.from(candidates).forEach(file => {
                const extension = extensionOf(file);
                if (files.length >= maxFiles) { messages.push(`Limite de ${maxFiles} arquivos atingido.`); return; }
                if (!accepted.includes(extension)) { messages.push(`${file.name}: formato não permitido.`); return; }
                if (file.size > maxBytes) { messages.push(`${file.name}: excede ${root.dataset.maxSizeMb || 5} MB.`); return; }
                if (existing.has(identity(file))) { messages.push(`${file.name}: arquivo já selecionado.`); return; }
                files.push(file); existing.add(identity(file));
            });
            syncInput(); render(); announce([...new Set(messages)]);
        }

        dropzone.addEventListener('click', () => input.click());
        dropzone.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); input.click(); }
        });
        input.addEventListener('change', () => addFiles(input.files));
        ['dragenter', 'dragover'].forEach(type => dropzone.addEventListener(type, event => { event.preventDefault(); dropzone.classList.add('is-dragging'); }));
        ['dragleave', 'drop'].forEach(type => dropzone.addEventListener(type, event => { event.preventDefault(); dropzone.classList.remove('is-dragging'); }));
        dropzone.addEventListener('drop', event => addFiles(event.dataTransfer.files));
        window.addEventListener('beforeunload', clearPreviews, { once: true });
        render();
    }

    document.addEventListener('DOMContentLoaded', () => {
        initThemeToggle();
        initSidebarTransitions();
        document.querySelectorAll('[data-btx-uploader]').forEach(initUploader);
        document.querySelectorAll('form').forEach(form => form.addEventListener('submit', () => {
            const submit = form.querySelector('button[type="submit"]');
            if (submit && form.checkValidity()) { submit.disabled = true; submit.setAttribute('aria-busy', 'true'); }
        }));
    });
})();
