<template>
  <!-- <span class="arrow" @click="$router.back()"> -->
  <svg class="arrow-left-4" @click="$router.back()" viewBox="0 0 100 85">
    <polygon
      points="58.263,0.056 100,41.85 58.263,83.641 30.662,83.641 62.438,51.866 0,51.866 0,31.611 62.213,31.611 30.605,0 58.263,0.056"
    />
  </svg>
  <!-- </span> -->
</template>

<script>
export default {
  computed: {
    arrow() {
      return "\u2BA8";
    },
  },
};
</script>

<style scoped>
/* .arrow {
  font-size: 50px;
  position: absolute;
  bottom: -5px;
  left: 15px;
  color: #3a0061;
  cursor: pointer;
}

.arrow:hover {
  color: #c659ae;
} */

.arrow-left-4,
.arrow-right-4,
.arrow-top-4,
.arrow-bottom-4 {
  position: absolute;
  bottom: -5px;
  left: 15px;
  margin: 20px 8px;
  width: 2rem;
  height: 2rem;
  cursor: pointer;
}
.arrow-left-4 polygon,
.arrow-right-4 polygon,
.arrow-top-4 polygon,
.arrow-bottom-4 polygon {
  fill: #3a0061;
  transition: fill 0.5s ease-out;
}
.arrow-left-4 {
  transform: rotate(180deg);
}
.arrow-top-4 {
  transform: rotate(270deg);
}
.arrow-bottom-4 {
  transform: rotate(90deg);
}
.arrow-left-4:hover polygon,
.arrow-right-4:hover polygon,
.arrow-top-4:hover polygon,
.arrow-bottom-4:hover polygon {
  fill: #c659ae;
}
</style>