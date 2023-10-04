

<template>
  <Toast />
  <div class="container bg-light">
    <div class="row">
      <div class="col-md-3 bg-white">
        <Sidebar></Sidebar>
      </div>
      <div class="col-md-9 bg-white">
        <main>
          <div class="row">
            <div class="d-flex justify-content-space-around">
              <a class="mx-2 text-decoration-none link-secondary" @click="addToCarousel" href="#"><i class="pi pi-plus"></i>Add to carousel</a>
            </div>
          </div>
          <div class="card bg-white">
            <DataTable v-model:selection="selectedReviews" :value="reviews" tableStyle="min-width: 50rem"
              :loading="loading">
              <template #header>
                <div class="flex justify-content-end">
                  <div class="p-input-icon-left">
                    <i class="pi pi-search" />
                    <InputText v-model="filters['global'].value" placeholder="Search for reviews..." />
                  </div>
                </div>
              </template>
              <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
              <Column sortable field="note" header="Note">
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
            </DataTable>
          </div>

        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { FilterMatchMode } from 'primevue/api';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Rating from 'primevue/rating';
import InputText from 'primevue/inputtext';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import { HTTP } from '../http-common';
import Sidebar from '../components/Sidebar.vue';


onMounted(() => {
  getPublishedReviews();
  shop.value = shopAttributes.getAttribute("data-shop");
});

const reviews = ref();
const loading = ref(true);
const review = ref();
const selectedReviews = ref([]);
const toast = useToast();
const shop = ref();

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  representative: { value: null, matchMode: FilterMatchMode.IN },
  status: { value: null, matchMode: FilterMatchMode.EQUALS },
  verified: { value: null, matchMode: FilterMatchMode.EQUALS }
});

function formatDate(stringDate) {
  const date = new Date(stringDate);
  const month = date.toLocaleString('default', { month: 'short' });
  const day = date.getDate();
  const formattedDate = `${month} ${day}`;
  stringDate = formattedDate;
  return stringDate;
}

function getPublishedReviews(event) {
  if (event) {
    event.preventDefault();
  }
  loading.value = true;
  axios.get('/shopify/admin/api/v1/reviews?published=1&shop=' + shop.value).then(res => {
    reviews.value = res.data
    loading.value = false;
    if (event) {
      updateActive(event);
    }
  });

}

function addToCarousel() {
  console.log(selectedReviews.value)
  HTTP.post('/shopify/admin/api/v1/carousel', selectedReviews.value).then(res => {
    selectedReviews.value = '';
    toast.add({ severity: 'success', summary: 'Confirmed', detail: 'The reviews was successfuly added to the carousel', life: 3000 });
  }).catch(function (error) {
    console.log(error);
  });
}

</script>
