<?php 
include('includes/header.php'); 
include('includes/db.php');

/** CONFIG **/
$per_page   = 6;   // cards per page
$MAX_WORDS  = 80;  // show "Read more" if description exceeds this
$page       = isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset     = ($page - 1) * $per_page;

/** COUNT TOTAL NEWS (avoid JOIN to keep accurate count) **/
$total_rows = 0;
if ($res = $conn->query("SELECT COUNT(*) AS total FROM news_events")) {
  $row = $res->fetch_assoc();
  $total_rows = (int)$row['total'];
}
$total_pages = max(1, (int)ceil($total_rows / $per_page));

/** FETCH CURRENT PAGE OF NEWS + IMAGES **/
$sql = "
  SELECT n.id, n.title, n.date, n.content, GROUP_CONCAT(i.image) AS images
  FROM (
    SELECT id, title, date, content
    FROM news_events
    ORDER BY date DESC
    LIMIT $per_page OFFSET $offset
  ) AS n
  LEFT JOIN news_images i ON n.id = i.news_id
  GROUP BY n.id
  ORDER BY n.date DESC
";
$result = $conn->query($sql);
?>

<!-- News & Events -->
<div class="container-fluid py-5">
  <div class="container py-4">
    <div class="text-center mb-4">
      <h2 class="fw-extrabold mb-2 page-headline">Latest News & Events</h2>
    </div>

    <div class="row g-4" id="blog-container">
      <?php
      if ($result && $result->num_rows > 0) {
        $animation_delay = 0.1;

        while ($event = $result->fetch_assoc()) {
          $allowed_tags      = '<strong><em><b><i><p><br><ul><ol><li>';
          $clean_description = strip_tags($event['content'], $allowed_tags);
          $words             = preg_split('/\s+/', trim($clean_description));
          $has_more          = count($words) > $MAX_WORDS;

          $short_desc        = $has_more ? implode(' ', array_slice($words, 0, $MAX_WORDS)) : $clean_description;
          $remaining_desc    = $has_more ? implode(' ', array_slice($words, $MAX_WORDS)) : '';

          $images            = !empty($event['images']) ? array_filter(explode(',', $event['images'])) : [];
          $date_formatted    = date('M d, Y', strtotime($event['date']));
          $event_id          = (int)$event['id'];
          $safe_title        = htmlspecialchars($event['title']);
          ?>

          <div class="col-lg-4 col-md-6" 
               data-aos="fade-up" 
               data-aos-delay="<?php echo (int)($animation_delay * 100); ?>"
               data-aos-duration="600">
            <article class="blog-item card-neo h-100">
              <!-- Image area (click to open modal gallery) -->
              <div class="blog-img position-relative overflow-hidden"
                   <?php if (!empty($images)) : ?>
                     data-gallery-target="#galleryModal-<?php echo $event_id; ?>"
                   <?php endif; ?>>
                <?php if (!empty($images)): ?>
                  <div id="carousel-<?php echo $event_id; ?>" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                      <?php foreach ($images as $index => $img): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                          <img src="uploads/news/<?php echo htmlspecialchars($img); ?>" 
                               class="d-block w-100 blog-photo img-loading" 
                               alt="<?php echo $safe_title; ?>" 
                               style="height: 240px; object-fit: cover;"
                               data-aos="zoom-in"
                               data-aos-delay="<?php echo (int)($animation_delay * 100 + 120); ?>">
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                      <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?php echo $event_id; ?>" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                      </button>
                      <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?php echo $event_id; ?>" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                      </button>
                    <?php endif; ?>
                  </div>

                  <!-- Hover overlay button -->
                  <button class="gallery-overlay-btn" type="button"
                          data-bs-toggle="modal"
                          data-bs-target="#galleryModal-<?php echo $event_id; ?>">
                    <i class="fas fa-expand me-1"></i> View Photos
                  </button>
                <?php else: ?>
                  <!-- Placeholder when no images -->
                  <div class="no-image-placeholder d-flex align-items-center justify-content-center">
                    <div class="placeholder-inner text-center">
                      <div class="ph-icon"><i class="fas fa-leaf"></i></div>
                      <div class="ph-text">No image available</div>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- Gradient overlay -->
                <div class="img-overlay"></div>

                <!-- Date Badge -->
                <div class="date-badge">
                  <i class="far fa-calendar"></i>
                  <span><?php echo $date_formatted; ?></span>
                </div>
              </div>

              <!-- Content -->
              <div class="p-3 p-md-4 d-flex flex-column h-100">
                <h4 class="mb-2 blog-title" data-aos="fade-up" data-aos-delay="<?php echo (int)($animation_delay * 100 + 200); ?>">
                  <?php echo $safe_title; ?>
                </h4>

                <div class="meta-row mb-2" data-aos="fade" data-aos-delay="<?php echo (int)($animation_delay * 100 + 230); ?>">
                  <small class="text-muted"><i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($event['date']); ?></small>
                  <small class="text-muted ms-auto"><i class="fas fa-images me-1"></i><?php echo count($images); ?> photos</small>
                </div>

                <div class="desc-wrap <?php echo $has_more ? 'is-collapsed' : ''; ?>"
                     data-aos="fade-up" data-aos-delay="<?php echo (int)($animation_delay * 100 + 260); ?>">
                  <p class="mb-3 short-desc"><?php echo $short_desc; ?></p>

                  <?php if ($has_more): ?>
                    <div class="collapse" id="full-desc-<?php echo $event_id; ?>">
                      <p class="mb-0 full-desc"><?php echo $remaining_desc; ?></p>
                    </div>
                    <div class="fade-edge"></div>
                  <?php endif; ?>
                </div>

                <div class="mt-3 mt-auto d-flex align-items-center justify-content-between">
                  <?php if ($has_more): ?>
                    <a href="#full-desc-<?php echo $event_id; ?>" 
                       class="btn btn-sm btn-ghost-success read-more-toggle collapsed" 
                       data-bs-toggle="collapse" 
                       data-bs-target="#full-desc-<?php echo $event_id; ?>" 
                       aria-expanded="false" 
                       aria-controls="full-desc-<?php echo $event_id; ?>">
                      <span class="when-collapsed">Read More <i class="fas fa-arrow-down ms-1"></i></span>
                      <span class="when-expanded">Read Less <i class="fas fa-arrow-up ms-1"></i></span>
                    </a>
                  <?php else: ?>
                    <span></span>
                  <?php endif; ?>

                  <?php if (!empty($images)): ?>
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3 soft-pill d-none d-md-inline"
                            data-bs-toggle="modal" data-bs-target="#galleryModal-<?php echo $event_id; ?>">
                      View Photos <i class="fas fa-images ms-1"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          </div>

          <?php if (!empty($images)): ?>
          <!-- Modal Gallery for this event -->
          <div class="modal fade gallery-modal" id="galleryModal-<?php echo $event_id; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header border-0">
                  <h5 class="modal-title"><i class="fas fa-images me-2"></i><?php echo $safe_title; ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                  <div id="galleryCarousel-<?php echo $event_id; ?>" class="carousel slide" data-bs-ride="false">
                    <?php if (count($images) > 1): ?>
                      <div class="carousel-indicators">
                        <?php foreach ($images as $idx => $img): ?>
                          <button type="button"
                                  data-bs-target="#galleryCarousel-<?php echo $event_id; ?>"
                                  data-bs-slide-to="<?php echo $idx; ?>"
                                  class="<?php echo $idx === 0 ? 'active' : ''; ?>"
                                  aria-current="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                                  aria-label="Slide <?php echo $idx+1; ?>"></button>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>

                    <div class="carousel-inner">
                      <?php foreach ($images as $index => $img): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                          <img src="uploads/news/<?php echo htmlspecialchars($img); ?>" 
                               class="d-block w-100 modal-photo"
                               alt="<?php echo $safe_title; ?>">
                        </div>
                      <?php endforeach; ?>
                    </div>

                    <?php if (count($images) > 1): ?>
                      <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel-<?php echo $event_id; ?>" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                      </button>
                      <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel-<?php echo $event_id; ?>" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="modal-footer border-0 justify-content-between">
                  <small class="text-muted"><i class="fas fa-camera me-1"></i><?php echo count($images); ?> photo(s)</small>
                  <button type="button" class="btn btn-success" data-bs-dismiss="modal"><i class="fas fa-check me-1"></i>Close</button>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php
          $animation_delay += 0.15;
        } // while
      } else {
        echo '<div class="col-12 text-center py-5" data-aos="fade-up">
                <div class="alert alert-success bg-success text-white border-0">
                  <i class="fas fa-info-circle me-2"></i> No news or events found
                </div>
              </div>';
      }
      ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <nav aria-label="Page navigation" class="mt-5" data-aos="fade-up">
        <ul class="pagination justify-content-center pagination-neo">
          <!-- First -->
          <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $page <= 1 ? '#' : '?page=1'; ?>" aria-label="First">
              <i class="fas fa-angles-left"></i>
            </a>
          </li>

          <!-- Prev -->
          <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $page <= 1 ? '#' : '?page=' . ($page - 1); ?>" aria-label="Previous">
              <i class="fas fa-angle-left"></i>
            </a>
          </li>

          <?php
          $window = 2;
          $start  = max(1, $page - $window);
          $end    = min($total_pages, $page + $window);

          if ($start > 1) {
              echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
              if ($start > 2) echo '<li class="page-item disabled"><span class="page-link ellipsis">…</span></li>';
          }

          for ($p = $start; $p <= $end; $p++) {
              $active = $p == $page ? 'active' : '';
              echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $p . '">' . $p . '</a></li>';
          }

          if ($end < $total_pages) {
              if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link ellipsis">…</span></li>';
              echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
          }
          ?>

          <!-- Next -->
          <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $page >= $total_pages ? '#' : '?page=' . ($page + 1); ?>" aria-label="Next">
              <i class="fas fa-angle-right"></i>
            </a>
          </li>

          <!-- Last -->
          <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $page >= $total_pages ? '#' : '?page=' . $total_pages; ?>" aria-label="Last">
              <i class="fas fa-angles-right"></i>
            </a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</div>

