<?php include 'includes/header.php'; ?>
<style>
  /* ====== Theme Vars ====== */
  :root {
    --gc-primary: #2e7d32;
    --gc-secondary: #81c784;
    --gc-dark: #1b5e20;
    --gc-muted: #6b7a6b;
    --gc-bg: #f5f7f6;
    --gc-card: #ffffff;
    --gc-shadow: 0 18px 40px rgba(46,125,50,.12), 0 4px 16px rgba(46,125,50,.08);
    --gc-gradient: linear-gradient(135deg, #2e7d32 0%, #81c784 100%);
  }
  body.dark-mode {
    --gc-bg: #101212;
    --gc-card: #151717;
    --gc-muted: #a0a9a0;
    --gc-shadow: 0 18px 40px rgba(0,0,0,.45), 0 4px 16px rgba(0,0,0,.35);
    --gc-gradient: linear-gradient(135deg, #1b5e20 0%, #4c8c4f 100%);
  }

  /* ====== Section ====== */
  .developer-section {
    padding: clamp(48px, 6vw, 120px) 0;
    background:
      radial-gradient(60% 80% at 15% 20%, rgba(129,199,132,.15), transparent 55%),
      radial-gradient(50% 80% at 85% 10%, rgba(46,125,50,.08), transparent 60%),
      var(--gc-bg);
    position: relative;
    overflow: hidden;
  }
  .developer-section::after {
    content: "";
    position: absolute;
    inset: auto -20% -120px -20%;
    height: 220px;
    background:
      radial-gradient(50% 70% at 50% 0%, rgba(129,199,132,.18), transparent 70%);
    filter: blur(40px);
    pointer-events: none;
    z-index: 0;
  }

  .section-header {
    text-align: center;
    margin-bottom: 42px;
    position: relative;
    z-index: 2;
  }
  .section-header h2 {
    margin: 0;
    font-weight: 900;
    letter-spacing: .4px;
    color: var(--gc-dark);
    font-size: clamp(2rem, 5vw, 3rem);
    background: var(--gc-gradient);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }
  .section-header p {
    margin-top: 12px;
    color: var(--gc-muted);
    font-size: 1.1rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
  }

  /* ====== Floating Particles ====== */
  .particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    overflow: hidden;
  }
  .particle {
    position: absolute;
    background: var(--gc-primary);
    border-radius: 50%;
    opacity: 0.15;
    animation: float linear infinite;
  }
  @keyframes float {
    0% { transform: translateY(0) rotate(0deg); }
    100% { transform: translateY(-100vh) rotate(360deg); }
  }

  /* ====== Card ====== */
  .developer-card {
    max-width: 980px;
    margin: 28px auto 0;
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 32px;
    background: color-mix(in oklab, var(--gc-card) 92%, white 8%);
    border: 1px solid color-mix(in oklab, var(--gc-secondary) 15%, transparent 85%);
    border-radius: 24px;
    box-shadow: var(--gc-shadow);
    position: relative;
    padding: 32px;
    backdrop-filter: saturate(120%) blur(4px);
    transform-style: preserve-3d;
    transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;
    z-index: 2;
    overflow: hidden;
  }
  .developer-card::after {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: 
      radial-gradient(circle at center, 
        rgba(129, 199, 132, 0.1) 0%, 
        transparent 70%);
    transform: rotate(30deg);
    z-index: -1;
    pointer-events: none;
  }
  .developer-card:hover {
    transform: translateY(-8px);
    box-shadow:
      0 22px 52px rgba(46,125,50,.16),
      0 6px 20px rgba(46,125,50,.10);
    border-color: color-mix(in oklab, var(--gc-secondary) 28%, transparent 72%);
  }

  /* ====== Image / Avatar ====== */
  .developer-image {
    position: relative;
    align-self: center;
    perspective: 1000px;
    margin: 0 auto;
  }
  .developer-image::after {
    content: "";
    position: absolute;
    inset: -10px;
    border-radius: 22px;
    background:
      radial-gradient(70% 70% at 50% 30%, rgba(129,199,132,.25), transparent 65%);
    z-index: 0;
    filter: blur(6px);
    transition: opacity .3s ease, transform .3s ease;
  }
  .developer-card:hover .developer-image::after { 
    opacity: .9; 
    transform: scale(1.02);
  }

  .developer-image img {
    position: relative;
    z-index: 1;
    width: 240px;
    height: 240px;
    object-fit: cover;
    border-radius: 18px;
    border: 5px solid #fff0;
    box-shadow: 0 12px 26px rgba(0,0,0,.10);
    transform: translateZ(20px);
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    filter: grayscale(15%) contrast(105%);
  }
  .developer-card:hover .developer-image img {
    transform: translateZ(26px) rotateZ(0.5deg) scale(1.02);
    box-shadow: 0 16px 32px rgba(0,0,0,.12);
    filter: grayscale(0%) contrast(110%);
    border: 5px solid rgba(255,255,255,0.1);
  }

  /* ====== Details ====== */
  .developer-details {
    position: relative;
  }
  .developer-details h3 {
    margin: 2px 0 4px;
    font-weight: 900;
    color: var(--gc-primary);
    letter-spacing: .2px;
    font-size: 2rem;
    background: var(--gc-gradient);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .developer-details .role {
    margin: 2px 0 24px;
    font-weight: 700;
    color: var(--gc-dark);
    font-size: 1.1rem;
    position: relative;
    display: inline-block;
  }

  /* Contact list */
  .contact-info {
    display: grid;
    gap: 16px;
  }
  .info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    background: color-mix(in oklab, var(--gc-card) 85%, #fff 15%);
    border: 1px solid color-mix(in oklab, var(--gc-secondary) 15%, transparent 85%);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    overflow: hidden;
  }
  .info-item:hover {
    transform: translateY(-2px);
    background: color-mix(in oklab, var(--gc-card) 82%, #fff 18%);
    border-color: color-mix(in oklab, var(--gc-secondary) 28%, transparent 72%);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }
  .info-item i {
    color: var(--gc-primary);
    width: 24px;
    text-align: center;
    font-size: 1.1rem;
    transition: transform 0.3s ease;
  }
  .info-item:hover i {
    transform: scale(1.1);
  }
  .info-item a {
    color: #263026;
    font-weight: 700;
    text-decoration: none;
    word-break: break-word;
    transition: color 0.2s ease;
  }
  .info-item a:hover {
    text-decoration: underline;
    color: var(--gc-primary);
  }

  /* Social Links */
  .social-links {
    display: flex;
    gap: 12px;
    margin-top: 24px;
  }
  .social-link {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: color-mix(in oklab, var(--gc-card) 85%, #fff 15%);
    border: 1px solid color-mix(in oklab, var(--gc-secondary) 15%, transparent 85%);
    color: var(--gc-primary);
    font-size: 1.1rem;
    transition: all 0.3s ease;
  }
  .social-link:hover {
    background: var(--gc-gradient);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(46,125,50,0.2);
  }

  /* ====== Reveal Animations ====== */
  .reveal {
    opacity: 0;
    transform: translateY(16px) scale(.98);
  }
  .reveal.in-view {
    opacity: 1;
    transform: none;
    transition: opacity .6s ease, transform .6s cubic-bezier(.2,.8,.2,1);
  }
  .reveal-stagger > * {
    opacity: 0;
    transform: translateY(12px);
  }
  .reveal-stagger.in-view > * {
    opacity: 1;
    transform: none;
    transition: opacity .55s ease, transform .55s ease;
  }
  .reveal-stagger.in-view > *:nth-child(1) { transition-delay: .04s; }
  .reveal-stagger.in-view > *:nth-child(2) { transition-delay: .10s; }
  .reveal-stagger.in-view > *:nth-child(3) { transition-delay: .16s; }

  /* ====== Responsive ====== */
  @media (max-width: 820px) {
    .developer-card {
      grid-template-columns: 1fr;
      padding: 24px;
      text-align: center;
    }
    .developer-image {
      margin-bottom: 24px;
    }
    .developer-image img {
      width: 200px;
      height: 200px;
    }
    .developer-details h3 {
      text-align: center;
    }
    .developer-details .role {
      display: block;
      text-align: center;
    }
    .contact-info {
      text-align: left;
    }
    .social-links {
      justify-content: center;
    }
  }

  /* ====== Dark Mode specific tweaks ====== */
  body.dark-mode .section-header h2 {
    background: linear-gradient(135deg, #9be2a0 0%, #4c8c4f 100%);
    -webkit-background-clip: text;
    background-clip: text;
  }
  body.dark-mode .section-header p {
    color: #c5d0c5;
  }
  body.dark-mode .developer-details h3 {
    background: linear-gradient(135deg, #b7ebbb 0%, #81c784 100%);
    -webkit-background-clip: text;
    background-clip: text;
  }
  body.dark-mode .developer-details .role {
    color: #b7deb9;
  }
  body.dark-mode .info-item a {
    color: #e8f2e8;
  }
  body.dark-mode .info-item:hover a {
    color: #9be2a0;
  }
</style>
<!-- Developer Info Section -->
<section class="developer-section">
    <div class="particles" id="particles"></div>
    <div class="container">
        <div class="section-header reveal">
            <h2>Developer Information</h2>
            <p>Meet the developer behind GreenCredit. A passionate full-stack developer dedicated to building sustainable digital solutions.</p>
        </div>

        <div class="developer-card reveal">
            <div class="developer-image">
                <img src="assets/images/kedah_fair7(1).jpg" alt="Chit Ko Ko" class="img-fluid">
            </div>

            <div class="developer-details">
                <h3>Chit Ko Ko</h3>
                <p class="role">Full Stack Developer</p>
                
                <div class="contact-info reveal-stagger">
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

                <!--<div class="social-links">-->
                <!--    <a href="#" class="social-link"><i class="fab fa-github"></i></a>-->
                <!--    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>-->
                <!--    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>-->
                <!--    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>-->
                <!--</div>-->
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script>
  // Floating particles animation
  function createParticles() {
    const container = document.getElementById('particles');
    if (!container) return;
    
    const particleCount = window.innerWidth < 768 ? 15 : 30;
    
    for (let i = 0; i < particleCount; i++) {
      const particle = document.createElement('div');
      particle.classList.add('particle');
      
      // Random size between 2px and 6px
      const size = Math.random() * 4 + 2;
      particle.style.width = `${size}px`;
      particle.style.height = `${size}px`;
      
      // Random position
      particle.style.left = `${Math.random() * 100}%`;
      particle.style.top = `${Math.random() * 100 + 100}%`;
      
      // Random animation duration between 20s and 40s
      const duration = Math.random() * 20 + 20;
      particle.style.animationDuration = `${duration}s`;
      
      // Random delay
      particle.style.animationDelay = `${Math.random() * 20}s`;
      
      container.appendChild(particle);
    }
  }
  
  // Smooth reveal on scroll
  (function(){
    const els = document.querySelectorAll('.developer-card, .section-header, .contact-info, .info-item, .social-links');
    document.querySelector('.developer-card')?.classList.add('reveal');
    document.querySelector('.section-header')?.classList.add('reveal');
    document.querySelector('.contact-info')?.classList.add('reveal-stagger');
    document.querySelector('.social-links')?.classList.add('reveal');

    const io = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          e.target.classList.add('in-view');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.18 });

    document.querySelectorAll('.reveal, .reveal-stagger').forEach(el=>io.observe(el));

    // Respect reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.querySelectorAll('.reveal, .reveal-stagger').forEach(el=>{
        el.classList.add('in-view');
      });
      io.disconnect();
    }
  })();
  
  // Initialize particles when DOM is loaded
  document.addEventListener('DOMContentLoaded', createParticles);
</script>