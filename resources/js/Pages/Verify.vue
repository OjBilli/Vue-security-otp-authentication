<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
// Get the email from the URL query params
const email = page.props.email || '';

// Initialize form with email and OTP field
const form = useForm({
    email: email,
    otp: '',
});
// Submit function
const submit = () => {
    form.post(route('verify.otp.request'), {
        onSuccess: () => {
            alert("OTP verified successfully! Redirecting...");
            window.location.href = '/dashboard'; // Change to your desired redirect page
        },
        onError: () => {
            alert("Invalid OTP. Please try again.");
        },
    });
};
</script>

<template>
    <Head title="Verify OTP" />

    <AuthenticatedLayout>
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="otp" value="Enter OTP" />

                <TextInput
                    id="otp"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.otp"
                    required
                />

                <InputError class="mt-2" :message="form.errors.otp" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton :disabled="form.processing">
                    Verify OTP
                </PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
