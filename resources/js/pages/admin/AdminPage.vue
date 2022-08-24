<template>
  <div>
    <h1>ADMIN PAGE</h1>
    <base-button link mode="outline" :to="'/admin/users'">Chat</base-button>
  </div>
</template>

<script>
export default {
  props: ["currentUser"],
};
</script>

<style>
</style>