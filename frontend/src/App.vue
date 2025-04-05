

<template>
  <div id="app">
    <div class="header">
      <h1>Apache Logs Parser</h1>
      <a href="https://byhanna.online/" target="_blank">
      <img alt="BHCode logo" src="./assets/icon.png"
      width="200px" height="200px">
    </a>
    </div>

    <div class="nav-links">
      <button
        @click="activeComponent = 'LogParser'"
        :class="{ active: activeComponent === 'LogParser' }"
      >
        Check Access Logs
      </button>
      <button
        @click="activeComponent = 'ErrorLogParser'"
        :class="{ active: activeComponent === 'ErrorLogParser' }"
      >
      Check Error Logs
      </button>
    </div>

    <div class="component-container">
      <component :is="activeComponent"/>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import LogParser from './components/LogParser/AccessLogParser.vue';
import ErrorLogParser from './components/LogParser/ErrorLogParser.vue';

export default {
  name: 'App',
  components: {
    LogParser,
    ErrorLogParser
  },
  setup() {
    const activeComponent = ref('LogParser');

    return {
      activeComponent
    };
  }
}
</script>
