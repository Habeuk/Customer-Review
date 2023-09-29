<template>
  <div class="container bg-light">
    <Toast />
    <ConfirmDialog />
    <div class="row">
      <div class="col-md-3">
        <Sidebar></Sidebar>
      </div>
      <div class="col-md-9">
        <main>
          <div class="container-fluid bg-light">
            <div class="row">
              <h1>Reviews</h1>
            </div>
            <div class="row">
              <div class="d-flex justify-content-space-around">
                <a class="mx-2 text-decoration-none link-secondary" href="#"><i class="pi pi-download"></i> Import
                  Reviews</a>
                <a class="mx-2 text-decoration-none link-secondary" href="#"><i class="pi pi-upload"></i> Export
                  reviews</a>
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
              <div class="d-flex justify-content-between">
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
                </ul>
                <div class="d-flex flex-column justify-content-center m-3"><Button icon="pi pi-plus"
                    @click="addReview()" />
                </div>
              </div>
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
                <Column field="is_published" header="Status">
                  <template #body="slotProps">
                    <div class="d-flex">
                      <InputSwitch :model-value="slotProps.data.isValidated"
                        @click="validate(slotProps, !slotProps.data.isValidated)" />
                    </div>
                  </template>
                </Column>
                <Column field="actions" header="Actions">
                  <template #body="slotProps">
                    <Button icon="pi pi-reply" v-tooltip="'Reply'" text raised rounded aria-label="Filter"
                      @click="reply(slotProps.data.id)" />
                  </template>
                </Column>
              </DataTable>
            </div>
          </div>
        </main>
      </div>
    </div>

    <Dialog v-model:visible="visible" header="Reply to review" :style="{ width: '75vw' }" modal="true">
      <div class="row">
        <div class="col-md-9">
          <div class="card mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between mb-4">
                <Rating :modelValue="review.note" readonly :cancel="false" />
                <Tag v-if="review.isValidated" severity="success" value="Published"></Tag>
                <Tag v-else severity="warning" value="Unpublished"></Tag>
              </div>
              <h5 class="card-title mb-4">{{ review.title }}</h5>
              <p class="card-text">{{ review.description }}</p>
            </div>
            <div class="card-footer bg-white">
              {{ review.name }} ( <a href="mailto:{{ review.email }}">{{ review.email }}</a> )
            </div>
          </div>
          <div class="card">
            <form @submit="onSubmit" class="form">
              <div class="card-body">
                <h5 class="card-title mb-4">Reply to review</h5>
                <span class="p-float-label">
                  <Textarea id="value" auto-resize="true" v-model="value" :rows="4" :class="{ 'p-invalid': errorMessage }"
                    :style="{ width: 100 + '%' }" aria-describedby="text-error" />
                  <label for="value">Add a reply to this review...</label>
                </span>
                <small id="text-error" class="p-error">{{ errorMessage || '&nbsp;' }}</small>
              </div>
              <div class="card-footer bg-white d-flex justify-content-end mt-2 pt-4">
                <Button label="Post reply" :type="'submit'" severity="help" />
              </div>
            </form>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Product details</h5>
              <img :src="product.imageSrc + '&width=80'" :alt="product.title" />
            </div>
            <div class="card-footer">
              <a href="#" class="text-decoration-none">{{ product.title }}</a>
              <Rating :modelValue="product.reviewSummary.mean * 5 / 100" readonly :cancel="false" />
              <p class="pt-1">{{ product.reviewSummary.total }} reviews</p>
            </div>
          </div>
        </div>
      </div>
    </Dialog>

    <Dialog v-model:visible="addReviewVisible" modal header="Add a Review" :style="{ width: '45vw' }" :modal="true"
        @reviewFormSubmited="hideHandler" @reviewFormVisible="showModal()">
        <div class="card" @review-form:submited="hideForm()">
            <form @submit="submitReviewForm($event)" class="form">
                <div class="card-body">
                    <h5 class="card-title mb-4">Reply to review</h5>

                    <Dropdown v-model="selectedProduct" :options="products" filter optionLabel="title"
                        placeholder="Select a Product" class="w-100" :loading="dropdownLoading">
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex align-items-center">
                                <div>{{ slotProps.value.title }}</div>
                            </div>
                            <span v-else>
                                {{ slotProps.placeholder }}
                            </span>
                        </template>
                        <template #option="slotProps">
                            <div class="flex align-items-center">
                                <div>{{ slotProps.option.title }}</div>
                            </div>
                        </template>
                    </Dropdown>
                    <div class="my-3">
                        <span>Give a note</span>
                        <div class="mx-2">
                            <Rating v-model="ratingNote" :cancel="false" />
                        </div>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="p-float-label">
                            <InputText id="name" v-model="userName" :name="userName" :required="true" />
                            <label for="name">Name</label>
                        </span>

                        <span class="p-float-label">
                            <InputText id="email" v-model="email" type="email" :required="true" />
                            <label for="email">Email</label>
                        </span>
                    </div>

                    <div class="row mb-2">
                        <div class="col">
                            <span class="p-float-label">
                                <InputText id="title" v-model="title" :style="{ width: 100 + '%' }" :required="true" />
                                <label for="title">Title</label>
                            </span>
                        </div>
                    </div>
                    <span class="p-float-label">
                        <Textarea id="value" auto-resize="true" v-model="reviewText" :rows="4"
                            :class="{ 'p-invalid': errorMessage }" :style="{ width: 100 + '%' }"
                            aria-describedby="text-error" :required="true" />
                        <label for="value">Add your review...</label>
                    </span>
                    <small id="text-error" class="p-error">{{ errorMessage || '&nbsp;' }}</small>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end mt-2 pt-4">
                    <Button label="Post reply" :type="'submit'" severity="help" />
                </div>
            </form>
        </div>
    </Dialog>
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
import ConfirmDialog from 'primevue/confirmdialog';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { useField, useForm } from 'vee-validate';
import axios from 'axios';
import { HTTP } from '../http-common';
import Sidebar from '../components/Sidebar.vue';
import Dropdown from 'primevue/dropdown';


