<template>
  <div>
    <h2>Apache Access Logs</h2>

    <div class="filters">
      <input v-model="filters.ip" placeholder="IP" @input="fetchLogs" />
      <input v-model="filters.method" placeholder="Method" @input="fetchLogs" />
      <input v-model="filters.status" placeholder="Status" @input="fetchLogs" />
    </div>

    <table v-if="logs.length">
      <thead>
        <tr>
          <th @click="sort('ip')">
            IP <span v-if="sortField === 'ip'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
          </th>
          <th @click="sort('date')">
            Date <span v-if="sortField === 'date'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
          </th>
          <th @click="sort('method')">
            Method <span v-if="sortField === 'method'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
          </th>
          <th>Path</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="log in logs" :key="log.raw">
          <td>{{ log.ip }}</td>
          <td>{{ log.date }}</td>
          <td>{{ log.method }}</td>
          <td>{{ log.path }}</td>
          <td>{{ log.status }}</td>
        </tr>
      </tbody>
    </table>

    <div class="pagination">
      <button @click="prevPage" :disabled="page === 1">← Prev</button>
      <span>Page {{ page }} of {{ totalPages }}</span>
      <button @click="nextPage" :disabled="page === totalPages">Next →</button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  setup() {
    const logs = ref([]);
    const filters = ref({
      ip: '',
      method: '',
      status: ''
    });

    const sortField = ref('date');
    const sortOrder = ref('asc');

    const page = ref(1);
    const perPage = ref(20);
    const totalPages = ref(1);

    const fetchLogs = async () => {
      try {
        const params = new URLSearchParams({
          ...filters.value,
          page: page.value,
          perPage: perPage.value,
          sort: sortOrder.value === 'asc' ? sortField.value : `-${sortField.value}`
        });

        const response = await fetch(`http://11.11.11.10/api_access.php?${params}`);

        if (!response.ok) throw new Error('API request failed');

        const data = await response.json();
        logs.value = data.logs;
        totalPages.value = data.totalPages;
      } catch (error) {
        console.error('Error fetching logs:', error);
        logs.value = [];
      }
    };

    const sort = (field) => {
      if (sortField.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
      } else {
        sortField.value = field;
        sortOrder.value = 'asc';
      }
      fetchLogs();
    };

    const nextPage = () => {
      if (page.value < totalPages.value) {
        page.value++;
        fetchLogs();
      }
    };

    const prevPage = () => {
      if (page.value > 1) {
        page.value--;
        fetchLogs();
      }
    };

    onMounted(fetchLogs);

    return {
      logs,
      filters,
      sortField,
      sortOrder,
      page,
      totalPages,
      fetchLogs,
      sort,
      nextPage,
      prevPage
    };
  }
};
</script>

