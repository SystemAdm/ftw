<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const step = ref(1);
const maxStep = 4;

const firstName = ref('');
const lastName = ref('');
const birthday = ref(''); // yyyy-mm-dd
const postalCode = ref('');
const email = ref('');
const phone = ref('');

const tosAccepted = ref(false);

const canPrev = computed(() => step.value > 1);
const canNext = computed(() => step.value < maxStep);

function next() {
    if (step.value < maxStep) step.value++;
}
function prev() {
    if (step.value > 1) step.value--;
}

function calculateAge(dateString?: string) {
    if (!dateString) return undefined;
    const today = new Date();
    const dob = new Date(dateString);
    if (Number.isNaN(dob.getTime())) return undefined;
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    return age;
}

const isMinor = computed(() => {
    const age = calculateAge(birthday.value);
    return typeof age === 'number' && age < 18;
});

// Guardian fields (only if minor)
const guardianFirstName = ref('');
const guardianLastName = ref('');
const guardianPhone = ref('');
const guardianEmail = ref('');
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details in a few quick steps">
        <Head title="Register" />

        <Form
            v-bind="RegisteredUserController.store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing, submit }"
            class="flex flex-col gap-6"
        >
            <!-- Hidden combined name that backend expects -->
            <input type="hidden" name="name" :value="`${firstName} ${lastName}`.trim()" />

            <!-- Step indicators -->
            <div class="flex items-center justify-center gap-2 text-xs text-muted-foreground">
                <div :class="['h-1.5 w-12 rounded-full', step >= 1 ? 'bg-primary' : 'bg-muted']"></div>
                <div :class="['h-1.5 w-12 rounded-full', step >= 2 ? 'bg-primary' : 'bg-muted']"></div>
                <div :class="['h-1.5 w-12 rounded-full', step >= 3 ? 'bg-primary' : 'bg-muted']"></div>
                <div :class="['h-1.5 w-12 rounded-full', step >= 4 ? 'bg-primary' : 'bg-muted']"></div>
            </div>

            <!-- Step 1: Personal info -->
            <div v-show="step === 1" class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="first_name">First name</Label>
                    <Input id="first_name" type="text" required autofocus tabindex="1" name="first_name" v-model="firstName" placeholder="First name" />
                </div>
                <div class="grid gap-2">
                    <Label for="last_name">Last name</Label>
                    <Input id="last_name" type="text" required tabindex="2" name="last_name" v-model="lastName" placeholder="Last name" />
                </div>
                <div class="grid gap-2">
                    <Label for="birthday">Birthday</Label>
                    <Input id="birthday" type="date" required tabindex="3" name="birthday" v-model="birthday" />
                </div>
                <div class="grid gap-2">
                    <Label for="postal_code">Postal code</Label>
                    <Input id="postal_code" type="text" tabindex="4" name="postal_code" v-model="postalCode" placeholder="e.g. 12345" />
                </div>
            </div>

            <!-- Step 2: Contact -->
            <div v-show="step === 2" class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" :required="true" tabindex="5" autocomplete="email" name="email" v-model="email" placeholder="email@example.com" />
                    <InputError :message="errors.email" />
                </div>
                <div class="grid gap-2">
                    <Label for="phone">Phone number</Label>
                    <Input id="phone" type="tel" tabindex="6" name="phone" v-model="phone" placeholder="e.g. +62812..." />
                </div>
            </div>

            <!-- Step 3: Security (password is optional; will be generated if left blank) -->
            <div v-show="step === 3" class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="password">Password <span class="text-xs text-muted-foreground">(optional)</span></Label>
                    <Input id="password" type="password" tabindex="7" autocomplete="new-password" name="password" placeholder="Password" />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        tabindex="8"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>
            </div>

            <!-- Step 4: Agreements and guardian (if minor) -->
            <div v-show="step === 4" class="grid gap-6">
                <div class="flex items-start gap-3">
                    <Checkbox id="tos" v-model:checked="tosAccepted" />
                    <div class="grid gap-1">
                        <Label for="tos" class="cursor-pointer">I agree to the Terms of Service</Label>
                        <p class="text-xs text-muted-foreground">You must agree before creating your account.</p>
                    </div>
                </div>

                <div v-if="isMinor" class="rounded-md border p-4">
                    <div class="mb-2 text-sm font-medium">Guardian information (required for minors)</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="guardian_first_name">Guardian first name</Label>
                            <Input id="guardian_first_name" type="text" name="guardian_first_name" v-model="guardianFirstName" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="guardian_last_name">Guardian last name</Label>
                            <Input id="guardian_last_name" type="text" name="guardian_last_name" v-model="guardianLastName" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="guardian_phone">Guardian phone</Label>
                            <Input id="guardian_phone" type="tel" name="guardian_phone" v-model="guardianPhone" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="guardian_email">Guardian email</Label>
                            <Input id="guardian_email" type="email" name="guardian_email" v-model="guardianEmail" required />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="mt-2 grid grid-cols-2 gap-3">
                <Button type="button" variant="outline" :disabled="!canPrev" @click="prev">Back</Button>
                <Button v-if="canNext" type="button" @click="next">Next</Button>
                <Button v-else type="submit" class="col-span-2 w-full" :disabled="processing || !tosAccepted" @click.prevent="submit">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="login()" class="underline underline-offset-4">Log in</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
