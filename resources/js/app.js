import Alpine from 'alpinejs';
import { Chart, LineController, LineElement, PointElement, CategoryScale, LinearScale, Filler, Tooltip, Legend } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, CategoryScale, LinearScale, Filler, Tooltip, Legend);

window.Alpine = Alpine;

// Dark mode with localStorage persistence
Alpine.data('darkMode', () => ({
    dark: false,
    init() {
        const stored = localStorage.getItem('loopengine-theme');
        if (stored === 'dark') {
            this.dark = true;
        } else if (stored === 'light') {
            this.dark = false;
        } else {
            this.dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        this.applyTheme();

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('loopengine-theme')) {
                this.dark = e.matches;
                this.applyTheme();
            }
        });
    },
    toggle() {
        this.dark = !this.dark;
        localStorage.setItem('loopengine-theme', this.dark ? 'dark' : 'light');
        this.applyTheme();
    },
    applyTheme() {
        document.documentElement.classList.toggle('dark', this.dark);
    }
}));

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

// Flash message auto-dismiss
Alpine.data('flashMessage', () => ({
    show: true,
    init() {
        setTimeout(() => { this.show = false; }, 5000);
    }
}));

// Dashboard runs chart
window.runsChart = () => ({
    chart: null,
    init() {
        const canvas = this.$refs.runsCanvas;
        if (!canvas) return;

        const dataEl = document.getElementById('runs-chart-data');
        if (!dataEl) return;

        const data = JSON.parse(dataEl.textContent);
        const isDark = document.documentElement.classList.contains('dark');

        this.chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'Total',
                    data: data.map(d => d.count),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3,
                }, {
                    label: 'Completed',
                    data: data.map(d => d.completed),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: isDark ? '#d1d5db' : '#374151', font: { size: 11 } }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: isDark ? '#9ca3af' : '#6b7280', font: { size: 10 }, maxTicksLimit: 7 },
                        grid: { color: isDark ? '#374151' : '#e5e7eb' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: isDark ? '#9ca3af' : '#6b7280', stepSize: 1 },
                        grid: { color: isDark ? '#374151' : '#e5e7eb' }
                    }
                }
            }
        });
    }
});

Alpine.start();

// Apply dark mode immediately before Alpine boots (prevent flash)
(function() {
    const stored = localStorage.getItem('loopengine-theme');
    if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }
})();
