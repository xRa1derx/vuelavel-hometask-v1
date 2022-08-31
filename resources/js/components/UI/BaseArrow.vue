<template>
  <span class="arrow" @click="$router.back()">&#11176;</span>
</template>

<script>
export default {};
</script>

<style scoped>
.arrow {
  font-size: 50px;
  position: absolute;
  bottom: -5px;
  left: 15px;
  color: #3a0061;
  cursor: pointer;
}

.arrow:hover {
  color: #c659ae;
}
</style>