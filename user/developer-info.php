<?php 
include '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<style>
  /* === Navbar profile dropdown offset === */
  #profileDropdown + .dropdown-menu {
    right: 0;
    transform: translateX(-20px);
  }

  /* === Developer Section Styles === */
  :root{
    --gc-primary:#2e7d32;
    --gc-secondary:#81c784;
    --gc-dark:#1b5e20;
    --gc-muted:#6b7a6b;
    --gc-bg:#f5f7f6;
    --gc-card:#ffffff;
  }

  body { background: var(--gc-bg); }

  .developer-section {
    padding: 60px 0 80px;
    position: relative;
    overflow: hidden;
  }

  /* Soft gradient background flourish */
  .developer-section::before {
    content: "";
    position: absolute;
    inset: -80px -40% auto -40%;
    height: 280px;
    background: radial-gradient(60% 80% at 30% 50%, rgba(129,199,132,.25), transparent 60%),
                radial-gradient(50% 70% at 70% 30%, rgba(46,125,50,.12), transparent 65%);
    pointer-events: none;
  }

  .developer-section .container {
    position: relative;
    z-index: 1;
  }

  .section-header {
    text-align: center;
    margin-bottom: 28px;
  }
  .section-header h2 {
    font-weight: 800;
    letter-spacing: .3px;
    margin: 0;
    color: var(--gc-dark);
  }
  .section-header p {
    color: var(--gc-muted);
    margin-top: 8px;
  }

  .developer-card {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 28px;
    max-width: 920px;
    margin: 24px auto 0;
    padding: 28px;
    background: var(--gc-card);
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(46,125,50,0.10), 0 2px 8px rgba(46,125,50,0.06);
    border: 1px solid rgba(129,199,132,.25);
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .developer-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 32px rgba(46,125,50,0.12), 0 4px 14px rgba(46,125,50,0.08);
  }

  .developer-image {
    align-self: center;
  }
  .developer-image img {
    width: 180px;
    height: 180px;
    object-fit: cover;
    border-radius: 16px;
    border: 4px solid #fff;
    box-shadow: 0 12px 20px rgba(0,0,0,.08);
  }

  .developer-details h3 {
    margin: 0;
    font-weight: 800;
    color: var(--gc-primary);
  }

  .role {
    margin: 6px 0 14px;
    color: var(--gc-dark);
    font-weight: 600;
  }

  /* Badge row */
  .dev-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 8px 0 14px;
  }
  .dev-badge {
    font-size: .85rem;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 999px;
    background: #e8f5e9;
    color: var(--gc-dark);
    border: 1px solid rgba(46,125,50,.15);
  }

  .contact-info {
    margin-top: 6px;
    display: grid;
    gap: 10px;
  }
  .info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fafcfb;
    border: 1px solid rgba(46,125,50,.10);
    border-radius: 12px;
    padding: 10px 12px;
    transition: background .2s ease, border-color .2s ease;
  }
  .info-item:hover {
    background: #f2fbf5;
    border-color: rgba(46,125,50,.20);
  }
  .info-item i {
    color: var(--gc-primary);
    width: 20px;
    text-align: center;
  }
  .info-item a {
    color: #2f3a2f;
    text-decoration: none;
    font-weight: 600;
    word-break: break-all;
  }
  .info-item a:hover { text-decoration: underline; }

  /* CTA row */
  .cta-row {
    margin-top: 18px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .btn-soft {
    border: 1px solid rgba(46,125,50,.2);
    background: #ffffff;
    color: var(--gc-dark);
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background .2s ease, border-color .2s ease, transform .1s ease;
  }
  .btn-soft:hover {
    background: #f2fbf5;
    border-color: rgba(46,125,50,.35);
  }
  .btn-soft:active {
    transform: translateY(1px);
  }

  /* Divider line */
  .subtle-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(46,125,50,.18), transparent);
    margin: 16px 0;
  }

  @media (max-width: 768px) {
    .developer-card {
      grid-template-columns: 1fr;
      text-align: center;
    }
    .developer-image img {
      margin: 0 auto;
    }
    .contact-info {
      text-align: left;
    }
    .cta-row {
      justify-content: center;
    }
  }
</style>

<!-- Developer Info Section -->
<section class="developer-section">
  <div class="container">
    <div class="section-header">
      <h2>Developer Information</h2>
      <p>Meet the developer behind GreenCredit</p>
    </div>

    <div class="developer-card">
      <!-- Photo -->
      <div class="developer-image">
        <img src="../assets/images/kedah_fair7(1).jpg" alt="Chit Ko Ko" class="img-fluid" />
      </div>

      <!-- Details -->
      <div class="developer-details">
        <h3>Chit Ko Ko</h3>
        <p class="role">Full Stack Developer</p>

        <div class="dev-badges">
          <span class="dev-badge"><i class="fas fa-code"></i> Developer </span>
          <span class="dev-badge"><i class="fas fa-database"></i> Bachelor of Computer Science </span>
          <span class="dev-badge"><i class="fas fa-shield-alt"></i> Security-Minded </span>
        </div>

        <div class="subtle-divider"></div>

        <div class="contact-info">
          <div class="info-item">
            <i class="fas fa-envelope"></i>
            <a href="mailto:chitko.ko@student.aiu.edu.my">chitko.ko@student.aiu.edu.my</a>
          </div>
          <div class="info-item">
            <i class="fas fa-envelope"></i>
            <a href="mailto:chitkoko.ali@gmail.com">chitkoko.ali@gmail.com</a>
          </div>
          <div class="info-item">
            <i class="fas fa-phone"></i>
            <a href="https://wa.me/+601112476299?text=Hello%20there!%20I%20have%20a%20question.">+60 11-1247 6299</a>
          </div>
        </div>

        <div class="cta-row">
          <a class="btn-soft" href="mailto:chitkoko.ali@gmail.com"><i class="fas fa-paper-plane"></i> Email</a>
          <a class="btn-soft" href="profile.php"><i class="fas fa-user"></i> View Profile</a>
          <a class="btn-soft" href="submit_item.php"><i class="fas fa-recycle"></i> Submit Item</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>