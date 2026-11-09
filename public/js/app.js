/* ============================================================
   KB 'Aisyiyah 2 Dau — app.js
   Shared front-end behaviour for the public site.
   Ported from proto/assets/script.js; extended with the About Us
   dropdown needed by the new header nav.
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ---------- 1. Toggle mobile menu ---------- */
  var menuBtn = document.getElementById('menuBtn');
  var mobileMenu = document.getElementById('mobileMenu');
  var iconOpen = document.getElementById('iconOpen');
  var iconClose = document.getElementById('iconClose');

  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', function () {
      mobileMenu.classList.toggle('hidden');
      if (iconOpen) iconOpen.classList.toggle('hidden');
      if (iconClose) iconClose.classList.toggle('hidden');
    });
    mobileMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        mobileMenu.classList.add('hidden');
        if (iconOpen) iconOpen.classList.remove('hidden');
        if (iconClose) iconClose.classList.add('hidden');
      });
    });
  }

  /* ---------- 2. Desktop "About Us" dropdown ---------- */
  var dropdownToggle = document.getElementById('aboutDropdownToggle');
  var dropdownPanel = document.getElementById('aboutDropdownPanel');

  if (dropdownToggle && dropdownPanel) {
    var closeDropdown = function () {
      dropdownPanel.classList.add('hidden');
      dropdownToggle.setAttribute('aria-expanded', 'false');
    };
    var openDropdown = function () {
      dropdownPanel.classList.remove('hidden');
      dropdownToggle.setAttribute('aria-expanded', 'true');
    };

    dropdownToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = !dropdownPanel.classList.contains('hidden');
      isOpen ? closeDropdown() : openDropdown();
    });

    document.addEventListener('click', function (e) {
      if (!dropdownPanel.contains(e.target) && e.target !== dropdownToggle) {
        closeDropdown();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeDropdown();
    });
  }

  /* ---------- 3. Mobile "About Us" submenu ---------- */
  var mobileAboutToggle = document.getElementById('mobileAboutToggle');
  var mobileAboutPanel = document.getElementById('mobileAboutPanel');
  if (mobileAboutToggle && mobileAboutPanel) {
    mobileAboutToggle.addEventListener('click', function () {
      mobileAboutPanel.classList.toggle('hidden');
      mobileAboutToggle.classList.toggle('rotate-180');
    });
  }

  /* ---------- 4. Tampil/sembunyi kata sandi (auth.login) ---------- */
  var togglePassword = document.getElementById('togglePassword');
  var passwordInput = document.getElementById('password');
  if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function () {
      var isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      togglePassword.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
    });
  }

  /* ---------- 5. Filter galeri (gallery.blade.php) ---------- */
  var filterBtns = document.querySelectorAll('.filter-btn');
  var galleryCards = document.querySelectorAll('.gallery-card');

  if (filterBtns.length && galleryCards.length) {
    filterBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = btn.getAttribute('data-filter');

        filterBtns.forEach(function (b) {
          b.classList.remove('bg-forest-700', 'text-sand-50');
          b.classList.add('border', 'border-sand-300', 'text-ink/70');
        });
        btn.classList.add('bg-forest-700', 'text-sand-50');
        btn.classList.remove('border', 'border-sand-300', 'text-ink/70');

        galleryCards.forEach(function (card) {
          var cat = card.getAttribute('data-category');
          var show = target === 'all' || target === cat;
          card.style.display = show ? '' : 'none';
        });
      });
    });
  }

  /* ---------- 6. Lightbox galeri (gallery.blade.php) ---------- */
  var lightbox = document.getElementById('galleryLightbox');
  if (lightbox) {
    var lightboxImage = lightbox.querySelector('[data-lightbox-image]');
    var lightboxText = lightbox.querySelector('[data-lightbox-text]');
    var lastFocused = null;

    function openLightbox(card) {
      lastFocused = card;
      lightboxImage.src = card.getAttribute('data-lightbox-src');
      lightboxImage.alt = card.getAttribute('data-lightbox-caption') || '';
      lightboxText.textContent = card.getAttribute('data-lightbox-caption') || '';
      lightbox.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
      lightbox.querySelector('[data-lightbox-close]').focus();
    }

    function closeLightbox() {
      lightbox.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      // Release the image so a large file isn't held in memory while closed.
      lightboxImage.src = '';
      if (lastFocused) lastFocused.focus();
    }

    document.querySelectorAll('.gallery-card[data-lightbox-src]').forEach(function (card) {
      card.addEventListener('click', function () { openLightbox(card); });
    });

    lightbox.querySelectorAll('[data-lightbox-close], [data-lightbox-backdrop]').forEach(function (closer) {
      closer.addEventListener('click', closeLightbox);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !lightbox.classList.contains('hidden')) closeLightbox();
    });
  }

});
