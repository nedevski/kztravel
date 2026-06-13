(function () {
  'use strict';

  var STORAGE_KEY = 'kz-theme';
  var THEME_TRANSITION_MS = 350;
  var THEME_TRANSITION_CLASS = 'theme-transition';
  var skipTransition = true;

  function getTheme() {
    return document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
  }

  function updateToggleButtons(theme) {
    document.querySelectorAll('.theme-toggle').forEach(function (button) {
      var isLight = theme === 'light';
      button.setAttribute('aria-label', isLight ? button.dataset.labelDark : button.dataset.labelLight);
      button.setAttribute('title', isLight ? button.dataset.titleDark : button.dataset.titleLight);

      var moon = button.querySelector('.theme-toggle__icon--moon');
      var sun = button.querySelector('.theme-toggle__icon--sun');
      if (moon) moon.hidden = !isLight;
      if (sun) sun.hidden = isLight;
    });
  }

  function applyTheme(theme) {
    var root = document.documentElement;
    var animate = !skipTransition;
    skipTransition = false;

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (animate && !prefersReducedMotion) {
      root.classList.add(THEME_TRANSITION_CLASS);
    }

    root.dataset.theme = theme;
    localStorage.setItem(STORAGE_KEY, theme);
    updateToggleButtons(theme);

    if (animate && !prefersReducedMotion) {
      window.setTimeout(function () {
        root.classList.remove(THEME_TRANSITION_CLASS);
      }, THEME_TRANSITION_MS);
    }
  }

  function toggleTheme() {
    applyTheme(getTheme() === 'light' ? 'dark' : 'light');
  }

  document.addEventListener('DOMContentLoaded', function () {
    updateToggleButtons(getTheme());

    document.querySelectorAll('.theme-toggle').forEach(function (button) {
      button.addEventListener('click', toggleTheme);
    });
  });
})();
