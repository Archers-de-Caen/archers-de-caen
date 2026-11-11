import { defineConfig } from 'vite'
import Symfony from '@symfony/reprise/vite'
import path from "node:path";

export default defineConfig({
    input: {
        'app/script': './assets/app/app.ts',
        'app/style': './assets/app/app.css',
        'admin/script': './assets/admin/admin.ts',
        'admin/style': './assets/admin/admin.css',
    },
    plugins: [
        Symfony({
            stimulus: {
                controllersJson: 'assets/app/controllers.json',
                controllersDir: 'assets/app/controllers',
            },
            copy: [
                {
                    from: './assets/app/images',
                    to: 'images/[path][name].[ext]',
                },
                {
                    from: './assets/app/fonts',
                    to: 'fonts/[path][name].[ext]',
                },
                {
                    from: './assets/app/documents',
                    to: 'documents/[path][name].[ext]',
                }
            ],
        }),
    ],
    server: {
        watch: {
            ignored: ['vendor', 'var'].map((dir) => path.resolve(__dirname, dir, '**')),
            usePolling: true,
        },
        host: "0.0.0.0",
        port: 5173,
    }
})
