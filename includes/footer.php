<footer class="text-white py-4">
    <div class="container-fluid">
        <!-- Main Footer Links -->
        <div class="footer-links">
            <a href="https://ace-sedi.aiu.edu.my/index.html">
                <i class="fas fa-leaf"></i>
                <span>ACE SEDI</span>
            </a>
            <a href="https://aiu.edu.my/">
                <i class="fas fa-university"></i>
                <span>AIU</span>
            </a>
            <a href="https://3zero.club/">
                <i class="fa-solid fa-trophy"></i>
                <span>3ZERO Club</span>
            </a>
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
        <div class="footer-social-icons">
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
        color: #000000 !important;
        font-weight: 500;
        transition: all 0.3s ease;
        padding: 8px 12px;
        border-radius: 4px;
    }
    
    .footer-links a:hover {
        color: #2E8B57 !important;
        background-color: #f8f9fa;
    }
    
    .footer-links i {
        color: #2E8B57 !important;
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
        color: #1877F2 !important; /* Facebook blue */
    }
    
    /* Instagram brand color */
    .nature-divider a:nth-child(2) {
        color: #E4405F !important; /* Instagram pink/red */
    }
    
    /* LinkedIn brand color */
    .nature-divider a:nth-child(3) {
        color: #0A66C2 !important; /* LinkedIn blue */
    }
    
    .nature-divider a:hover {
        transform: translateY(-3px) scale(1.1);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    /* Facebook hover effect */
    .nature-divider a:nth-child(1):hover {
        background-color: #1877F2;
        color: #ffffff !important;
    }
    
    /* Instagram hover effect */
    .nature-divider a:nth-child(2):hover {
        background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
        color: #ffffff !important;
    }
    
    /* LinkedIn hover effect */
    .nature-divider a:nth-child(3):hover {
        background-color: #0A66C2;
        color: #ffffff !important;
    }
    
    .copyright {
        color: #000000 !important;
        margin-bottom: 0;
    }
    
    .copyright i {
        color: #2E8B57 !important;
        margin-right: 4px;
    }
    
    .copyright small {
        color: #6c757d !important;
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
    .footer-social-icons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    }
    
    .footer-social-icons a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: transparent; /* No white overlay */
        font-size: 1.2rem;
        color: #333; /* Dark color for better contrast */
        transition: all 0.3s ease;
    }
    
    /* Specific brand colors */
    .footer-social-icons a:nth-child(1) {
        color: #1877F2; /* Facebook */
    }
    .footer-social-icons a:nth-child(2) {
        color: #E4405F; /* Instagram */
    }
    .footer-social-icons a:nth-child(3) {
        color: #0A66C2; /* LinkedIn */
    }
    
    /* Hover effects */
    .footer-social-icons a:hover {
        transform: translateY(-3px) scale(1.1);
        background: rgba(0,0,0,0.05);
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    body.dark-mode footer {
        background-color: #111814 !important;
        color: #edf6ef !important;
        box-shadow: 0 -2px 14px rgba(0, 0, 0, .38);
        border-top: 1px solid rgba(190, 233, 205, .12);
    }

    body.dark-mode .footer-links a,
    body.dark-mode .copyright {
        color: #edf6ef !important;
    }

    body.dark-mode .footer-links a:hover {
        color: #9be7b3 !important;
        background: rgba(120, 217, 154, .12);
    }

    body.dark-mode .copyright small {
        color: #a9b8ad !important;
    }

    body.dark-mode .footer-social-icons a {
        color: #d9f7e2;
    }

    body.dark-mode {
        background: radial-gradient(circle at top left, rgba(46, 139, 87, .14), transparent 34%), #0f1411 !important;
        color: #edf6ef !important;
    }

    body.dark-mode .hero-section,
    body.dark-mode .mv-section {
        background-color: #0f1411 !important;
    }

    body.dark-mode .category-card,
    body.dark-mode .mv-card,
    body.dark-mode .team-card,
    body.dark-mode .card,
    body.dark-mode .login-container,
    body.dark-mode .register-container,
    body.dark-mode .news-card,
    body.dark-mode .event-card,
    body.dark-mode .modal-content,
    body.dark-mode .table-responsive {
        background: #171d19 !important;
        color: #edf6ef !important;
        border-color: rgba(190, 233, 205, .14) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, .35) !important;
    }

    body.dark-mode h1,
    body.dark-mode h2,
    body.dark-mode h3,
    body.dark-mode h4,
    body.dark-mode h5,
    body.dark-mode .section-title,
    body.dark-mode .category-title,
    body.dark-mode .mv-title,
    body.dark-mode .card-title {
        color: #d9f7e2 !important;
    }

    body.dark-mode p,
    body.dark-mode small,
    body.dark-mode .section-sub,
    body.dark-mode .mv-text,
    body.dark-mode .lead,
    body.dark-mode .text-muted {
        color: #a9b8ad !important;
    }

    body.dark-mode .form-control,
    body.dark-mode .form-select,
    body.dark-mode .input-group-text,
    body.dark-mode textarea {
        background: #101713 !important;
        color: #edf6ef !important;
        border-color: rgba(190, 233, 205, .16) !important;
    }

    body.dark-mode .table,
    body.dark-mode .table td {
        background: #171d19 !important;
        color: #edf6ef !important;
        border-color: rgba(190, 233, 205, .14) !important;
    }

    body.dark-mode .table tbody tr:nth-child(even) td {
        background: #1d2620 !important;
    }

    body.dark-mode .table tbody tr:hover td {
        background: rgba(120, 217, 154, .13) !important;
    }

    body.dark-mode .points-display {
        background: #d9f7e2 !important;
        color: #155c36 !important;
    }
</style>
