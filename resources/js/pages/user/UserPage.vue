<template>
  <div class="wrapper">
    <div class="chat-wrapper">
      <section class="chat">
        <div v-if="isLoading">
          <base-spinner></base-spinner>
        </div>
        <div class="messages" v-for="msg in chat" :key="msg.id">
          <p
            :key="msg.id"
            :class="msg.from == $store.state.currentUserId ? 'send' : 'receive'"
            @click="isContextMenu($event, msg.id)"
          >
            <span v-if="msg.replyMessage"
              >&crarr;
              <p class="replied">{{ msg.replyMessage }}</p></span
            >
            {{ msg.message }}
          </p>
          <base-message-edit
            v-if="contextMenu == true"
            @close="contextMenu = false"
            @replyMsg="replyMessage"
            @editMsg="editMessage"
            @deleteMsg="removeMessage"
            :msgId="messageId"
            :clientX="clientX"
            :clientY="clientY"
            :editBtn="editButton"
            :deleteBtn="deleteButtonVisible"
          >
          </base-message-edit>
        </div>
      </section>
    </div>
    <section class="edit-section">
      <div class="edit-wrap">
        <transition name="fade-textarea" mode="out-in">
          <base-textarea
            v-if="textareaEdit == false"
            key="textarea"
            :respondMsg="respondMsg"
            :respondMessage="respondMessage"
            :selectedToSend="1"
            :updateData="getData"
            :from="$store.state.currentUserId"
            @closeReply="closeReply"
            @updateFromAddMessage="updateFromAddMessage"
          ></base-textarea>
          <base-textarea-edit
            v-else
            @cancel="getData"
            @updateFromEditMessage="updateFromEditMessage"
            :selectedToSend="1"
            :msgToEdit="messageEditId"
            :updateData="getData"
            :msg="message"
            key="textarea-edit"
          >
          </base-textarea-edit>
        </transition>
      </div>
    </section>
  </div>
</template>

