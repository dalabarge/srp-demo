window._ = require('lodash')

// @link https://github.com/uikit/uikit/issues/2519
import UIkit from 'uikit'
window.UIkit = UIkit

/**
 * Set up Axios for XHR requests.
 */
window.axios = require('axios')
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}

/**
 * Setup Vue as the frontend component framework.
 */
import Vue from 'vue'
window.vue = Vue

// Add FontAwesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { dom, library } from '@fortawesome/fontawesome-svg-core'
library.add(fas)
library.add(fab)
library.add(far)
dom.watch()
Vue.component('font-awesome-icon', FontAwesomeIcon)

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});
