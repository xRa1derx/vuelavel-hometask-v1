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
        <ul  v-for="user in $store.state.users" :key="user.id">
          <li @click="getChat" v-if="user.role">
            <div class="new-message">
                <img
                  v-show="isNewMessage(user)"
                  src="../../assets/images/comment-exclamation.png"
                  alt=""
                />
              </div>
              <li @click="getChat">
                <base-button
                @click="toggleBurger"
                  link
                  class="aside-user-list-button"
                  :to="`/admin/users/chat/${user.id}`"
                  >{{ user.name }}
                </base-button>
              </li>
              <div class="onlineAlert-wrapper">
                <span v-show="onlineStatus(user.id)" class="onlineAlert"></span>
              </div>
          </li>
        </ul>
      </section>
    </div>
  </transition>
</template>



<script>
import gsap from "gsap";

export default {
  props: ["getChat"],
  data() {
    return {
      show: true,
    };
  },
  methods: {
    onlineStatus(id) {
      return this.$store.state.onlineUsers.find((user) => user.id === id);
    },
    isNewMessage(user) {
      return this.$store.state.newMessage.has(user.id);
    },
    toggleBurger() {
      this.show = !this.show;
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
}
.burger {
  display: flex;
  align-items: center;
  position: absolute;
  left: 0;
  top: 0;
  width: 250px;
  height: 100%;
  background-color: #ebdce5ce;
  z-index: 2;
}

.user-list {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 70%;
  justify-content: center;
  align-items: center;
  overflow: auto;
  margin: auto;
}

ul {
  list-style: none;
  margin-bottom: 0.5rem;
  padding: 0;
}

li {
  display: flex;
}

li > a{
  text-align: center;
  width: 100px;
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

.new-message {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  min-height: 20px;
}

.new-message > img {
  width: 20px;
  height: 20px;
  object-fit: cover;
}

.onlineAlert-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  min-height: 20px;
}

.onlineAlert {
  content: "";
  width: 12px;
  height: 12px;
  border-radius: 50px;
  background-color: #59c671;
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