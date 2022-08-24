<template>
  <section class="user-edit">
    <form @submit.prevent="editUser">
      <div class="form-control">
        <input type="text" name="name" v-model.trim="name" />

        <input type="email" name="email" v-model.trim="email" />

        <input
          type="password"
          placeholder="must be at least 6 characters"
          name="password"
        />
        <button
          @click="$emit('cancel', 0)"
          type="button"
          class="cancel-message"
        >
          &times
        </button>
        <button type="submit" class="edit-user">Edit</button>
      </div>
    </form>
  </section>
</template>

<script>
import axios from "axios";
export default {
  props: ["currentName", "users", "selectedToSend"],
  emits: ["cancel"],
  data() {
    return {
      name: "",
      email: "",
    };
  },
  mounted() {
    this.currentUser();
  },
  methods: {
    editUser() {
      axios
        .patch(`/api/admin/users/${this.selectedToSend}`, {
          name: this.name,
          email: this.email,
        })
        .then((res) => console.log(res))
        .catch((error) => console.log(error));
    },
    currentUser() {
      const user = this.users.find((user) => user.id == this.selectedToSend);
      this.name = user.name;
      this.email = user.email;
    },
  },
};
</script>

<style scoped>
.user-edit {
  width: 100%;
  height: 100px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.26);
}

form {
  margin: auto;
  height: 100%;
}

button {
  width: 100%;
  border: none;
}

.form-control {
  position: relative;
  display: flex;
  width: 100%;
  height: 100%;
  justify-content: space-around;
  align-items: center;
  flex-wrap: wrap;
}

input {
  display: block;
  width: fit-content;
  border: 1px solid rgba(204, 204, 204, 0.419);
  font: inherit;
}

input:focus {
  background-color: #f0e6fd;
  outline: none;
  border-color: #3d008d;
}

.invalid label {
  color: red;
}

.invalid input {
  border: 1px solid red;
}

.edit-user {
  position: absolute;
  bottom: 0;
  right: 0;
  border: none;
  border-top-left-radius: 30px;
  border-bottom-left-radius: 30px;
  background-color: #8d006e;
  color: #fff;
  width: 5em;
  height: 2.5em;
}

.cancel-message {
  width: 1px;
  height: 1px;
  position: absolute;
  background-color: inherit;
  top: -9px;
  right: 13px;
  border: none;
  font-size: 26px;
  color: rgb(121, 121, 121);
}

.cancel-message:hover {
  color: rgb(182, 182, 182);
}
</style>