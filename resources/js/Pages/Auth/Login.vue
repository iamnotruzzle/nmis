<template>
  <Head title="Login" />
  <div class="surface-0 flex align-items-center justify-content-center min-h-screen min-w-screen overflow-hidden">
    <div
      class="grid justify-content-center p-2 lg:p-0"
      style="min-width: 80%"
    >
      <div class="col-12 mt-5 xl:mt-0 text-center">
        <h1>NURSING MANAGEMENT INFORMATION SYSTEM</h1>
      </div>
      <div
        class="col-12 xl:col-6"
        style="
          border-radius: 56px;
          padding: 0.3rem;
          background: linear-gradient(180deg, var(--primary-color), rgba(33, 150, 243, 0) 30%);
        "
      >
        <div
          class="h-full w-full m-0 py-7 px-4"
          style="border-radius: 53px; background: linear-gradient(180deg, var(--surface-50) 38.9%, var(--surface-0))"
        >
          <div class="text-center mb-5">
            <img
              src="images/hosp_logo.png"
              alt="Image"
              height="100"
              class="mb-3"
            />
            <div class="text-900 text-3xl font-medium mb-3">Welcome</div>
            <span class="text-600 font-medium">Sign in to continue</span>
          </div>
          <transition-group
            name="p-message"
            tag="div"
            v-if="form.errors"
          >
            <Message
              v-for="(error, key) of form.errors"
              severity="error"
              :key="key"
            >
              {{ error }}
            </Message>
          </transition-group>
          <div class="w-full md:w-10 mx-auto">
            <div class="mb-3">
              <label
                for="location"
                class="block text-900 text-xl font-medium mb-1"
              >
                Location
              </label>
              <Dropdown
                v-model="form.wardcode"
                :options="locationsList"
                :virtualScrollerOptions="{ itemSize: 38 }"
                optionLabel="wardname"
                optionValue="wardcode"
                filter
                class="w-full"
                style="padding: 0.5rem"
              >
              </Dropdown>
            </div>

            <div class="mb-3">
              <label
                for="employeeid"
                class="block text-900 text-xl font-medium mb-1"
              >
                Username
              </label>
              <InputText
                id="employeeid"
                v-model="form.login"
                name="login"
                type="text"
                class="w-full uppercase"
                style="padding: 1rem"
              />
            </div>

            <div class="mb-5">
              <label
                for="password"
                class="block text-900 font-medium text-xl mb-1"
              >
                Password
              </label>
              <Password
                id="password"
                v-model="form.password"
                :toggleMask="true"
                class="w-full uppercase"
                inputClass="w-full"
                :feedback="false"
                @keyup.enter="submit"
              ></Password>
            </div>

            <Button
              type="submit"
              @click="submit"
              label="Sign In"
              class="w-full p-3 text-xl"
              :loading="form.processing"
            ></Button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Message from 'primevue/message';
import Dropdown from 'primevue/dropdown';
import { usePage } from '@inertiajs/vue3';

export default {
  components: {
    Button,
    Checkbox,
    InputText,
    Password,
    Message,
    Head,
    Dropdown,
  },
  computed: {
    logoColor() {
      return 'dark';
    },
  },
  props: {
    locations: Array,
  },
  data() {
    return {
      locationsList: [],
      form: this.$inertia.form({
        wardcode: null,
        login: '',
        password: '',
        // remember: false,
      }),
    };
  },
  mounted() {
    // console.log(usePage().props.auth.user);
    this.initializeLocation();
    // console.log(this.locationsList);
  },
  methods: {
    initializeLocation() {
      this.locations.forEach((e) => {
        this.locationsList.push({
          wardcode: e.wardcode,
          wardname: e.wardname,
        });
      });
    },
    submit() {
      this.form.password = this.form.password.toUpperCase();
      this.form.post(this.route('login'), {
        onSuccess: () => {
          this.form.reset('password');
          //   console.log(this.form.wardcode); // 4FSA correct
        },
      });
    },
  },
};
</script>

<style scoped>
.pi-eye {
  transform: scale(1.6);
  margin-right: 1rem;
}

.pi-eye-slash {
  transform: scale(1.6);
  margin-right: 1rem;
}
</style>
