// Admin Gudang Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle reject button clicks
    const rejectButtons = document.querySelectorAll('.reject-btn');
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    const rejectForm = document.getElementById('rejectForm');
    const rejectionReasonTextarea = document.getElementById('rejection_reason');

    rejectButtons.forEach(button => {
        button.addEventListener('click', function() {
            const submissionId = this.getAttribute('data-submission-id');
            const rejectUrl = `/gudang/submissions/${submissionId}/reject`;

            // Set form action
            rejectForm.setAttribute('action', rejectUrl);

            // Clear previous rejection reason
            rejectionReasonTextarea.value = '';

            // Show modal
            rejectModal.show();
        });
    });

    // Reset form when modal is hidden
    document.getElementById('rejectModal').addEventListener('hidden.bs.modal', function () {
        rejectionReasonTextarea.value = '';
    });

    // Daily stock movements line chart
    const ctx = document.getElementById('dailyChart');
    if (ctx) {
        const dailyData = JSON.parse(ctx.dataset.chartData || '[]');

        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: dailyData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
                }),
                datasets: [{
                    label: 'Stock In',
                    data: dailyData.map(item => item.stock_in),
                    borderColor: 'rgb(25, 135, 84)',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(25, 135, 84)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }, {
                    label: 'Stock Out',
                    data: dailyData.map(item => item.stock_out),
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(220, 53, 69)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            maxTicksLimit: 10
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toLocaleString();
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Auto-refresh notification for new submissions
    const pendingSubmissions = parseInt(document.querySelector('[data-pending-count]')?.dataset.pendingCount || 0);
    if (pendingSubmissions > 0) {
        // Check for new submissions every 5 minutes
        setInterval(function() {
            fetch('/gudang/submissions/statistics')
                .then(response => response.json())
                .then(data => {
                    if (data.pending > pendingSubmissions) {
                        showToast('info', `Ada ${data.pending - pendingSubmissions} submission baru yang perlu ditinjau.`);
                    }
                })
                .catch(error => console.log('Error checking for updates'));
        }, 300000); // 5 minutes
    }

    // Show success message on approval
    const successMessage = document.querySelector('[data-success-message]')?.dataset.successMessage;
    if (successMessage) {
        showToast('success', successMessage);
    }

    // Add floating action buttons
    addFloatingActionButtons();

    // Auto refresh notification count
    setInterval(function() {
        fetch('/notifications/count')
            .then(response => response.json())
            .then(data => {
                const notifBadges = document.querySelectorAll('.notification-count');
                notifBadges.forEach(badge => {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                    }
                });
            })
            .catch(error => console.log('Error fetching notification count:', error));
    }, 30000); // Check every 30 seconds
});

// Utility functions
function showToast(type, message) {
    const colors = {
        success: 'success',
        info: 'info',
        warning: 'warning',
        danger: 'danger'
    };

    const icons = {
        success: 'check-circle-fill',
        info: 'info-circle-fill',
        warning: 'exclamation-triangle-fill',
        danger: 'x-circle-fill'
    };

    const toast = document.createElement('div');
    toast.className = `alert alert-${colors[type]} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `
        <i class="bi bi-${icons[type]} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function refreshDashboard() {
    showToast('info', 'Memperbarui data...');
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

function exportDailyReport() {
    const today = new Date().toISOString().split('T')[0];
    const statsElement = document.querySelector('[data-daily-stats]');
    const stats = JSON.parse(statsElement?.dataset.dailyStats || '{}');

    // Create CSV content with proper formatting for Excel using comma separators with quotes
    let csvContent = "data:text/csv;charset=utf-8,%EF%BB%BF"; // Add BOM for UTF-8
    csvContent += '"LAPORAN HARIAN GUDANG"\n';
    csvContent += '"Tanggal","' + today + '"\n\n';
    csvContent += '"Metrik","Nilai"\n';
    csvContent += '"Stock Masuk","' + (stats.stockIn || 0) + '"\n';
    csvContent += '"Stock Keluar","' + (stats.stockOut || 0) + '"\n';
    csvContent += '"Total Pengajuan","' + (stats.submissions || 0) + '"\n';
    csvContent += '"Pending Review","' + (stats.pending || 0) + '"\n';
    csvContent += '"Disetujui","' + (stats.approved || 0) + '"\n';
    csvContent += '"Ditolak","' + (stats.rejected || 0) + '"\n';
    csvContent += '"Approval Rate","' + (stats.approvalRate || 0) + '%"\n';

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "laporan_harian_" + today + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showToast('success', 'Laporan berhasil diekspor!');
}

function addFloatingActionButtons() {
    const fab = document.createElement('div');
    fab.className = 'position-fixed bottom-0 end-0 mb-4 me-4';
    fab.style.zIndex = '1000';
    fab.innerHTML = `
        <div class="btn-group-vertical" role="group">
            <button type="button" class="btn btn-primary rounded-circle shadow-lg mb-2"
                    onclick="refreshDashboard()"
                    style="width: 56px; height: 56px;"
                    title="Refresh Dashboard">
                <i class="bi bi-arrow-clockwise fs-5"></i>
            </button>
            <button type="button" class="btn btn-success rounded-circle shadow-lg mb-2"
                    onclick="window.location.href='/gudang/stocks/create'"
                    style="width: 56px; height: 56px;"
                    title="Tambah Stok Cepat">
                <i class="bi bi-plus-lg fs-5"></i>
            </button>
            <button type="button" class="btn btn-warning rounded-circle shadow-lg"
                    onclick="exportDailyReport()"
                    style="width: 56px; height: 56px;"
                    title="Export Laporan">
                <i class="bi bi-download fs-5"></i>
            </button>
        </div>
    `;
    document.body.appendChild(fab);
}