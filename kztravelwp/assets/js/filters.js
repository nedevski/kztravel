(function () {
  'use strict';

  var data = window.kztravelFilterData || {};
  var filterIndex = data.filterIndex || { priceRanges: [] };
  var priceRanges = filterIndex.priceRanges || [];
  var countryLabels = data.countryLabels || {};

  function parseDurationDays(duration) {
    if (!duration) return null;
    var match = String(duration).match(/(\d+)/);
    return match ? parseInt(match[1], 10) : null;
  }

  function getTripFilterPrice(trip) {
    if (trip.displayPrice === null || trip.displayPrice === undefined) {
      if (trip.price === null || trip.price === undefined) return null;
      return trip.price;
    }
    return trip.displayDiscountedPrice != null ? trip.displayDiscountedPrice : trip.displayPrice;
  }

  function priceInRange(price, range) {
    switch (range.id) {
      case 'under-400':
        return price < 400;
      case '400-600':
        return price >= 400 && price <= 600;
      case '600-900':
        return price > 600 && price <= 900;
      case 'over-900':
        return price > 900;
      default:
        if (range.max === null || range.max === undefined || range.max === '') {
          return price >= range.min;
        }
        return price >= range.min && price <= range.max;
    }
  }

  function tripHasActiveDiscount(trip) {
    return trip.hasDiscount === true || trip.hasDiscount === 1 || trip.hasDiscount === '1';
  }

  function parseFiltersFromSearch(search) {
    var params = new URLSearchParams(search);
    var country = params.get('country');
    var categories = (params.get('category') || '').split(',').filter(Boolean);
    var durations = (params.get('duration') || '').split(',').filter(Boolean);
    var priceId = params.get('price');
    var priceRange = null;
    if (priceId) {
      priceRange = priceRanges.find(function (range) { return range.id === priceId; }) || null;
    }
    return {
      country: country,
      categories: categories,
      durations: durations,
      priceRange: priceRange,
      discountedOnly: params.get('discount') === '1',
    };
  }

  function filterTrips(trips, filters) {
    return trips.filter(function (trip) {
      if (filters.country && trip.country !== filters.country) return false;

      if (filters.categories.length > 0) {
        var hasCategory = filters.categories.some(function (category) {
          return (trip.category || []).includes(category);
        });
        if (!hasCategory) return false;
      }

      if (filters.durations.length > 0) {
        var days = trip.durationDays != null ? String(trip.durationDays) : null;
        if (!days || !filters.durations.includes(days)) return false;
      }

      if (filters.priceRange) {
        var price = trip.price;
        if (price === null || price === undefined) return false;
        if (!priceInRange(price, filters.priceRange)) return false;
      }

      if (filters.discountedOnly && !tripHasActiveDiscount(trip)) return false;

      return true;
    });
  }

  function getFilterPool(trips, filters, dimension) {
    var partial = Object.assign({}, filters);
    switch (dimension) {
      case 'country':
        partial.country = null;
        break;
      case 'price':
        partial.priceRange = null;
        break;
      case 'duration':
        partial.durations = [];
        break;
      case 'category':
        partial.categories = [];
        break;
      case 'discount':
        partial.discountedOnly = false;
        break;
    }
    return filterTrips(trips, partial);
  }

  function hasActiveFilters(filters) {
    return Boolean(
      filters.country ||
      filters.categories.length ||
      filters.durations.length ||
      filters.priceRange ||
      filters.discountedOnly
    );
  }

  function buildFilterParams(filters) {
    var params = new URLSearchParams();
    if (filters.country) params.set('country', filters.country);
    if (filters.categories.length) params.set('category', filters.categories.join(','));
    if (filters.durations.length) params.set('duration', filters.durations.join(','));
    if (filters.priceRange) params.set('price', filters.priceRange.id);
    if (filters.discountedOnly) params.set('discount', '1');
    return params;
  }

  function formatCountryLabel(country) {
    return countryLabels[country] || country;
  }

  function formatLabel(value) {
    return value.split('-').map(function (word) {
      return word.charAt(0).toUpperCase() + word.slice(1);
    }).join(' ');
  }

  document.addEventListener('DOMContentLoaded', function () {
    var filterBar = document.querySelector('[data-filter-bar]');
    var tripGrid = document.querySelector('[data-trip-grid]');
    var emptyState = document.querySelector('[data-empty-state]');

    if (!filterBar || !tripGrid) return;

    var tripsJson = document.getElementById('kztravel-trips');
    var trips = tripsJson ? JSON.parse(tripsJson.textContent || '[]') : [];

    var cards = Array.from(tripGrid.querySelectorAll('.trip-card'));
    var openPanel = null;
    var filters = parseFiltersFromSearch(window.location.search);

    function syncUrl() {
      var params = buildFilterParams(filters);
      var search = params.toString();
      var next = search ? '?' + search : window.location.pathname;
      history.replaceState(null, '', next);
    }

    function applyFilters() {
      var filtered = filterTrips(trips, filters);
      var filteredSlugs = new Set(filtered.map(function (trip) { return trip.slug; }));

      cards.forEach(function (card) {
        var slug = card.dataset.tripSlug || '';
        card.classList.toggle('trip-card--hidden', !filteredSlugs.has(slug));
      });

      if (emptyState) {
        emptyState.hidden = filtered.length > 0;
      }

      var clearAll = document.querySelector('[data-filter-clear-all]');
      if (clearAll) {
        clearAll.hidden = !hasActiveFilters(filters);
      }

      updateFilterUI();
      syncUrl();
    }

    function countByCountry(pool, country) {
      return pool.filter(function (trip) { return trip.country === country; }).length;
    }

    function countByCategory(pool, category) {
      return pool.filter(function (trip) { return (trip.category || []).includes(category); }).length;
    }

    function countByDuration(pool, days) {
      return pool.filter(function (trip) { return String(trip.durationDays) === days; }).length;
    }

    function countByPriceRange(pool, range) {
      return pool.filter(function (trip) {
        return trip.price != null && priceInRange(trip.price, range);
      }).length;
    }

    function setTriggerState(trigger, isActive) {
      trigger.classList.toggle('filter-box__trigger--active', isActive);
      var dot = trigger.querySelector('.filter-box__trigger-dot');
      if (dot) dot.hidden = !isActive;
    }

    function updateFilterUI() {
      var filteredCount = filterTrips(trips, filters).length;
      var isPoolEmpty = filteredCount === 0 && hasActiveFilters(filters);
      var discountPool = getFilterPool(trips, filters, 'discount');
      var discountCount = discountPool.filter(tripHasActiveDiscount).length;
      var isDiscountDisabled = discountCount === 0;

      filterBar.classList.toggle('filter-box--empty', isPoolEmpty);

      var clearBtn = filterBar.querySelector('[data-filter-clear]');
      if (clearBtn) clearBtn.disabled = !hasActiveFilters(filters);

      filterBar.querySelectorAll('[data-panel]').forEach(function (trigger) {
        if (trigger.dataset.panel === 'discount') {
          trigger.classList.toggle('filter-box__trigger--active', filters.discountedOnly);
          trigger.setAttribute('aria-pressed', filters.discountedOnly ? 'true' : 'false');
          trigger.disabled = isDiscountDisabled;
          trigger.classList.toggle('filter-box__trigger--disabled', isDiscountDisabled);
          return;
        }

        var panel = trigger.dataset.panel;
        var isOpen = openPanel === panel;
        var isActive = false;

        if (panel === 'country') isActive = filters.country !== null;
        if (panel === 'price') isActive = filters.priceRange !== null;
        if (panel === 'duration') isActive = filters.durations.length > 0;
        if (panel === 'category') isActive = filters.categories.length > 0;

        trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        trigger.classList.toggle('filter-box__trigger--open', isOpen);
        var chevron = trigger.querySelector('.filter-box__chevron');
        if (chevron) {
          chevron.classList.toggle('filter-box__chevron--open', isOpen);
        }
        setTriggerState(trigger, isActive);
        trigger.disabled = isPoolEmpty;
        trigger.classList.toggle('filter-box__trigger--disabled', isPoolEmpty);

        var fullLabel = trigger.dataset.shortLabel;
        if (panel === 'country' && filters.country) {
          fullLabel = (data.strings && data.strings.filterCountry ? data.strings.filterCountry : 'Държава') + ': ' + formatCountryLabel(filters.country);
        }
        if (panel === 'price' && filters.priceRange) {
          fullLabel = (data.strings && data.strings.filterPrice ? data.strings.filterPrice : 'Цена') + ': ' + filters.priceRange.label;
        }
        if (panel === 'duration' && filters.durations.length) {
          fullLabel = (data.strings && data.strings.filterDuration ? data.strings.filterDuration : 'Продължителност') + ' (' + filters.durations.length + ')';
        }
        if (panel === 'category' && filters.categories.length) {
          fullLabel = (data.strings && data.strings.filterCategory ? data.strings.filterCategory : 'Категория') + ' (' + filters.categories.length + ')';
        }

        var full = trigger.querySelector('.filter-box__trigger-label--full');
        if (full) full.textContent = fullLabel;
      });

      var panelWrap = filterBar.querySelector('.filter-box__panel-wrap');
      if (panelWrap) {
        panelWrap.classList.toggle('filter-box__panel-wrap--open', Boolean(openPanel) && !isPoolEmpty);
      }

      filterBar.querySelectorAll('[data-panel-content]').forEach(function (panel) {
        var name = panel.dataset.panelContent;
        panel.hidden = openPanel !== name || isPoolEmpty;
      });

      updateChipStates();
      updateCounts(isPoolEmpty);
    }

    function updateChipStates() {
      filterBar.querySelectorAll('[data-filter-country]').forEach(function (chip) {
        var country = chip.dataset.filterCountry;
        var isSelected = country ? filters.country === country : filters.country === null;
        chip.classList.toggle('chip--active', isSelected);
      });

      filterBar.querySelectorAll('[data-filter-price]').forEach(function (chip) {
        var priceId = chip.dataset.filterPrice;
        var isSelected = priceId
          ? Boolean(filters.priceRange && filters.priceRange.id === priceId)
          : filters.priceRange === null;
        chip.classList.toggle('chip--active', isSelected);
      });

      filterBar.querySelectorAll('[data-filter-duration]').forEach(function (chip) {
        var days = chip.dataset.filterDuration;
        chip.classList.toggle('chip--active', filters.durations.includes(days));
      });

      filterBar.querySelectorAll('[data-filter-category]').forEach(function (chip) {
        var category = chip.dataset.filterCategory;
        chip.classList.toggle('chip--active', filters.categories.includes(category));
      });
    }

    function setChipVisibility(chip, count) {
      chip.hidden = count === 0;
      var countEl = chip.querySelector('.chip__count');
      if (countEl) countEl.textContent = String(count);
    }

    function updateCounts(isPoolEmpty) {
      if (isPoolEmpty) return;

      var countryPool = getFilterPool(trips, filters, 'country');
      var pricePool = getFilterPool(trips, filters, 'price');
      var durationPool = getFilterPool(trips, filters, 'duration');
      var categoryPool = getFilterPool(trips, filters, 'category');

      filterBar.querySelectorAll('[data-filter-country]').forEach(function (chip) {
        var country = chip.dataset.filterCountry;
        if (!country) {
          setChipVisibility(chip, countryPool.length);
          return;
        }
        setChipVisibility(chip, countByCountry(countryPool, country));
      });

      filterBar.querySelectorAll('[data-filter-price]').forEach(function (chip) {
        var priceId = chip.dataset.filterPrice;
        if (!priceId) {
          setChipVisibility(chip, pricePool.length);
          return;
        }
        var range = priceRanges.find(function (item) { return item.id === priceId; });
        setChipVisibility(chip, range ? countByPriceRange(pricePool, range) : 0);
      });

      filterBar.querySelectorAll('[data-filter-duration]').forEach(function (chip) {
        var days = chip.dataset.filterDuration;
        setChipVisibility(chip, countByDuration(durationPool, days));
      });

      filterBar.querySelectorAll('[data-filter-category]').forEach(function (chip) {
        var category = chip.dataset.filterCategory;
        setChipVisibility(chip, countByCategory(categoryPool, category));
      });
    }

    function clearFilters() {
      filters = {
        country: null,
        categories: [],
        durations: [],
        priceRange: null,
        discountedOnly: false,
      };
      openPanel = null;
      applyFilters();
    }

    filterBar.querySelectorAll('[data-panel]').forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        if (filterBar.classList.contains('filter-box--empty')) return;

        if (trigger.dataset.panel === 'discount') {
          if (trigger.disabled) return;
          openPanel = null;
          filters.discountedOnly = !filters.discountedOnly;
          applyFilters();
          return;
        }

        var panel = trigger.dataset.panel;
        openPanel = openPanel === panel ? null : panel;
        updateFilterUI();
      });
    });

    filterBar.querySelectorAll('[data-filter-country]').forEach(function (button) {
      button.addEventListener('click', function () {
        filters.country = button.dataset.filterCountry || null;
        applyFilters();
      });
    });

    filterBar.querySelectorAll('[data-filter-price]').forEach(function (button) {
      button.addEventListener('click', function () {
        var id = button.dataset.filterPrice;
        if (!id) {
          filters.priceRange = null;
        } else {
          filters.priceRange = priceRanges.find(function (range) { return range.id === id; }) || null;
        }
        applyFilters();
      });
    });

    filterBar.querySelectorAll('[data-filter-duration]').forEach(function (button) {
      button.addEventListener('click', function () {
        var days = button.dataset.filterDuration;
        if (filters.durations.includes(days)) {
          filters.durations = filters.durations.filter(function (value) { return value !== days; });
        } else {
          filters.durations = filters.durations.concat([days]);
        }
        applyFilters();
      });
    });

    filterBar.querySelectorAll('[data-filter-category]').forEach(function (button) {
      button.addEventListener('click', function () {
        var category = button.dataset.filterCategory;
        if (filters.categories.includes(category)) {
          filters.categories = filters.categories.filter(function (value) { return value !== category; });
        } else {
          filters.categories = filters.categories.concat([category]);
        }
        applyFilters();
      });
    });

    filterBar.querySelectorAll('[data-filter-clear]').forEach(function (button) {
      button.addEventListener('click', clearFilters);
    });

    document.querySelectorAll('[data-filter-clear-all]').forEach(function (button) {
      button.addEventListener('click', clearFilters);
    });

    applyFilters();
  });
})();
