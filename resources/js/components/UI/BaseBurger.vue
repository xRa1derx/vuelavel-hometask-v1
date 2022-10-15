<template>
  <transition-group
    appear
    tag="div"
    class="open-burger"
    @click="toggleBurger"
    @before-enter="beforeEnter"
    @before-leave="beforeLeave"
    @enter="enter"
    @leave="leave"
  >
    <span v-if="show" v-for="(item, index) of 3" key="bar" :data-index="index">
      <span class="bar1"></span>
    </span>
  </transition-group>

  <transition name="fade-backdrop">
    <div class="backdrop" v-if="!show" @click="toggleBurger"></div>
  </transition>

  <transition name="slide">
    <div v-if="!show" class="burger">
      <section class="user-list">
        <ul @click="getData" v-for="user in $store.state.users" :key="user.id">
          <li v-if="user.role">
            <base-button
              @click="toggleBurger"
              link
              :to="`/admin/users/chat/${user.id}`"
              >{{ user.name }}
            </base-button>
          </li>
        </ul>
      </section>
    </div>
  </transition>
</template>



<script>
import gsap from "gsap";

export default {
  props: ["getData"],
  data() {
    return {
      show: true,
    };
  },
  methods: {
    toggleBurger() {
      this.show = !this.show;
    },
    getDataAndClose() {
      this.toggleBurger();
      this.getData();
    },
    beforeEnter(el) {
      el.style.opacity = 0;
    },
    enter(el, done) {
      gsap.to(el, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        onComplete: done,
        delay: el.dataset.index * 0.2,
      });
    },
    beforeLeave(el) {
      el.style.opacity = 1;
    },
    leave(el, done) {
      gsap.to(el, {
        opacity: 1,
        y: 0,
        duration: 0.3,
        onComplete: done,
        delay: el.dataset.index * 0.1,
      });
    },
  },
};
</script>

<style scoped>
.open-burger {
  margin-left: auto;
  /* position: absolute;
  top: 6rem;
  right: 5px; */
}
.burger {
  display: flex;
  align-items: center;
  position: absolute;
  left: 0;
  top: 0;
  width: 250px;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.813);
  z-index: 2;
}

.user-list {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 70%;
  justify-content: flex-start;
  align-items: center;
  overflow: auto;
  margin: auto;
}

ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

li {
  margin: 0.5rem;
}

.backdrop {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
  background-color: rgba(0, 0, 0, 0.049);
}

.bar1,
.bar2,
.bar3 {
  width: 40px;
  height: 2px;
  background-color: black;
  display: block;
  margin: 7px;
  background-color: #8d006e;
}

::-webkit-scrollbar {
  width: 0;
}

/* animations */

.slide-enter-from {
  transform: translateX(-250px);
  animation-delay: 1s;
}

.slide-enter-active {
  transition: all 0.3s ease-in;
}
.slide-leave-active {
  transition: all 0.5s ease-out;
}

.slide-leave-to {
  transform: translateX(-250px);
}

.fade-backdrop-enter-from {
  opacity: 0;
}

.fade-backdrop-enter-active {
  transition: opacity 0.3s ease-in;
}
.fade-backdrop-leave-active {
  transition: opacity 0.5s ease-out;
}

.fade-backdrop-leave-to {
  opacity: 0;
}

@media (min-width: 551px) {
  .open-burger {
    display: none;
  }
}
</style>