<template>
<div v-if="pages > 1">
  <ul class="pagination">
    <li :class="[page === 1 ? 'disabled' : '']">
      <a @click="loadPage(1)">
          <span>&laquo;</span>
      </a>
    </li>
    <li :class="[page === 1 ? 'disabled' : '']">
      <a @click="loadPage('prev')">
          <span>&nbsp;&lsaquo;</span>
      </a>
    </li>
    <template v-if="notEnoughPages">
      <template v-for="n in pages">
        <li :class="[page === n ? 'active' : '']">
          <a @click="loadPage(n)" v-html="n">
          </a>
        </li>
      </template>
    </template>
    <template v-else>
      <template v-for="n in windowSize">
        <li>
          <a @click="loadPage(windowStart+n-1)" v-html="windowStart+n-1">
          </a>
        </li>
      </template>
    </template>
    <li :class="[page === pages ? 'disabled' : '']">
      <a @click="loadPage('next')">
        <span>&rsaquo;&nbsp;</span>
      </a>
    </li>
    <li :class="[page === pages ? 'disabled' : '']">
      <a @click="loadPage(pages)">
        <span>&raquo;</span>
      </a>
    </li>
  </ul>
</div>
</template>

<script>
export default {
  props: {
    pages: {
      type: Number,
      required:true
    },
    page: {
      type: Number,
      retuired:true
    },
    onEachSide: {
      type: Number,
      default() {
        return 2
      }
    }
  },
  methods: {
    loadPage(page) {
      this.$emit('change-page',page)
    }
  },
  computed: {
    notEnoughPages() {
      return this.pages <= this.windowSize
    },
    windowSize() {
      return 5
    },
    windowStart() {
      if(this.page <= this.onEachSide) {
        return 1
      }else if (this.page >= this.pages - this.onEachSide) {
        return this.pages - this.onEachSide * 2
      }
      return this.page - this.onEachSide
    }
  }
}
</script>
<style>
.pagination {
  margin: 1rem 0 0 .5rem;
  display: flex;
  justify-content: center;
}
.pagination a {
  cursor:pointer;
}
</style>