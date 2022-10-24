<template>
  <div class="wrapper">
    <div class="user-menu">
      <div class="avatar">
        <div class="avatar_image">
          <img src="../../assets/images/Girl-Avatar-PNG-Picture.png" alt="" />
          <div v-if="onlineStatus" class="onlineAlert"></div>
        </div>
      </div>
      <p>
        Alyona <span v-if="isTyping" class="isTyping"><p>is typing...</p></span>
      </p>
      <img
        class="notification"
        v-if="notification"
        @click="scrollDown()"
        src="../../assets/images/new-message-notification.png"
        alt=""
        style="margin-left: auto; border: none; cursor: pointer"
      />
    </div>
    <div class="chat-wrapper">
      <section class="chat">
        <div v-if="isLoading">
          <base-spinner></base-spinner>
        </div>
        <div v-for="day in chat">
          <p v-if="day[0]" class="fulldate">
            {{ getFullDate(day[0]).toLocaleString().slice(0, 10) }}
          </p>
          <div
            class="messages"
            v-for="msg in day"
            :key="msg.uuid"
            :class="{
              notificationAlert:
                msg.is_notified === 0 &&
                msg.from !== $store.state.currentUserId,
            }"
          >
            <p
              :key="msg.uuid"
              :class="
                msg.from == $store.state.currentUserId ? 'send' : 'receive'
              "
              @click="isContextMenu($event, msg)"
            >
              <span v-if="msg.replyMessage">
                <p class="replied">{{ msg.replyMessage }}</p></span
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
              v-if="contextMenu == true && messageId == msg.uuid"
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
            key="textarea-edit"
            @cancel="getData"
            @updateFromEditMessage="updateFromEditMessage"
            :selectedToSend="1"
            :msgToEdit="messageEdit"
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
import BaseMessageEdit from "../../components/UI/BaseMessageEdit.vue";
import BaseTextareaEdit from "../../components/UI/BaseTextareaEdit.vue";

