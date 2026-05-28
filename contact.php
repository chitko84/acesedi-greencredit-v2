<?php 
include 'includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = $_POST['name'];
    $email   = $_POST['email'];
    $message = $_POST['message'];

    $query = "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)";
    $stmt  = $conn->prepare($query);
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        $admin_email   = "chitko.ko@student.aiu.edu.my";
        $admin_subject = "New Contact Form Message from $name";

        $admin_message = "
        <html>
        <head>
            <title>New Contact Message</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2E8B57; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { margin-top: 20px; text-align: center; font-size: 0.8em; color: #666; }
                ul { list-style: none; padding-left: 0; }
                li { margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Contact Message</h2>
                </div>
                <div class='content'>
                    <p>You have received a new message via the GreenCredit contact form:</p>
                    <ul>
                        <li><strong>Name:</strong> ".htmlspecialchars($name)."</li>
                        <li><strong>Email:</strong> ".htmlspecialchars($email)."</li>
                        <li><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</li>
                        <li><strong>Sent At:</strong> " . date("Y-m-d H:i:s") . "</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>This is an automated message from GreenCredit</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $admin_headers  = "MIME-Version: 1.0\r\n";
        $admin_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $admin_headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>\r\n";
        
        if(!mail($admin_email, $admin_subject, $admin_message, $admin_headers)){
            // Optional: log email failure
        }

        $_SESSION['success'] = "Your message has been sent successfully!";
        header('Location: contact.php');
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
        header('Location: contact.php');
        exit();
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
  :root{
    --gc-primary:#2e7d32;
    --gc-primary-600:#236526;
    --gc-accent:#81c784;
    --gc-bg:#f5f7f6;
    --gc-card:#ffffff;
    --gc-dark:#1b5e20;
    --gc-muted:#6b756b;
    --gc-border:1px solid rgba(46,125,50,.14);
    --gc-radius:16px;
    --gc-shadow:0 12px 28px rgba(46,125,50,.12), 0 4px 12px rgba(46,125,50,.08);
    --gc-shadow-xl:0 18px 44px rgba(46,125,50,.16), 0 8px 20px rgba(46,125,50,.1);
  }
  body.dark-mode{
    --gc-bg:#0f1110;
    --gc-card:#151917;
    --gc-muted:#c8d3c8;
    --gc-border:1px solid rgba(129,199,132,.2);
    --gc-shadow:0 12px 32px rgba(0,0,0,.35), 0 6px 16px rgba(0,0,0,.28);
    --gc-shadow-xl:0 22px 54px rgba(0,0,0,.45), 0 10px 28px rgba(0,0,0,.35);
  }

  /* Page backdrop flourish */
  .contact-container{
    position: relative;
    max-width:1200px;
    margin: 48px auto 64px;
    padding: 0 18px 18px;
  }
  .contact-container::before,
  .contact-container::after{
    content:"";
    position:absolute;
    width: 420px; height: 420px;
    border-radius: 50%;
    filter: blur(60px);
    opacity:.22; z-index: 0;
    pointer-events:none;
  }
  .contact-container::before{
    background: radial-gradient(ellipse at center, rgba(129,199,132,.6), transparent 60%);
    top:-120px; left:-120px;
  }
  .contact-container::after{
    background: radial-gradient(ellipse at center, rgba(46,125,50,.45), transparent 60%);
    bottom:-120px; right:-120px;
  }

  .contact-header{
    position:relative; z-index:1;
    text-align:center;
    margin-bottom: 28px;
  }
  .contact-header h2{
    font-weight: 900; margin:0;
    color: var(--gc-dark);
    letter-spacing:.3px;
  }
  body.dark-mode .contact-header h2{ color:#c6efc9; }
  .contact-header p{ color: var(--gc-muted); margin-top:8px; }

  .contact-content{
    position:relative; z-index:1;
    display:grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 22px;
  }
  @media (max-width: 992px){
    .contact-content{ grid-template-columns: 1fr; }
  }

  /* Cards */
  .contact-info, .contact-form{
    display:flex; flex-direction:column; gap:18px;
  }
  .contact-card, .contact-form{
    background: var(--gc-card);
    border: var(--gc-border);
    border-radius: var(--gc-radius);
    box-shadow: var(--gc-shadow);
    padding: 22px;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    backdrop-filter: blur(4px);
  }
  .contact-card:hover{ transform: translateY(-4px); box-shadow: var(--gc-shadow-xl); border-color: rgba(46,125,50,.28); }

  .contact-card h3,
  .contact-form h3{
    font-weight: 900;
    color: var(--gc-dark);
    margin: 0 0 12px;
    display:flex; align-items:center; gap:10px;
  }
  body.dark-mode .contact-card h3,
  body.dark-mode .contact-form h3{ color:#c6efc9; }
  .contact-card h3 i, .contact-form h3 i{ color: var(--gc-primary); }

  /* Info list */
  .contact-details{ list-style:none; padding:0; margin: 8px 0 0; }
  .contact-details li{
    display:flex; align-items:center; gap:10px;
    padding: 10px 12px; border-radius: 12px;
    border: 1px dashed rgba(46,125,50,.22);
    background: linear-gradient(180deg, rgba(129,199,132,.06), transparent 45%);
    margin-bottom: 10px;
    transition: background .25s ease, border-color .25s ease, transform .2s ease;
  }
  .contact-details li:hover{
    background: linear-gradient(180deg, rgba(129,199,132,.12), transparent 55%);
    border-color: rgba(46,125,50,.32);
    transform: translateX(2px);
  }
  .contact-details i{ color: var(--gc-primary); width: 18px; text-align:center; }
  .contact-details a{ color: inherit; text-decoration:none; font-weight: 700; }
  .contact-details a:hover{ text-decoration:underline; }

  /* Map */
  .map-container{
    position: relative;
    width:100%;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: var(--gc-shadow);
    border: var(--gc-border);
    background: #e8f5e9;
    aspect-ratio: 16 / 10;
  }
  .map-container iframe{
    position:absolute; inset:0; width:100%; height:100%; border:0;
    filter: saturate(1.02) contrast(1.02);
  }
  .map-container::after{
    content:"";
    position:absolute; inset:0; pointer-events:none;
    background: linear-gradient(180deg, rgba(0,0,0,.06), rgba(0,0,0,0) 30%);
  }

  /* Form */
  .form-group{ margin-bottom: 14px; }
  .form-group label{
    font-weight:700; color: var(--gc-dark); margin-bottom:6px; display:block;
  }
  body.dark-mode .form-group label{ color:#dfeedf; }
  .form-group input,
  .form-group textarea{
    width:100%;
    border: 1px solid rgba(46,125,50,.22);
    border-radius: 12px;
    padding: 12px 14px;
    background: linear-gradient(180deg, #fff, #fdfefe);
    transition: border-color .2s ease, box-shadow .2s ease, transform .05s ease;
    outline: none;
  }
  body.dark-mode .form-group input,
  body.dark-mode .form-group textarea{
    background:#1b201d; color:#eaf6ea; border-color: rgba(129,199,132,.25);
  }
  .form-group input:focus,
  .form-group textarea:focus{
    border-color: var(--gc-primary);
    box-shadow: 0 0 0 4px rgba(46,125,50,.15);
  }

  /* Submit button with ripple */
  .submit-btn{
    position:relative; overflow:hidden;
    background: linear-gradient(135deg, var(--gc-primary), var(--gc-primary-600));
    border: none; color:#fff; font-weight: 900;
    padding: 12px 18px; border-radius: 12px;
    box-shadow: 0 10px 22px rgba(46,125,50,.28);
    transition: transform .15s ease, box-shadow .2s ease;
  }
  .submit-btn:hover{ transform: translateY(-1px); box-shadow: 0 14px 28px rgba(46,125,50,.34); }
  .submit-btn:active{ transform: translateY(0); }
  .submit-btn i{ opacity:.95; }

  .submit-btn .ripple{
    position:absolute; border-radius:50%;
    transform: scale(0); animation: ripple .6s linear;
    background: rgba(255,255,255,.45);
  }
  @keyframes ripple{
    to{ transform: scale(4); opacity:0; }
  }

  /* Alerts */
  .alert-message{
    padding: 12px 14px; border-radius: 12px; margin-bottom: 12px;
    font-weight:700; box-shadow: var(--gc-shadow);
  }
  .alert-message.success{
    background: #e6f7ea; border: 1px solid #b2dfbd; color:#135e1c;
  }
  .alert-message.error{
    background: #fdecec; border: 1px solid #f5b5b5; color:#8a1f1f;
  }
  body.dark-mode .alert-message.success{ background:#0f2114; border-color:#285f36; color:#cfead4; }
  body.dark-mode .alert-message.error{ background:#2a1717; border-color:#6f2d2d; color:#ffd3d3; }

  /* Entrance animations (scroll reveal) */
  .reveal{ opacity:0; transform: translateY(22px); transition: opacity .6s ease, transform .6s ease; }
  .reveal.revealed{ opacity:1; transform: translateY(0); }
</style>

<div class="contact-container">
  <div class="contact-header reveal">
    <h2>Contact Us</h2>
    <p>Have questions or feedback? We'd love to hear from you!</p>
  </div>

  <div class="contact-content">
    <div class="contact-info">
      <div class="contact-card reveal">
        <h3><i class="fas fa-envelope"></i> Contact Information</h3>
        <ul class="contact-details">
          <li><i class="fas fa-envelope"></i> <a href="mailto:sedi.ysbc@aiu.edu.my">sedi.ysbc@aiu.edu.my</a></li>
          <li><i class="fas fa-phone"></i> +123 123 123</li>
          <li><i class="fab fa-whatsapp"></i> <a href="https://wa.me/+1234567890" target="_blank">Click to chat on WhatsApp</a></li>
        </ul>
      </div>

      <div class="contact-card reveal">
        <h3><i class="fas fa-map-marker-alt"></i> Our Location</h3>
        <div class="map-container">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.978022254398!2d100.38382217422982!3d6.133655127572455!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304b5ac489a998b9%3A0x3fa53e13f07fbb01!2sAlbukhary%20International%20University%20(AIU)!5e0!3m2!1sen!2smy!4v1746077900027!5m2!1sen!2smy" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </div>

    <div class="contact-form reveal">
      <?php 
      if (isset($_SESSION['error'])) {
          echo "<div class='alert-message error'><i class='fas fa-exclamation-circle me-2'></i>" . $_SESSION['error'] . "</div>";
          unset($_SESSION['error']);
      }
      if (isset($_SESSION['success'])) {
          echo "<div class='alert-message success'><i class='fas fa-check-circle me-2'></i>" . $_SESSION['success'] . "</div>";
          unset($_SESSION['success']);
      }
      ?>

      <h3><i class="fas fa-paper-plane"></i> Send Us a Message</h3>
      <form action="contact.php" method="POST" id="contactForm">
        <div class="form-group">
          <label for="name">Your Name</label>
          <input type="text" id="name" name="name" required autocomplete="name" />
        </div>

        <div class="form-group">
          <label for="email">Your Email</label>
          <input type="email" id="email" name="email" required autocomplete="email" />
        </div>

        <div class="form-group">
          <label for="message">Your Message</label>
          <textarea id="message" name="message" rows="5" required></textarea>
        </div>

        <button type="submit" class="submit-btn">
          <i class="fas fa-paper-plane me-1"></i> Send Message
        </button>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
  // Scroll-reveal with IntersectionObserver
  (function(){
    const els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
      els.forEach(el => el.classList.add('revealed'));
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting){
          entry.target.classList.add('revealed');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: .15 });
    els.forEach(el => io.observe(el));
  })();

  // Ripple on submit button
  document.querySelectorAll('.submit-btn').forEach(btn => {
    btn.addEventListener('click', function(e){
      const rect = this.getBoundingClientRect();
      const span = document.createElement('span');
      const size = Math.max(rect.width, rect.height);
      span.className = 'ripple';
      span.style.width = span.style.height = size + 'px';
      span.style.left = (e.clientX - rect.left - size/2) + 'px';
      span.style.top  = (e.clientY - rect.top  - size/2) + 'px';
      this.appendChild(span);
      setTimeout(() => span.remove(), 600);
    });
  });
</script>
