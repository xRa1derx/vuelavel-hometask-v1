<template>
    <div class="wrapper">
        <div class="avatar">
            <img src="../../assets/images/Girl-Avatar-PNG-Picture.png" alt="" />
            <p>
                {{ name }}
                <span class="isTyping"
                    ><p v-if="isTyping">is typing...</p>
                    <p v-else-if="isOnline && onlineStatus(+to)">
                        online
                    </p></span
                >
            </p>
            <base-burger :getChat="getChat"></base-burger>
        </div>
        <div class="aside-and-chat-wrapper">
            <aside>
                <base-card>
                    <section class="aside-user-list">
                        <ul
                            v-for="user in users.filter(
                                (user) => user.role != 0
                            )"
                            :key="user.id"
                        >
                            <div class="new-message">
                                <img
                                    v-show="isNewMessage(user)"
                                    src="../../assets/images/comment-exclamation.png"
                                    alt=""
                                />
                            </div>
                            <li @click="getChat">
                                <base-button
                                    link
                                    class="aside-user-list-button"
                                    :to="`/admin/users/chat/${user.id}`"
                                    >{{ user.name }}
                                </base-button>
                            </li>
                            <div class="onlineAlert-wrapper">
                                <span
                                    v-show="onlineStatus(user.id)"
                                    class="onlineAlert"
                                ></span>
                            </div>
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
                    <div v-else class="loadedChat">
                        <div v-for="day in chat">
                            <p v-if="day[0]" class="fulldate">
                                {{
                                    getFullDate(day[0])
                                        .toLocaleString()
                                        .slice(0, 10)
                                }}
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
                                    <span
                                        class="edited"
                                        v-if="msg.created_at !== msg.updated_at"
                                    >
                                        <p>&nbsp; edited</p>
                                    </span>
                                    &nbsp;
                                    <span class="time">
                                        <p>{{ getTime(msg) }}</p>
                                    </span>
                                </p>
                                <base-message-edit
                                    v-if="
                                        contextMenu == true &&
                                        messageId == msg.id
                                    "
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
                        @cancel="getChat"
                        :users="users"
                    >
                    </router-view>
                    <base-textarea-edit
                        v-else
                        key="textarea-edit"
                        @cancel="getChat"
                        @updateFromEditMessage="updateFromEditMessage"
                        :selectedToSend="to"
                        :msgToEdit="messageEdit"
                        :updateData="getChat"
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
            messageEdit: null,

            clientX: 0,
            clientY: 0,
            isLoading: false,

            isTyping: false,
            typingTimer: false,
            isOnline: false,
        };
    },
    mounted() {
        const el = document.querySelector(".chat");
        el.addEventListener("scroll", this.handleScroll);
    },
    created() {
        this.getData();
        this.pusherWhisper();
    },
    methods: {
        handleScroll() {
            const el = document.querySelector(".chat");
            if (
                el.scrollTop + el.clientHeight >= el.scrollHeight - 2 ||
                el.scrollHeight < el.clientHeight
            ) {
                // if scroll is down OR no scroll
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
                this.$store.state.newMessage.delete(+this.to);
            }
        },
        getChat(exit) {
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
            this.isOnline = false;
            this.name = "";
            axios
                .get(`/api/chat`)
                .then((res) => {
                    console.log(res);
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
                    this.textarea = true;
                    this.textareaEdit = false;
                    this.textareaUserEdit = false;
                    this.isOnline = true;
                    this.name = res.data.users.find(
                        (user) => user.id == this.to
                    ).name;
                })
                .finally(() => {
                    this.isLoading = false;
                    this.scrollDown();
                });
        },
        connect() {
            this.users
                .filter((user) => user.id != 1)
                .map((channelName) => {
                    Echo.join(`chat.${channelName.id}`)
                        .here((users) => {
                            let currentUsers =
                                this.$store.state.onlineUsers.concat(
                                    users.filter((user) => user.id != 1)
                                );
                            this.$store.state.onlineUsers = currentUsers;
                            console.log(users);
                            console.log("SUBSCRIBED!");
                        })
                        .joining((user) => {
                            this.$store.state.onlineUsers.push(user);
                            console.log({ user }, "joined");
                        })
                        .leaving((user) => {
                            this.$store.state.onlineUsers =
                                this.$store.state.onlineUsers.filter(
                                    (onlineUsers) => onlineUsers.id !== user.id
                                );
                            console.log({ user }, "leaving");
                        })
                        .listen("Message", () => {
                            axios.get(`/api/chat`).then((res) => {
                                this.init(res);
                                console.log(res);
                                this.isTyping = false;
                                const el = document.querySelector(".chat");
                                if (
                                    el.scrollTop + el.clientHeight >=
                                    el.scrollHeight
                                ) {
                                    this.scrollDown();
                                    for (let day in this.chat) {
                                        this.chat[day]
                                            .filter((msg) => msg.to != this.to)
                                            .map(
                                                (msg) => (msg.is_notified = 1)
                                            );
                                    }
                                }
                            });
                        });
                });
        },
        // disconnect() {
        //   Echo.join(`chat.${this.to}`).stopListening("Message");
        // },
        getData(exit) {
            // if (exit == 0) {
            //   this.exitFromEditUser();
            //   return;
            // }
            // if (exit == 1) {
            //   this.exitFromEditMessage();
            //   return;
            // }
            this.closeReply();
            this.to = this.$route.params.id;
            this.isLoading = true;
            this.isOnline = false;
            axios
                .get(`/api/chat`)
                .then((res) => {
                    this.init(res);
                    this.connect();
                    this.textarea = true;
                    this.textareaEdit = false;
                    this.textareaUserEdit = false;
                })
                .finally(() => {
                    this.isLoading = false;
                    this.scrollDown();
                });
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
            this.users = res.data.users;
            this.$store.state.newMessage = new Set(
                res.data.new_message.map((item) => item.from)
            );
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
            this.isOnline = true;
        },
        getFullDate(msg) {
            let date = msg.created_at.slice(0, 16).replace("T", " ");
            let t = date.split(/[- :]/);
            let time = new Date(Date.UTC(t[0], t[1] - 1, t[2], t[3], t[4]));
            return time;
        },
        updateFromAddMessage(msg) {
            const messageDate = this.getFullDate(msg)
                .toLocaleString()
                .slice(0, 10);
            if (!this.chat[messageDate]) {
                this.chat[messageDate] = [];
            }
            this.chat[messageDate].push(msg);
            this.scrollDown();
        },
        updateFromEditMessage(msg) {
            console.log(msg);
            const messageDate = this.getFullDate(msg)
                .toLocaleString()
                .slice(0, 10);
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
            event.preventDefault();
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
        onlineStatus(id) {
            return this.$store.state.onlineUsers.find((user) => user.id === id);
        },
        isNewMessage(user) {
            return this.$store.state.newMessage.has(user.id);
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
        // to(value, oldVal) {
        //   if (oldVal != null) {
        //     Echo.leave(`chat.${this.to}`);
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
    justify-content: space-between;
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
    background-color: #fff;
    color: rgba(0, 0, 0, 0.83);
    padding: 3px 12px;
    font-size: 1em;
    width: fit-content;
    margin: 10px auto;
    border-radius: 25px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.549);
}

.aside-and-chat-wrapper {
    display: flex;
    height: 10%;
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
    padding: 16px 8px !important;
    background-color: #ebdce5;
    border: none;
}

.chat {
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: auto;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.26);
    background-image: url("../../assets/images/output-onlinepngtools(20).png");
    background-position: center;
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

.aside-user-list > ul {
    position: relative;
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
    display: flex;
    list-style: none;
    padding: 0;
    justify-content: space-between;
    gap: 3px;
}

.new-message {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    min-height: 20px;
}

.new-message > img {
    width: 20px;
    height: 20px;
    object-fit: cover;
}

li {
    width: 100%;
    text-align: center;
}

.onlineAlert-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    min-height: 20px;
}

.onlineAlert {
    content: "";
    width: 12px;
    height: 12px;
    border-radius: 50px;
    background-color: #59c671;
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
    background-color: #e39ed3;
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
