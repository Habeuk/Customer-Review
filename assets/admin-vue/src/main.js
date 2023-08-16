import './assets/main.css'

import { createApp } from 'vue'
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';
import App from './App.vue'
import router from './router'

//bootstrp js
const $ = require('jquery');
require('bootstrap');

const app = createApp(App)

app.use(router)
app.use(PrimeVue);
app.use(ConfirmationService);
app.use(ToastService);
app.directive('tooltip', Tooltip);

app.mount('#app')
