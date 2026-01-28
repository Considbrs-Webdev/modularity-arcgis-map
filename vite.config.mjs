import { createViteConfig } from "vite-config-factory";

const entries = {
        'css/modularity-arcgis-map':               './source/sass/modularity-arcgis-map.scss',
        'js/modularity-arcgis-map':                './source/js/modularity-arcgis-map.js',
};

export default createViteConfig(entries, {
	outDir: "assets/dist",
	manifestFile: "manifest.json",
});
