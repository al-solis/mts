import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/flowbite.min.js",
                "resources/js/preline.js",
                "resources/js/select2.min.js",
            ],
            refresh: true,
        }),
    ],
});
