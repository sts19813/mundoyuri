(function () {
    const catalog = document.querySelector('[data-home-catalog]');
    const select = catalog?.querySelector('[data-home-catalog-sort]');
    const grid = catalog?.querySelector('.row');
    if (!select || !grid) return;

    const items = Array.from(grid.querySelectorAll('[data-home-catalog-item]'));
    const originalOrder = new Map(items.map((item, index) => [item, index]));
    const collator = new Intl.Collator('es', { sensitivity: 'base', numeric: true });
    const compareTitle = (a, b) => collator.compare(a.dataset.title, b.dataset.title)
        || Number(a.dataset.id) - Number(b.dataset.id);

    select.addEventListener('change', function () {
        const sort = select.value;
        const ordered = [...items].sort((a, b) => {
            if (sort === 'title') return compareTitle(a, b);
            if (sort === 'views') return Number(b.dataset.views) - Number(a.dataset.views) || compareTitle(a, b);
            // Restore the server's default order, including undated titles last.
            return originalOrder.get(a) - originalOrder.get(b);
        });
        const fragment = document.createDocumentFragment();
        ordered.forEach(item => fragment.appendChild(item));
        grid.appendChild(fragment);
    });
})();
