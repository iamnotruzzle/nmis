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
        <div class="text-xl">
          <p class="m-0">
            1. Go to the sidebar and click on <span class="font-bold text-yellow-500">Inventory</span> tab.
          </p>
          <p class="m-0">
            2. <span class="font-bold text-yellow-500">Click Request Stocks</span>, and a modal will appear.
          </p>
          <img
            src="images/req_stock_1.png"
            class="w-10 h-10 my-2 border-3 border-yellow-500"
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
          <img
            src="images/req_stock_modal.png"
            class="w-8 h-8 my-2 border-3 border-yellow-500"
          />
          <p class="m-0">
            6. After requesting an item, the order will have a
            <span class="font-bold text-yellow-500">pending</span> status.
          </p>
          <p class="m-0 flex justify-center align-center">
            <span class="font-bold text-error mr-1">NOTE:</span>
            You can only update the requested items if the order status is still
            <Tag
              value="PENDING"
              severity="contrast"
              class="mx-1"
            />
            or
            <Tag
              value="ACKNOWLEDGED"
              severity="primary"
              class="ml-1"
            />.
          </p>
          <img
            src="images/req_stock_pending.png"
            class="w-8 h-8 my-2 border-3 border-yellow-500"
          />
          <p class="m-0">
            7. When status is
            <Tag
              value="ACKNOWLEDGED"
              severity="primary"
            />, it means Central supply unit is preparing your order.
          </p>
          <p class="m-0">
            8. When status is
            <Tag
              value="FILLED"
              severity="info"
            />, it means the order is ready to be deploy and a
            <Button
              label="Receive"
              class="ml-2"
              icon="pi pi-check"
              iconPos="right"
              severity="success"
            />
            button will appear. By clicking it, the items will be added to your inventory.
          </p>
          <img
            src="images/req_stock_filled.png"
            class="w-8 h-8 my-2 border-3 border-yellow-500"
          />
          <p class="m-0">
            <span class="font-bold text-error mr-1 mt-3">NOTE:</span> Remember that clicking the button means you have
            counted the items you received, and your information will be saved in the order logs. This certifies that
            you are the one who received the items and acknowledged that they are correct.
          </p>
        </div>
      </Fieldset>

      <Fieldset
        legend="VIEW PATIENT'S BILL"
        :toggleable="true"
        collapsed
      >
        <div class="text-xl">
          <p class="m-0">1. Go to the sidebar and click on <span class="font-bold text-yellow-500">Patients</span>.</p>
          <p class="m-0">
            2. In the <span class="font-bold text-yellow-500">Action</span> column, click on
            <span class="font-bold text-yellow-500">Bills</span>.
          </p>
          <img
            src="images/patient_list.png"
            class="w-10 h-10 my-2 border-3 border-yellow-500"
          />
        </div>
      </Fieldset>

      <Fieldset
        legend="CHARGE A PATIENT"
        :toggleable="true"
        collapsed
      >
        <div class="text-xl">
          <p class="m-0">1. Go to patient’s <span class="font-bold text-yellow-500">bills</span>.</p>
          <p class="m-0">
            2. <span class="font-bold text-yellow-500">Click Charge patient button</span>, and a modal will appear.
          </p>
          <img
            src="images/patient_charge_1.png"
            class="w-10 h-10 my-2 border-3 border-yellow-500"
          />
          <p class="m-0 mt-2">
            3. Click the <span class="font-bold text-yellow-500">Available Items</span> dropdown to view the items
            available in the inventory.
          </p>
          <p class="m-0">
            4. After selecting an item, specify the quantity you want to charge, then press the Enter key on your
            keyboard to add it to the <span class="font-bold text-yellow-500">ITEMS/SERVICES TO CHARGE</span>.
          </p>
          <p class="m-0 mb-2">
            5. Note that clicking the <span class="font-bold text-error">X</span> button beside an item on the list will
            remove it.
          </p>
          <img
            src="images/patient_charge_2.png"
            class="w-10 h-10 my-2 border-3 border-yellow-500"
          />
        </div>
      </Fieldset>

      <Fieldset
        legend="REORDER STOCK"
        :toggleable="true"
        collapsed
      >
        <div class="text-xl">
          <p class="m-0">
            1. Go to the sidebar and click on <span class="font-bold text-yellow-500">Stock Level</span> tab.
          </p>
          <img
            src="images/reorder_1.png"
            class="w-2 h-2 my-2"
          />
          <p class="m-0">
            2. <span class="font-bold text-yellow-500">Click ADD STOCK LEVEL button</span>, and a modal will appear.
          </p>
          <img
            src="images/reorder_2.png"
            class="w-10 h-10 my-2"
          />
          <p class="m-0 mt-2">3. Fill out the form to set an item's reorder point and reorder quantity.</p>
          <img
            src="images/reorder_3.png"
            class="w-5 h-5 my-2"
          />
          <p class="m-0 mt-2">
            4. On the Request Stocks page, click the "Reorder" button to automatically fetch your reorder list.
          </p>
          <img
            src="images/reorder_4.png"
            class="w-10 h-10 my-2"
          />
          <img
            src="images/reorder_5.png"
            class="w-5 h-5 my-2"
          />
        </div>
      </Fieldset>

      <Fieldset
        legend="ENDORSEMENTS"
        :toggleable="true"
        collapsed
      >
        <div class="text-xl">
          <p class="m-0 text-yellow-500">
            Endorsement feature is the handover of ward-related responsibilities from one ward assistant to another
            during shift changes.
          </p>
          <img
            src="images/endorsement-1.png"
            class="w-2 h-2 my-2"
          />
          <p class="m-0 text-yellow-500">
            You can create an <span class="font-italic">endorsement</span> by clicking the Endorsement button.
          </p>
          <img
            src="images/endorsement-2.png"
            class="w-6 h-6 my-2"
          />
          <p class="m-0 text-yellow-500">
            Add the endorsement description. Don’t forget to include the type and current status. You can add or remove
            endorsement items by clicking the designated buttons.
          </p>
          <img
            src="images/endorsement-3.png"
            class="w-6 h-6 my-2"
          />
          <p class="m-0 text-yellow-500">
            After saving the endorsement, it will appear in the list. Expand the endorsement to view the included items.
          </p>
          <img
            src="images/endorsement-4.png"
            class="w-6 h-6 my-2"
          />
          <p class="m-0 text-yellow-500">
            To update an endorsement, click the
            <i class="pi pi-pencil text-yellow-500"></i> icon — for example, to change the status.
          </p>
          <img
            src="images/endorsement-5.png"
            class="w-6 h-6 my-2"
          />
          <p class="m-0 text-yellow-500">
            When creating a new endorsement, any previous endorsement with a status other than Completed or Cancelled
            will be automatically added to the list.
          </p>
          <img
            src="images/endorsement-6.png"
            class="w-6 h-6 my-2"
          />
          <p class="m-0 text-yellow-500">The latest endorsement will be shown on the dashboard page.</p>
          <img
            src="images/endorsement-7.png"
            class="w-6 h-6 my-2"
          />
        </div>
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
import Tag from 'primevue/tag';
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
    Tag,
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
