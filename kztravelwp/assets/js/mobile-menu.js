(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var header = document.querySelector('.site-header');
    var toggle = document.querySelector('.site-header__menu-toggle');
    var menu = document.getElementById('site-header-menu');

    if (!header || !toggle || !menu) return;

    function setOpen(open) {
      header.classList.toggle('site-header--menu-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? toggle.dataset.closeLabel : toggle.dataset.openLabel);

      var openIcon = toggle.querySelector('.site-header__menu-icon--open');
      var closeIcon = toggle.querySelector('.site-header__menu-icon--close');
      if (openIcon) openIcon.hidden = open;
      if (closeIcon) closeIcon.hidden = !open;
    }

    toggle.addEventListener('click', function () {
      setOpen(!header.classList.contains('site-header--menu-open'));
    });

    menu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        setOpen(false);
      });
    });

    window.addEventListener('resize', function () {
      if (window.matchMedia('(min-width: 768px)').matches) {
        setOpen(false);
      }
    });
  });
})();
