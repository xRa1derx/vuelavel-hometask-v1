<template>
  <section class="user-list">
    <div v-if="loadingSpinner">
      <base-spinner></base-spinner>
    </div>
    <base-card class="students" v-else>
      <h1>Students</h1>

      <section>
        <ul v-for="user in $store.state.users" :key="user.id">
          <li @click="getChat" v-if="user.role">
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
                <span v-show="onlineStatus(user.id)" class="onlineAlert"></span>
              </div>
          </li>
        </ul>
      </section>
    </base-card>
    <base-card class="create-user">
      <div class="controls">
        <base-button
              link
              to="/admin/users/register"
              >Register a new student</base-button
            >
        <!-- <router-link to="/admin/users/register"
          ><h3>Register a new student</h3></router-link
        > -->
      </div>
    </base-card>
  </section>
</template>

<script>
import axios from "axios";
export default {
  created() {
    this.getUserList();
    if (this.$store.state.onlineUsers.length == 0) {
      setTimeout(() => {
        this.connect();
      }, 1000);
    }
  },
  methods: {
    connect() {
      axios.get("/api/chat").then((res) => {
        this.$store.state.newMessage = new Set(
          res.data.new_message.map((item) => item.from)
        );
        this.$store.state.users
          .filter((user) => user.id != 1)
          .map((channelName) => {
            Echo.join(`chat.${channelName.id}`)
              .here((users) => {
                let currentUsers = this.$store.state.onlineUsers.concat(
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
              });
          });
      });
    },
    onlineStatus(id) {
      return this.$store.state.onlineUsers.find((user) => user.id === id);
    },
    isNewMessage(user) {
      if (this.$store.state.newMessage.length != 0) {
        return this.$store.state.newMessage.has(user.id);
      }
    },
    getUserList() {
      this.$store.dispatch("listOfUsers");
    },
  },
  computed: {
    loadingSpinner() {
      return this.$store.getters.loadingSpinner;
    },
  },
};
</script>

<style scoped>
ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

li {
  display: flex;
  justify-content: center;
  margin: 0.3rem;
}

li > a {
  width: 100%;
}

.controls a {
  text-decoration: none;
  color: #fff;
  padding: 10px 35px;
}

.controls a:hover {
  background-color: #c659ae;
}

.user-list {
  position: relative;
  height: 70%;
  display: flex;
  flex-direction: column;
  text-align: center;
  margin: auto;
  padding-top: 5rem;
}

.students {
  height: 100%;
  overflow: auto;
  margin-bottom: 20px;
  background-image: url("../../assets/images/output-onlinepngtools(20).png");
  background-position: center;
}

::-webkit-scrollbar {
  width: 0;
}

h3 {
  text-align: center;
  margin: 0;
}

.create-user {
  background-image: url("../../assets/images/output-onlinepngtools(20).png");
  background-position: center;
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
</style>