export default {
  components: { BaseMessageEdit, BaseTextareaEdit },
  data() {
    return {
      contextMenu: false,
      isLoading: false,
      chat: {},
      textarea: true,
      textareaEdit: false,
      message: "",
      messageId: null,
      editButton: false,
      deleteButton: false,
      messageEdit: null,
      respondMsg: null,
      respondMessage: false,

      clientX: 0,
      clientY: 0,

      isTyping: false,
      typingTimer: false,
      notification: false,
    };
  },
  mounted() {
    const el = document.querySelector(".chat");
    setTimeout(() => {
      if (el.scrollTop === 0) {
        this.clearNotifyFromNewMessage();
      }
    }, 1500);
    el.addEventListener("scroll", this.handleScroll);
  },
  created() {
    this.getData();
    this.pusherWhisper();
  },
  beforeUnmount() {
    const el = document.querySelector(".chat");
    el.removeEventListener("scroll", this.handleScroll);
  },
  methods: {
    handleScroll() {
      const el = document.querySelector(".chat");
      if (el.scrollTop + el.clientHeight >= el.scrollHeight) {
        this.clearNotifyFromNewMessage();
        this.notification = false;
      }
    },
    clearNotifyFromNewMessage() {
      axios.patch(`/api/chat/notify/1`, {
        from: this.$store.state.currentUserId,
        to: 1,
        is_notified: 1,
      });
      for (let day in this.chat) {
        let res = this.chat[day].filter((msg) => msg.to != 1 && msg.from);
        res.map((msg) =>
          setTimeout(() => {
            msg.is_notified = 1;
          }, 2000)
        );
      }
    },
    connect() {
      Echo.join(`chat.${this.$store.state.currentUserId}`)
        .here((users) => {
          this.$store.state.onlineUsers = [...users];
          console.log({ users });
          console.log("SUBSCRIBED!");
        })
        .joining((user) => {
          this.$store.state.onlineUsers.push(user);
          console.log({ user }, "joined");
        })
        .leaving((user) => {
          this.$store.state.onlineUsers = this.$store.state.onlineUsers.filter(
            (onlineUsers) => onlineUsers.id !== user.id
          );
          console.log({ user }, "leaving");
        })
        .listen("Message", (data) => {
          axios.get(`/api/chat`).then((res) => {
            if (data.message.from != this.$store.state.currentUserId) {
              this.init(res);
              this.isTyping = false;
              const el = document.querySelector(".chat");
              if (el.scrollTop + el.clientHeight >= el.scrollHeight) {
                this.clearNotifyFromNewMessage();
                this.scrollDown();
                for (let day in this.chat) {
                  this.chat[day]
                    .filter((msg) => msg.to != this.to)
                    .map((msg) => (msg.is_notified = 1));
                }
              } else {
                this.notification = true;
              }
            }
          });
        });
    },
    // disconnect() {
    //   Echo.join(`chat.${this.$store.state.currentUserId}`).stopListening(
    //     "Message"
    //   );
    // },
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
          this.init(res);
          this.connect();
          this.scrollDown();
        })
        .finally(() => {
          this.isLoading = false;
        });
      this.textareaEdit = false;
    },
    init(res) {
      this.$store.state.currentUserId = res.data.user;
      const admin = Boolean(
        res.data.users.find(
          (user) => user.role == 0 && user.id == res.data.user
        )
      );
      this.$store.state.isAdmin = admin;
      if (
        this.$store.state.isAdmin === true &&
        this.$store.state.isAuth === false
      ) {
        this.$router.replace("/admin");
      }
      if (this.$store.state.isAdmin === false) {
        this.$router.replace("/chat");
      }
      this.$store.state.isAuth = true;
      const currentUserId = this.$store.state.currentUserId;
      const msgUser = res.data.messages.filter(
        (msg) => msg.from == currentUserId && msg.to == 1
      );
      const msgAdmin = res.data.messages.filter(
        (msg) => msg.from == 1 && msg.to == currentUserId
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
    },
    getFullDate(msg) {
      let date = msg.created_at.slice(0, 16).replace("T", " ");
      let t = date.split(/[- :]/);
      let time = new Date(Date.UTC(t[0], t[1] - 1, t[2], t[3], t[4]));
      return time;
    },
    updateFromAddMessage(msg) {
      const messageDate = this.getFullDate(msg).toLocaleString().slice(0, 10);
      if (!this.chat[messageDate]) {
        this.chat[messageDate] = [];
      }
      this.chat[messageDate].push(msg);
      this.scrollDown();
    },
    updateFromEditMessage(msg) {
      console.log(msg);
      const messageDate = this.getFullDate(msg).toLocaleString().slice(0, 10);
      const editedMessageIndex = this.chat[messageDate].findIndex(
        (message) => message.uuid == msg.uuid
      );
      this.chat[messageDate].splice(editedMessageIndex, 1, msg);
      this.exitFromEditMessage();
    },
    removeMessage(message) {
      axios.delete(`/api/chat/${message.uuid}`);
      const messageDate = this.getFullDate(message)
        .toLocaleString()
        .slice(0, 10);
      const deletedMessageIndex = this.chat[messageDate].findIndex(
        (msg) => msg.uuid == message.uuid
      );
      this.chat[messageDate].splice(deletedMessageIndex, 1);
      this.contextMenu = false;
    },
    editMessage(message) {
      this.messageEdit = message;
      this.message = message.message;
      this.textareaEdit = true;
      this.textarea = false;
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
    isContextMenu(event, msg) {
      this.contextMenu = !this.contextMenu;
      this.messageId = msg.uuid;
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
        this.clientY = event.clientY + window.scrollY - 80;
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
      Echo.private(`chat.${this.$store.state.currentUserId}`).listenForWhisper(
        "typing",
        (e) => {
          if (1 == e.idFrom && e.idTo === this.$store.state.currentUserId) {
            this.isTyping = true;
            if (this.typingTimer) clearTimeout(this.typingTimer);
            this.typingTimer = setTimeout(() => {
              this.isTyping = false;
            }, 2000);
          }
        }
      );
    },
    scrollDown() {
      this.$nextTick(function () {
        if (document.querySelector(".chat")) {
          const el = document.querySelector(".chat");
          el.scrollTop = el.scrollHeight;
        }
      });
    },
  },
  computed: {
    onlineStatus() {
      return this.$store.getters.onlineStatus.find(
        (user) => user.id != this.$store.state.currentUserId
      )
        ? true
        : false;
    },
  },
  watch: {
    // chat: function (val, oldVal) {
    //   this.connect();
    //   this.pusherWhisper();
    //   if (Object.keys(oldVal).length != 0) {
    //     this.disconnect();
    //     this.connect();
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
  padding: 6rem 0 5rem 0;
  flex-direction: column;
  justify-content: center;
}

.user-menu {
  display: flex;
  align-items: center;
  width: 100%;
  margin-bottom: 16px;
  height: 4rem;
  gap: 1rem;
}

.avatar_image {
  position: relative;
}

.avatar_image > img,
.notification {
  object-fit: contain;
  width: 4rem;
  height: 4rem;
  border: 1px solid #eee;
  border-radius: 50px;
}

.onlineAlert {
  position: absolute;
  top: 0;
  right: 0;
  width: 10px;
  height: 10px;
  border-radius: 50px;
  background-color: rgb(26, 132, 26);
}

.isTyping {
  font-style: italic;
  color: rgba(0, 0, 0, 0.504);
  z-index: 1;
}

.isTyping > p,
.user-menu > p {
  margin: 0;
  font-style: italic;
  color: rgba(0, 0, 0, 0.504);
  display: inline-block;
}

.chat-wrapper {
  position: relative;
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
  background-color: rgba(255, 255, 255, 0.724);
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

.notificationAlert {
  background-color: #c659ae31;
  width: 100%;
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

.fulldate {
  color: rgba(0, 0, 0, 0.395);
  padding: 3px 12px;
  font-size: 1em;
  width: fit-content;
  margin: 10px auto;
  border-radius: 25px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.26);
}

.arrow:hover {
  color: #c659ae;
}

@media (max-width: 550px) {
  .wrapper {
    padding: 6rem 0px 10px 0px;
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
