import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Step reorder with drag-and-drop
Alpine.data('stepReorder', (processId) => ({
    dragging: null,
    init() {
        const container = this.$el;
        const items = container.querySelectorAll('[data-step-id]');

        items.forEach(item => {
            const handle = item.querySelector('.drag-handle');
            if (!handle) return;

            handle.setAttribute('draggable', true);

            handle.addEventListener('dragstart', (e) => {
                this.dragging = item;
                item.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const rect = item.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                if (e.clientY < midY) {
                    container.insertBefore(this.dragging, item);
                } else {
                    container.insertBefore(this.dragging, item.nextSibling);
                }
            });

            handle.addEventListener('dragend', () => {
                item.classList.remove('opacity-50');
                const newOrder = [...container.querySelectorAll('[data-step-id]')]
                    .map(el => parseInt(el.dataset.stepId));
                this.saveOrder(newOrder);
            });
        });
    },
    saveOrder(steps) {
        fetch(`/processes/${processId}/steps/reorder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ steps }),
        }).then(r => {
            if (r.ok) {
                window.location.reload();
            }
        });
    }
}));

Alpine.start();
