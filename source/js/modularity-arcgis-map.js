/**
 * Modularity ArcGIS Map
 * Initializes ArcGIS maps from data attributes on map containers
 */
(function() {
    'use strict';

    // Load ArcGIS SDK (uses shared window promise/flags to avoid duplicate loads)
    const loadArcGISSDK = () => {
        // Reuse a single promise across calls
        window.__ModularityArcgisMap = window.__ModularityArcgisMap || {};
        if (window.__ModularityArcgisMap.sdkPromise) {
            return window.__ModularityArcgisMap.sdkPromise;
        }

        const promise = new Promise((resolve, reject) => {
            console.log('Loading ArcGIS SDK...');

            // If ArcGIS runtime already present, resolve immediately
            if (window.$arcgis) {
                window.__ModularityArcgisMap.sdkLoaded = true;
                resolve();
                return;
            }

            // Add CSS
            const themeUrl = (window.ModularityArcgisMapSettings && window.ModularityArcgisMapSettings.theme_url) || (window.ModularityArcgisMapSettings && window.ModularityArcgisMapSettings.themeUrl) || 'https://js.arcgis.com/4.33/esri/themes/light/main.css';
            let themeSelectorExact = `link[href="${themeUrl}"]`;
            let themeSelectorContains = 'link[href*="js.arcgis.com"]';
            try {
                const themeHost = new URL(themeUrl).hostname;
                themeSelectorContains = `link[href*="${themeHost}"]`;
            } catch (e) {
                // ignore; keep fallback
            }

            if (!document.querySelector(themeSelectorExact) && !document.querySelector(themeSelectorContains)) {
                console.log('Adding ArcGIS CSS...', themeUrl);
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = themeUrl;
                link.setAttribute('data-arcgis-theme', '1');
                document.head.appendChild(link);
            }

            // Add JS
            const sdkUrl = (window.ModularityArcgisMapSettings && window.ModularityArcgisMapSettings.sdk_url) || (window.ModularityArcgisMapSettings && window.ModularityArcgisMapSettings.sdkUrl) || 'https://js.arcgis.com/4.33/';
            const sdkSelectorExact = `script[src="${sdkUrl}"]`;
            let sdkSelectorContains = 'script[src*="js.arcgis.com"]';
            try {
                const sdkHost = new URL(sdkUrl).hostname;
                sdkSelectorContains = `script[src*="${sdkHost}"]`;
            } catch (e) {
                // ignore; keep fallback
            }

            // If an appropriate script element already exists, attach to its load/error
            const existingScript = document.querySelector(sdkSelectorExact) || document.querySelector(sdkSelectorContains) || document.querySelector('script[data-arcgis-sdk]');

            if (existingScript) {
                // If SDK already exposed, resolve
                if (window.$arcgis) {
                    window.__ModularityArcgisMap.sdkLoaded = true;
                    resolve();
                    return;
                }

                // Otherwise attach listeners to the existing element
                existingScript.addEventListener('load', () => {
                    window.__ModularityArcgisMap.sdkLoaded = true;
                    resolve();
                });
                existingScript.addEventListener('error', () => {
                    reject(new Error('Failed to load ArcGIS SDK (existing script)'));
                });

                return;
            }

            // Inject script and mark it so future calls can detect it
            console.log('Adding ArcGIS JS...', sdkUrl);
            const script = document.createElement('script');
            script.src = sdkUrl;
            script.setAttribute('data-arcgis-sdk', '1');
            script.onload = () => {
                window.__ModularityArcgisMap.sdkLoaded = true;
                resolve();
            };
            script.onerror = () => reject(new Error('Failed to load ArcGIS SDK'));
            document.head.appendChild(script);
        });

        window.__ModularityArcgisMap.sdkPromise = promise;
        return promise;
    };

    // Initialize a single map
    const initMap = async (container) => {
        // Get configuration from data attributes
        const config = {
            lat: parseFloat(container.dataset.lat),
            lng: parseFloat(container.dataset.lng),
            zoom: parseInt(container.dataset.zoom, 10),
            portalUrl: container.dataset.portalUrl || (window.ModularityArcgisMapSettings && (window.ModularityArcgisMapSettings.portal_url || window.ModularityArcgisMapSettings.portalUrl)),
            webmapId: container.dataset.webmapId || (window.ModularityArcgisMapSettings && (window.ModularityArcgisMapSettings.map_id || window.ModularityArcgisMapSettings.mapId)),
            markerUrl: container.dataset.markerUrl || (window.ModularityArcgisMapSettings && (window.ModularityArcgisMapSettings.marker || window.ModularityArcgisMapSettings.marker_url || window.ModularityArcgisMapSettings.markerUrl)),
            markerWidth: parseInt(container.dataset.markerWidth || '27', 10),
            markerHeight: parseInt(container.dataset.markerHeight || '40', 10),
            showMarker: container.dataset.showMarker !== 'false',
            geoJsonData: container.dataset.geojson || null,
        };

        try {
            // Import required modules
            const [esriConfig, MapView, WebMap, Graphic, GraphicsLayer, GeoJSONLayer, LayerList, Expand] = await $arcgis.import([
                '@arcgis/core/config.js',
                '@arcgis/core/views/MapView.js',
                '@arcgis/core/WebMap.js',
                '@arcgis/core/Graphic.js',
                '@arcgis/core/layers/GraphicsLayer.js',
                '@arcgis/core/layers/GeoJSONLayer.js',
                '@arcgis/core/widgets/LayerList.js',
                '@arcgis/core/widgets/Expand.js',
            ]);

            // Set portal URL
            esriConfig.portalUrl = config.portalUrl;

            // Create WebMap
            const webmap = new WebMap({
                portalItem: {
                    id: config.webmapId,
                },
            });

            // Create MapView
            const view = new MapView({
                map: webmap,
                container: container,
            });

            // Add GeoJSON layer if data is provided
            if (config.geoJsonData) {
                try {
                    const geoJson = typeof config.geoJsonData === 'string'
                        ? config.geoJsonData
                        : JSON.stringify(config.geoJsonData);

                    const blob    = new Blob([geoJson], { type: 'application/json' });
                    const blobUrl = URL.createObjectURL(blob);

                    const geoJsonLayer = new GeoJSONLayer({
                        url: blobUrl,
                        title: container.dataset.geojsonTitle || 'GeoJSON Layer',
                        // Use a simple renderer with a thicker line for LineString/Polyline geometries
                        renderer: {
                            type: 'simple',
                            symbol: {
                                type: 'simple-line',
                                //color: [42, 58, 34, 1],
                                color: [255, 0, 0, 1],
                                width: 4,
                            },
                        },
                        popupTemplate: {
                            title: '{RUBRIK}',
                            content: [
                                {
                                    type: 'fields',
                                    fieldInfos: [
                                        { fieldName: 'INFO',  label: 'Info' },
                                        { fieldName: 'TID',   label: 'Tid' },
                                    ],
                                },
                            ],
                        },
                    });

                    view.map.add(geoJsonLayer);

                    // Zoom to the GeoJSON layer extent once both the layer and view are ready
                    Promise.all([view.when(), geoJsonLayer.when()]).then(() => {
                        geoJsonLayer.queryExtent().then((result) => {
                            if (result.extent) {
                                view.goTo(result.extent.expand(1.5));
                            }
                        });
                        URL.revokeObjectURL(blobUrl);
                    });
                } catch (geoJsonError) {
                    console.error('Error adding GeoJSON layer:', geoJsonError);
                }
            }

            // Add layer list widget
            const layerList = new LayerList({
                view: view,
            });

            const layerListExpand = new Expand({
                view: view,
                content: layerList,
                expanded: false,
            });

            view.ui.add(layerListExpand, 'top-right');

            // Add marker if enabled
            if (config.showMarker && config.lat && config.lng) {
                // Create graphics layer for markers
                const graphicsLayer = new GraphicsLayer();

                // Add graphics layer to map
                view.map.add(graphicsLayer);

                const point = {
                    type: 'point',
                    longitude: config.lng,
                    latitude: config.lat,
                };

                const markerSymbol = {
                    type: 'picture-marker',
                    url: config.markerUrl,
                    width: config.markerWidth + 'px',
                    height: config.markerHeight + 'px',
                };

                const pointGraphic = new Graphic({
                    geometry: point,
                    symbol: markerSymbol,
                });

                graphicsLayer.add(pointGraphic);

                // Only zoom to marker when no GeoJSON layer is driving the viewport
                if (!config.geoJsonData) {
                    view.when(() => {
                        view.goTo({
                            target: pointGraphic.geometry,
                            zoom: config.zoom,
                        });
                    });
                }
            } else if (!config.geoJsonData) {
                // Set zoom/center only when no GeoJSON layer handles navigation
                view.when(() => {
                    view.goTo({
                        center: [config.lng, config.lat],
                        zoom: config.zoom,
                    });
                });
            }

            // Store view reference on container for external access
            container._arcgisView = view;

        } catch (error) {
            console.error('Error initializing ArcGIS map:', error);
            container.innerHTML = '<p class="error">Failed to load map. Please try again later.</p>';
        }
    };

    // Initialize all maps on the page
    const initAllMaps = async () => {
        const mapContainers = document.querySelectorAll('.modularity-arcgis-map');
        
        if (mapContainers.length === 0) {
            return;
        }

        try {
            await loadArcGISSDK();
            
            // Initialize each map
            for (const container of mapContainers) {
                // Skip if already initialized
                if (container._arcgisView) {
                    continue;
                }
                await initMap(container);
            }
        } catch (error) {
            console.error('Error loading ArcGIS SDK:', error);
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllMaps);
    } else {
        initAllMaps();
    }

    // Expose initialization function for dynamic content
    window.ModularityArcgisMap = {
        init: initAllMaps,
        initContainer: initMap,
    };
})();