<script>
import BaseMessageEdit from "../../components/UI/BaseMessageEdit.vue";
import BaseTextareaEdit from "../../components/UI/BaseTextareaEdit.vue";
export default {
  components: { BaseMessageEdit, BaseTextareaEdit },
  data() {
    return {
      contextMenu: false,
      isLoading: false,
      chat: [],
      textarea: true,
      textareaEdit: false,
      message: "",
      messageId: null,
      deleteButtonVisible: false,
      messageEditId: null,
      respondMsg: null,
      respondMessage: false,

      isTyping: false,
      typingTimer: false,
    };
  },
  mounted() {
    Echo.private(`chat.1`).listenForWhisper("typing", (e) => {
      if (this.to == e.id) {
        this.isTyping = true;
        if (this.typingTimer) clearTimeout(this.typingTimer);
        this.typingTimer = setTimeout(() => {
          this.isTyping = false;
        }, 2000);
      }
    });
  },
  created() {
    this.getRole();
    this.getData();
  },
  methods: {
    connect() {
      Echo.private(`chat.1`).listen("Message", (e) => {
        console.log(e);
        axios.get(`/api/chat`).then((res) => {
          const currentUserId = this.$store.state.currentUserId;
          const msgUser = res.data.messages.filter(
            (msg) => msg.from == currentUserId && msg.to == 1
          );
          const msgAdmin = res.data.messages.filter(
            (msg) => msg.from == 1 && msg.to == currentUserId
          );
          const chat = msgAdmin.concat(msgUser);
          this.chat = chat.sort((a, b) => a.id - b.id);
          this.isTyping = false;
        });
      });
    },
    getRole() {
      this.$store.dispatch("getRole");
      this.$store.state.isAuth = true;
    },
    getData(exit) {
      if (exit == 1) {
        this.exitFromEditMessage();
        return;
      }
      this.closeReply();
      this.isLoading = true;
      axios
        .get(`/api/chat`)
        .then((res) => {
          const currentUserId = this.$store.state.currentUserId;
          const msgUser = res.data.messages.filter(
            (msg) => msg.from == currentUserId && msg.to == 1
          );
          const msgAdmin = res.data.messages.filter(
            (msg) => msg.from == 1 && msg.to == currentUserId
          );
          const chat = msgAdmin.concat(msgUser);
          this.chat = chat.sort((a, b) => a.id - b.id);
          this.textareaEdit = false;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    updateFromAddMessage(res) {
      this.chat.push(res.data);
      const el = document.querySelector(".chat");
      setTimeout(() => {
        el.scrollTop = el.scrollHeight;
      }, 0);
    },
    updateFromEditMessage(res) {
      const foundIndex = this.chat.findIndex(
        (message) => message.id == res.data.id
      );
      this.chat[foundIndex] = res.data;
      this.exitFromEditMessage();
    },
    removeMessage(id) {
      axios.delete(`/api/chat/${id}`).then(() => {
        this.chat = this.chat.filter((message) => id != message.id);
        this.contextMenu = false;
      });
    },
    editMessage(id) {
      this.messageEditId = id;
      this.message = this.chat.find((msg) => msg.id == id).message;
      this.textareaEdit = true;
      this.textarea = false;
      this.closeReply();
    },
    replyMessage(id) {
      this.exitFromEditMessage();
      this.respondMsg = this.chat.find((msg) => msg.id == id).message;
      // const textarea = document.getElementById("message");
      // textarea.focus();
      setTimeout(() => {
        this.respondMessage = true;
      }, 350);
    },
    isContextMenu(event, id) {
      this.contextMenu = !this.contextMenu;
      this.messageId = id;
      this.hideEditButton(event);
      this.hideDeleteButton(event);
      this.setPositionToContextMenu(event);
    },
    hideEditButton(event) {
      if (event.target.className == "send") {
        this.editButton = true;
      } else {
        this.editButton = false;
      }
    },
    hideDeleteButton(event) {
      if (event.target.className == "send") {
        this.deleteButtonVisible = true;
      } else {
        this.deleteButtonVisible = false;
      }
    },
    exitFromEditMessage() {
      this.textarea = true;
      this.textareaEdit = false;
    },
    setPositionToContextMenu(event) {
      let halfScreenX = document.documentElement.clientWidth / 2;
      let halfScreenY = document.documentElement.clientHeight / 2;

      if (halfScreenX < event.clientX) {
        this.clientX = event.clientX + window.scrollX - 100;
      } else {
        this.clientX = event.clientX + window.scrollX + 20;
      }

      if (halfScreenY < event.clientY) {
        this.clientY = event.clientY + window.scrollY - 50;
      } else {
        this.clientY = event.clientY + window.scrollY;
      }
    },
    closeReply() {
      this.respondMsg = null;
      this.respondMessage = false;
    },
  },
  watch: {
    chat: function () {
      this.connect();
      this.$nextTick(function () {
        const el = document.querySelector(".chat");
        el.scrollTop = el.scrollHeight;
      });
    },
  },
};
</script>

<style scoped>
h1 {
  text-align: center;
}

.wrapper {
  display: flex;
  width: 100%;
  height: 100%;
  padding: 10rem 0 5rem 0;
  flex-direction: column;
  justify-content: center;
}

.chat-wrapper {
  display: flex;
  width: 100%;
  height: 60%;
  padding-bottom: 15px;
  flex: 1 0 auto;
}

.edit-section {
  display: flex;
  width: 100%;
  justify-content: center;
}

.edit-wrap {
  min-width: 50%;
}

.router-link-active,
.outline,
a {
  margin: 0 !important;
}

.card {
  height: 100%;
}

ul {
  list-style: none;
  margin: 0.5rem;
  padding: 0;
}

li {
  text-align: center;
}

.chat {
  width: 100%;
  height: 100%;
  overflow: auto;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.26);
  padding: 1rem;
}

.messages {
  position: relative;
  display: flex;
  flex-direction: column;
}

.messages > p {
  max-width: 90%;
  overflow-wrap: break-word;
  margin-bottom: 12px;
  line-height: 24px;
  position: relative;
  padding: 10px 20px;
  border-radius: 25px;
}
.send {
  align-self: flex-end;
  color: black;
  background-color: #c659ae3c;
  cursor: pointer;
}
.receive {
  align-self: flex-start;
  background-color: #eee;
  position: relative;
  cursor: pointer;
}

.replied {
  font-style: italic;
  padding: 5px 10px 5px 10px;
  border: 1px solid rgba(0, 0, 0, 0.185);
  border-radius: 15px;
}

a:active,
a:hover,
a.router-link-active {
  background-color: #c659ae;
}

.arrow {
  font-size: 50px;
  position: absolute;
  bottom: -5px;
  left: 15px;
  color: #3a0061;
  cursor: pointer;
}

.arrow:hover {
  color: #c659ae;
}

@media (max-width: 550px) {
  .wrapper {
    padding: 6rem 10px 10px 10px;
  }
  .edit-wrap {
    width: 100%;
  }
}

/* animations */

.fade-textarea-enter-from {
  opacity: 0;
}

.fade-textarea-enter-active {
  transition: all 0.1s ease-in;
}
.fade-textarea-leave-active {
  transition: all 0.1s ease-out;
}

.fade-textarea-leave-to {
  opacity: 0;
}
</style>
