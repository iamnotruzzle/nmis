<template>
  <Head title="Login" />
  <div class="surface-0 flex align-items-center justify-content-center min-h-screen min-w-screen overflow-hidden">
    <div
      class="grid justify-content-center p-2 lg:p-0"
      style="min-width: 80%"
    >
      <div class="col-12 mt-5 xl:mt-0 text-center">
        <h1>TEMPLATE</h1>
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
            <label
              for="username"
              class="block text-900 text-xl font-medium mb-2"
            >
              Username
            </label>
            <InputText
              id="username"
              v-model="form.login"
              type="text"
              class="w-full mb-3"
              style="padding: 1rem"
            />

            <label
              for="password"
              class="block text-900 font-medium text-xl mb-2"
            >
              Password
            </label>
            <Password
              id="password"
              v-model="form.password"
              :toggleMask="true"
              class="w-full mb-5"
              inputClass="w-full"
              :feedback="false"
              @keyup.enter="submit"
            ></Password>

            <!-- <div class="flex align-items-center justify-content-between mb-5">
              <div class="flex align-items-center">
                <Checkbox
                  id="rememberme"
                  v-model="checked"
                  :binary="true"
                  class="mr-2"
                ></Checkbox>
                <label for="rememberme">Remember me</label>
              </div>
              <a
                :href="route('register')"
                class="font-medium no-underline ml-2 text-right cursor-pointer"
                style="color: var(--primary-color)"
                >Register</a
              >
              <a
                class="font-medium no-underline ml-2 text-right cursor-pointer"
                style="color: var(--primary-color)"
                >Forgot password?</a
              >
            </div> -->
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

export default {
  components: {
    Button,
    Checkbox,
    InputText,
    Password,
    Message,
    Head,
  },
  computed: {
    logoColor() {
      return 'dark';
    },
  },
  data() {
    return {
      form: this.$inertia.form({
        login: '',
        password: '',
        // remember: false,
      }),
    };
  },
  methods: {
    submit() {
      this.form
        // .transform((data) => ({
        //   ...data,
        //   remember: this.form.remember ? 'on' : '',
        // }))
        .post(this.route('login'), {
          onFinish: () => this.form.reset('password'),
        });
      //   console.log(this.form);
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
