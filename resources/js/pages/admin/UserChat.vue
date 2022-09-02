<template>
  <div class="wrapper">
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
          <svg
            class="arrow-left-4"
            @click="$router.push({ name: 'admin.users' })"
            viewBox="0 0 100 85"
          >
            <polygon
              points="58.263,0.056 100,41.85 58.263,83.641 30.662,83.641 62.438,51.866 0,51.866 0,31.611 62.213,31.611 30.605,0 58.263,0.056"
            />
          </svg>
        </base-card>
      </aside>
      <div class="chat-wrapper">
        <section class="chat">
          <div v-if="isLoading">
            <base-spinner></base-spinner>
          </div>
          <div v-else class="messages" v-for="msg in chat" :key="msg.id">
            <p
              :key="msg.id"
              :class="{
                receive: msg.from == to,
                send: msg.from != to,
                reply: respondMessage == true && messageId == msg.id,
              }"
              @click="isContextMenu($event, msg.id)"
            >
            <span v-if="msg.replyMessage">&crarr;<p class="replied">{{ msg.replyMessage }}</p></span>
            
              {{ msg.message }}
              <button
                v-if="messageId == msg.id && respondMessage == true"
                @click.stop="closeReply"
                class="closeReply"
              >
                &times
              </button>
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
        <base-textarea
          :respondMsg="respondMsg"
          :updateData="getData"
          :selectedToSend="to"
          :from="$store.state.currentUserId"
          @afterReply="closeReply"
          v-if="textarea == true"
        ></base-textarea>
        <router-view
          :currentName="name"
          :selectedToSend="to"
          @cancel="getData"
          :users="users"
          v-else-if="textareaUserEdit == true"
        >
        </router-view>
        <base-textarea-edit
          @cancel="getData"
          :selectedToSend="to"
          :updateData="getData"
          :msg="message"
          :msgToEdit="messageEditId"
          v-else
        >
        </base-textarea-edit>
      </div>
    </section>
  </div>
</template>

<script>
import axios from "axios";

import BaseMessageEdit from "../../components/UI/BaseMessageEdit.vue";
import BaseTextareaEdit from "../../components/UI/BaseTextareaEdit.vue";

export default {
  components: { BaseMessageEdit, BaseTextareaEdit },
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
    };
  },
  created() {
    this.getRole();
    this.getData();
  },
  methods: {
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
    removeMessage(id) {
      axios.delete(`/api/chat/${id}`).then(() => {
        this.getData();
        this.contextMenu = false;
      });
    },
    editMessage(id) {
      this.message = this.chat.find((msg) => msg.id == id).message;
      this.messageEditId = id;
      this.textareaEdit = true;
      this.textarea = false;
      this.textareaUserEdit = false;
    },
    replyMessage(id) {
      this.respondMsg = this.chat.find((msg) => msg.id == id).message;
      const textarea = document.getElementById("message");
      textarea.focus();
      this.respondMessage = true;
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
      this.respondMessage = false;
      this.respondMsg = null;
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
        this.clientX = event.clientX + window.scrollX - 70;
      } else {
        this.clientX = event.clientX + window.scrollX + 20;
      }

      if (halfScreenY < event.clientY) {
        this.clientY = event.clientY + window.scrollY - 110;
      } else {
        this.clientY = event.clientY + window.scrollY;
      }
    },
    closeReply() {
      this.respondMsg = null;
      this.respondMessage = false;
    },
  },
};
</script>

<style scope>
h1 {
  text-align: center;
}

.wrapper {
  width: 100%;
  margin-top: 70px;
}

.aside-and-chat-wrapper {
  width: 100%;
  height: 65vh;
  display: flex;
}

.textarea-or-edit-wrapper {
  flex: 3 3 0%;
  margin-top: 15px;
  margin-left: 15px;
}

aside {
  position: relative;
  display: flex;
  flex: 1 1 0%;
  padding: 0 15px 0 0;
  flex-direction: column;
  justify-content: space-between;
}

.edit-section {
  display: flex;
  width: 100%;
}

.edit-profile {
  flex: 1 1 0%;
  margin: auto;
  padding: 60px 0;
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

.aside-user-list {
  display: flex;
  flex-direction: column;
  overflow: auto;
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

.chat-wrapper {
  position: relative;
  flex: 3 3 0%;
}

.chat {
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
  /* word-wrap: break-word; */
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

.arrow-left-4,
.arrow-right-4,
.arrow-top-4,
.arrow-bottom-4 {
  position: absolute;
  bottom: -5px;
  left: 15px;
  margin: 20px 8px;
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

/* animations */

.fade-enter-from {
  opacity: 0;
}

.fade-enter-active {
  transition: opacity 0.5s ease;
}

.fade-leave-to {
  opacity: 0;
}

.reply {
  color: #000;
  font-style: italic;
  border: 1px solid rgba(0, 0, 0, 0.185);
  cursor: auto !important;
}

.replied {
  font-style: italic;
  padding: 5px 10px 5px 10px;
  border: 1px solid rgba(0, 0, 0, 0.185);
  border-radius: 15px;
}

.closeReply {
  position: absolute;
  background-color: rgba(0, 0, 0, 0);
  top: -1.3rem;
  right: -1rem;
  border: none;
  font-size: 26px;
  color: rgb(121, 121, 121);
}
</style>
