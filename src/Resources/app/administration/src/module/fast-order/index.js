import './page/fast-order-list';

Shopware.Module.register('fast-order', {
    type: 'plugin',
    name: 'fast-order',
    title: 'fast-order.main.menuLabel',
    description: 'fast-order.main.menuDescription',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',

    routes: {
        list: {
            component: 'fast-order-list',
            path: 'list'
        }
    },

    navigation: [{
        label: 'fast-order.main.menuLabel',
        color: '#82b1ff',
        path: 'fast.order.list',
        icon: 'default-shopping-paper-bag-product',
        parent: 'sw-content',
        position: 100
    }],

    settingsItem: [{
        group: 'plugin',
        to: 'fast-order.plugin.list',
        icon: 'default-object-rocket',
        name: 'fast-order.general.mainMenuItemGeneral'
    }]
});
