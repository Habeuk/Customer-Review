<template>
    <Dialog v-model:visible="visible" modal header="Add a Review" :style="{ width: '45vw' }" :modal="true"
        @update:visible="hideHandler">
        <div class="card">
            <form @submit="submitReviewForm" class="form">
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Rating from 'primevue/rating';
import Textarea from 'primevue/textarea';
import { useField, useForm } from 'vee-validate';
import { HTTP } from '../http-common';


const { value, errorMessage } = useField('value', validateField);
const ratingNote = ref();
const selectedProduct = ref();
const userName = ref();
const email = ref();
const title = ref();
const reviewText = ref();
const products = ref();
const visible = defineProps({
    value: Boolean
});
const modalVisible = ref(visible);


onMounted(() => {
    getProducts();
});

function validateField() {
    if (!value) {
        return 'Description is required.';
    }
    if (!title) {
        return 'Title is required.';
    }

    return true;
}

function getProducts() {
    HTTP.get('/shopify/admin/api/v1/products').then(res => {
        products.value = res.data
        dropdownLoading.value = false
    }).catch(function (error) {
    });
}

function hideHandler(value) { if (!value) { modalVisible.value = false; } }


</script>