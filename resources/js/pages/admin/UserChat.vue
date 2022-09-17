<template>
  <div class="wrapper">
    <base-burger :getData="getData"></base-burger>
    <div v-if="isTyping" class="isTyping">
      <p>{{ name }} is typing...</p>
    </div>
    <div class="aside-and-chat-wrapper">
      <aside>
        <base-card>
          <section class="aside-user-list">
            <ul @click="getData" v-for="user in users" :key="user.id">
              <li v-if="user.role">
                <base-button
                  link
                  class="aside-user-list-button"
                  :to="`/admin/users/chat/${user.id}`"
                  >{{ user.name }}
                </base-button>
              </li>
            </ul>
          </section>
          <div class="arrow">
            <svg
              class="arrow-left-4"
              @click="$router.push({ name: 'admin.users' })"
              viewBox="0 0 100 85"
            >
              <polygon
                points="58.263,0.056 100,41.85 58.263,83.641 30.662,83.641 62.438,51.866 0,51.866 0,31.611 62.213,31.611 30.605,0 58.263,0.056"
              />
            </svg>
          </div>
        </base-card>
      </aside>
      <div class="chat-wrapper">
        <section class="chat">
          <div v-if="isLoading">
            <base-spinner></base-spinner>
          </div>
          <div class="loadedChat">
            <div class="messages" v-for="msg in chat" :key="msg.id">
              <p
                :key="msg.id"
                :class="{
                  receive: msg.from == to,
                  send: msg.from != to,
                }"
                @click="isContextMenu($event, msg.id)"
              >
                <span v-if="msg.replyMessage"
                  >&crarr;
                  <p class="replied">{{ msg.replyMessage }}</p></span
                >
                {{ msg.message }}
              </p>
              <base-message-edit
                v-if="contextMenu == true && messageId == msg.id"
                @close="contextMenu = false"
                @replyMsg="replyMessage"
                @editMsg="editMessage"
                @deleteMsg="removeMessage"
                :msgId="msg.id"
                :clientX="clientX"
                :clientY="clientY"
                :editBtn="editButton"
                :deleteBtn="deleteButton"
              >
              </base-message-edit>
            </div>
          </div>
        </section>
      </div>
    </div>
    <section class="edit-section">
      <div class="edit-profile">
        <base-button
          @click="toggleTextarea"
          link
          :to="`/admin/users/chat/${to}/edit`"
          >Edit {{ name }}'s profile</base-button
        >
      </div>

      <div class="textarea-or-edit-wrapper">
        <transition name="fade-textarea" mode="out-in">
          <base-textarea
            v-if="textarea == true"
            key="textarea"
            :respondMsg="respondMsg"
            :respondMessage="respondMessage"
            :updateData="getData"
            :selectedToSend="to"
            :from="$store.state.currentUserId"
            @closeReply="closeReply"
            @updateFromAddMessage="updateFromAddMessage"
          ></base-textarea>
          <router-view
            v-else-if="textareaUserEdit == true"
            key="textarea-user-edit"
            :currentName="name"
            :selectedToSend="to"
            @cancel="getData"
            :users="users"
          >
          </router-view>
          <base-textarea-edit
            v-else
            key="textarea-edit"
            @cancel="getData"
            @updateFromEditMessage="updateFromEditMessage"
            :selectedToSend="to"
            :updateData="getData"
            :msg="message"
            :msgToEdit="messageEditId"
          >
          </base-textarea-edit>
        </transition>
      </div>
    </section>
  </div>
</template>

<script>
import axios from "axios";
import BaseMessageEdit from "../../components/UI/BaseMessageEdit.vue";
import BaseTextareaEdit from "../../components/UI/BaseTextareaEdit.vue";
import BaseBurger from "../../components/UI/BaseBurger.vue";

