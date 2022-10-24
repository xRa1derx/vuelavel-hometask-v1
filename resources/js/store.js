import { createStore } from "vuex";
import router from "./router";

const store = createStore({
    state() {
        return {
            currentUserId: null,
            isAdmin: false,
            isAuth: false,
            isLoading: false,
            users: [],
            onlineUsers: [],
            newMessage: [],
        };
    },
    mutations: {
        getRole(state, res) {
            state.currentUserId = res.data.user;
            const admin = Boolean(
                res.data.users.find(
                    (user) => user.role == 0 && user.id == res.data.user
                )
            );
            state.isAdmin = admin;
            if (state.isAdmin === true && state.isAuth === false) {
                router.replace("/admin");
            }
            if (state.isAdmin === false) {
                router.replace("/chat");
            }
            state.isAuth = true;
        },
        listOfUsers(state, res) {
            state.users = res.data.filter((user) => user.role != 0);
            router.push({ name: "admin.users" });
        },
    },
    actions: {
        getRole(context) {
            axios.get("/api").then((res) => {
                context.commit("getRole", res);
            });
        },
        listOfUsers(context) {
            this.state.isLoading = true;
            axios
                .get(`/api/admin/users`)
                .then((res) => {
                    context.commit("listOfUsers", res);
                })
                .finally(() => {
                    this.state.isLoading = false;
                });
        },
    },
    getters: {
        loadingSpinner(state) {
            return state.isLoading;
        },
        onlineStatus(state) {
            return state.onlineUsers;
        },
    },
});

export default store;
