<?php include 'includes/header.php'; ?>

<style>
  /* ===== Theme Vars ===== */
  :root{
    --gc-primary:#2e7d32;
    --gc-secondary:#81c784;
    --gc-dark:#1b5e20;
    --gc-bg:#f5f7f6;
    --gc-muted:#5d6a5d;
    --gc-card:#ffffff;
    --gc-radius:18px;
    --gc-shadow:0 14px 36px rgba(46,125,50,.12), 0 3px 16px rgba(46,125,50,.08);
  }
  body.dark-mode{
    --gc-bg:#0f1110;
    --gc-card:#151917;
    --gc-muted:#b6c0b6;
    --gc-shadow:0 16px 44px rgba(0,0,0,.45), 0 6px 18px rgba(0,0,0,.35);
  }

  body {
    background:
      linear-gradient(180deg, #f7faf8 0%, #eef6f0 46%, #f8fbf8 100%);
  }

  /* ===== Hero ===== */
  .hero-section{
    position: relative;
    min-height: clamp(520px, 70vh, 760px);
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
  }

  #hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    z-index: 0;
    transition: opacity 1s ease-in-out;
  }

  /* Simple dark overlay for text readability */
  .hero-overlay{
    position:absolute; inset:0; z-index:1;
    background: rgba(0,0,0,0.3);
  }

  .hero-content{
    position: relative;
    z-index: 2;
    color: #fff;
    text-shadow: 0 2px 16px rgba(0,0,0,.4);
    max-width: 1100px;
    padding: clamp(28px, 4vw, 56px) 16px;
  }
  .hero-eyebrow{
    display:inline-flex; align-items:center; gap:8px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    font-weight: 700;
    letter-spacing:.2px;
    margin-bottom: 12px;
  }
  .hero-title{
    font-weight: 900;
    font-size: clamp(2.2rem, 5vw, 3.6rem);
    line-height: 1.08;
    margin: 6px 0 12px;
  }
  .hero-subtitle{
    font-size: clamp(1rem, 2.2vw, 1.25rem);
    opacity: .95;
    margin-bottom: 22px;
  }

  .cta-buttons{
    display:flex; flex-wrap:wrap; gap:10px; justify-content:center;
  }
  .cta-button{
    padding: 12px 18px;
    font-weight: 800;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.22);
    backdrop-filter: blur(4px);
    transition: transform .12s ease, box-shadow .2s ease, background .2s ease;
  }
  .cta-button:hover{ transform: translateY(-2px); box-shadow: 0 8px 22px rgba(0,0,0,.25); }
  .btn-primary.cta-button{ background: #2e7d32; border-color: transparent; }
  .btn-secondary.cta-button{ background: rgba(255,255,255,.18); color:#fff; }
  .btn-secondary.cta-button:hover{ background: rgba(255,255,255,.24); }

  /* ===== Sections Shared ===== */
  .section-wrap{
    padding: clamp(40px, 5vw, 80px) 0;
  }
  .section-wrap:nth-of-type(odd){
    background: rgba(255,255,255,.56);
  }
  .section-title{
    text-align:center;
    font-weight: 900;
    color: var(--gc-dark);
    margin-bottom: 8px;
  }
  .section-description{
    text-align:center;
    color: var(--gc-muted);
    max-width: 860px;
    margin: 0 auto 24px;
  }
  body.dark-mode .section-title{ color:#bde3c0; }
  body.dark-mode .section-description{ color:#cdd7cd; }

  /* ===== About Section ===== */
  .about-greencredit{
    max-width: 980px;
    margin: 0 auto;
    background: var(--gc-card);
    box-shadow: var(--gc-shadow);
    border-radius: 14px;
    border: 1px solid rgba(129,199,132,.18);
    padding: clamp(18px, 3.2vw, 28px);
    font-size: 1.04rem;
    line-height: 1.8;
  }

  /* ===== How It Works ===== */
  .hiw-grid{
    display:grid;
    grid-template-columns: repeat(4, 1fr);
    gap: clamp(12px, 2vw, 18px);
    margin-top: 16px;
  }
  @media (max-width: 992px){ .hiw-grid{ grid-template-columns: repeat(2, 1fr);} }
  @media (max-width: 560px){ .hiw-grid{ grid-template-columns: 1fr;} }

  .hiw-card{
    background: var(--gc-card);
    border-radius: 12px;
    border: 1px solid rgba(129,199,132,.2);
    padding: 18px 16px;
    box-shadow: var(--gc-shadow);
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
  }
  .hiw-card:hover{
    transform: translateY(-3px);
    border-color: rgba(129,199,132,.36);
    box-shadow: 0 18px 36px rgba(46,125,50,.16);
  }
  .hiw-icon{
    width:48px; height:48px;
    border-radius:12px;
    display:grid; place-items:center;
    background: #e8f5e9;
    color: #2e7d32;
    margin-bottom: 10px;
    font-size: 1.1rem;
  }
  body.dark-mode .hiw-icon{ background:#1e2a22; color:#abdeb0; }
  .hiw-card h4{ font-weight: 800; margin: 6px 0 6px; color: var(--gc-dark); }
  .hiw-card p{ margin:0; color: var(--gc-muted); }
  body.dark-mode .hiw-card h4{ color:#c1e7c4; }
  body.dark-mode .hiw-card p{ color:#cfd8cf; }

  /* ===== Big CTA band ===== */
  .cta-band{
    margin-top: 28px;
    background: #e8f5e9;
    border-radius: 14px;
    padding: clamp(18px, 3vw, 28px);
    display:flex; align-items:center; justify-content:space-between; gap:16px;
    flex-wrap: wrap;
    box-shadow: var(--gc-shadow);
    border: 1px solid rgba(129,199,132,.24);
  }
  .cta-band h3{
    margin:0; font-weight:900; color: #1b5e20;
  }
  .cta-band p{ margin: 0; color:#335233; }
  .cta-band .cta-actions{ display:flex; gap:10px; flex-wrap:wrap; }
  .cta-band .btn{
    border-radius: 12px;
    padding: 10px 16px;
    font-weight: 800;
  }
  body.dark-mode .cta-band{
    background: #0f2013;
    border-color: rgba(129,199,132,.22);
  }
  body.dark-mode .cta-band h3{ color:#c5f0c9; }
  body.dark-mode .cta-band p{ color:#cfe2cf; }

  /* ===== Steps List (Getting Started) ===== */
  .steps-list{
    max-width: 1000px;
    margin: 0 auto;
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }
  @media (max-width: 820px){ .steps-list{ grid-template-columns: 1fr; } }

  .step-item{
    background: var(--gc-card);
    border-radius: 12px;
    border: 1px solid rgba(129,199,132,.18);
    padding: 14px 16px;
    display:flex; gap:12px; align-items:flex-start;
    box-shadow: var(--gc-shadow);
    transition: transform .2s ease, border-color .2s ease;
  }
  .step-item:hover{
    transform: translateY(-2px);
    border-color: rgba(129,199,132,.34);
  }
  .step-index{
    width:36px; height:36px; border-radius:8px;
    background:#2e7d32; color:#fff; font-weight:900;
    display:grid; place-items:center;
    margin-top:2px;
  }

  /* ===== Partners ===== */
  .partnership-logos{
    display:flex; gap: clamp(14px, 3vw, 24px);
    justify-content:center; align-items:center; flex-wrap: wrap;
    margin-top: 16px;
  }
  .partnership-logo{
    height: 56px; width:auto; object-fit: contain;
    filter: saturate(0.95) contrast(1.05);
    transition: transform .15s ease, filter .15s ease, opacity .2s ease;
    opacity: .92;
  }
  .partnership-logo:hover{
    transform: translateY(-2px) scale(1.02);
    filter: saturate(1.1) contrast(1.08);
    opacity: 1;
  }

  /* ===== Reveal Animations (no library) ===== */
  .reveal{ opacity:0; transform: translateY(16px); }
  .reveal.in-view{ opacity:1; transform:none; transition: .6s ease; }
  .reveal-stagger > *{ opacity:0; transform: translateY(12px); }
  .reveal-stagger.in-view > *{ opacity:1; transform:none; transition:.5s ease; }
  .reveal-stagger.in-view > *:nth-child(1){ transition-delay:.04s; }
  .reveal-stagger.in-view > *:nth-child(2){ transition-delay:.10s; }
  .reveal-stagger.in-view > *:nth-child(3){ transition-delay:.16s; }
  .reveal-stagger.in-view > *:nth-child(4){ transition-delay:.22s; }

  /* Respect Reduced Motion */
  @media (prefers-reduced-motion: reduce){
    .reveal, .reveal-stagger > *{ opacity:1 !important; transform:none !important; }
  }
  @media (max-width: 576px){
    .hero-section{ min-height: 560px; align-items:flex-end; }
    .hero-content{ padding-bottom: 44px; }
    .hero-eyebrow{ font-size:.86rem; }
    .cta-buttons{ flex-direction:column; align-items:stretch; }
    .cta-button{ width:100%; }
    .cta-band{ align-items:flex-start; }
    .cta-band .cta-actions, .cta-band .btn{ width:100%; }
    .step-item{ padding: 13px; }
  }
    #hero-background {
    transition: opacity 0.6s ease-in-out;
    opacity: 1;
    }

</style>

<!-- =================== HERO =================== -->
<section class="hero-section">
  <div id="hero-background"></div>
  <div class="hero-overlay"></div>

  <div class="hero-content container text-center py-5">
    <span class="hero-eyebrow">
      <i></i> Impactful Actions • Real Rewards
    </span>
    <h1 class="hero-title">Welcome to GreenCredit</h1>
    <p class="hero-subtitle">
      Turn your actions into rewards and make a lasting impact on the Albukhary International University (AIU) campus and beyond!
    </p>
    <div class="cta-buttons">
      <a href="login.php" class="btn btn-primary cta-button" style="color:white;">
        <i class="fas fa-sign-in-alt me-2" style="color:white;"></i>Log In
      </a>
      <a href="register.php" class="btn btn-secondary cta-button">
        <i class="fas fa-user-plus me-2"></i>Sign Up Today
      </a>
      <a href="guidelines.php" class="btn btn-secondary cta-button">
        <i class="fas fa-coins me-2"></i>Read Our Points System
      </a>
    </div>
  </div>
</section>

<!-- =================== ABOUT =================== -->
<section class="section-wrap">
  <div class="container">
    <h2 class="section-title reveal">About GreenCredit</h2>
    <p class="section-description reveal">
      GreenCredit is a reward system designed to drive sustainability initiatives in environmental, social, and economic development among AIU students.
    </p>
    <div class="about-greencredit reveal">
      <p style="margin:0;">
        Participate in sustainability actions, submit proof, and earn credits that can be redeemed for exclusive benefits and recognition.  
        Every small action contributes to a sustainable future — and we're here to celebrate your impact.
      </p>
    </div>
  </div>
</section>

<!-- =================== HOW IT WORKS =================== -->
<section class="section-wrap">
  <div class="container">
    <h2 class="section-title reveal">How It Works</h2>
    <p class="section-description reveal">
      Every step is simple and purposeful. Start small, keep going, and watch your impact grow.
    </p>

    <div class="hiw-grid reveal-stagger">
      <div class="hiw-card">
        <div class="hiw-icon"><i class="fas fa-user-plus"></i></div>
        <h4>Register</h4>
        <p>Join a community that values sustainability and action.</p>
      </div>
      <div class="hiw-card">
        <div class="hiw-icon"><i class="fas fa-leaf"></i></div>
        <h4>Take Action</h4>
        <p>Participate in sustainability activities on and off campus.</p>
      </div>
      <div class="hiw-card">
        <div class="hiw-icon"><i class="fas fa-upload"></i></div>
        <h4>Submit Evidence</h4>
        <p>Upload proof of your activities and get them reviewed.</p>
      </div>
      <div class="hiw-card">
        <div class="hiw-icon"><i class="fas fa-gift"></i></div>
        <h4>Redeem Rewards</h4>
        <p>Exchange your credits for perks, recognition, and more.</p>
      </div>
    </div>

    <div class="cta-band reveal" style="margin-top:26px;">
      <div>
        <h3>Start earning GreenCredit today</h3>
        <p>Registration is quick — your first sustainability action could be minutes away.</p>
      </div>
      <div class="cta-actions">
        <a href="register.php" class="btn btn-success">
          <i class="fas fa-leaf me-2"></i>Get Started Now
        </a>
        <a href="guidelines.php" class="btn btn-outline-success">
          <i class="fas fa-book-open me-2"></i>View Guidelines
        </a>
      </div>
    </div>
  </div>
</section>

<!-- =================== GETTING STARTED (NEXT STEPS) =================== -->
<section class="section-wrap">
  <div class="container">
    <h2 class="section-title reveal">Getting Started</h2>
    <p class="section-description reveal">
      Here's your quick start playbook to make the most of GreenCredit:
    </p>

    <div class="steps-list reveal-stagger">
      <div class="step-item">
        <div class="step-index">1</div>
        <div>Log in to access your personalized sustainability dashboard.</div>
      </div>
      <div class="step-item">
        <div class="step-index">2</div>
        <div>Submit your eco-friendly actions with photo evidence.</div>
      </div>
      <div class="step-item">
        <div class="step-index">3</div>
        <div>Earn Green Credits when your submissions are approved.</div>
      </div>
      <div class="step-item">
        <div class="step-index">4</div>
        <div>Redeem your credits for rewards and track your impact.</div>
      </div>
    </div>
  </div>
</section>

<!-- =================== PARTNERS =================== -->
<section class="section-wrap">
  <div class="container">
    <h2 class="section-title reveal">Our Collaborators</h2>
    <p class="section-description reveal">We're proud to partner with organizations that champion sustainability.</p>
    <div class="partnership-logos reveal-stagger">
      <img src="assets/images/3 zero club (1).png" alt="3 Zero Club" class="partnership-logo">
      <img src="assets/images/aiu_logo.png" alt="AIU" class="partnership-logo">
      <img src="assets/images/ace sedi logo.png" alt="ACE SEDI" class="partnership-logo">
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
  // Intersection Observer for reveals
  (function () {
    const els = document.querySelectorAll('.reveal, .reveal-stagger');
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('in-view');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.16 });

    els.forEach(el => io.observe(el));

    // Reduced motion support
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      els.forEach(el => el.classList.add('in-view'));
      io.disconnect();
    }
  })();

  // Smooth slideshow functionality with preloading + fade
  (function () {
    const heroBackground = document.getElementById('hero-background');
    const images = [
      'assets/images/slide1.jpg',
      'assets/images/slide2.jpg',
      'assets/images/slide3.jpg',
      'assets/images/slide4.jpg',
      'assets/images/slide5.jpg',
      'assets/images/slide6.jpg',
      'assets/images/slide7.jpg',
      'assets/images/slide8.jpg',
    ];

    let currentIndex = 0;
    heroBackground.style.backgroundImage = `url('${images[currentIndex]}')`;

    function changeBackground() {
      const nextIndex = (currentIndex + 1) % images.length;
      const img = new Image();
      img.src = images[nextIndex];

      img.onload = () => {
        // Fade out, switch, fade back in
        heroBackground.style.opacity = 0;
        setTimeout(() => {
          currentIndex = nextIndex;
          heroBackground.style.backgroundImage = `url('${img.src}')`;
          heroBackground.style.opacity = 1;
        }, 300); // 300ms fade transition
      };
    }

    setInterval(changeBackground, 3000);
  })();
</script>
