Nova.booting((Vue, router) => {
    Vue.component('index-paste-image', require('./components/IndexField'));
    Vue.component('detail-paste-image', require('./components/DetailField'));
    Vue.component('form-paste-image', require('./components/FormField'));
})
