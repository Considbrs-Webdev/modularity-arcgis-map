/**
 * ACF Open Street Map field
 *
 * Initialises a Leaflet map inside each .acf-osm-field element rendered by
 * the AcfField\OpenStreetMap PHP class.
 *
 * Behaviour
 * ---------
 * - A click on the map places / moves the marker and records lat + lng.
 * - A zoom change while a marker exists updates the saved zoom.
 * - The "Reset to default" button removes the stored value so the filter
 *   defaults (from the settings page) take effect on next load.
 */

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerUrl from '../../assets/images/marker.svg';

(function () {
    'use strict';

    /**
     * Initialise a single OSM field element.
     *
     * @param {HTMLElement} wrapper
     */
    function initOsmField(wrapper) {
        const fieldId    = wrapper.dataset.fieldId;
        const defaultLat = parseFloat(wrapper.dataset.lat);
        const defaultLng = parseFloat(wrapper.dataset.lng);
        const defaultZoom = parseInt(wrapper.dataset.zoom, 10);
        const hasValue   = wrapper.dataset.hasValue === 'true';

        const mapEl       = document.getElementById(fieldId + '-map');
        const hiddenInput = document.getElementById(fieldId);
        const latInput    = wrapper.querySelector('.acf-osm-field__lat');
        const lngInput    = wrapper.querySelector('.acf-osm-field__lng');
        const zoomInput   = wrapper.querySelector('.acf-osm-field__zoom');
        const resetBtn    = wrapper.querySelector('.acf-osm-field__reset');
        const searchInput = wrapper.querySelector('.acf-osm-field__search-input');
        const searchResults = wrapper.querySelector('.acf-osm-field__search-results');

        if (!mapEl || !hiddenInput) {
            return;
        }

        // ------------------------------------------------------------------ //
        // Leaflet map
        // ------------------------------------------------------------------ //
        const map = L.map(mapEl).setView([defaultLat, defaultLng], defaultZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 20,
        }).addTo(map);

        // Marker (only visible when the user has explicitly picked a location)
        // Use bundled marker SVG as the icon so the plugin doesn't rely on
        // Leaflet's external image assets.
        const markerIcon = L.icon({
            iconUrl: markerUrl,
            iconSize: [30, 44],
            iconAnchor: [15, 44],
            popupAnchor: [1, -34],
            tooltipAnchor: [16, -28],
        });

        let marker = null;

        if (hasValue) {
            marker = L.marker([defaultLat, defaultLng], { draggable: true, icon: markerIcon }).addTo(map);
            bindMarkerEvents(marker);
        }

        // ------------------------------------------------------------------ //
        // Helpers
        // ------------------------------------------------------------------ //

        function bindMarkerEvents(m) {
            m.on('dragend', function () {
                const pos = m.getLatLng();
                updateValue(pos.lat, pos.lng, map.getZoom());
            });
        }

        function updateValue(lat, lng, zoom) {
            const roundedLat  = Math.round(lat  * 1e6) / 1e6;
            const roundedLng  = Math.round(lng  * 1e6) / 1e6;
            const roundedZoom = Math.round(zoom);

            latInput.value  = roundedLat;
            lngInput.value  = roundedLng;
            zoomInput.value = roundedZoom;

            hiddenInput.value = JSON.stringify({
                lat:  roundedLat,
                lng:  roundedLng,
                zoom: roundedZoom,
            });

            // Ensure any listeners (ACF or WP) detect the programmatic change
            // by dispatching input/change events.
            try {
                const evt = new Event('change', { bubbles: true });
                hiddenInput.dispatchEvent(evt);
            } catch (e) {
                // fallback for older browsers
                const evt = document.createEvent('HTMLEvents');
                evt.initEvent('change', true, false);
                hiddenInput.dispatchEvent(evt);
            }
        }

        function clearValue() {
            latInput.value    = '';
            lngInput.value    = '';
            zoomInput.value   = '';
            hiddenInput.value = '';

            try {
                const evt = new Event('change', { bubbles: true });
                hiddenInput.dispatchEvent(evt);
            } catch (e) {
                const evt = document.createEvent('HTMLEvents');
                evt.initEvent('change', true, false);
                hiddenInput.dispatchEvent(evt);
            }
        }

        // ------------------------------------------------------------------ //
        // Map events
        // ------------------------------------------------------------------ //

        map.on('click', function (e) {
            const { lat, lng } = e.latlng;

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { draggable: true, icon: markerIcon }).addTo(map);
                bindMarkerEvents(marker);
            }

            updateValue(lat, lng, map.getZoom());
        });

        // When the user zooms while a marker is set, persist the new zoom.
        map.on('zoomend', function () {
            if (marker) {
                const pos = marker.getLatLng();
                updateValue(pos.lat, pos.lng, map.getZoom());
            }
        });

        // ------------------------------------------------------------------ //
        // Location search (Nominatim)
        // ------------------------------------------------------------------ //

        let searchTimer = null;

        function showResults(items) {
            searchResults.innerHTML = '';

            if (!items.length) {
                const li = document.createElement('li');
                li.className = 'acf-osm-field__search-no-results';
                li.textContent = 'No results found.';
                searchResults.appendChild(li);
                searchResults.hidden = false;
                return;
            }

            items.forEach(function (item) {
                const li = document.createElement('li');
                li.className = 'acf-osm-field__search-result';
                li.textContent = item.display_name;
                li.addEventListener('click', function () {
                    const lat  = parseFloat(item.lat);
                    const lng  = parseFloat(item.lon);
                    const zoom = map.getZoom() < 12 ? 14 : map.getZoom();

                    map.setView([lat, lng], zoom);

                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng], { draggable: true, icon: markerIcon }).addTo(map);
                        bindMarkerEvents(marker);
                    }

                    updateValue(lat, lng, zoom);
                    searchResults.hidden = true;
                    searchInput.value = item.display_name;
                });
                searchResults.appendChild(li);
            });

            searchResults.hidden = false;
        }

        function runSearch(query) {
            const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent(query);
            fetch(url, {
                headers: { 'Accept-Language': document.documentElement.lang || 'en' },
            })
                .then(function (res) { return res.json(); })
                .then(function (data) { showResults(data); })
                .catch(function () { searchResults.hidden = true; });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                const q = searchInput.value.trim();
                if (q.length < 3) {
                    searchResults.hidden = true;
                    return;
                }
                searchTimer = setTimeout(function () { runSearch(q); }, 400);
            });

            // Close results when clicking outside
            document.addEventListener('click', function (e) {
                if (!wrapper.contains(e.target)) {
                    searchResults.hidden = true;
                }
            });

            // Keyboard navigation
            searchInput.addEventListener('keydown', function (e) {
                const items = searchResults.querySelectorAll('.acf-osm-field__search-result');
                if (!items.length) return;

                const active = searchResults.querySelector('.acf-osm-field__search-result--active');
                let idx = Array.prototype.indexOf.call(items, active);

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    idx = (idx + 1) % items.length;
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    idx = (idx - 1 + items.length) % items.length;
                } else if (e.key === 'Enter' && active) {
                    e.preventDefault();
                    active.click();
                    return;
                } else if (e.key === 'Escape') {
                    searchResults.hidden = true;
                    return;
                } else {
                    return;
                }

                items.forEach(function (el) { el.classList.remove('acf-osm-field__search-result--active'); });
                items[idx].classList.add('acf-osm-field__search-result--active');
                items[idx].scrollIntoView({ block: 'nearest' });
            });
        }

        // ------------------------------------------------------------------ //
        // Reset button
        // ------------------------------------------------------------------ /

        resetBtn.addEventListener('click', function () {
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }

            clearValue();
            map.setView([defaultLat, defaultLng], defaultZoom);
        });
    }

    // ------------------------------------------------------------------ //
    // Boot: run after ACF has finished rendering its fields
    // ------------------------------------------------------------------ //

    function boot() {
        document.querySelectorAll('.acf-osm-field').forEach(function (wrapper) {
            // Avoid double-initialisation (ACF can clone / duplicate fields)
            if (wrapper.dataset.osmInit === '1') {
                return;
            }
            wrapper.dataset.osmInit = '1';
            initOsmField(wrapper);
        });
    }

    // ACF fires this action when new fields are added (e.g. repeater rows).
    if (typeof acf !== 'undefined') {
        acf.addAction('ready', boot);
        acf.addAction('append', boot);
    } else {
        document.addEventListener('DOMContentLoaded', boot);
    }
})();
