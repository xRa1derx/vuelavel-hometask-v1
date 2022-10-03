<template>
    <form @submit.prevent="login">
        <base-card>
            <div
                class="form-control"
                :class="{ invalid: !email.isValid || !email.isValidEmail }"
            >
                <label for="email">Email</label>
                <input
                    type="text"
                    name="email"
                    id="email"
                    v-model.trim="email.val"
                    @blur="isValidity('email')"
                />
                <p v-if="!email.isValid">Email must not be empty</p>
                <p v-else-if="!email.isValidEmail">
                    Please enter a valid email
                </p>
            </div>
            <div class="form-control" :class="{ invalid: !password.isValid }">
                <label for="password">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    v-model.trim="password.val"
                    @blur="isValidity('password')"
                />
                <p v-if="!password.isValid">Password must not be empty</p>
            </div>
            <base-button>Login</base-button>
        </base-card>
    </form>
</template>

<script>
import axios from "axios";
export default {
    data() {
        return {
            email: {
                val: "",
                isValid: true,
                isValidEmail: true,
            },
            password: {
                val: "",
                isValid: true,
            },
            formIsValid: true,
        };
    },
    methods: {
        validateForm() {
            this.formIsValid = true;
            if (this.email.val == "") {
                this.email.isValid = false;
                this.formIsValid = false;
            }
            if (!this.email.val.includes("@")) {
                this.email.isValidEmail = false;
                this.formIsValid = false;
            }
            if (this.password.val == "") {
                this.password.isValid = false;
                this.formIsValid = false;
            }
        },
        isValidity(value) {
            this[value].isValid = true;
            this[value].isValidEmail = true;
        },
        login() {
            this.validateForm();
            if (!this.formIsValid) {
                return;
            }
            axios.get("/sanctum/csrf-cookie").then((response) => {
                axios
                    .post("/login", {
                        email: this.email.val,
                        password: this.password.val,
                    })
                    .then((res) => {
                        localStorage.setItem(
                            "x_xsrf_token",
                            res.config.headers["X-XSRF-TOKEN"]
                        );
                        this.getRole(); 
                    })
                    .catch((err) => console.log(err));
            });
        },
        async getRole() {
            await this.$store.dispatch("getRole");
        },
    },
};
</script>

<style scoped>
form {
    padding-top: 5rem;
    margin: auto;
}

button {
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
</style>
