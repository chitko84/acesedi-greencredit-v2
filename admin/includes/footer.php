<!-- Footer for Admin Panel -->
<footer class="admin-footer py-3 w-100">
    <div class="container">
        <p class="text-center mb-0">
            &copy; <?php echo date("Y"); ?> GreenCredit Admin Panel. All Rights Reserved.
        </p>
    </div>
</footer>

<!-- Footer Styling -->
<style>
    /* Default (Light Mode) */
    .admin-footer {
        background-color: #ffffff;
        color: #000000;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.08);
        font-size: 0.9rem;
    }

    .admin-footer p {
        margin: 0;
        color: inherit;
    }

    /* Dark Mode Support */
    body.dark-mode .admin-footer {
        background-color: #1e1e1e;
        color: #f0f0f0;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.6);
    }

    body.dark-mode .admin-footer p {
        color: #cccccc;
    }

    /* Admin dark UI consolidation: loaded late to override page-level light styles. */
    html.dark-mode,
    body.dark-mode {
        background:
            radial-gradient(circle at top left, rgba(52, 211, 153, 0.08), transparent 34rem),
            linear-gradient(135deg, #0d1511 0%, #111a15 45%, #0a0f0c 100%) !important;
        color: #edf7ef !important;
    }

    body.dark-mode main,
    body.dark-mode .main-content,
    body.dark-mode .admin-content,
    body.dark-mode .page-content,
    body.dark-mode .dashboard-container,
    body.dark-mode .container,
    body.dark-mode .container-fluid,
    body.dark-mode .admin-page-shell,
    body.dark-mode .message-container,
    body.dark-mode .leaderboard-container,
    body.dark-mode .profile-container,
    body.dark-mode .points-container,
    body.dark-mode .card,
    body.dark-mode .stat-card,
    body.dark-mode .summary-card,
    body.dark-mode .analytics-panel,
    body.dark-mode .analytics-card,
    body.dark-mode .chart-card,
    body.dark-mode .chart-box,
    body.dark-mode .sort-panel,
    body.dark-mode .filter-panel,
    body.dark-mode .bulk-actions,
    body.dark-mode .modal-content,
    body.dark-mode .list-group-item,
    body.dark-mode .news-card,
    body.dark-mode .reward-card,
    body.dark-mode .admin-panel,
    body.dark-mode .content-box,
    body.dark-mode .box,
    body.dark-mode .panel,
    body.dark-mode section[style],
    body.dark-mode div[style*="background: #fff"],
    body.dark-mode div[style*="background:#fff"],
    body.dark-mode div[style*="background: white"],
    body.dark-mode div[style*="background-color: #fff"],
    body.dark-mode div[style*="background-color:#fff"],
    body.dark-mode div[style*="background-color: white"] {
        background: rgba(20, 29, 24, 0.96) !important;
        color: #edf7ef !important;
        border-color: rgba(179, 232, 190, 0.18) !important;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.32) !important;
    }

    body.dark-mode .navbar-admin {
        background: rgba(14, 22, 18, 0.96) !important;
        border-bottom: 1px solid rgba(179, 232, 190, 0.16);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3) !important;
        backdrop-filter: blur(12px);
    }

    body.dark-mode .navbar-admin .navbar-brand,
    body.dark-mode .navbar-admin .nav-link {
        color: #edf7ef !important;
    }

    body.dark-mode .navbar-admin .nav-link:hover,
    body.dark-mode .navbar-admin .nav-link:focus,
    body.dark-mode .navbar-admin .nav-link.active {
        background: rgba(74, 222, 128, 0.13) !important;
        color: #bdf5ca !important;
    }

    body.dark-mode .navbar-admin > .container,
    body.dark-mode .admin-footer > .container {
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }

    body.dark-mode h1,
    body.dark-mode h2,
    body.dark-mode h3,
    body.dark-mode h4,
    body.dark-mode h5,
    body.dark-mode h6,
    body.dark-mode label,
    body.dark-mode .form-label,
    body.dark-mode .card-title,
    body.dark-mode .modal-title,
    body.dark-mode .page-title,
    body.dark-mode .section-title,
    body.dark-mode .fw-semibold,
    body.dark-mode .fw-bold,
    body.dark-mode .text-black,
    body.dark-mode .text-dark {
        color: #f4fbf5 !important;
    }

    body.dark-mode p,
    body.dark-mode small,
    body.dark-mode .small,
    body.dark-mode .text-muted,
    body.dark-mode .card-text,
    body.dark-mode .page-info,
    body.dark-mode .help-text,
    body.dark-mode .form-text,
    body.dark-mode .dropdown-header,
    body.dark-mode .analytics-card span,
    body.dark-mode .sort-chip small {
        color: #b8c8bd !important;
    }

    body.dark-mode a:not(.btn):not(.nav-link):not(.dropdown-item) {
        color: #9ee7b0 !important;
    }

    body.dark-mode .form-control,
    body.dark-mode .form-select,
    body.dark-mode textarea,
    body.dark-mode input[type="text"],
    body.dark-mode input[type="email"],
    body.dark-mode input[type="password"],
    body.dark-mode input[type="number"],
    body.dark-mode input[type="date"],
    body.dark-mode input[type="search"],
    body.dark-mode input[type="tel"],
    body.dark-mode .input-group-text,
    body.dark-mode .custom-select {
        background: #0f1712 !important;
        color: #edf7ef !important;
        border-color: rgba(179, 232, 190, 0.26) !important;
        box-shadow: none !important;
    }

    body.dark-mode .form-control:focus,
    body.dark-mode .form-select:focus,
    body.dark-mode textarea:focus,
    body.dark-mode input:focus {
        background: #101a14 !important;
        color: #ffffff !important;
        border-color: #69d58a !important;
        box-shadow: 0 0 0 0.2rem rgba(105, 213, 138, 0.16) !important;
    }

    body.dark-mode .form-control::placeholder,
    body.dark-mode textarea::placeholder,
    body.dark-mode input::placeholder {
        color: #839287 !important;
    }

    body.dark-mode option {
        background: #0f1712 !important;
        color: #edf7ef !important;
    }

    body.dark-mode .form-check-label,
    body.dark-mode .form-check-input + label {
        color: #edf7ef !important;
    }

    body.dark-mode .dropdown-menu {
        background: #121b16 !important;
        color: #edf7ef !important;
        border: 1px solid rgba(179, 232, 190, 0.18) !important;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.38) !important;
    }

    body.dark-mode .dropdown-item {
        color: #edf7ef !important;
    }

    body.dark-mode .dropdown-item i {
        color: #8be3a0 !important;
    }

    body.dark-mode .dropdown-item:hover,
    body.dark-mode .dropdown-item:focus {
        background: rgba(105, 213, 138, 0.14) !important;
        color: #c9f8d2 !important;
    }

    body.dark-mode .dropdown-divider,
    body.dark-mode hr {
        border-color: rgba(179, 232, 190, 0.18) !important;
        opacity: 1;
    }

    body.dark-mode .table-responsive {
        background: rgba(20, 29, 24, 0.96) !important;
        border: 1px solid rgba(179, 232, 190, 0.16) !important;
        border-radius: 14px;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.26) !important;
    }

    body.dark-mode table,
    body.dark-mode .table {
        background: transparent !important;
        color: #edf7ef !important;
        border-color: rgba(179, 232, 190, 0.16) !important;
    }

    body.dark-mode .table thead,
    body.dark-mode .table thead tr,
    body.dark-mode .table thead th,
    body.dark-mode table thead th {
        background: #1f7045 !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.14) !important;
    }

    body.dark-mode .table tbody tr,
    body.dark-mode .table tbody td,
    body.dark-mode table tbody td {
        background: #141d18 !important;
        color: #edf7ef !important;
        border-color: rgba(179, 232, 190, 0.13) !important;
    }

    body.dark-mode .table tbody tr:nth-child(even) td {
        background: #18231d !important;
    }

    body.dark-mode .table-hover tbody tr:hover td,
    body.dark-mode table tbody tr:hover td {
        background: rgba(105, 213, 138, 0.11) !important;
        color: #ffffff !important;
    }

    body.dark-mode .auto-row-control,
    body.dark-mode .table-controls,
    body.dark-mode .dataTables_wrapper,
    body.dark-mode .dataTables_length,
    body.dark-mode .dataTables_filter,
    body.dark-mode .dataTables_info,
    body.dark-mode .dataTables_paginate,
    body.dark-mode .pagination {
        color: #edf7ef !important;
    }

    body.dark-mode .page-link,
    body.dark-mode .pagination .page-link {
        background: #101713 !important;
        color: #edf7ef !important;
        border-color: rgba(179, 232, 190, 0.18) !important;
    }

    body.dark-mode .page-item.active .page-link,
    body.dark-mode .pagination .active .page-link {
        background: #2e8b57 !important;
        border-color: #2e8b57 !important;
        color: #ffffff !important;
    }

    body.dark-mode .sort-chip,
    body.dark-mode .filter-chip,
    body.dark-mode .metric-chip {
        background: #101713 !important;
        color: #edf7ef !important;
        border: 1px solid rgba(179, 232, 190, 0.16) !important;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18) !important;
    }

    body.dark-mode .sort-chip.active,
    body.dark-mode .filter-chip.active,
    body.dark-mode .metric-chip.active {
        background: rgba(46, 139, 87, 0.3) !important;
        border-color: rgba(137, 225, 157, 0.48) !important;
        color: #ffffff !important;
    }

    body.dark-mode .progress {
        background: #0d1410 !important;
    }

    body.dark-mode .progress-bar {
        color: #ffffff !important;
    }

    body.dark-mode .alert {
        border-color: rgba(255, 255, 255, 0.12) !important;
        color: #f6fbf7 !important;
    }

    body.dark-mode .alert-success {
        background: rgba(25, 135, 84, 0.2) !important;
    }

    body.dark-mode .alert-danger {
        background: rgba(220, 53, 69, 0.18) !important;
    }

    body.dark-mode .alert-warning {
        background: rgba(255, 193, 7, 0.17) !important;
        color: #fff4c2 !important;
    }

    body.dark-mode .alert-info {
        background: rgba(13, 202, 240, 0.15) !important;
    }

    body.dark-mode .modal-header,
    body.dark-mode .modal-footer {
        background: #121b16 !important;
        border-color: rgba(179, 232, 190, 0.16) !important;
    }

    body.dark-mode .btn-close {
        filter: invert(1) grayscale(100%) brightness(160%);
    }

    body.dark-mode .btn-outline-secondary,
    body.dark-mode .btn-outline-primary,
    body.dark-mode .btn-outline-success {
        color: #dffbe6 !important;
        border-color: rgba(179, 232, 190, 0.34) !important;
    }

    body.dark-mode .btn-outline-secondary:hover,
    body.dark-mode .btn-outline-primary:hover,
    body.dark-mode .btn-outline-success:hover {
        background: rgba(105, 213, 138, 0.14) !important;
        color: #ffffff !important;
    }

    body.dark-mode .badge.bg-light,
    body.dark-mode .badge.text-dark {
        background: rgba(255, 255, 255, 0.12) !important;
        color: #edf7ef !important;
    }

    @media (max-width: 768px) {
        body.dark-mode .container,
        body.dark-mode .container-fluid,
        body.dark-mode .admin-page-shell,
        body.dark-mode .card,
        body.dark-mode .sort-panel,
        body.dark-mode .filter-panel,
        body.dark-mode .analytics-panel {
            border-radius: 12px !important;
        }

        body.dark-mode .table-responsive {
            border-radius: 12px;
        }

        body.dark-mode .table td::before,
        body.dark-mode table td::before {
            color: #9ee7b0 !important;
        }
    }
