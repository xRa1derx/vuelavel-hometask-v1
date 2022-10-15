<template>
  <div class="wrapper">
    <div class="avatar">
      <img src="../../assets/images/Girl-Avatar-PNG-Picture.png" alt="" />
      <p>
        {{ name }}
        <span v-if="isTyping" class="isTyping"><p>is typing...</p></span>
      </p>
      <base-burger :getData="getData"></base-burger>
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
        <!-- <div v-if="notification && to == 2" class="notification" @click="notificationAlert"></div> -->
        <!-- <div v-if="isTyping" class="isTyping">
          <p>{{ name }} is typing...</p>
        </div> -->
        <section class="chat">
          <div v-if="isLoading">
            <base-spinner></base-spinner>
          </div>
          <div class="loadedChat">
            <div v-for="day in chat">
              <p v-if="day[0]" class="fulldate">
                {{ getFullDate(day[0]).toLocaleString().slice(0, 10) }}
              </p>
              <div
                class="messages"
                v-for="msg in day"
                :key="msg.id"
                :class="{
                  notificationAlert:
                    msg.is_notified === 0 &&
                    msg.from !== $store.state.currentUserId,
                }"
              >
                <p
                  :key="msg.id"
                  :class="{
                    receive: msg.from == to,
                    send: msg.from != to,
                  }"
                  @click="isContextMenu($event, msg)"
                >
                  <span v-if="msg.replyMessage">
                    <p class="replied">
                      {{ msg.replyMessage }}
                    </p></span
                  >
                  {{ msg.message }}
                  <br v-if="msg.message.length > 10" />
                  <span class="edited" v-if="msg.created_at !== msg.updated_at">
                    <p>&nbsp; edited</p>
                  </span>
                  &nbsp;
                  <span class="time">
                    <p>{{ getTime(msg) }}</p>
                  </span>
                </p>
                <base-message-edit
                  v-if="contextMenu == true && messageId == msg.id"
                  @close="contextMenu = false"
                  @replyMsg="replyMessage"
                  @editMsg="editMessage"
                  @deleteMsg="removeMessage"
                  :message="msg"
                  :clientX="clientX"
                  :clientY="clientY"
                  :editBtn="editButton"
                  :deleteBtn="deleteButton"
                >
                </base-message-edit>
              </div>
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
            :msgToEdit="messageEditId"
            :updateData="getData"
            :msg="message"
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
      chat: {},
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
    this.pusherWhisper();
    const el = document.querySelector(".chat");
    el.addEventListener("scroll", this.handleScroll);
  },
  created() {
    this.getRole();
    this.getData();
  },
  methods: {
    handleScroll() {
      const el = document.querySelector(".chat");
      if (el.scrollTop + el.clientHeight >= el.scrollHeight) {
        axios.patch(`/api/chat/notify/${this.to}`, {
          from: this.$store.state.currentUserId,
          to: +this.to,
          is_notified: 1,
        });
        for (let day in this.chat) {
          let res = this.chat[day].filter((msg) => msg.to != this.to);
          res.map((msg) =>
            setTimeout(() => {
              msg.is_notified = 1;
            }, 2000)
          );
        }
      }
    },
    connect() {
      Echo.private(`chat.${this.to}`).listen("Message", (e) => {
        axios.get(`/api/chat`).then((res) => {
          this.init(res);
          this.isTyping = false;
          const el = document.querySelector(".chat");
          if (el.scrollTop + el.clientHeight >= el.scrollHeight) {
            this.scrollDown();
            for (let day in this.chat) {
              this.chat[day]
                .filter((msg) => msg.to != this.to)
                .map((msg) => (msg.is_notified = 1));
            }
          }
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
          this.init(res);
          this.textarea = true;
          this.textareaEdit = false;
          this.textareaUserEdit = false;
          this.scrollDown();
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    init(res) {
      this.users = res.data.users;
      const msgUser = res.data.messages.filter(
        (msg) => msg.from == this.to && msg.to == 1
      );
      const msgAdmin = res.data.messages.filter(
        (msg) => msg.from == 1 && msg.to == this.to
      );
      const chat = msgAdmin.concat(msgUser);
      this.chat = chat.sort((a, b) => a.id - b.id);

      this.chat = chat.reduce((acc, message) => {
        const dayMonthYear = this.getFullDate(message)
          .toLocaleString()
          .slice(0, 10);
        acc[dayMonthYear] = acc[dayMonthYear] || [];
        acc[dayMonthYear].push(message);
        return acc;
      }, {});

      this.name = res.data.users.find((user) => user.id == this.to).name;
    },
    getFullDate(msg) {
      let date = msg.created_at.slice(0, 16).replace("T", " ");
      let t = date.split(/[- :]/);
      let time = new Date(Date.UTC(t[0], t[1] - 1, t[2], t[3], t[4]));
      return time;
    },
    updateFromAddMessage(msg) {
      const messageDate = this.getFullDate(msg.data)
        .toLocaleString()
        .slice(0, 10);
      if (!this.chat[messageDate]) {
        this.chat[messageDate] = [];
      }
      this.chat[messageDate].push(msg.data);
      this.scrollDown();
    },
    updateFromEditMessage(msg) {
      const messageDate = this.getFullDate(msg.data)
        .toLocaleString()
        .slice(0, 10);
      const editedMessageIndex = this.chat[messageDate].findIndex(
        (message) => message.id == msg.data.id
      );
      this.chat[messageDate].splice(editedMessageIndex, 1, msg.data);
      this.exitFromEditMessage();
    },
    removeMessage(message) {
      axios.delete(`/api/chat/${message.id}`);
      const messageDate = this.getFullDate(message)
        .toLocaleString()
        .slice(0, 10);
      const deletedMessageIndex = this.chat[messageDate].findIndex(
        (msg) => msg.id == message.id
      );
      this.chat[messageDate].splice(deletedMessageIndex, 1);
      this.contextMenu = false;
    },
    editMessage(message) {
      this.messageEditId = message.id;
      this.message = message.message;
      this.textareaEdit = true;
      this.textarea = false;
      this.textareaUserEdit = false;
      this.closeReply();
    },
    replyMessage(message) {
      this.exitFromEditMessage();
      this.respondMsg = message.message;
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
    isContextMenu(event, msg) {
      this.contextMenu = !this.contextMenu;
      this.messageId = msg.id;
      this.hideEditAndDeleteButtons(msg);
      this.setPositionToContextMenu(event);
    },
    hideEditAndDeleteButtons(msg) {
      if (msg.from === this.$store.state.currentUserId) {
        this.editButton = true;
        this.deleteButton = true;
      } else {
        this.editButton = false;
        this.deleteButton = false;
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
    getTime(msg) {
      const time = this.getFullDate(msg).toTimeString().slice(0, 5);
      return time;
    },
    pusherWhisper() {
      Echo.private(`chat.${this.to}`).listenForWhisper("typing", (e) => {
        if (this.to == e.idFrom) {
          this.isTyping = true;
          if (this.typingTimer) clearTimeout(this.typingTimer);
          this.typingTimer = setTimeout(() => {
            this.isTyping = false;
          }, 2000);
        }
      });
    },
    scrollDown() {
      this.$nextTick(function () {
        if (document.querySelector(".chat")) {
          const el = document.querySelector(".chat");
          el.scrollTop = el.scrollHeight;
        }
      });
    },
    notificationAlert() {
      this.scrollDown();
      this.notification = false;
    },
  },
  watch: {
    chat: function (val, oldVal) {
      this.connect();
      this.pusherWhisper();
      if (Object.keys(oldVal).length != 0) {
        this.disconnect();
        this.connect();
      }
    },
    to(value, oldVal) {
      if (oldVal != null) {
        Echo.leave(`chat.${this.to}`);
        // this.connect();
      }
    },
    // notification(value) {
    //   if (value === true) {
    //     setTimeout(() => {
    //       this.$refs.messages.lastElementChild.classList.add(
    //         "notification-alert"
    //       );
    //     }, 0);
    //   }
    // },
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

.avatar {
  display: flex;
  align-items: center;
  width: 100%;
  margin-bottom: 16px;
  height: 4rem;
  gap: 1rem;
}

.avatar > img {
  object-fit: contain;
  width: 4rem;
  height: 4rem;
  border: 1px solid #eee;
  border-radius: 50px;
}

.isTyping {
  font-style: italic;
  color: rgba(0, 0, 0, 0.504);
  z-index: 1;
}

.isTyping > p,
.avatar > p {
  margin: 0;
  font-style: italic;
  color: rgba(0, 0, 0, 0.504);
  display: inline-block;
}

.fulldate {
  color: rgba(0, 0, 0, 0.395);
  padding: 3px 12px;
  font-size: 1em;
  width: fit-content;
  margin: 10px auto;
  border-radius: 25px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.26);
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
}

.loadedChat {
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
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
  padding: 0.5rem 1rem;
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
  margin: 0 !important;
  white-space: pre-wrap;
}
.receive {
  align-self: flex-start;
  background-color: #eee;
  position: relative;
  cursor: pointer;
  margin: 0 !important;
  white-space: pre-wrap;
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
  margin: 10px;
}

.edited {
  display: inline-block;
  float: right;
}

.edited > p {
  font-size: 0.8em;
  font-style: italic;
  text-align: end;
  margin: 0;
  color: rgba(0, 0, 0, 0.462);
}

.time {
  display: inline-block;
  float: right;
}

.time > p {
  font-size: 0.8em;
  font-style: italic;
  text-align: end;
  margin: 0;
  color: rgba(0, 0, 0, 0.462);
}

/* .notification {
  position: fixed;
  width: 50px;
  height: 50px;
  border-radius: 50px;
  background-color: #c659ae;
  z-index: 1;
  margin-left: 1rem;
  margin-top: 1rem;
  cursor: pointer;
} */

/* .notification > p {
  color: #fff;
  text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;
  text-align: center;
} */
/* 
.notification:hover {
  background-color: #c659ae31;
} */

.notificationAlert {
  background-color: #c659ae31;
  width: 100%;
}

@media (max-width: 550px) {
  aside,
  .edit-profile {
    display: none;
  }

  .wrapper {
    padding: 6rem 0px 10px 0px;
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
