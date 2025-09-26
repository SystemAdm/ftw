<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';

const props = defineProps<{
    step?: number;
    mode?: 'select' | 'create';
    candidates?: Array<{ id: number; name: string; email?: string; username?: string; avatar?: string }>;
    identifier?: string;
    canCreate?: boolean;
}>();
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head, router } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const stepLocal = ref(1);
const maxStep = 4;

const firstName = ref('');
const lastName = ref('');
const birthday = ref(''); // yyyy-mm-dd
const postalCode = ref('');
const identifierLocal = ref(props?.identifier ?? '');

const tosAccepted = ref(false);

const canPrev = computed(() => stepLocal.value > 1);
const canNext = computed(() => stepLocal.value < maxStep);

function next() {
    if (stepLocal.value === 1) {
        router.post('/register/identify', { identifier: identifierLocal.value });
        return; // Wait for server to navigate to step 2 (duplicate check)
    }
    if (stepLocal.value < maxStep) stepLocal.value++;
}
function prev() {
    if (stepLocal.value > 1) stepLocal.value--;
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

// Lightweight client-side validations per step
const emailRegex = /.+@.+\..+/;
const postalRegex = /^\d{4,6}$/;

const isStep1Valid = computed(() => {
    // Step 1: one combined field. If it contains '@', treat as email; otherwise treat as phone.
    const val = identifierLocal.value.trim();
    if (!val) return false;
    if (val.includes('@')) {
        return emailRegex.test(val);
    }
    // For phone, do minimal check: at least 7 digits; let backend do strict validation.
    const digits = val.replace(/\D/g, '');
    return digits.length >= 7;
});

const isStep2Valid = computed(() => {
    // Step 2: personal info
    const age = calculateAge(birthday.value);
    const namesOk = firstName.value.trim().length > 0 && lastName.value.trim().length > 0;
    const birthdayOk = typeof age === 'number' && age >= 10;
    const postalOk = postalCode.value.trim() === '' || postalRegex.test(postalCode.value.trim());
    return namesOk && birthdayOk && postalOk;
});

const isStep3Valid = computed(() => {
    const pwd = (document.getElementById('password') as HTMLInputElement | null)?.value ?? '';
    const pwd2 = (document.getElementById('password_confirmation') as HTMLInputElement | null)?.value ?? '';
    // Password optional; if provided, must match confirmation
    if (!pwd && !pwd2) return true;
    return pwd.length > 0 && pwd === pwd2;
});

const isGuardianBlockValid = computed(() => {
    if (!isMinor.value) return true;
    const fn = guardianFirstName.value.trim().length > 0;
    const ln = guardianLastName.value.trim().length > 0;
    const hasEmail = guardianEmail.value.trim().length > 0;
    const hasPhone = guardianPhone.value.trim().length > 0;
    const gEmailOk = !hasEmail || emailRegex.test(guardianEmail.value.trim());
    const eitherContact = (hasEmail && gEmailOk) || hasPhone;
    return fn && ln && eitherContact;
});

const canProceed = computed(() => {
    switch (stepLocal.value) {
        case 1:
            return isStep1Valid.value;
        case 2:
            return isStep2Valid.value;
        case 3:
            return isStep3Valid.value;
        default:
            return true;
    }
});

const canSubmitFinal = computed(() => {
    return tosAccepted.value && isGuardianBlockValid.value;
});
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details in a few quick steps">
        <Head title="Register" />

        <!-- Server-driven Step 2 (duplicate check) -->
        <div v-if="props.step === 2" class="flex flex-col gap-6">
            <template v-if="props.mode === 'select' && props.candidates?.length">
                <div class="space-y-2">
                    <div class="text-sm text-muted-foreground">
                        We found existing account(s) for this identifier. Choose one to continue to login, or create a new account if allowed.
                    </div>
                    <div class="flex flex-col gap-3">
                        <Form :action="'/login/identify'" method="post" class="flex flex-col gap-3">
                            <template v-for="u in props.candidates" :key="u.id">
                                <button
                                    type="submit"
                                    class="flex w-full items-center justify-between rounded-md border p-3 text-left hover:bg-accent"
                                    name="identifier"
                                    :value="props.identifier || ''"
                                >
                                    <div>
                                        <div class="font-medium">{{ u.name }}</div>
                                        <div class="text-xs text-muted-foreground">
                                            <span v-if="u.username">@{{ u.username }}</span>
                                            <span v-if="u.email">{{ u.email }}</span>
                                        </div>
                                    </div>
                                    <div class="text-xs">Continue to login</div>
                                </button>
                            </template>
                        </Form>
                        <div v-if="props.canCreate">
                            <Button type="button" variant="outline" class="w-full" @click="stepLocal = 2">Create a new user instead</Button>
                        </div>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="space-y-2">
                    <div class="text-sm text-muted-foreground">No existing account found.</div>
                    <div v-if="props.canCreate">
                        <Button type="button" class="w-full" @click="stepLocal = 2">Create a new user</Button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Default client-side multi-step registration flow -->
        <Form
            v-else
            v-bind="RegisteredUserController.store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing, submit }"
            class="flex flex-col gap-6"
        >
            <!-- Hidden combined name that backend expects and contact splitter -->
            <input type="hidden" name="name" :value="`${firstName} ${lastName}`.trim()" />
            <!-- Split the single identifier into email or phone for backend -->
            <input type="hidden" name="email" :value="identifierLocal.includes('@') ? identifierLocal.trim() : ''" />
            <input type="hidden" name="phone" :value="!identifierLocal.includes('@') ? identifierLocal.trim() : ''" />

            <!-- Step indicators -->
            <div class="flex items-center justify-center gap-2 text-xs text-muted-foreground">
                <div :class="['h-1.5 w-12 rounded-full', stepLocal >= 1 ? 'bg-primary' : 'bg-muted']"></div>
                <div :class="['h-1.5 w-12 rounded-full', stepLocal >= 2 ? 'bg-primary' : 'bg-muted']"></div>
                <div :class="['h-1.5 w-12 rounded-full', stepLocal >= 3 ? 'bg-primary' : 'bg-muted']"></div>
                <div :class="['h-1.5 w-12 rounded-full', stepLocal >= 4 ? 'bg-primary' : 'bg-muted']"></div>
            </div>

            <!-- Step 1: Contact method (single identifier) -->
            <div v-show="stepLocal === 1" class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="identifier">Email or phone</Label>
                    <Input
                        id="identifier"
                        type="text"
                        tabindex="1"
                        name="identifier"
                        v-model="identifierLocal"
                        placeholder="Enter email or phone (you don't need both)"
                        autocomplete="email tel"
                    />
                    <InputError :message="errors.email || errors.phone" />
                </div>

                <div class="text-xs text-muted-foreground">You can provide either an email address or a phone number. You don't need both.</div>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t"></span>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-background px-2 text-muted-foreground">Or continue with</span>
                    </div>
                </div>
                <div>
                    <Button as="a" :href="'/auth/google'" variant="outline" class="w-full">Register with Google</Button>
                </div>
            </div>

            <!-- Step 2: Personal info -->
            <div v-show="stepLocal === 2" class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="first_name">First name</Label>
                    <Input
                        id="first_name"
                        type="text"
                        required
                        autofocus
                        tabindex="3"
                        name="first_name"
                        v-model="firstName"
                        placeholder="First name"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="last_name">Last name</Label>
                    <Input id="last_name" type="text" required tabindex="4" name="last_name" v-model="lastName" placeholder="Last name" />
                </div>
                <div class="grid gap-2">
                    <Label for="birthday">Birthday</Label>
                    <Input id="birthday" type="date" required tabindex="5" name="birthday" v-model="birthday" />
                </div>
                <div class="grid gap-2">
                    <Label for="postal_code">Postal code</Label>
                    <Input id="postal_code" type="text" tabindex="6" name="postal_code" v-model="postalCode" placeholder="e.g. 12345" />
                </div>
            </div>

            <!-- Step 3: Security (password is optional; will be generated if left blank) -->
            <div v-show="stepLocal === 3" class="grid gap-6">
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
            <div v-show="stepLocal === 4" class="grid gap-6">
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
                <Button v-if="canNext" type="button" :disabled="!canProceed" @click="next">Next</Button>
                <Button v-else type="submit" class="col-span-2 w-full" :disabled="processing || !canSubmitFinal" @click.prevent="submit">
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
