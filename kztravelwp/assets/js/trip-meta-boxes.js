(function () {
	'use strict';

	function reindexSection(section) {
		var repeater = section.getAttribute('data-repeater');
		var prefixMap = {
			dates: 'kztravel_trip_dates',
			itinerary: 'kztravel_trip_itinerary',
			included: 'kztravel_trip_included',
			excluded: 'kztravel_trip_excluded',
		};
		var prefix = prefixMap[repeater];
		if (!prefix) {
			return;
		}

		var rows = section.querySelectorAll('.kztravel-trip-meta__row');
		rows.forEach(function (row, index) {
			row.querySelectorAll('[name]').forEach(function (input) {
				input.name = input.name.replace(
					new RegExp(prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\[[^\\]]+\\]'),
					prefix + '[' + index + ']'
				);
			});
		});
	}

	function addRow(section) {
		var template = section.querySelector('.kztravel-trip-meta__template');
		if (!template) {
			return;
		}

		var container = section.querySelector('tbody') || section.querySelector('.kztravel-trip-meta__cards');
		if (!container) {
			return;
		}

		var index = container.querySelectorAll('.kztravel-trip-meta__row').length;
		var html = template.innerHTML.replace(/__INDEX__/g, String(index));
		var wrapper = document.createElement('div');
		wrapper.innerHTML = html.trim();
		var row = wrapper.firstElementChild;
		if (!row) {
			return;
		}

		container.appendChild(row);
		reindexSection(section);
	}

	document.addEventListener('click', function (event) {
		var addButton = event.target.closest('.kztravel-trip-meta__add');
		if (addButton) {
			var section = addButton.closest('.kztravel-trip-meta__section');
			if (section) {
				addRow(section);
			}
			return;
		}

		var removeButton = event.target.closest('.kztravel-trip-meta__remove');
		if (removeButton) {
			var row = removeButton.closest('.kztravel-trip-meta__row');
			var parentSection = removeButton.closest('.kztravel-trip-meta__section');
			if (row && parentSection) {
				row.remove();
				reindexSection(parentSection);
			}
		}
	});
})();
