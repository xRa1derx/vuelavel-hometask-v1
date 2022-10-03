<template>
    <section>
        <base-card>
            <form @submit.prevent="addUser">
                <h2>Register a Student</h2>
                <div v-if="errors">
                    <div v-for="category in errors" :key="category">
                        <div
                            class="m-alert m-alert--outline alert alert-danger alert-dismissible"
                            role="alert"
                            v-for="error in category"
                            :key="error"
                        >
                            <span>{{ error }}</span>
                        </div>
                    </div>
                </div>
                <transition name="fadeHeight">
                    <div
                        class="form-control"
                        :class="{ invalid: !name.isValid }"
                    >
                        <label for="name">Name</label>
                        <input
                            type="text"
                            name="name"
                            v-model.trim="name.val"
                            @blur="isValidity('name')"
                        />
                        <p v-if="!name.isValid">Name must not be empty</p>
                    </div>
                </transition>
                <div
                    class="form-control"
                    :class="{ invalid: !email.isValid || !email.isEmail }"
                >
                    <label for="email">Email</label>
                    <input
                        type="text"
                        name="email"
                        v-model.trim="email.val"
                        @blur="isValidity('email')"
                    />
                    <p v-if="!email.isValid">Email must not be empty</p>
                    <p v-else-if="!email.isEmail">Please enter a valid email</p>
                </div>
                <div
                    class="form-control"
                    :class="{
                        invalid: !password.isValid || !password.isValidLength,
                    }"
                >
                    <label for="password">Password</label>
                    <input
                        type="password"
                        placeholder="must be at least 6 characters"
                        name="password"
                        v-model.trim="password.val"
                        @blur="isValidity('password')"
                    />
                    <p v-if="!password.isValid">Password must not be empty</p>
                    <p v-else-if="!password.isValidLength">
                        must be at least 6 characters
                    </p>
                </div>
                <div
                    class="form-control"
                    :class="{ invalid: !password_confirmation.isConfirmed }"
                >
                    <label for="password_confirmation">Confirm Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        v-model.trim="password_confirmation.val"
                        @blur="isValidity('password_confirmation')"
                    />
                    <p v-if="!password_confirmation.isConfirmed">
                        Passwords do not match
                    </p>
                </div>
                <base-button>Create</base-button>
            </form>
        </base-card>

        <base-arrow></base-arrow>
    </section>
</template>

<script>
import axios from "axios";
export default {
    data() {
        return {
            name: {
                val: "",
                isValid: true,
            },
            password: {
                val: "",
                isValid: true,
                isValidLength: true,
            },
            email: {
                val: "",
                isValid: true,
                isEmail: true,
            },
            password_confirmation: {
                val: "",
                isConfirmed: true,
            },
            formIsValid: true,
            errors: {},
        };
    },
    methods: {
        isValidity(value) {
            this[value].isValid = true;
            this[value].isValidLength = true;
            this[value].isEmail = true;
            this[value].isConfirmed = true;
        },
        validateForm() {
            this.formIsValid = true;
            if (this.name.val == "") {
                this.name.isValid = false;
                this.formIsValid = false;
            }
            if (this.email.val == "") {
                this.email.isValid = false;
                this.formIsValid = false;
            }
            if (!this.email.val.includes("@")) {
                this.email.isEmail = false;
                this.formIsValid = false;
            }
            if (this.password.val == "") {
                this.password.isValid = false;
                this.formIsValid = false;
            }
            if (this.password.val.length < 6) {
                this.password.isValidLength = false;
                this.formIsValid = false;
            }
            if (this.password.val != this.password_confirmation.val) {
                this.password_confirmation.isConfirmed = false;
                this.formIsValid = false;
            }
        },
        addUser() {
            this.validateForm();
            if (!this.formIsValid) {
                return;
            }
            axios
                .post("/api/admin/users/create", {
                    name: this.name.val,
                    password: this.password.val,
                    password_confirmation: this.password_confirmation.val,
                    email: this.email.val,
                })
                .then((res) => {
                    this.$router.push({ name: "admin.users" });
                    console.log(res);
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                });
        },
    },
};
</script>

<style scoped>
section {
    width: 100%;
    position: relative;
    display: flex;
    margin: auto;
    padding-top: 5rem;
}

.card {
    width: 100%;
}

form {
    margin: auto;
    padding: 60px 0;
}

button {
    width: 100%;
    margin-top: 20px;
    border: none;
}

.form-control {
    margin: 0.5rem 0;
    border: none;
}

label {
    font-weight: bold;
    display: block;
    margin-bottom: 0.5rem;
}

input {
    display: block;
    width: 100%;
    border: 1px solid #ccc;
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

span,
h2 {
    text-align: center;
}

/* Animations */
.fadeHeight-enter-active,
.fadeHeight-leave-active {
    transition: all 0.2s;
    max-height: 230px;
}
.fadeHeight-enter,
.fadeHeight-leave-to {
    opacity: 0;
    max-height: 0px;
}
</style>
