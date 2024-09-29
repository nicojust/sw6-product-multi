import FastOrderPlugin from './fast-order/fast-order.plugin';

const PluginManager = window.PluginManager;
PluginManager.register('FastOrderPlugin', FastOrderPlugin, '[data-fast-order-plugin]');
