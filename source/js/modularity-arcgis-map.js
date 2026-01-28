/**
 * Modularity ArcGIS Map
 * Initializes ArcGIS maps from data attributes on map containers
 */
(function() {
    'use strict';

    // Load ArcGIS SDK
    const loadArcGISSDK = () => {
        console.log('Loading ArcGIS SDK...');
        return new Promise((resolve, reject) => {
            console.log('Checking if ArcGIS SDK is already loaded...');
            // Check if already loaded
            if (window.$arcgis) {
                console.log('ArcGIS SDK already loaded.');
                resolve();
                return;
            }

            console.log(document.querySelector('link[href*="arcgis"]'));

            // Add CSS
            if (!document.querySelector('link[href*="js.arcgis.com"]')) {
                console.log('Adding ArcGIS CSS...');
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://js.arcgis.com/4.33/esri/themes/light/main.css';
                document.head.appendChild(link);
            }

            // Add JS
            if (!document.querySelector('script[src*="js.arcgis.com"]')) {
                console.log('Adding ArcGIS JS...');
                const script = document.createElement('script');
                script.src = 'https://js.arcgis.com/4.33/';
                script.onload = () => resolve();
                script.onerror = () => reject(new Error('Failed to load ArcGIS SDK'));
                document.head.appendChild(script);
            } else {
                resolve();
            }
        });
    };

    // Initialize a single map
    const initMap = async (container) => {
        // Get configuration from data attributes
        const config = {
            lat: parseFloat(container.dataset.lat) || 65.319797,
            lng: parseFloat(container.dataset.lng) || 21.474190,
            zoom: parseInt(container.dataset.zoom, 10) || 14,
            portalUrl: container.dataset.portalUrl || 'https://pitea.maps.arcgis.com/',
            webmapId: container.dataset.webmapId || '0d275d0c94884258a24c70d3be3924b0',
            markerUrl: container.dataset.markerUrl || 'https://wip.pitea.se/karta/img/mappin_red.svg',
            showMarker: container.dataset.showMarker !== 'false',
        };

        try {
            // Import required modules
            const [esriConfig, MapView, WebMap, Graphic, GraphicsLayer, LayerList, Expand] = await $arcgis.import([
                '@arcgis/core/config.js',
                '@arcgis/core/views/MapView.js',
                '@arcgis/core/WebMap.js',
                '@arcgis/core/Graphic.js',
                '@arcgis/core/layers/GraphicsLayer.js',
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

            // Create graphics layer for markers
            const graphicsLayer = new GraphicsLayer();

            // Create MapView
            const view = new MapView({
                map: webmap,
                container: container,
            });

            // Add graphics layer to map
            view.map.add(graphicsLayer);

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
                const point = {
                    type: 'point',
                    longitude: config.lng,
                    latitude: config.lat,
                };

                const markerSymbol = {
                    type: 'picture-marker',
                    url: config.markerUrl,
                    width: '27px',
                    height: '40px',
                };

                const pointGraphic = new Graphic({
                    geometry: point,
                    symbol: markerSymbol,
                });

                graphicsLayer.add(pointGraphic);

                // Zoom to marker when view is ready
                view.when(() => {
                    view.goTo({
                        target: pointGraphic.geometry,
                        zoom: config.zoom,
                    });
                });
            } else {
                // Just set zoom without marker
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
