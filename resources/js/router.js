import { createRouter, createWebHistory } from "vue-router";

import LoginPage from "./pages/LoginPage";
import axios from "axios";
import AdminPage from "./pages/admin/AdminPage";
import UserList from "./pages/admin/UserList";
import UserEdit from "./pages/admin/UserEdit";
import UserChat from "./pages/admin/UserChat";
import RegisterPage from "./pages/admin/RegisterPage";

import UserPage from "./pages/user/UserPage";

import NotFound from "./pages/NotFound";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/", component: LoginPage, name: "login" },
        {
            path: "/admin",
            component: AdminPage,
            name: "admin",
        },
        {
            path: "/admin/users",
            component: UserList,
            name: "admin.users",
        },
        {
            path: "/admin/users/chat/:id",
            component: UserChat,
            name: "admin.users.chat.id",
            props: true,
            children: [{ path: "edit", component: UserEdit }],
        },
        {
            path: "/admin/users/register",
            component: RegisterPage,
            name: "register",
        },
        {
            path: "/user/:id",
            component: UserPage,
            name: "user",
            meta: {
                requeiresAuth: true,
            },
        },
        {
            path: "/:notFound(.*)",
            component: NotFound,
            name: "notfound",
        },
    ],
});

router.beforeEach((to, from, next) => {
    console.log("from Router", to);

    const token = localStorage.getItem("x_xsrf_token");
    if (!token) {
        if (to.name === "login") {
            return next();
        } else {
            return next({ name: "login" });
        }
    }

    next();


    console.log(to.params.id);
});

export default router;
