<?php include 'includes/header.php'; ?>

<style>
  /* ========= THEME VARS ========= */
  :root{
    --gc-primary:#2e7d32;
    --gc-secondary:#81c784;
    --gc-dark:#1b5e20;
    --gc-muted:#5f6a5f;
    --gc-bg:#f5f7f6;
    --gc-card:#ffffff;
    --gc-radius:18px;
    --gc-shadow:0 14px 36px rgba(46,125,50,.12), 0 3px 16px rgba(46,125,50,.08);
  }
  body.dark-mode{
    --gc-bg:#0f1110;
    --gc-card:#151917;
    --gc-muted:#c3cec3;
    --gc-shadow:0 16px 44px rgba(0,0,0,.45), 0 6px 18px rgba(0,0,0,.35);
  }
  body {
    background:
      linear-gradient(180deg, #f7faf8 0%, #eef6f0 50%, #f8fbf8 100%);
  }

  /* ========= UTILITIES ========= */
  .bg-green    { background-color: var(--gc-primary) !important; }
  .text-green  { color: var(--gc-primary) !important; }
  .btn-outline-green{
    color: var(--gc-primary);
    border: 1px solid var(--gc-primary);
    background: transparent;
  }
  .btn-outline-green:hover{
    color: #fff;
    background: var(--gc-primary);
    border-color: var(--gc-primary);
  }

  .section-title{
    font-weight: 900;
    color: var(--gc-dark);
    letter-spacing: .25px;
  }
  .section-sub{
    color: var(--gc-muted);
    max-width: 900px;
    margin: 0 auto;
  }
  body.dark-mode .section-title{ color:#bde3c0; }
  body.dark-mode .section-sub{ color:#d7e1d7; }

  /* ========= REVEAL ========= */
  .reveal{ opacity:0; transform: translateY(16px); }
  .reveal.in-view{ opacity:1; transform:none; transition:.6s ease; }
  .reveal-stagger > *{ opacity:0; transform: translateY(12px); }
  .reveal-stagger.in-view > *{ opacity:1; transform:none; transition:.5s ease; }
  .reveal-stagger.in-view > *:nth-child(1){ transition-delay:.04s; }
  .reveal-stagger.in-view > *:nth-child(2){ transition-delay:.10s; }
  .reveal-stagger.in-view > *:nth-child(3){ transition-delay:.16s; }
  .reveal-stagger.in-view > *:nth-child(4){ transition-delay:.22s; }
  @media (prefers-reduced-motion: reduce){
    .reveal, .reveal-stagger > *{ opacity:1 !important; transform:none !important; }
  }

  /* ========= MISSION / VISION ========= */
  .mv-section{
    position: relative;
    overflow: hidden;
    padding: clamp(48px, 6vw, 96px) 0;
    background: linear-gradient(135deg, #eef8ef, #ffffff 52%, #e6f3e9);
  }
  body.dark-mode .mv-section{
    background:
      radial-gradient(60% 80% at 20% 25%, rgba(129,199,132,.16), transparent 62%),
      linear-gradient(120deg, #0f2013, #17301c);
  }
  .mv-card{
    border: 1px solid rgba(129,199,132,.22);
    border-radius: 14px;
    background: var(--gc-card);
    box-shadow: var(--gc-shadow);
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
  }
  .mv-card:hover{
    transform: translateY(-3px);
    border-color: rgba(129,199,132,.36);
    box-shadow: 0 18px 36px rgba(46,125,50,.16);
  }

  .icon-square{
    position: relative;
    box-shadow: 0 14px 28px rgba(46,125,50,.25);
  }
  .icon-square::after{
    /* glow ring */
    content:"";
    position:absolute; inset:-8px;
    border-radius: 999px;
    background: radial-gradient(60% 60% at 50% 40%, rgba(129,199,132,.35), transparent 70%);
    filter: blur(6px);
    z-index:-1;
  }
  .mv-title{
    font-weight: 800;
    color: var(--gc-dark);
  }
  .mv-text{
    color: #3a483a;
  }
  body.dark-mode .mv-title{ color:#c8f0cb; }
  body.dark-mode .mv-text { color:#dfe8df; }

  /* ========= TEAM ========= */
  .team-section{
    padding: clamp(44px, 5vw, 80px) 0;
  }
  .team-card{
    border: 1px solid rgba(129,199,132,.18);
    border-radius: 12px;
    background: var(--gc-card);
    box-shadow: var(--gc-shadow);
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    position: relative;
    overflow: visible;
  }
  .team-card:hover{
    transform: translateY(-4px);
    border-color: rgba(129,199,132,.34);
    box-shadow: 0 18px 36px rgba(46,125,50,.16);
  }

  /* Avatar ring and lift */
  .team-card .card-img-top{
    position: relative;
    box-shadow: 0 12px 26px rgba(0,0,0,.12);
    border: 4px solid #fff;
  }
  .team-card .card-img-top::after{ display:none; } /* prevent weird defaults */

  .team-card .card-body h5{
    font-weight: 800;
    color: var(--gc-dark);
  }
  body.dark-mode .team-card .card-body h5{ color:#c6ecc9; }

  .team-role{
    color: var(--gc-primary);
    font-weight: 700;
  }
  body.dark-mode .team-role{ color:#a2dca7; }

  /* Micro “shine” on hover */
  .team-card::after{
    content:"";
    position:absolute; left:0; top:0; right:0; height: 4px;
    background: linear-gradient(90deg, transparent, rgba(129,199,132,.55), transparent);
    opacity:.0;
    transition: opacity .25s ease;
    border-radius: 16px 16px 0 0;
  }
  .team-card:hover::after{ opacity:.9; }

  /* Buttons */
  .pill-btn{
    border-radius: 999px;
    padding: 6px 14px;
    font-weight: 700;
  }
  @media (max-width: 576px){
    .mv-section{ padding: 36px 0; }
    .team-section{ padding: 34px 0; }
    .team-card .card-img-top{ width: 124px !important; height: 124px !important; }
    .team-card .card-body h5{ font-size: 1rem; line-height: 1.35; }
    .team-role{ font-size: .92rem; }
  }
</style>

<!-- =================== Mission & Vision =================== -->
<section class="mv-section">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-9 reveal">
        <h2 class="display-5 fw-bold mb-2 section-title">Our Mission & Vision</h2>
        <p class="section-sub mb-4">Turning every sustainability action into lasting change.</p>
      </div>
    </div>

    <div class="row justify-content-center g-4">
      <!-- Mission -->
      <div class="col-lg-10 col-xl-8 reveal">
        <div class="card mv-card shadow-sm mb-3">
          <div class="card-body p-4 p-md-5">
            <div class="icon-square bg-green text-white rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
              <i class="fas fa-bullseye fs-4"></i>
            </div>
            <h3 class="h4 mv-title mb-3">Our Mission</h3>
            <p class="lead mv-text mb-0">
              At Green Credits, our mission is to empower individuals to drive sustainable change through meaningful actions. By offering a rewards-driven platform, we inspire and recognize sustainability-conscious choices, fostering a culture of sustainability that positively impacts our community and the environment. Together, we turn every green action into lasting change.
            </p>
          </div>
        </div>
      </div>

      <!-- Vision -->
      <div class="col-lg-10 col-xl-8 reveal">
        <div class="card mv-card shadow-sm">
          <div class="card-body p-4 p-md-5">
            <div class="icon-square bg-green text-white rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
              <i class="fas fa-eye fs-4"></i>
            </div>
            <h3 class="h4 mv-title mb-3">Our Vision</h3>
            <p class="lead mv-text mb-0">
              Our vision is to create a global movement where sustainability is seamlessly integrated into everyday life. We aim to inspire individuals, institutions, and communities to take responsibility for their sustainability actions for future generations. Through innovation, collaboration, and engagement, we envision a world where every action contributes to a sustainable tomorrow.
            </p>
          </div>
        </div>
      </div>
    </div> <!-- /row -->
  </div>
</section>

<!-- =================== Team =================== -->
<section class="team-section">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-12 mb-4 reveal">
        <h2 class="display-5 fw-bold section-title">Our Team</h2>
        <p class="lead section-sub">Meet the passionate individuals driving our mission forward</p>
      </div>
    </div>

    <div class="row g-4 justify-content-center reveal-stagger">
      <div class="col-md-6 col-lg-3">
        <div class="card team-card h-100 border-0 text-center p-2">
          <img src="assets/images/Arezo Jafari .png" class="card-img-top rounded-circle mx-auto mt-4" alt="Arezo Jafari portrait" style="width: 150px; height: 150px; object-fit: cover;">
          <div class="card-body">
            <h5 class="card-title">Arezo Jafari</h5>
            <p class="team-role mb-2">Project Leader</p>
            <a href="#" class="btn btn-sm btn-outline-green rounded-pill pill-btn px-3">
              <i class="fas fa-graduation-cap me-1"></i> BBA
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-3">
        <div class="card team-card h-100 border-0 text-center p-2">
          <img src="assets/images/kedah_fair7(1).jpg" class="card-img-top rounded-circle mx-auto mt-4" alt="Chit Ko Ko portrait" style="width: 150px; height: 150px; object-fit: cover;">
          <div class="card-body">
            <h5 class="card-title">Chit Ko Ko</h5>
            <p class="team-role mb-2">System Creator & Full Stack Developer</p>
            <a href="#" class="btn btn-sm btn-outline-green rounded-pill pill-btn px-3">
              <i class="fas fa-graduation-cap me-1"></i> BCS (Hons.)
            </a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card team-card h-100 border-0 text-center p-2">
          <img src="assets/images/ayman.jpeg" class="card-img-top rounded-circle mx-auto mt-4" alt="Ayman portrait" style="width: 150px; height: 150px; object-fit: cover;">
          <div class="card-body">
            <h5 class="card-title">Ayman Abdulsalam Mohammed Hussein Al-Sinan</h5>
            <p class="team-role mb-2">Operations Manager</p>
            <a href="#" class="btn btn-sm btn-outline-green rounded-pill pill-btn px-3">
              <i class="fas fa-graduation-cap me-1"></i> BBA
            </a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card team-card h-100 border-0 text-center p-2">
          <img src="assets/images/farhana.jpg" class="card-img-top rounded-circle mx-auto mt-4" alt="Farhana portrait" style="width: 150px; height: 150px; object-fit: cover;">
          <div class="card-body">
            <h5 class="card-title">Farhana Akter</h5>
            <p class="team-role mb-2">Finance and Administration Manager</p>
            <a href="#" class="btn btn-sm btn-outline-green rounded-pill pill-btn px-3">
              <i class="fas fa-graduation-cap me-1"></i> BBA (HRM)
            </a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card team-card h-100 border-0 text-center p-2">
          <img src="assets/images/hilal.jpg" class="card-img-top rounded-circle mx-auto mt-4" alt="Hilal portrait" style="width: 150px; height: 150px; object-fit: cover;">
          <div class="card-body">
            <h5 class="card-title">Hilal Abid</h5>
            <p class="team-role mb-2">Marketing and Communications Manager</p>
            <a href="#" class="btn btn-sm btn-outline-green rounded-pill pill-btn px-3">
              <i class="fas fa-graduation-cap me-1"></i> BBA
            </a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card team-card h-100 border-0 text-center p-2">
          <img src="assets/images/jubair.JPG" class="card-img-top rounded-circle mx-auto mt-4" alt="Mohamed Abdi Mohamed portrait" style="width: 150px; height: 150px; object-fit: cover;">
          <div class="card-body">
            <h5 class="card-title">Mohamed Abdi Mohamed</h5>
            <p class="team-role mb-2">Partnerships Manager</p>
            <a href="#" class="btn btn-sm btn-outline-green rounded-pill pill-btn px-3">
              <i class="fas fa-graduation-cap me-1"></i> BBA
            </a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card team-card h-100 border-0 text-center p-2">
          <img src="assets/images/monfus.jpg" class="card-img-top rounded-circle mx-auto mt-4" alt="Monfus Olanipekun portrait" style="width: 150px; height: 150px; object-fit: cover;">
          <div class="card-body">
            <h5 class="card-title">Monfus Olanipekun</h5>
            <p class="team-role mb-2">Logistics</p>
            <a href="#" class="btn btn-sm btn-outline-green rounded-pill pill-btn px-3">
              <i class="fas fa-graduation-cap me-1"></i> BBA
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  // Intersection Observer reveals
  (function(){
    const els = document.querySelectorAll('.reveal, .reveal-stagger');
    const io = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          e.target.classList.add('in-view');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.16 });

    els.forEach(el=> io.observe(el));

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      els.forEach(el=> el.classList.add('in-view'));
      io.disconnect();
    }
  })();
</script>

<?php include 'includes/footer.php'; ?>
