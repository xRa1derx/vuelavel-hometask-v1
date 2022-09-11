<template>
    <div>
        <textarea v-model="message" name="message" id="message"></textarea>
        <button
            @click="$emit('cancel', 1)"
            type="button"
            class="cancel-message"
        >
            &times
        </button>
        <button @click="editMessage" type="submit" class="edit-message">
            Save
        </button>
    </div>
</template>

<script>
import axios from "axios";
export default {
    emits: ["cancel"],
    props: ["selectedToSend", "updateData","msg", "msgToEdit"],
    data() {
        return {
            message: this.msg,
            from: null,
        };
    },
    mounted() {
        this.getCurrentUser();
    },
    methods: {
        async editMessage() {
            await axios
                .patch(`/api/chat/${this.msgToEdit}`, {
                    to: +this.selectedToSend,
                    from: +this.from,
                    message: this.message,
                })
                .then((res) => {});
            this.updateData();
            this.message = "";
        },
        getCurrentUser() {
            axios.get("/api/chat").then((res) => {
                if (this.$store.state.isAdmin === true) {
                    this.from = res.data.users.find(
                        (user) => user.role == 0
                    ).id;
                } else {
                    this.from = this.$store.state.currentUserId;
                }
            });
        },
    },
    watch: {
        msg(value) {
            this.message = value;
        },
    },
};
</script>

<style scoped>
div {
    display: flex;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.26);
    border-radius: 12px;
    border-bottom-right-radius: 0;
}

textarea {
    display: block;
    width: 100%;
    height: 100px;
    border: none;
    font: inherit;
    border-radius: 12px;
    border-bottom-right-radius: 0;
    padding: 15px 15px 15px 20px;
    resize: none;
    outline: none;
}

.edit-message {
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
    position: absolute;
    background-color: inherit;
    top: -0.5rem;
    right: 0;
    border: none;
    font-size: 26px;
    color: rgb(121, 121, 121);
}

.cancel-message:hover {
    color: rgb(182, 182, 182);
}

.invalid textarea {
    border: 1px solid red;
}
</style>
