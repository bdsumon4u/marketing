import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/global.js",
                "resources/css/filament/app/theme.css",
                "resources/css/filament/admin/theme.css",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
