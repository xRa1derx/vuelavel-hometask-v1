<template>
    <div class="wrapper">
        <div class="aside-and-chat-wrapper">
            <aside>
                <base-card>
                    <section class="aside-user-list">
                        <ul
                            @click="getData"
                            v-for="user in users"
                            :key="user.id"
                        >
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
                    <span
                        class="arrow"
                        @click="$router.push({ name: 'admin.users' })"
                        >&#11176;</span
                    >
                </base-card>
            </aside>
            <div class="chat-wrapper">
                <section class="chat">
                    <div v-if="isLoading">
                        <base-spinner></base-spinner>
                    </div>
                    <div
                        v-else
                        class="messages"
                        v-for="msg in chat"
                        :key="msg.id"
                    >
                        <p
                            :key="msg.id"
                            :class="msg.from == to ? 'receive' : 'send'"
                            @click="isContextMenu($event, msg.id)"
                        >
                            {{ msg.message }}
                        </p>
                        <base-message-edit
                            v-if="contextMenu == true && messageId == msg.id"
                            @close="contextMenu = false"
                            @copyMsg="copyMessage"
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
                    :updateData="getData"
                    :selectedToSend="to"
                    :from="$store.state.currentUserId"
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
    props: ["userss"],
    data() {
        return {
            chat: [],
            users: [],
            to: null,
            name: "",
            message: "",

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

                    this.name = res.data.users.find(
                        (user) => user.id == this.to
                    ).name;

                    this.textarea = true;
                    this.textareaEdit = false;
                    this.textareaUserEdit = false;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        removeMessage(id) {
            axios.delete(`/api/chat/${id}`);
            this.getData();
        },
        editMessage(id) {
            this.message = this.chat.find((msg) => msg.id == id).message;
            this.messageEditId = id;
            this.textareaEdit = true;
            this.textarea = false;
            this.textareaUserEdit = false;
        },
        copyMessage(id) {
            const message = this.chat.find((msg) => msg.id == id).message;
            navigator.clipboard.writeText(message);
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
