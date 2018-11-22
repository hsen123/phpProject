// webpack.config.js
const Encore = require('@symfony/webpack-encore');

Encore
// the project directory where all compiled assets will be stored
    .setOutputPath('public/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // will create public/build/app.js and public/build/app.css
    .addEntry('app', './assets/js/app.js')
    // create a bundle for each page
    .addEntry('login', './assets/js/login.js')
    .addEntry('sidebar', './assets/js/sidebar.js')
    .addEntry('form-elements', './assets/js/form-elements.js')
    .addEntry('dashboard', './assets/js/dashboard/index.jsx')
    .addEntry('register', './assets/js/register.js')
    .addEntry('custom-dropdown', './assets/js/custom-dropdown.js')
    .addEntry('result-detail', './assets/js/result-detail/index.jsx')
    .addEntry('right-bar', './assets/js/right-bar.js')
    .addEntry('profile', './assets/js/profile.js')
    .addEntry('profile-right-bar', './assets/js/profile-right-bar.js')
    .addEntry('static-info-pages', './assets/js/static-info-pages.js')
    .addEntry('faq-edit', './assets/js/faq-edit/faq-edit.js')
    .addEntry('webview', './assets/js/webview.js')
    .addEntry('analysis', './assets/js/analysis/index.jsx')
    .addEntry('analysis-detail', './assets/js/analysis-detail/index.jsx')
    .addEntry('shared-analysis-detail', './assets/js/shared-analysis/index.jsx')
    .addEntry('result-list', './assets/js/result-list/index.jsx')
    .addEntry('feedback-modal', './assets/js/feedback-modal.js')
    .addEntry('notifications', './assets/js/notification/notification.js')
    .addEntry('admin-notifications', './assets/js/admin-notifications/index.jsx')
    .addEntry('admin-detail-notification', './assets/js/admin-notification-detail/index.jsx')
    .addEntry('admin-users', './assets/js/admin-users-table/index.jsx')
    .addEntry('admin-result-list', './assets/js/admin-result-list/index.jsx')
    .addEntry('admin-analyses', './assets/js/admin-analyses/index.jsx')
    .addEntry('manage-share-links', './assets/js/manage-share-links/index.jsx')

    .addEntry('header', './assets/css/header.scss')
    .addEntry('footer', './assets/css/footer.scss')
    .addEntry('merck-buttons', './assets/css/merck-buttons.scss')
    .addEntry('app-information', './assets/css/app-information.scss')
    .addEntry('error-pages', './assets/css/error-pages.scss')
    .addEntry('overlay', './assets/css/overlay.scss')
    .addEntry('admin-dashboard', './assets/css/admin-dashboard.scss')
    .addEntry('tooltip', './assets/css/tooltip.scss')
    .addEntry('achievement-badges', './assets/css/shared/achievement-badges.scss')
    .addEntry('level-badge', './assets/css/shared/level-badge.scss')
    .addEntry('admin-dashboard-tile', './assets/css/admin/dashboard/admin-dashboard-tile.scss')
    .addEntry('success-page', './assets/css/success-page.scss')

    .createSharedEntry('vendor', [
        'babel-polyfill',
        'whatwg-fetch',
        'moment',
        'jquery',
        'bootstrap',
        'react',
        'react-dom',
    ])

    // allow sass/scss files to be processed
    .enableSassLoader()
    .enableLessLoader(options => {
        options.javascriptEnabled = true;
    })

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // Enable react
    .enableReactPreset()

    .configureBabel(babelConfig => {
        babelConfig.presets.push(['env', {
            'targets': {
                browsers: ['>2%']
            }
        }]);
        // no plugins are added by default, but you can add some
        // babelConfig.plugins.push('babel-plugin-transform-async-to-generator');
        babelConfig.plugins.push('babel-plugin-transform-class-properties');
        babelConfig.plugins.push('babel-plugin-transform-object-rest-spread');
    })

    .enablePostCssLoader(options => {
        options.config = {
            path: 'config/postcss.config.js',
        };
    });

// export the final configuration
module.exports = Encore.getWebpackConfig();