onMounted(() => {
  getReviews();
  getProducts();
});

const reviews = ref();
const loading = ref(true);
const confirm = useConfirm();
const visible = ref(false);
const dropdownLoading = ref(true);
const addReviewVisible = ref(false);
const review = ref();
const product = ref();
const { handleSubmit, resetForm } = useForm();
const { value, errorMessage } = useField('value', validateField);
const ratingNote = ref();
const selectedProduct = ref();
const userName = ref();
const email = ref();
const title = ref();
const reviewText = ref();
const products = ref();
const modalVisible = ref(visible);
const toast = useToast();

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
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
  HTTP.get('/shopify/admin/api/v1/reviews').then(res => {
    reviews.value = res.data
    loading.value = false;
    if (event) {
      updateActive(event);
    }
  }).catch(function (error) {
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

function getUnpublishedReviews(event) {
  if (event) {
    event.preventDefault();
  }
  loading.value = true;
  axios.get('/shopify/admin/api/v1/reviews?unpublished=1').then(res => {
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
  axios.get('/shopify/admin/api/v1/reviews?published=1').then(res => {
    reviews.value = res.data
    loading.value = false;
    if (event) {
      updateActive(event);
      console.log(review)
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

const validate = (slotProps, value) => {

  let updateMessage = value ? "Are you sure you want to publish this review ?" : "Are you sure you want to unpublish this review ?";
  confirm.require({
    message: updateMessage,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    accept: () => {
      axios.put('/reviews/' + slotProps.data.id, {
        isValidated: value
      }).then(() => {
        reviews.value[slotProps.index].isValidated = value;
        toast.add({ severity: 'success', summary: 'Confirmed', detail: 'The review was modified successfuly', life: 3000 });
      }).catch(function (error) {
        console.log(error);
      });
    },
    reject: () => {
      toast.add({ severity: 'info', summary: 'No Change', detail: 'updateSelectedValueNothing changed', life: 3000 });
    }
  });
};

function reply(id) {
  axios.get('/shopify/admin/api/v1/reviews/' + id).then(res => {
    review.value = res.data;
    axios.get('/shopify/admin/api/v1/product/' + id).then(res => {
      product.value = res.data;
      visible.value = true;
    })
  });

}

function validateField() {
  if (!value) {
    return 'Description is required.';
  }

  return true;
}

const onSubmit = handleSubmit((values) => {
  if (values.value && values.value.length > 0) {
    console.log(values)
    HTTP.post(
      '/comments/' + review.value.id,
      {
        comment: values.value
      }
    ).then(() => {
      visible.value = false
      toast.add({ severity: 'info', summary: 'The reply was added successfuly', detail: values.value, life: 3000 });
      resetForm();
      getReviews();
    }).catch(() => {
      toast.add({ severity: 'danger', summary: 'An error occured', detail: values.value, life: 3000 });
    });
  }
});

function addReview() {
  addReviewVisible.value = true;
}

function resetReviewForm() {
  ratingNote.value = '';
  selectedProduct.value = '';
  userName.value = '';
  email.value = '';
  title.value = '';
  reviewText.value = '';
  products.value = '';
}

const submitReviewForm = handleSubmit((values) => {
  axios.post(
    '/shopify/admin/api/v1/reviews',
    {
      name: userName.value,
      email: email.value,
      title: title.value,
      note: ratingNote.value,
      handle: selectedProduct.value.handle,
      description: reviewText.value
    }
  ).then(() => {
    addReviewVisible.value = false
    toast.add({ severity: 'info', summary: 'The review was added successfuly', detail: values.value, life: 3000 });
    resetReviewForm();
  });
});


function getProducts() {
    HTTP.get('/shopify/admin/api/v1/products').then(res => {
        products.value = res.data
        dropdownLoading.value = false
    }).catch(function (error) {
    });
}


</script>