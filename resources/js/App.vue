<template>
  <the-header></the-header>
  <div class="container">
    <router-view v-slot="slotProps">
      <transition name="route" mode="out-in">
        <component :is="slotProps.Component"></component>
      </transition>
    </router-view>
  </div>
</template>

<script>
import TheHeader from "./components/layout/TheHeader.vue";

export default {
  components: { TheHeader },
};
</script>

<style>
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap");

* {
  box-sizing: border-box;
}

p {
  font-size: calc(14px + 3 * (100vw / 1360));
}

.html {
  font-family: "Roboto", sans-serif;
}

body {
  margin: 0;
}

.container {
  display: flex;
  height: 100vh;
  align-items: center;
}

.route-enter-from {
  opacity: 0;
  transform: translateY(-30px);
}

.route-enter-active {
  transition: all 0.6s ease-out;
}

.route-leave-active {
  transition: all 0.3s ease-in;
}

.route-leave-to {
  opacity: 0;
  transform: translateY(30px);
}

</style>