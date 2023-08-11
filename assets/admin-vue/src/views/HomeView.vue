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
            <a class="nav-link active" @click="getReviews($event)" aria-current="page" href="#">All Reviews</a>
          </li>
          <li class="nav-item">
            <a class="nav-link link-secondary" @click="getUnpublishedReviews($event)" href="#">Unpublished</a>
          </li>
          <li class="nav-item">
            <a class="nav-link link-secondary" @click="getPublishedReviews($event)" href="#">Published</a>
          </li>
        </ul><span>Delete</span>
        <DataTable :value="reviews" tableStyle="min-width: 50rem" :loading="loading">
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
          <Column field="description" header="Review">
            <template #body="slotProps">
              <h6 class=".fs-6 text-primary">{{ slotProps.data.title }}</h6>
              <p>{{ slotProps.data.description }}</p>
              <p>- {{ slotProps.data.name }}</p>
            </template>
          </Column>
          <Column field="createdAt" header="Date">
            <template #body="slotProps">
              {{ formatDate(slotProps.data.createdAt) }}
            </template>
          </Column>
          <Column  field="is_published" header="Status">
            <template #body="slotProps">
              <div class="d-flex">
                <InputSwitch v-model="slotProps.data.isValidated" />
              </div>
            </template>
          </Column>
          <Column field="actions" header="Actions">
          <template #body="slotProps">
            <Button icon="pi pi-check" text raised rounded aria-label="Filter" />
            <Button icon="pi pi-check" text raised rounded aria-label="Filter" />
          </template>
          </Column>
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
import Tag from 'primevue/tag';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputSwitch from 'primevue/inputswitch';
import axios from 'axios';


onMounted(() => {
  getReviews();
});

const reviews = ref();
const loading = ref(true);

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  representative: { value: null, matchMode: FilterMatchMode.IN },
  status: { value: null, matchMode: FilterMatchMode.EQUALS },
  verified: { value: null, matchMode: FilterMatchMode.EQUALS }
});

function getReviews(event) {
  if (event) {
    event.preventDefault();
  }
  loading.value = true;
  axios.get('https://127.0.0.1:8000/shopify/admin/reviews').then(res => {
    reviews.value = res.data
    loading.value = false;
    if (event) {
      updateActive(event);
    }
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

function getUnpublishedReviews(event) {
  if (event) {
    event.preventDefault();
  }
  loading.value = true;
  axios.get('https://127.0.0.1:8000/shopify/admin/reviews?unpublished=1').then(res => {
    reviews.value = res.data
    loading.value = false;
    if (event) {
      updateActive(event);
    }
  });

}

function getPublishedReviews(event) {
  if (event) {
    event.preventDefault();
  }
  loading.value = true;
  axios.get('https://127.0.0.1:8000/shopify/admin/reviews?published=1').then(res => {
    reviews.value = res.data
    loading.value = false;
    if (event) {
      updateActive(event);
    }
  });

}

function updateActive(event) {
  let oldElement = document.querySelector(".nav-link.active");
  oldElement.removeAttribute('aria-current');
  oldElement.classList.remove("active");
  oldElement.classList.add("link-secondary");
  event.target.classList.add("active");
  event.target.classList.remove("link-secondary");
  event.target.setAttribute("aria-current", "page");
}
</script>