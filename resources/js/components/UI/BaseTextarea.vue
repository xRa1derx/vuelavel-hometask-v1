<template>
  <div class="textarea-wrap">
    <transition-group @enter="enter" @leave="leave" key="quote">
      <p
        key="quote"
        v-show="respondMessage"
        class="quote"
        :style="{ height: tweenedHeight + 'px' }"
      >
        {{ subString }}
      </p>
    </transition-group>
    <button
      v-if="respondMessage"
      @click.stop="$emit('closeReply')"
      class="closeReply"
    >
      &times
    </button>
    <textarea
      v-model="message"
      name="message"
      id="message"
      placeholder="type please..."
    ></textarea>
    <button @click="addMessage" type="submit" class="send-message">Send</button>
  </div>
</template>

<script>
import axios from "axios";
import gsap from "gsap/all";
export default {
  props: [
    "selectedToSend",
    "updateData",
    "from",
    "respondMsg",
    "respondMessage",
  ],
  emit: ["closeReply"],
  data() {
    return {
      message: "",
      tweenedHeight: 0,
    };
  },
  methods: {
    enter(el, done) {
      gsap.to(this.$data, {
        tweenedHeight: 20,
        duration: 0.5,
        ease: "ease.in",
        onComplete: done,
      });
    },
    leave(el, done) {
      gsap.to(this.$data, {
        tweenedHeight: 0,
        duration: 0.1,
        ease: "Circ.easeIn",
        onComplete: done,
      });
    },
    addMessage() {
      axios
        .post("/api/chat", {
          from: this.$store.state.currentUserId,
          to: +this.selectedToSend,
          message: this.message,
          replyMessage: this.respondMsg,
        })
        .then(() => {
          this.updateData();
          this.message = "";
          if (this.respondMsg != null) {
            this.$emit("closeReply");
          }
        });
    },
  },
  computed: {
    subString() {
      if (this.respondMsg && this.respondMsg.length > 25) {
        return this.respondMsg.slice(0, 25) + "...";
      }
      return this.respondMsg;
    },
  },
};
</script>

<style scoped>
.textarea-wrap {
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.26);
  border-radius: 12px;
  border-bottom-right-radius: 0;
  background-color: #eee;
}

.quote {
  position: relative;
  border: none;
  font: inherit;
  padding-left: 10px;
  padding-right: 10px;
  font-style: italic;
  margin: 15px;
  border-left: solid 3px #8d006e;
}

textarea {
  width: 100%;
  border: none;
  font: inherit;
  border-radius: 12px;
  border-bottom-right-radius: 0;
  padding: 5px 80px 0 15px;
  resize: none;
  background-color: #f8fafc;
}

textarea:focus {
  background-color: #fff;
  outline: none;
}

.send-message {
  position: absolute;
  bottom: 0;
  right: 0;
  border: none;
  border-top-left-radius: 30px;
  border-bottom-left-radius: 30px;
  background-color: #8d006e;
  color: #fff;
  width: 5em;
  height: 2.5em;
}

.invalid textarea {
  border: 1px solid red;
}

.closeReply {
  position: absolute;
  top: 0;
  right: 0;
  background-color: rgba(0, 0, 0, 0);
  border: none;
  font-size: 26px;
  color: rgb(121, 121, 121);
}

.test {
  visibility: hidden;
}

.reply-slide-enter-from {
  opacity: 0;
}

.reply-slide-enter-active {
  transition: all 1s ease-in;
}
.reply-slide-leave-active {
  transition: all 0.5s ease-out;
}

.reply-slide-leave-to {
  opacity: 0;
}
</style>