export default {
  components: { BaseMessageEdit, BaseTextareaEdit, BaseBurger },
  data() {
    return {
      chat: [],
      users: [],
      to: null,
      name: "",
      message: "",
      respondMsg: null,

      respondMessage: false,
      deleteButton: true,
      editButton: false,
      textarea: true,
      textareaEdit: false,
      textareaUserEdit: false,

      contextMenu: false,
      messageId: null,
      messageEditId: null,

      clientX: 0,
      clientY: 0,
      isLoading: false,

      isTyping: false,
      typingTimer: false,
    };
  },
  mounted() {
    Echo.private(`chat.${this.to}`).listenForWhisper("typing", (e) => {
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
      Echo.private(`chat.${this.to}`).listen("Message", (e) => {
        console.log(e);
        axios.get(`/api/chat`).then((res) => {
          const msgUser = res.data.messages.filter(
            (msg) => msg.from == this.to && msg.to == 1
          );
          const msgAdmin = res.data.messages.filter(
            (msg) => msg.from == 1 && msg.to == this.to
          );
          const chat = msgAdmin.concat(msgUser);
          this.chat = chat.sort((a, b) => a.id - b.id);
          this.isTyping = false;
        });
      });
    },
    disconnect() {
      Echo.private(`chat.${this.to}`).stopListening("Message");
    },
    getRole() {
      this.$store.dispatch("getRole");
    },

    getData(exit) {
      if (exit == 0) {
        this.exitFromEditUser();
        return;
      }
      if (exit == 1) {
        this.exitFromEditMessage();
        return;
      }
      this.closeReply();
      this.to = this.$route.params.id;
      this.isLoading = true;
      axios
        .get(`/api/chat`)
        .then((res) => {
          this.users = res.data.users;
          const msgUser = res.data.messages.filter(
            (msg) => msg.from == this.to && msg.to == 1
          );
          const msgAdmin = res.data.messages.filter(
            (msg) => msg.from == 1 && msg.to == this.to
          );
          const chat = msgAdmin.concat(msgUser);
          this.chat = chat.sort((a, b) => a.id - b.id);

          this.name = res.data.users.find((user) => user.id == this.to).name;

          this.textarea = true;
          this.textareaEdit = false;
          this.textareaUserEdit = false;
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
      const el = document.querySelector(".chat");
      setTimeout(() => {
        el.scrollTop = el.scrollHeight;
      }, 0);
      this.exitFromEditMessage();
    },
    removeMessage(id) {
      axios.delete(`/api/chat/${id}`).then(() => {
        this.chat = this.chat.filter((message) => id != message.id);
        this.contextMenu = false;
      });
    },
    editMessage(id) {
      this.message = this.chat.find((msg) => msg.id == id).message;
      this.messageEditId = id;
      this.textareaEdit = true;
      this.textarea = false;
      this.textareaUserEdit = false;
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
    toggleTextarea() {
      this.textareaUserEdit = true;
      this.textareaEdit = false;
      this.textarea = false;
    },
    exitFromEditUser() {
      this.$router.back();
      this.textarea = true;
      this.textareaEdit = false;
      this.textareaUserEdit = false;
    },
    exitFromEditMessage() {
      this.textarea = true;
      this.textareaEdit = false;
      this.textareaUserEdit = false;
    },
    isContextMenu(event, id) {
      this.contextMenu = !this.contextMenu;
      this.messageId = id;
      this.hideEditButton(event);
      this.setPositionToContextMenu(event);
    },
    hideEditButton(event) {
      if (event.target.className == "send") {
        this.editButton = true;
      } else {
        this.editButton = false;
      }
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
    chat: function (val, oldVal) {
      this.connect();
      if (Object.keys(oldVal).length != 0) {
        this.disconnect();
        this.connect();
      }
      this.$nextTick(function () {
        const el = document.querySelector(".chat");
        el.scrollTop = el.scrollHeight;
      });
    },
    to(value, oldVal) {
      if (oldVal != null) {
        Echo.leave(`chat.${this.to}`);
        this.connect();
      }
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

.aside-and-chat-wrapper {
  display: flex;
  height: 60%;
  padding-bottom: 15px;
  flex: 1 0 auto;
}

.edit-section {
  display: flex;
  width: 100%;
  flex: 0 0 auto;
}

aside {
  position: relative;
  display: flex;
  flex: 1 1 0%;
  padding-right: 15px;
  flex-direction: column;
  justify-content: space-between;
}

.chat-wrapper {
  position: relative;
  flex: 3 3 0%;
  width: 100%;
}

.edit-profile {
  flex: 1 1 0%;
  padding-right: 15px;
  margin: auto;
}
.textarea-or-edit-wrapper {
  flex: 3 3 0%;
  height: 100%;
}

.card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
}

.chat {
  height: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
  overflow: auto;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.26);
  padding: 1rem;
}

.loadedChat {
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
  padding-right: 15px;
}

.aside-user-list {
  display: flex;
  flex-direction: column;
  overflow: auto;
  height: 98%;
}

.edit-profile a {
  width: 100%;
  text-align: center;
  border-radius: 10px !important;
  padding: 1px;
  font-size: calc(10px + 5 * (100vw / 1360));
}

.router-link-active,
.outline,
a {
  margin: 0 !important;
}

ul {
  list-style: none;
  margin: 0.5rem;
  padding: 0;
}

li {
  text-align: center;
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

.aside-user-list-button {
  border-radius: 7px !important;
  width: 100%;
  padding: 1px 0 !important;
}

a:active,
a:hover,
a.router-link-active {
  background-color: #c659ae;
}

.arrow {
  position: relative;
  width: 100%;
  min-height: 2rem;
}

.arrow-left-4,
.arrow-right-4,
.arrow-top-4,
.arrow-bottom-4 {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 2rem;
  height: 2rem;
  cursor: pointer;
}
.arrow-left-4 polygon,
.arrow-right-4 polygon,
.arrow-top-4 polygon,
.arrow-bottom-4 polygon {
  fill: #3a0061;
  transition: fill 0.5s ease-out;
}
.arrow-left-4 {
  transform: rotate(180deg);
}
.arrow-top-4 {
  transform: rotate(270deg);
}
.arrow-bottom-4 {
  transform: rotate(90deg);
}
.arrow-left-4:hover polygon,
.arrow-right-4:hover polygon,
.arrow-top-4:hover polygon,
.arrow-bottom-4:hover polygon {
  fill: #c659ae;
}

.replied {
  font-style: italic;
  padding: 5px 10px 5px 10px;
  border: 1px solid rgba(0, 0, 0, 0.185);
  border-radius: 15px;
}

.isTyping {
  position: absolute;
  top: 6.5rem;
  left: 1rem;
  font-style: italic;
  color: rgba(0, 0, 0, 0.394);
}

@media (max-width: 550px) {
  aside,
  .edit-profile {
    display: none;
  }

  .wrapper {
    padding: 0;
    padding-top: 9rem;
    padding-bottom: 10px;
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
