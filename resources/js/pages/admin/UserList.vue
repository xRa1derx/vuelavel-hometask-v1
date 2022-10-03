<template>
  <section class="user-list">
    <div v-if="loadingSpinner">
      <base-spinner></base-spinner>
    </div>
    <base-card class="students" v-else>
      <h1>Students</h1>

      <section>
        <ul>
          <li v-for="user in $store.state.users" :key="user.id">
            <base-button
              link
              :to="`/admin/users/chat/${user.id}`"
              :key="user.id"
              >{{ user.name }}</base-button
            >
          </li>
        </ul>
      </section>
    </base-card>
    <base-card class="create-user">
      <div class="controls">
        <router-link to="/admin/users/register"
          ><h3>Register a new student</h3></router-link
        >
      </div>
    </base-card>
  </section>
</template>

<script>
export default {
  created() {
    this.getUserList();
  },
  methods: {
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
  margin: 0.5rem;
}

.controls {
  display: flex;
  justify-content: flex-end;
}

.controls a {
  text-decoration: none;
  color: #000;
  padding: 10px;
  border: solid 1px #3a00612d;
  border-radius: 30px;
}

.controls a:hover {
  color: #fff;
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
}

::-webkit-scrollbar {
  width: 0;
}

h3 {
  text-align: center;
  margin: 0;
}
</style>
