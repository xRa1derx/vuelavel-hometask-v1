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

::-webkit-scrollbar {
  width: 10px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #8d006e;
}

::-webkit-scrollbar-thumb:hover {
  background: #8d006e82;
}

* {
  box-sizing: border-box;
}

p {
  font-size: calc(14px + 3 * (100vw / 1360));
}

html {
  font-family: "Roboto", sans-serif;
}

body {
  margin: 0;
  padding: 0;
}

#app{
  position: absolute;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.container {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

footer {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 5rem;
  background-color: #3d008d;
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
