/**
 * CustomSelect — lightweight searchable select built with Tailwind CSS.
 *
 * Features:
 * - Renders a styled trigger button + dropdown panel
 * - Live search/filter options as user types
 * - Syncs value to the underlying native <select> (form submit works normally)
 * - Dispatches native 'change' event — compatible with existing onchange / addEventListener
 * - Guards against double-init via el._cs property
 *
 * Usage:
 *   new CustomSelect(nativeSelectElement)
 *   window.CustomSelect is set in app.js for use in blade scripts
 */
export default class CustomSelect {
    constructor(el) {
        if (el._cs) return;
        el._cs = this;
        this.el = el;
        this.isOpen = false;
        this._build();
        this._bind();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    _placeholder() {
        return Array.from(this.el.options).find(o => !o.value)?.text ?? 'Pilih...';
    }

    _currentOption() {
        return Array.from(this.el.options).find(o => o.value && o.value === this.el.value) ?? null;
    }

    // ── DOM Build ──────────────────────────────────────────────────────────────

    _build() {
        this.el.hidden = true;
        this.el.style.display = 'none'; // override Tailwind utility classes (e.g. `block`) that outlast the hidden attribute

        // Inherit error border state from native select (Laravel @error directive)
        const errorBorder = this.el.classList.contains('border-red-400')
            ? 'border-red-400'
            : 'border-gray-200';

        // Wrap
        this.root = document.createElement('div');
        this.root.className = 'cs-root relative';
        this.el.after(this.root);
        this.root.appendChild(this.el);

        const sel = this._currentOption();

        this.root.insertAdjacentHTML('afterbegin', `
            <button type="button"
                class="cs-btn w-full flex items-center justify-between gap-2 rounded-xl border ${errorBorder} bg-white px-3.5 py-2.5 text-sm shadow-sm transition-colors hover:border-gray-300 focus:outline-none focus:border-blue-400 focus:ring focus:ring-blue-100">
                <span class="cs-label flex-1 truncate text-left ${sel ? 'text-gray-800' : 'text-gray-400'}">${sel ? sel.text : this._placeholder()}</span>
                <svg class="cs-icon h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-200"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="cs-panel hidden absolute left-0 top-[calc(100%+4px)] z-50 w-full min-w-max
                        overflow-hidden rounded-xl border border-gray-200 bg-white
                        shadow-xl shadow-gray-900/10">
                <div class="p-2 border-b border-gray-100">
                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50
                                px-2.5 py-1.5 transition-colors
                                focus-within:border-blue-400 focus-within:bg-white focus-within:ring focus-within:ring-blue-100">
                        <svg class="h-3.5 w-3.5 flex-shrink-0 text-gray-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input class="cs-search flex-1 bg-transparent text-sm text-gray-700
                                      outline-none placeholder:text-gray-400"
                            type="text" placeholder="Cari..." autocomplete="off">
                    </div>
                </div>
                <ul class="cs-list max-h-52 overflow-y-auto py-1"></ul>
            </div>
        `);

        this.btn    = this.root.querySelector('.cs-btn');
        this.label  = this.root.querySelector('.cs-label');
        this.icon   = this.root.querySelector('.cs-icon');
        this.panel  = this.root.querySelector('.cs-panel');
        this.search = this.root.querySelector('.cs-search');
        this.list   = this.root.querySelector('.cs-list');

        this._renderList();
    }

    _renderList(q = '') {
        this.list.innerHTML = '';
        const lq = q.toLowerCase().trim();
        let hasResults = false;

        for (const opt of this.el.options) {
            // Placeholder row — only show when not filtering
            if (!opt.value) {
                if (!lq) {
                    this.list.appendChild(this._makeItem(opt.text, '', false, true));
                }
                continue;
            }

            if (lq && !opt.text.toLowerCase().includes(lq)) continue;

            hasResults = true;
            this.list.appendChild(this._makeItem(opt.text, opt.value, opt.value === this.el.value));
        }

        if (lq && !hasResults) {
            const li = document.createElement('li');
            li.className = 'px-3.5 py-2.5 text-sm text-gray-400';
            li.textContent = 'Tidak ada hasil';
            this.list.appendChild(li);
        }
    }

    _makeItem(text, value, active = false, isPlaceholder = false) {
        const li = document.createElement('li');
        li.dataset.v = value;
        li.className = [
            'cs-item flex cursor-pointer items-center gap-2 px-3.5 py-2 text-sm transition-colors select-none',
            isPlaceholder ? 'text-gray-400 hover:bg-gray-50'
                : active   ? 'bg-blue-50/70 font-medium text-blue-600'
                           : 'text-gray-700 hover:bg-blue-50/60 hover:text-blue-700',
        ].join(' ');

        const span = document.createElement('span');
        span.className = 'flex-1';
        span.textContent = text;
        li.appendChild(span);

        if (active) {
            li.insertAdjacentHTML('beforeend', `
                <svg class="h-3.5 w-3.5 flex-shrink-0 text-blue-500"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>`);
        }

        return li;
    }

    // ── Events ─────────────────────────────────────────────────────────────────

    _bind() {
        // Toggle open/close
        this.btn.addEventListener('click', e => {
            e.stopPropagation();
            this.isOpen ? this._close() : this._open();
        });

        // Live search
        this.search.addEventListener('input', () => this._renderList(this.search.value));

        // Option click
        this.list.addEventListener('click', e => {
            const item = e.target.closest('.cs-item');
            if (item) this._pick(item.dataset.v);
        });

        // Close on outside click (capture phase to beat stopPropagation)
        document.addEventListener('click', e => {
            if (!this.root.contains(e.target)) this._close();
        }, true);

        // Escape to close
        this.root.addEventListener('keydown', e => {
            if (e.key === 'Escape') this._close();
        });
    }

    _open() {
        // Close any other open panel first
        document.querySelectorAll('.cs-panel:not(.hidden)').forEach(p => {
            if (p !== this.panel) {
                p.classList.add('hidden');
                p.closest('.cs-root')?.querySelector('.cs-icon')?.removeAttribute('style');
                const root = p.closest('.cs-root');
                if (root) root._cs && (root._cs.isOpen = false);
            }
        });

        this._renderList(this.search.value);
        this.panel.classList.remove('hidden');
        this.icon.style.transform = 'rotate(180deg)';
        this.isOpen = true;

        requestAnimationFrame(() => this.search.focus());
    }

    _close() {
        if (!this.isOpen) return;
        this.panel.classList.add('hidden');
        this.icon.removeAttribute('style');
        this.isOpen = false;
    }

    _pick(value) {
        this.el.value = value;
        const opt = this._currentOption();
        const placeholder = this._placeholder();

        if (opt) {
            this.label.textContent = opt.text;
            this.label.className = 'cs-label flex-1 truncate text-left text-gray-800';
        } else {
            this.label.textContent = placeholder;
            this.label.className = 'cs-label flex-1 truncate text-left text-gray-400';
        }

        this._close();

        // Dispatch native change event — triggers onchange attribute & addEventListener handlers
        this.el.dispatchEvent(new Event('change', { bubbles: true }));
    }
}
