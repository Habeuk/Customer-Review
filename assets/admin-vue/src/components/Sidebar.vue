<template>
    <div class="card flex justify-content-center">
          <PanelMenu :model="items" class="w-100" />
        </div>
</template>

<script setup>
import { ref } from "vue";
import PanelMenu from 'primevue/panelmenu';

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

</script>