<template>
  <div class="card flex justify-content-center">
    <PanelMenu :model="items" class="w-100" />
  </div>
  <div class="mt-5">
    <Button v-if="visible" icon="pi pi-bolt" :loading="loading" label="Upgrade to premium" class="w-100" @click="upgrade()" />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import PanelMenu from 'primevue/panelmenu';
import Button from 'primevue/button';
import { HTTP } from '../http-common';

const visible = ref(false);
const loading = ref(false);
const shop = ref();

const items = ref([
  {
    label: 'Manage Reviews',
    icon: 'pi pi-image',
    to: '/'
  },
  {
    label: 'Reviews Widget',
    icon: 'pi pi-image',
    items: [
      {
        label: 'General',
      },
      {
        label: 'Styling',
      }
    ]
  },
  {
    label: 'Star rating',
    icon: 'pi pi-star',
  },
  {
    label: 'Carousel',
    icon: 'pi pi-images',
    to: '/carousel'
  },
  {
    label: 'Media Carousel',
    icon: 'pi pi-images',
  },
  {
    label: 'Media Grid',
    icon: 'pi pi-images',
  },
  {
    label: 'Review Badge',
    icon: 'pi pi-tag',
  }
]);

onMounted(() => {
  const shopAttributes = document.getElementById("shop-attributes");
  shop.value = shopAttributes.getAttribute("data-shop");
  showUpgradeButton();
});

function showUpgradeButton() {
  HTTP.get('/api/v1/premium?shop=' + shop.value).then( res => {
    visible.value = !res.data;
  }).catch(function(res){
    console.log(res)
  });
}

function upgrade() {
  loading.value = true;
  HTTP.get('/api/v1/subscription').then( res => {
    window.location.href = res.data.confirmationUrl;
    console.log(res.data)
  }).catch(function(res){
    console.log(res)
  });
}

</script>