<style>
  /* ====== Theme / Tokens ====== */
  :root{
    --gc-primary:#2e7d32;
    --gc-primary-600:#236526;
    --gc-primary-700:#1b5e20;
    --gc-accent:#81c784;
    --gc-bg:#f5f7f6;
    --gc-card:#ffffff;
    --gc-muted:#5f6a5f;
    --gc-radius:16px;
    --gc-shadow:0 14px 36px rgba(46,125,50,.12), 0 3px 16px rgba(46,125,50,.08);
  }
  body.dark-mode{
    --gc-bg:#0f1110;
    --gc-card:#151917;
    --gc-muted:#c3cec3;
    --gc-shadow:0 16px 44px rgba(0,0,0,.45), 0 6px 18px rgba(0,0,0,.35);
  }
  body { background: var(--gc-bg); }

  .fw-extrabold { font-weight: 900; }
  .page-headline { color: var(--gc-primary-700); position:relative; display:inline-block; }
  .page-headline::after{
    content:""; position:absolute; left:50%; bottom:-10px; transform:translateX(-50%);
    width:72px; height:4px; border-radius:4px;
    background: linear-gradient(90deg, var(--gc-accent), var(--gc-primary));
    box-shadow: 0 6px 16px rgba(46,125,50,.25);
  }
  .page-subtitle { max-width:760px; margin:0 auto; color: var(--gc-muted); }
  body.dark-mode .page-headline { color:#c6efc9; }
  body.dark-mode .page-subtitle { color:#d6e0d6; }

  /* ====== Card ====== */
  .card-neo{
    background: var(--gc-card);
    border: 1px solid rgba(129,199,132,.18);
    border-radius: var(--gc-radius);
    box-shadow: var(--gc-shadow);
    overflow: hidden;
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    display: flex; flex-direction: column;
  }
  .card-neo:hover{
    transform: translateY(-4px);
    border-color: rgba(129,199,132,.35);
    box-shadow: 0 18px 36px rgba(46,125,50,.16), 0 6px 20px rgba(46,125,50,.10);
  }

  /* ====== Image Header ====== */
  .blog-img{
    height: 240px; position: relative; background: #eaf4ec; cursor: pointer;
  }
  .blog-photo{
    height: 240px; object-fit: cover;
    filter: brightness(.96) saturate(1.02);
    transition: transform .55s ease, filter .35s ease;
  }
  .card-neo:hover .blog-photo{ transform: scale(1.05) rotate(.3deg); filter: brightness(1) saturate(1.06); }
  .img-overlay{ position:absolute; inset:0; background: linear-gradient(180deg, rgba(0,0,0,0) 45%, rgba(0,0,0,.18) 100%); pointer-events:none; }

  /* Gallery hover button */
  .gallery-overlay-btn{
    position:absolute; right:10px; bottom:10px;
    border:1px solid rgba(255,255,255,.65);
    background: rgba(0,0,0,.35);
    color:#fff; border-radius:12px; padding:.35rem .6rem; font-weight:700;
    display:inline-flex; align-items:center; gap:.35rem; opacity:0;
    transition: all .2s ease; backdrop-filter: blur(4px);
  }
  .blog-img:hover .gallery-overlay-btn{ opacity:1; transform: translateY(-1px); }
  .gallery-overlay-btn:hover{ background: rgba(0,0,0,.55); }

  /* Placeholder when no images */
  .no-image-placeholder{ position:relative; height:240px; border-bottom:1px solid rgba(129,199,132,.15);
    background: radial-gradient(60% 80% at 20% 25%, rgba(129,199,132,.20), transparent 60%), linear-gradient(120deg, #e8f5e9, #d6eed8);
  }
  body.dark-mode .no-image-placeholder{ background: linear-gradient(120deg, #0f2013, #17301c); }
  .placeholder-inner{ color:#fff; text-shadow:0 2px 12px rgba(0,0,0,.35); }
  .ph-icon{ font-size:28px; margin-bottom:6px; }

  /* Date badge */
  .date-badge{
    position:absolute; left:10px; top:10px; background:#2e7d32; color:#fff; border-radius:10px;
    padding:6px 10px; font-size:.85rem; font-weight:700; box-shadow:0 6px 16px rgba(46,125,50,.35);
    display:flex; align-items:center; gap:6px; pointer-events:none;
  }

  /* Carousel Controls */
  .carousel-control-prev, .carousel-control-next{
    background: rgba(46,125,50,0.25); width: 38px; height: 38px; border-radius: 50%;
    top:50%; transform: translateY(-50%); opacity:0; transition: opacity .25s ease, background .2s ease;
    backdrop-filter: blur(4px); border:1px solid rgba(255,255,255,.25);
  }
  .card-neo:hover .carousel-control-prev, .card-neo:hover .carousel-control-next{ opacity:1; }
  .carousel-control-prev:hover, .carousel-control-next:hover { background: rgba(46,125,50,0.5); }

  /* ====== Content ====== */
  .blog-title{ font-weight: 800; color: var(--gc-primary-700); line-height:1.2; }
  body.dark-mode .blog-title{ color:#c6efc9; }
  .meta-row{ display:flex; gap:10px; align-items:center; }

  /* Description with fade edge */
  .desc-wrap{ position: relative; color:#2f3b2f; }
  body.dark-mode .desc-wrap{ color:#dce7dc; }
  .desc-wrap p{ margin-bottom:.9rem; line-height:1.6; }
  .desc-wrap .fade-edge{
    position:absolute; left:0; right:0; bottom:0; height:42px;
    background: linear-gradient(180deg, rgba(255,255,255,0), var(--gc-card));
    pointer-events:none; border-bottom-left-radius: var(--gc-radius); border-bottom-right-radius: var(--gc-radius);
  }
  .desc-wrap.is-expanded .fade-edge{ display:none; }

  /* Read more button */
  .btn-ghost-success{
    border: 1px solid rgba(46,125,50,.28);
    background: #ffffff; color: #2e7d32; border-radius: 999px; font-weight: 800;
    transition: all .2s ease;
  }
  .btn-ghost-success:hover{
    background: #2e7d32; color:#fff; border-color: #2e7d32; box-shadow: 0 8px 20px rgba(46,125,50,.25);
  }
  .when-collapsed, .when-expanded { display:inline-block; transition: opacity .2s ease, transform .2s ease; }
  .read-more-toggle.collapsed .when-expanded { display: none; }
  .read-more-toggle:not(.collapsed) .when-collapsed { display: none; }

  /* Modal gallery */
  .gallery-modal .modal-content{
    border-radius:18px; border:1px solid rgba(129,199,132,.25); box-shadow: var(--gc-shadow);
  }
  .gallery-modal .modal-title{ font-weight:800; color: var(--gc-primary-700); }
  .gallery-modal .modal-body{ padding-top: 0; }
  .modal-photo{ max-height: 70vh; object-fit: contain; background: #0b0f0d; }
  .gallery-modal .carousel-control-prev, 
  .gallery-modal .carousel-control-next{ opacity: 1; }

  /* Pagination */
  .pagination-neo .page-link{
    color: var(--gc-primary-700); background:#fff; border:1px solid rgba(46,125,50,.25);
    border-radius: 10px; margin: 0 4px; box-shadow: 0 3px 10px rgba(46,125,50,.08); transition: all .22s ease;
  }
  .pagination-neo .page-link:hover{
    background: #2e7d32; color:#fff; border-color:#2e7d32; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(46,125,50,.22);
  }
  .pagination-neo .page-item.active .page-link{
    background: linear-gradient(135deg, var(--gc-primary), var(--gc-primary-600)); color:#fff; border-color: transparent;
    box-shadow: 0 10px 24px rgba(46,125,50,.28);
  }
  .pagination-neo .page-item.disabled .page-link{ opacity:.55; }

  /* AOS helpers */
  [data-aos="fade-up"]{ opacity:0; transform: translateY(26px); transition: opacity .6s ease, transform .6s ease; }
  [data-aos="fade-up"].aos-animate{ opacity:1; transform: translateY(0); }
  [data-aos="fade"]{ opacity:0; transition: opacity .6s ease; }
  [data-aos="fade"].aos-animate{ opacity:1; }
  [data-aos="zoom-in"]{ opacity:0; transform: scale(.94); transition: opacity .6s ease, transform .6s ease; }
  [data-aos="zoom-in"].aos-animate{ opacity:1; transform: scale(1); }

  /* Image load reveal */
  .img-loading{ filter: blur(6px) saturate(1.08); transform: scale(1.02); }
  .img-loaded{ filter: none !important; transform: none !important; transition: filter .35s ease, transform .35s ease; }
</style>

<!-- AOS (Animate On Scroll) -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize AOS (if present)
  if (window.AOS) {
    AOS.init({ duration: 650, easing: 'ease-out-quad', once: true, offset: 100 });
  }

  // Initialize all card carousels
  document.querySelectorAll('.carousel').forEach(carousel => {
    new bootstrap.Carousel(carousel, { interval: 5000, ride: 'carousel', pause: 'hover' });
  });

  // Reveal loaded images
  document.querySelectorAll('.blog-photo').forEach(img => {
    if (img.complete) {
      img.classList.remove('img-loading'); img.classList.add('img-loaded');
    } else {
      img.addEventListener('load', () => { img.classList.remove('img-loading'); img.classList.add('img-loaded'); });
      img.addEventListener('error', () => { img.classList.remove('img-loading'); });
    }
  });

  // Read More behavior: toggle label + fade edge
  document.querySelectorAll('.read-more-toggle').forEach(btn => {
    const targetSel = btn.getAttribute('data-bs-target');
    const target    = document.querySelector(targetSel);
    if (!target) return;

    const wrap = btn.closest('.card-neo')?.querySelector('.desc-wrap');

    target.addEventListener('shown.bs.collapse', () => {
      btn.classList.remove('collapsed');
      wrap && wrap.classList.add('is-expanded');
      // small smooth scroll into view when expanded
      target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
    target.addEventListener('hidden.bs.collapse', () => {
      btn.classList.add('collapsed');
      wrap && wrap.classList.remove('is-expanded');
    });
  });

  // Clicking image area opens modal gallery (but ignore carousel buttons)
  document.querySelectorAll('.blog-img[data-gallery-target]').forEach(box => {
    box.addEventListener('click', (e) => {
      if (e.target.closest('.carousel-control-prev, .carousel-control-next')) return; // let arrows work
      const sel = box.getAttribute('data-gallery-target');
      const modalEl = document.querySelector(sel);
      if (!modalEl) return;
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    });
  });
});
</script>

<?php include('includes/footer.php'); ?>