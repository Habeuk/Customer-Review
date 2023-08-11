<template>
  <main>
    <div class="container-fluid bg-light">
      <div class="row">
        <h1>Reviews</h1>
      </div>
      <div class="row">
        <div class="d-flex justify-content-space-around">
          <a class="mx-2 text-decoration-none link-secondary" href="#"><i class="pi pi-download"></i> Import Reviews</a>
          <a class="mx-2 text-decoration-none link-secondary" href="#"><i class="pi pi-upload"></i> Export reviews</a>
          <div class="dropdown mx-2">
            <a class="dropdown-toggle text-decoration-none link-secondary" href="#" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">
              More actions
            </a>

            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Action</a></li>
              <li><a class="dropdown-item" href="#">Another action</a></li>
              <li><a class="dropdown-item" href="#">Something else here</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card bg-white">

        <ul class="nav nav-underline p-3">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">All Reviews</a>
          </li>
          <li class="nav-item">
            <a class="nav-link link-secondary" href="#">Unpublished</a>
          </li>
          <li class="nav-item">
            <a class="nav-link link-secondary" href="#">Published</a>
          </li>
          <li class="nav-item">
            <a class="nav-link link-secondary">Flagged</a>
          </li>
          <li class="nav-item">
            <a class="nav-link link-secondary">Spam</a>
          </li>
        </ul>
        <DataTable :value="products" tableStyle="min-width: 50rem" :loading="loading">
          <template #header>
            <div class="flex justify-content-end">
              <div class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText v-model="filters['global'].value" placeholder="Search for reviews..." />
              </div>
            </div>
          </template>
          <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
          <Column field="note" header="Reviews">
            <template #body="slotProps">
              <Rating :modelValue="slotProps.data.note" readonly :cancel="false" />
            </template>
          </Column>
          <Column field="review" header="Review">
            <template #body="slotProps">
              <h6 class=".fs-6 text-primary">{{ slotProps.data.title }}</h6>
              <p>{{ slotProps.data.review }}</p>
              <p>- {{ slotProps.data.name }}</p>
            </template>
          </Column>
          <Column field="createdAt" header="Date">
            <template #body="slotProps">
              {{ formatDate(slotProps.data.createdAt) }}
            </template>
          </Column>
          <Column field="is_published" header="Status"></Column>
        </DataTable>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { FilterMatchMode } from 'primevue/api';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Rating from 'primevue/rating';
import InputText from 'primevue/inputtext';
import axios from 'axios';


onMounted(() => {
  getReviews();
});

const products = ref();
const loading = ref(true);

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  representative: { value: null, matchMode: FilterMatchMode.IN },
  status: { value: null, matchMode: FilterMatchMode.EQUALS },
  verified: { value: null, matchMode: FilterMatchMode.EQUALS }
});

function getReviews() {
  axios.get('https://localhost:8000/reviews').then(res => {
    products.value = res.data
    loading.value = false;
  });
}

function formatDate(stringDate) {
  const date = new Date(stringDate);
  const month = date.toLocaleString('default', { month: 'short' });
  const day = date.getDate();
  const formattedDate = `${month} ${day}`;
  stringDate = formattedDate;
  return stringDate;
}
</script>