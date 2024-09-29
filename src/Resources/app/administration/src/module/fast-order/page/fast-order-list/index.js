import template from './fast-order-list.html.twig';

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
                {
                    property: 'sessionId',
                    label: this.$t('fast-order.list.columns.sessionId')
                },
                {
                    property: 'productId',
                    label: this.$t('fast-order.list.columns.productId')
                },
                {
                    property: 'qty',
                    label: this.$t('fast-order.list.columns.qty')
                },
                {
                    property: 'comment',
                    label: this.$t('fast-order.list.columns.comment'),
                    inlineEdit: 'string'
                },
                {
                    property: 'createdAt',
                    label: this.$t('fast-order.list.columns.createdAt')
                },
                {
                    property: 'updatedAt',
                    label: this.$t('fast-order.list.columns.updatedAt'),
                    visible: false
                }
            ]
        };
    },

    computed: {
        getCriteria() {
            if (this.criteria) {
                return this.criteria
            }

            return new Criteria();
        },
    },

    methods: {
        getList() {
            const context = Shopware.Context.api;
            this.repository.search(this.getCriteria, context).then(result => {
                this.fastOrders = result;
            });
        },

        onRefresh() {
            this.getList();
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('fast_order');
        this.getList();
    },
});
