import { createViteConfig } from "vite-config-factory";
import { defineConfig } from "vite";

const entries = {
        'css/modularity-arcgis-map':               './source/sass/modularity-arcgis-map.scss',
        'js/modularity-arcgis-map':                './source/js/modularity-arcgis-map.js',
        'css/acf-field-osm':                       './source/sass/acf-field-osm.scss',
        'js/acf-field-osm':                        './source/js/acf-field-osm.js',
};

const baseConfig = createViteConfig(entries, {
	outDir: "assets/dist",
	manifestFile: "manifest.json",
});

export default defineConfig(({ mode }) => {
	const config = typeof baseConfig === 'function' ? baseConfig({ mode }) : baseConfig;
	
	// Strip console statements in production builds
	if (mode === 'production') {
		config.esbuild = {
			...config.esbuild,
			drop: ['console', 'debugger'],
		};
	}
	
	return config;
});
