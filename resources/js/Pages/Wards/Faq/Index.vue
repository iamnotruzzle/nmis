<template>
  <app-layout>
    <Head title="NMIS - Faq" />

    <div class="card">
      <h3>FREQUENTLY ASKED QUESTIONS</h3>

      <Fieldset
        legend="ADDING STOCKS TO INVENTORY"
        :toggleable="true"
        collapsed
      >
        <p class="m-0">
          1. Go to the sidebar and click on <span class="font-bold text-yellow-500">Inventory</span> tab.
        </p>
        <p class="m-0">
          2. <span class="font-bold text-yellow-500">Click Request Stocks</span>, and a modal will appear.
        </p>
        <img
          src="images/req_stock_1.png"
          class="w-10 h-10"
        />
        <p class="m-0">
          3. Click the <span class="font-bold text-yellow-500">Available Items</span> dropdown to view the items
          available in the Central Supply Unit's inventory.
        </p>
        <p class="m-0">
          4. After selecting an item, specify the quantity you want to request, then press the Enter key on your
          keyboard to add it to the Requested Stock List.
        </p>
        <p class="m-0">
          5. Note that clicking the <span class="font-bold text-error">X</span> button beside an item on the list will
          remove it.
        </p>
      </Fieldset>

      <Fieldset
        legend="Header"
        :toggleable="true"
      >
        <p class="m-0">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
          consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
          pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id
          est laborum.
        </p>
      </Fieldset>
    </div>
  </app-layout>
</template>

<script>
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Fieldset from 'primevue/fieldset';
import { Link } from '@inertiajs/vue3';

export default {
  components: {
    AppLayout,
    Head,
    InputText,
    Button,
    Dialog,
    Link,
    Fieldset,
  },
  props: {},
  data() {
    return {
      expand: false,
    };
  },
};
</script>

<style scoped>
.rounded-card {
  border-radius: 50%;
  /* min-height: 100px;
  min-width: 100px; */
}

.form-container {
  display: flex;
  justify-content: space-around;
  align-items: center;
  /* padding: 20px; */
}

.form-side {
  flex: 1;
  /* margin-right: 20px; */
}

.p-field {
  margin-bottom: 20px;
}

/* Remove arrow for input type number */
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type='number'] {
  -moz-appearance: textfield;
}
/* END Remove arrow for input type number */

#toItem {
  width: 250px;
  overflow: hidden;
  white-space: pre;
  text-overflow: ellipsis;
  -webkit-appearance: none;
}

.my-link {
  opacity: 0.7;
  transition: opacity 0.2s ease-in-out; /* Optional: Add a smooth transition effect */
}

/* Change the opacity on hover */
.my-link:hover {
  opacity: 1; /* Adjust the opacity value as needed */
  border-bottom-style: solid;
}

.button-link-current {
  display: inline-block;
  padding: 0.5rem 1rem;
  border: 1px solid #818cf8;
  background-color: #818cf8;
  color: #fff;
  text-decoration: none;
  text-align: center;
  /* border-radius: 4px; */
  transition: background-color 0.3s ease;
}
.button-link {
  display: inline-block;
  padding: 0.5rem 1rem;
  border: 1px solid #818cf8;
  /* background-color: #818cf8; */
  color: #fff;
  text-decoration: none;
  text-align: center;
  /* border-radius: 4px; */
  transition: background-color 0.3s ease;
}

.button-link-current:hover {
  background-color: #5561d7;
}
.button-link:hover {
  background-color: #5561d7;
  color: white !important;
}
</style>
