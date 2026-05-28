<footer class="py-4">
    <div class="container-fluid">
        <!-- Main Footer Links -->
        <div class="footer-links">
            <a href="contact.php">
                <i class="fas fa-envelope"></i>
                <span>Contact Us</span>
            </a>
            <a href="developer-info.php">
                <i class="fas fa-code-branch"></i>
                <span>Developer Info</span>
            </a>
        </div>

        <!-- Social Media Links -->
        <div class="nature-divider">
            <a href="https://www.instagram.com/aiu.socialbusiness?igsh=Mzc1bGVpcjVzZ2Zu" target="_blank" aria-label="Instagram">
                <i class="fab fa-instagram"></i>
            </a>
        </div>
        
        <!-- Copyright -->
        <p class="copyright text-center">
            <i class="far fa-copyright"></i> 2025 GreenCredit | All Rights Reserved<br>
            <small>Sustainability Through Innovation</small>
        </p>
    </div>
</footer>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const themeToggle = document.getElementById("themeToggle");
    const storedTheme = localStorage.getItem("theme") || (localStorage.getItem("darkMode") === "enabled" ? "dark" : "light");

    if (storedTheme === "dark") {
        document.body.classList.add("dark-mode");
        if (themeToggle) themeToggle.checked = true;
    } else {
        document.body.classList.remove("dark-mode");
        if (themeToggle) themeToggle.checked = false;
    }

    if (!themeToggle) return;

    themeToggle.addEventListener("change", function() {
        if (this.checked) {
            document.body.classList.add("dark-mode");
            localStorage.setItem("theme", "dark");
            localStorage.setItem("darkMode", "enabled");
        } else {
            document.body.classList.remove("dark-mode");
            localStorage.setItem("theme", "light");
            localStorage.setItem("darkMode", "disabled");
        }
    });
});
</script>
<style>
    /* Footer Styling */
    footer {
        background-color: #ffffff !important;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        margin-top: auto;
        color: #000000; /* Default text color is black */
    }
    
    .footer-links {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .footer-links a {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #000000;
        font-weight: 500;
        transition: all 0.3s ease;
        padding: 8px 12px;
        border-radius: 4px;
    }
    
    .footer-links a:hover {
        color: #2E8B57;
        background-color: #f8f9fa;
    }
    
    .footer-links i {
        color: #2E8B57;
        width: 20px;
        text-align: center;
        margin-right: 8px;
    }
    
    .nature-divider {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .nature-divider a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #f8f9fa;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    /* Facebook brand color */
    .nature-divider a:nth-child(1) {
        color: #1877F2;
    }
    
    /* Instagram brand color */
    .nature-divider a:nth-child(2) {
        color: #E4405F;
    }
    
    /* LinkedIn brand color */
    .nature-divider a:nth-child(3) {
        color: #0A66C2;
    }
    
    .nature-divider a:hover {
        transform: translateY(-3px) scale(1.1);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    /* Facebook hover effect */
    .nature-divider a:nth-child(1):hover {
        background-color: #1877F2;
        color: #ffffff;
    }
    
    /* Instagram hover effect */
    .nature-divider a:nth-child(2):hover {
        background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
        color: #ffffff;
    }
    
    /* LinkedIn hover effect */
    .nature-divider a:nth-child(3):hover {
        background-color: #0A66C2;
        color: #ffffff;
    }
    
    .copyright {
        color: #000000;
        margin-bottom: 0;
    }
    
    .copyright i {
        color: #2E8B57;
        margin-right: 4px;
    }
    
    .copyright small {
        color: #6c757d;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .footer-links {
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        
        .footer-links a {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
        
        .nature-divider {
            gap: 0.8rem;
        }
        
        .nature-divider a {
            width: 36px;
            height: 36px;
        }
    }
    /* 🌙 Dark Mode Footer Styling */
    body.dark-mode footer {
        background-color: #1a1a1a !important;
        color: #f1f1f1 !important;
        box-shadow: 0 -2px 10px rgba(255, 255, 255, 0.05);
    }
    
    /* Footer Links in Dark Mode */
    body.dark-mode .footer-links a {
        color: #f1f1f1 !important;
    }
    
    body.dark-mode .footer-links a:hover {
        color: #a8ffb3 !important;
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    /* Footer Icons in Dark Mode */
    body.dark-mode .footer-links i {
        color: #a8ffb3 !important;
    }
    
    /* Social Media Section in Dark Mode */
    body.dark-mode .nature-divider {
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    body.dark-mode .nature-divider a {
        background-color: rgba(255, 255, 255, 0.05);
        box-shadow: 0 2px 5px rgba(255, 255, 255, 0.1);
    }
    
    body.dark-mode .nature-divider a:hover {
        box-shadow: 0 5px 15px rgba(255, 255, 255, 0.15);
    }
    
    /* Copyright Text in Dark Mode */
    body.dark-mode .copyright {
        color: #f1f1f1;
    }
    
    body.dark-mode .copyright i {
        color: #a8ffb3;
    }
    
    body.dark-mode .copyright small {
        color: #aaaaaa;
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
