import laravel from "laravel-vite-plugin";
import { defineConfig, loadEnv } from "vite";

<<<<<<< HEAD
export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), "");
    const host = env.VITE_HOST || "127.0.0.1";

    return {
        plugins: [
            laravel({
                input: ["resources/css/app.css", "resources/js/app.js"],
                refresh: true,
            }),
        ],
        server: {
            host: "0.0.0.0",
            port: 5173,
            cors: true,
            hmr: {
                host: host,
            },
=======
export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: {
        host: "0.0.0.0",
        port: 5173,
        cors: true,
        hmr: {
            host: "10.2.14.89",
>>>>>>> 3d9fef9 (24112026 Update QCO preparation file management and preview)
        },
    };
});
