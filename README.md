# Modularity ArcGIS Map

`modularity-arcgis-map` is a WordPress plugin that adds an ArcGIS-powered map module to [Modularity](https://github.com/helsingborg-stad/modularity). It is built for Modularity/Municipio-style installations where editors configure maps through ACF, while the frontend lazy-loads the ArcGIS JavaScript SDK and renders the selected web map.

The plugin also includes:

- a global settings page for shared ArcGIS defaults
- a custom ACF map picker based on Leaflet, OpenStreetMap tiles, and Nominatim search
- a reusable PHP helper for rendering ArcGIS map containers outside the Modularity module
- optional GeoJSON overlay support when using the helper directly

## Features

- Registers an `ArcGIS Map` Modularity module
- Adds a global settings page under `Settings -> ArcGIS Map Settings`
- Stores map coordinates as structured latitude, longitude, and zoom values
- Lets editors search for locations in the admin UI
- Supports default global portal URL, map ID, marker, and start position
- Supports per-module overrides for portal URL, map ID, marker, marker size, height, and map description
- Loads the ArcGIS SDK once per page and reuses it across multiple map instances
- Adds a Layer List widget to the map UI
- Exposes a `MapRenderer` helper so other PHP code can render compatible map containers

## Runtime Expectations

This plugin is not a fully standalone WordPress plugin in practice. The code expects a project that already provides:

- WordPress
- [Advanced Custom Fields](https://www.advancedcustomfields.com/) with options-page support
- [Modularity](https://github.com/helsingborg-stad/modularity)
- the Component Library Blade renderer used by Modularity
- the `AcfExportManager` class used for ACF import/export

The plugin repository itself contains no third-party Composer packages. Composer is only used to generate PSR-4 autoloading for the plugin namespace.

## How It Works

1. `modularity-arcgis-map.php` boots the plugin, loads translations, imports ACF field groups, exposes the Blade view path, and starts the app.
2. `source/php/App.php` registers the Modularity module, settings page, custom ACF field, admin assets, and frontend assets.
3. `source/php/Module/ArcgisMap.php` reads module fields and passes them to `ModularityArcgisMap\Helper\MapRenderer`.
4. `source/js/modularity-arcgis-map.js` scans for `.modularity-arcgis-map` elements, loads the ArcGIS SDK, and initializes the actual map view.
5. `source/php/AcfField/OpenStreetMap.php` and `source/js/acf-field-osm.js` power the admin-side coordinate picker.

## Installation

### 1. Place the plugin in your WordPress plugins directory

Example:

```text
wp-content/plugins/modularity-arcgis-map
```

### 2. Install JavaScript dependencies and build frontend assets

```bash
npm ci
npm run build
```

If you prefer the bundled helper script:

```bash
php build.php
```

### 3. Regenerate Composer autoloading when needed

```bash
composer dump-autoload
```

### 4. Activate the plugin

Activate `Modularity ArcGIS Map` from the WordPress admin.

## Global Configuration

After activation, go to `Settings -> ArcGIS Map Settings`.

### Settings fields

| Field | Purpose |
| --- | --- |
| `SDK URL` | URL to the ArcGIS JavaScript SDK loader. The plugin defaults to `https://js.arcgis.com/4.33/`. |
| `Theme URL` | URL to the matching ArcGIS stylesheet. The plugin defaults to `https://js.arcgis.com/4.33/esri/themes/light/main.css`. |
| `Default portal URL` | The ArcGIS portal or ArcGIS Online URL used when a module does not define its own portal. |
| `Default map ID` | Fallback ArcGIS web map ID used when a module does not define one. |
| `Default marker` | Global fallback marker image. If empty, the plugin uses `assets/images/marker.svg`. |
| `Default map position` | Default latitude, longitude, and zoom used when new modules are created and no explicit position has been chosen yet. |

### Recommended setup

- Keep `SDK URL` and `Theme URL` on the same ArcGIS version.
- Set a global `Default portal URL` and `Default map ID` if most modules should reuse the same ArcGIS content.
- Set a `Default map position` so editors get a sensible starting view in the coordinate picker.

## Using the Modularity Module

Create a new Modularity module of type `ArcGIS Map`.

### Module settings

| Field | Purpose |
| --- | --- |
| `Map position` | Coordinate picker storing latitude, longitude, and zoom. |
| `Height in pixels` | Frontend map height. Default `500`, minimum `300`, maximum `1000`. |
| `Show marker` | Enables a picture marker at the configured coordinates. |
| `Description` | Optional text rendered as a `<figcaption>` below the map. |
| `Portal URL` | Optional per-module portal override. |
| `Theme URL` | Present in the field group, but not currently used by the frontend runtime. Use the global setting instead. |
| `Map ID` | Optional per-module web map ID override. |
| `Marker` | Optional per-module marker image override. |
| `Marker width` | Marker width in pixels. Defaults to `27`. |
| `Marker height` | Marker height in pixels. Defaults to `40`. |

### Frontend output

The module view renders:

- the module title, unless Modularity is configured to hide it
- a `<figure>` wrapper
- a `.modularity-arcgis-map` container with data attributes consumed by the frontend script
- an optional `<figcaption>` when a description is set

## Admin Coordinate Picker

The custom `arcgis_open_street_map` ACF field is used for both:

- the global default map position
- the module-specific map position

### Editor behavior

- The field shows a Leaflet map with OpenStreetMap tiles.
- Editors can click the map to place or move a marker.
- Dragging the marker updates the saved coordinates.
- Zoom changes are saved when a marker exists.
- The search field uses Nominatim to search for places.
- `Reset to default` clears the stored value and resets the view to the configured default location.

### Stored value format

The raw stored value is a JSON string like:

```json
{"lat":59.3293,"lng":18.0686,"zoom":13}
```

When the field is read through ACF, the plugin formats it into:

```php
[
    'lat'  => 59.3293,
    'lng'  => 18.0686,
    'zoom' => 13,
]
```

## Developer Usage

### Rendering a map outside Modularity

Use `ModularityArcgisMap\Helper\MapRenderer` when you want to render a compatible ArcGIS map container from custom PHP code:

```php
<?php

use ModularityArcgisMap\Helper\MapRenderer;

echo MapRenderer::render([
    'lat'          => 65.5848,
    'lng'          => 22.1567,
    'zoom'         => 13,
    'portalUrl'    => 'https://www.arcgis.com',
    'webmapId'     => 'YOUR_WEBMAP_ID',
    'showMarker'   => true,
    'markerWidth'  => 27,
    'markerHeight' => 40,
    'height'       => '500px',
]);
```

### Supported `MapRenderer` options

| Key | Description |
| --- | --- |
| `id` | Optional HTML `id`. Auto-generated when omitted. |
| `lat` | Latitude. Defaults to `0`. |
| `lng` | Longitude. Defaults to `0`. |
| `zoom` | Zoom level. Defaults to `14`. |
| `portalUrl` | ArcGIS portal URL. Falls back to global settings. |
| `webmapId` | ArcGIS web map ID. Falls back to global settings. |
| `markerUrl` | Marker image URL. Falls back to global settings. |
| `markerWidth` | Marker width in pixels. |
| `markerHeight` | Marker height in pixels. |
| `showMarker` | Whether to render a marker. |
| `height` | CSS height, for example `500px`. |
| `geoJsonData` | Optional GeoJSON FeatureCollection rendered as a layer. |
| `geoJsonTitle` | Optional GeoJSON layer title. |

### GeoJSON overlays

When `geoJsonData` is provided, the frontend script creates an ArcGIS `GeoJSONLayer`, adds it to the web map, and zooms to its extent. The current popup template is geared toward feature properties named `RUBRIK`, `INFO`, and `TID`, so adjust the JavaScript if your GeoJSON schema differs.

## Frontend Behavior

The frontend map script:

- loads the ArcGIS SDK and theme CSS lazily
- avoids loading the SDK more than once per page
- creates an ArcGIS `WebMap` and `MapView`
- applies the configured portal URL
- optionally adds a picture marker at the chosen coordinates
- optionally adds a GeoJSON layer
- adds a Layer List widget in an `Expand` control at the top right

For custom integrations, the ArcGIS `MapView` instance is stored on the container element as `container._arcgisView` after initialization.

## Hooks and Extension Points

### Filter: `Modularity/Module/ArcGISMap/DefaultLocation`

The custom ACF coordinate field applies this filter when deciding its initial map view.

The plugin itself uses the filter to feed the global `Default map position` into module field defaults. You can also override it from custom code:

```php
add_filter('Modularity/Module/ArcGISMap/DefaultLocation', function (array $default): array {
    return [
        'lat'  => 59.3293,
        'lng'  => 18.0686,
        'zoom' => 12,
    ];
});
```

### Filter: `ModularityArcgisMap/Helper/CacheBust/RevManifestPath`

Use this if your build pipeline places the Vite manifest somewhere other than `assets/dist/manifest.json`.

## Development

### Available scripts

```bash
npm run dev
npm run build
npm run build:dev
npm run watch
npm run make-pot
```

### Build tooling

- Vite builds the JS and SCSS entries listed in `vite.config.mjs`
- output is written to `assets/dist`
- `assets/dist/manifest.json` is required so PHP can find the hashed file names
- the admin ACF field bundles Leaflet and its CSS through Vite

### Translation workflow

Generate the translation template with:

```bash
npm run make-pot
```

This updates `languages/modularity-arcgis-map.pot`.

## Project Structure

```text
modularity-arcgis-map.php          Plugin bootstrap
Public.php                         Blade rendering helper
source/php/App.php                 Application bootstrap and hooks
source/php/Module/ArcgisMap.php    Modularity module
source/php/Helper/MapRenderer.php  Reusable map container helper
source/php/Helper/SettingsHelper.php
source/php/AcfField/OpenStreetMap.php
source/js/modularity-arcgis-map.js Frontend ArcGIS integration
source/js/acf-field-osm.js         Admin coordinate picker
source/sass/                       Frontend and admin styles
assets/images/marker.svg           Default marker icon
```

## Troubleshooting

### The map does not render on the frontend

- Make sure `npm run build` has been run and that `assets/dist/manifest.json` points to files that actually exist.
- Confirm that the ArcGIS `SDK URL`, `Theme URL`, `Default portal URL`, and `Default map ID` are valid.
- Check browser console errors for ArcGIS loading failures.

### WordPress shows an "Assets not built" warning

The debug warning in `CacheBust.php` still says to run `gulp`, but this plugin uses Vite. Run:

```bash
npm run build
```

### The admin location search does not work

The ACF field search uses the public Nominatim endpoint. Make sure the admin environment can reach:

- `https://nominatim.openstreetmap.org`

### The editor cannot pick a useful default location

Set `Settings -> ArcGIS Map Settings -> Default map position`. New module instances will use that location as their initial view.

### The theme stylesheet does not change per module

The field group contains a module-level `Theme URL`, but the current frontend implementation reads the theme URL from global plugin settings. Configure the theme globally unless you also update the JS runtime.

## License

MIT. See [LICENSE](LICENSE).
