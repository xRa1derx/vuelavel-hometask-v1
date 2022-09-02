<template>
  <div>
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
export default {
  props: ["selectedToSend", "updateData", "from", "respondMsg"],
  emit: ["afterReply"],
  data() {
    return {
      message: "",
    };
  },
  methods: {
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
            this.$emit("afterReply");
          }
        });
    },
  },
};
</script>

<style scoped>
div {
  display: flex;
  position: relative;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.26);
  border-radius: 12px;
  border-bottom-right-radius: 0;
}

textarea {
  width: 100%;
  height: 100px;
  border: none;
  font: inherit;
  border-radius: 12px;
  border-bottom-right-radius: 0;
  padding: 15px 15px 15px 20px;
  resize: none;
  background-color: #f8fafc;
}

textarea:focus {
  background-color: #f0e6fd;
  outline: none;
  border-color: #3d008d;
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

/* animations */

.fade-enter-active,
.fade-leave-active {
  transition: opacity 3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
