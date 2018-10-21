module.exports = {
    base: '/oc-mall-plugin/',
    title: 'oc-mall',
    description: 'E-commerce solution for October CMS',
    markdown: {
        lineNumbers: true,
        anchor: {permalink: true, permalinkBefore: true, permalinkSymbol: '#'}
    },
    themeConfig: {
        sidebar: [
            {
                title: 'Installation',
                children: [
                    '/getting-started/installation',
                    '/getting-started/pages-setup'
                ]
            },
            {
                title: 'Digging deeper',
                children: [
                    '/digging-deeper/properties',
                    '/digging-deeper/categories',
                    '/digging-deeper/products',
                    '/digging-deeper/currencies',
                    '/digging-deeper/shipping-methods',
                    '/digging-deeper/payment-methods',
                    '/digging-deeper/taxes',
                ]
            },
            {
                title: 'Components',
                children: [
                    '/components/product',
                    '/components/products'
                ]
            },
            {
                title: 'Extending',
                children: [
                    '/extending/payment-providers'
                ]
            },
            {
                title: 'Services',
                children: [
                    '/services/console'
                ]
            }
        ],
        repo: 'OFFLINE-GmbH/oc-mall-plugin',
        nav: [
            {text: 'Guide', link: '/'},
            {text: 'Marketplace', link: 'https://octobercms.com/plugin/offline-mall'},
        ],
        docsRepo: 'OFFLINE-GmbH/oc-mall-plugin',
        docsDir: 'docs',
        docsBranch: 'develop',
        editLinks: true,
        editLinkText: 'Help us improve this page!',
    }
}
