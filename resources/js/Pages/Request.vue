<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },

});


const form = useForm({
    email: props.email,
});


const submit = () => {
    form.post(route('send.otp.request'), {
        onSuccess: () => {
            alert("OTP has been sent to your email!");
            form.get(route('verify'), { email: form.email }); // 🔥 FIXED REDIRECTION
        },
    });
};
// const submit = () => {
//     form.post(route('send.otp.request'), {
//         onSuccess: () => alert("OTP has been sent to your email!")
//     });
// };


</script>


<template>
    <Head title="Request OTP" />

    <AuthenticatedLayout>
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>



            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Request OTP
                </PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

