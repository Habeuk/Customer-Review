
<template>
    <div class="card">
        <Carousel :value="reviews" :numVisible="3" :numScroll="3" :responsiveOptions="responsiveOptions">
            <template #item="slotProps">
                <div class="">
                    <div class="d-flex mb-4">
                        <Rating :modelValue="slotProps.data.note" readonly :cancel="false" /> <span class="mx-2 fs-6 fs-italic">le {{
                            formatDate(slotProps.data.created_at) }}</span>
                    </div>
                    <div class="d-flex">
                        <div class="d-flex flex-column">
                            <img class="" :src="slotProps.data.product.imageSrc + '&width=80'" :alt="slotProps.data.product.title" :width="80" :height="80">
                            <span class="fs-6">{{ slotProps.data.product.title }}</span>
                        </div>
                        <div class="blockquote text-left ">
                            <p class="fs-6">{{ slotProps.data.description }}</p>
                            <footer class="blockquote-footer fs-6">{{ slotProps.data.name }}</footer>
                        </div>
                    </div>
                </div>
            </template>
        </Carousel>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { HTTP } from '../http-common';
import Carousel from 'primevue/carousel';
import Rating from 'primevue/rating';


onMounted(() => {
    getCarouselReviews();
})

const reviews = ref();
const loading = ref(false);
const responsiveOptions = ref([
    {
        breakpoint: '1199px',
        numVisible: 1,
        numScroll: 1
    },
    {
        breakpoint: '991px',
        numVisible: 2,
        numScroll: 1
    },
    {
        breakpoint: '767px',
        numVisible: 1,
        numScroll: 1
    }
]);

function getCarouselReviews() {
    loading.value = true;
    HTTP.get('/api/v1/carousel').then(res => {
        reviews.value = res.data
        loading.value = false;
        console.log(reviews.value)
    }).catch(function (error) {
        loading.value = false;
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
    return date.toLocaleDateString('fr-FR', options);
}



</script>