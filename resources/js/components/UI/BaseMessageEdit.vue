<template>
  <div class="backdrop" @click="$emit('close')">
    <div
      :style="{ left: clientX + 'px', top: clientY + 'px' }"
      class="contextMenu"
    >
      <base-button mode="flat" @click="$emit('replyMsg', message)">
        <slot name="reply">Reply</slot>
      </base-button>
      <base-button v-if="editBtn" mode="flat" @click="$emit('editMsg', message)">
        <slot name="edit">Edit</slot>
      </base-button>
      <base-button
        v-if="deleteBtn"
        mode="flat"
        @click="$emit('deleteMsg', message)"
      >
        <slot name="delete">Delete</slot>
      </base-button>
    </div>
  </div>
</template>

<script>
import BaseButton from "./BaseButton.vue";
export default {
  components: { BaseButton },
  props: ["message", "clientX", "clientY", "editBtn", "deleteBtn"],
  emits: ["replyMsg", "deleteMsg", "editMsg", "close"],
};
</script>

<style scoped>
.contextMenu {
  display: flex;
  flex-direction: column;
  position: absolute;
  background-color: white;
  z-index: 3;
  box-shadow: 0 2px 2px rgba(0, 0, 0, 0.036);
}

.backdrop {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  z-index: 3;
}

button {
  color: #000 !important;
  margin: 0px !important;
  padding: 5px 15px 5px 15px;
  border-radius: 0;
}

@media (max-width: 550px) {
  button {
    padding: 10px 20px 10px 20px;
  }
}
</style>
