<template>
  <div class="textarea-wrap">
    <transition @enter="enter" @leave="leave" key="quote">
      <p
        key="quote"
        v-show="respondMessage"
        class="quote"
        :style="{ height: tweenedHeight + 'px' }"
      >
        {{ subString }}
      </p>
    </transition>
    <button
      v-if="respondMessage"
      @click.stop="$emit('closeReply')"
      class="closeReply"
    >
      &times
    </button>
    <textarea
      @keyup.ctrl.enter="addMessage"
      @keydown.tab.exact.prevent="tabLeft($event)"
      @keydown="typeMessage"
      :value="message"
      @input="(e) => (message = e.target.value)"
      name="message"
      id="message"
      placeholder="type please..."
      @click="touchTextarea()"
    ></textarea>
    <button
      @click="addMessage"
      type="submit"
      class="send-message"
      :class="{ inputDisable: isInputDisabled === true }"
      :disabled="isInputDisabled"
    >
      Send
    </button>
  </div>
</template>

<script>
import axios from "axios";
import gsap from "gsap/all";
import { v4 as uuidv4 } from "uuid";
export default {
  props: ["selectedToSend", "from", "respondMsg", "respondMessage"],
  emit: ["closeReply, updateFromAddMessage", "updateLocalChat"],
  data() {
    return {
      message: "",
      tweenedHeight: 0,
      isInputDisabled: true,
    };
  },
  created() {
    document.addEventListener("touchend", (e) => {
      if (e.target.classList.contains("send-message")) {
        e.preventDefault();
        this.addMessageAxios();
      }
    });
  },
  beforeUnmount() {
    document.removeEventListener("touchend");
  },
  methods: {
    touchTextarea() {
      setTimeout(() => {
        const el = document.querySelector(".chat");
        el.scrollTop = el.scrollHeight;
      }, 500);
    },
    enter(el, done) {
      gsap.to(this.$data, {
        tweenedHeight: 20,
        duration: 0.3,
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
    tabLeft(event) {
      let text = this.message,
        originalSelectionStart = event.target.selectionStart,
        textStart = text.slice(0, originalSelectionStart),
        textEnd = text.slice(originalSelectionStart);

      this.message = `${textStart}\t${textEnd}`;
      event.target.value = this.message;
      event.target.selectionEnd = event.target.selectionStart =
        originalSelectionStart + 1;
    },
    addMessage(event) {
      if (event.ctrlKey || event.type == "click") {
        this.addMessageAxios();
      } else {
        let caret = event.target.selectionStart;
        event.target.setRangeText("\n", caret, caret, "end");
        this.message = event.target.value;
      }
    },
    addMessageAxios() {
      if (this.message.trim() !== "") {
        const data = {
          uuid: uuidv4(),
          message: this.message,
          replyMessage: this.respondMsg || null,
          from: +this.$store.state.currentUserId,
          to: +this.selectedToSend,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        };
        axios.post("/api/chat", data);
        this.$emit("updateFromAddMessage", data);
        this.message = "";
        if (this.respondMsg != null) {
          this.$emit("closeReply");
        }
      }
    },
    typeMessage() {
      Echo.private(
        `chat.${
          this.$store.state.isAdmin
            ? this.selectedToSend
            : this.$store.state.currentUserId
        }`
      ).whisper("typing", {
        idFrom: this.$store.state.currentUserId,
        idTo: +this.selectedToSend,
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
  watch: {
    message(val) {
      val.trim() == ""
        ? (this.isInputDisabled = true)
        : (this.isInputDisabled = false);
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

.inputDisable {
  background-color: rgb(213, 213, 213);
  color: black;
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
