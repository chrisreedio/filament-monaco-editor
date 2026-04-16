import esbuild from 'esbuild'

const isDev = process.argv.includes('--dev')

async function compile(options) {
    const context = await esbuild.context(options)

    if (isDev) {
        await context.watch()
    } else {
        await context.rebuild()
        await context.dispose()
    }
}

const defaultOptions = {
    define: {
        'process.env.NODE_ENV': isDev ? `'development'` : `'production'`,
    },
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: isDev ? 'inline' : false,
    sourcesContent: isDev,
    treeShaking: true,
    target: ['es2020'],
    minify: !isDev,
    loader: {
        '.css': 'css',
        '.ttf': 'base64',
        '.woff': 'base64',
        '.woff2': 'base64',
        '.eot': 'base64',
        '.svg': 'dataurl',
        '.png': 'dataurl',
    },
    plugins: [
        {
            name: 'watchPlugin',
            setup: function (build) {
                build.onStart(() => {
                    console.log(`Build started at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`)
                })

                build.onEnd((result) => {
                    if (result.errors.length > 0) {
                        console.log(`Build failed at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`, result.errors)
                    } else {
                        console.log(`Build finished at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`)
                    }
                })
            },
        },
    ],
}

;(async () => {
    const buildPromise = compile({
        ...defaultOptions,
        entryPoints: ['./resources/js/index.js'],
        outfile: './resources/dist/components/monaco-code-editor.js',
    })

    const cssPromise = compile({
        ...defaultOptions,
        entryPoints: ['./resources/css/index.css'],
        outfile: './resources/dist/monaco-code-editor.css',
    })

    await Promise.all([buildPromise, cssPromise])

    console.log('Build completed for monaco-code-editor.js and monaco-code-editor.css')
})()