</style>

<div class="modal fade" id="globalConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="globalConfirmMessage">Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="globalConfirmButton">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.showConfirmModal = function(message, onConfirm) {
    const modalEl = document.getElementById('globalConfirmModal');
    const msgEl = document.getElementById('globalConfirmMessage');
    const btn = document.getElementById('globalConfirmButton');
    if (!modalEl || !msgEl || !btn || typeof bootstrap === 'undefined') {
        if (confirm(message)) onConfirm();
        return;
    }
    msgEl.textContent = message;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const freshBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(freshBtn, btn);
    freshBtn.addEventListener('click', function() {
        modal.hide();
        onConfirm();
    }, { once: true });
    modal.show();
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[data-confirm]').forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const href = this.href;
            window.showConfirmModal(this.dataset.confirm || 'Are you sure?', function() {
                window.location.href = href;
            });
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (form.dataset.confirmed === 'true') return;
            event.preventDefault();
            window.showConfirmModal(form.dataset.confirm || 'Are you sure?', function() {
                form.dataset.confirmed = 'true';
                form.submit();
            });
        });
    });

    const hasServerRowsControl = document.querySelector('select[name="per_page"], select#per_page');
    const hasPagePagination = document.querySelector('.pagination');
    document.querySelectorAll('.auto-row-control').forEach(function(control, index) {
        if (index > 0 || hasServerRowsControl || hasPagePagination) {
            control.remove();
        }
    });

    document.querySelectorAll('table').forEach(function(table, index) {
        const tbody = table.tBodies[0];
        if (!tbody || table.dataset.rowsControl === 'off') return;
        if (hasServerRowsControl || hasPagePagination) return;
        if (table.closest('.dataTables_wrapper') || table.classList.contains('dataTable')) return;
        const rows = Array.from(tbody.rows).filter(row => row.cells.length > 1);
        if (rows.length <= 10) return;
        const tableWrap = table.closest('.table-responsive') || table;
        if (tableWrap.previousElementSibling?.classList?.contains('auto-row-control')) return;

        const control = document.createElement('div');
        control.className = 'auto-row-control d-flex justify-content-end align-items-center gap-2 flex-wrap mb-2';
        control.innerHTML = `
            <label class="fw-semibold mb-0" for="autoRows${index}">Rows</label>
            <select id="autoRows${index}" class="form-select form-select-sm" style="width:auto;">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
                <option value="all">All rows</option>
            </select>`;
        tableWrap.before(control);
        const select = control.querySelector('select');
        const applyRows = () => {
            const limit = select.value === 'all' ? rows.length : parseInt(select.value, 10);
            rows.forEach((row, rowIndex) => {
                row.style.display = rowIndex < limit ? '' : 'none';
            });
        };
        select.addEventListener('change', applyRows);
        applyRows();
    });
});
</script>
</body>
</html>
