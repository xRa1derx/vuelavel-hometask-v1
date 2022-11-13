import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";

import BaseButton from "./components/UI/BaseButton";
import BaseCard from "./components/UI/BaseCard";
import BaseTextarea from "./components/UI/BaseTextarea";
import BaseArrow from "./components/UI/BaseArrow";
import BaseSpinner from "./components/UI/BaseSpinner";

import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
  components,
  directives,
})

require("./bootstrap");

const app = createApp(App);

app.use(vuetify);

app.use(store);

app.use(router);
app.component("base-card", BaseCard);
app.component("base-button", BaseButton);
app.component("base-textarea", BaseTextarea);
app.component("base-arrow", BaseArrow);
app.component("base-spinner", BaseSpinner);

app.mount("#app");
