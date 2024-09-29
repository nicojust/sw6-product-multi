import template from './fast-order-list.html.twig';
import './fast-order-list.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('fast-order-list', {
    inject: [
        'repositoryFactory'
    ],

    template,

    data() {
        return {
            fastOrders: [],
            repository: null,
            criteria: new Criteria(),
            columns: [
                { property: 'sessionId', label: 'Session ID' },
                { property: 'productId', label: 'Product ID' },
                { property: 'qty', label: 'Quantity' },
                { property: 'comment', label: 'Comment', inlineEdit: 'string' },
                { property: 'createdAt', label: 'Created At' }
            ]
        };
    },

    methods: {
        loadFastOrders() {
            const context = Shopware.Context.api;
            this.repository.search(this.criteria, context).then(result => {
                this.fastOrders = result;
            });
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('fast_order');
        this.loadFastOrders();
    },
});
