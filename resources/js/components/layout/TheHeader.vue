<template>
    <header>
        <nav>
            <h1>myHometask</h1>
            <ul v-if="this.$route.name != 'login'">
                <li>
                    <base-button link @click.prevent="logout"
                        >Logout</base-button
                    >
                </li>
            </ul>
        </nav>
    </header>
</template>

<script>
import axios from "axios";
export default {
    methods: {
        logout() {
            axios.post("/logout").then((res) => {
                localStorage.removeItem("x_xsrf_token");
                this.$store.state.isAdmin = false;
                this.$store.state.isAuth = false;
                this.$store.state.currentUserId = null;
                this.$router.push({ name: "login" });
            });
        },
    },
};
</script>

<style scoped>
header {
    position: fixed;
    width: 100%;
    height: 5rem;
    background-color: #3d008d;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100;
}

header a {
    text-decoration: none;
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border: 1px solid rgb(122, 122, 122);
}

a:active,
a:hover,
a.router-link-active {
    border: 1px solid #f391e3;
}

h1 {
    color: #fff;
    margin: 0;
}

h1 a {
    color: white;
    margin: 0;
}

h1 a:hover,
h1 a:active,
h1 a.router-link-active {
    border-color: transparent;
}

header nav {
    width: 90%;
    margin: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
}

li {
    margin: 0 0.5rem;
}
</style>
