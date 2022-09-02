<template>
  <div class="wrapper">
    <div class="chat-wrapper">
      <section class="chat">
        <div v-if="isLoading">
          <base-spinner></base-spinner>
        </div>
        <div v-else class="messages" v-for="msg in chat" :key="msg.id">
          <p
            :key="msg.id"
            :class="msg.from == $store.state.currentUserId ? 'send' : 'receive'"
            @click="isContextMenu($event, msg.id)"
          >
            {{ msg.message }}
          </p>
          <base-message-edit
            v-if="contextMenu == true"
            @close="contextMenu = false"
            @copyMsg="copyMessage"
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
        <base-textarea
          v-if="textareaEdit == false"
          :selectedToSend="1"
          :updateData="getData"
          :from="$store.state.currentUserId"
        ></base-textarea>
        <base-textarea-edit
          @cancel="getData"
          :selectedToSend="1"
          :msgToEdit="messageEditId"
          :updateData="getData"
          :msg="message"
          v-else
        >
        </base-textarea-edit>
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
      textareaEdit: false,
      messageId: null,
      deleteButtonVisible: false,
      messageEditId: null,
    };
  },
  created() {
    this.getRole();
    this.getData();
  },
  methods: {
    getRole() {
      this.$store.dispatch("getRole");
      this.$store.state.isAuth = true;
    },
    getData(exit) {
      if (exit == 1) {
        this.exitFromEditMessage();
        return;
      }
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
    removeMessage(id) {
      axios.delete(`/api/chat/${id}`).then(() => {
        this.getData();
        this.contextMenu = false;
      });
    },
    editMessage(id) {
      this.messageEditId = id;
      this.message = this.chat.find((msg) => msg.id == id).message;
      this.textareaEdit = true;
    },
    copyMessage(id) {
      const message = this.chat.find((msg) => msg.id == id).message;
      navigator.clipboard.writeText(message);
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
      this.textareaEdit = false;
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

.chat-wrapper {
  width: 100%;
  height: 65vh;
  display: flex;
}

.edit-section {
  display: flex;
  width: 100%;
  justify-content: center;
}

.edit-wrap {
  margin-top: 15px;
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
</style>
