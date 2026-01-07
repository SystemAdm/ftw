<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Field, FieldDescription, FieldGroup, FieldLabel, FieldSeparator } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { InputOTP, InputOTPGroup, InputOTPSeparator, InputOTPSlot } from '@/components/ui/input-otp';
import { cn } from '@/lib/utils';
import { faGoogle } from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed, HTMLAttributes, ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { terms as showTerms, privacy as showPrivacy, logout as logoutAction } from '@/routes';
import { lookup } from '@/routes/auth/users';
import { store as loginStore } from '@/routes/login';
import { store as registerStore } from '@/routes/register';
import { request as create } from '@/routes/password';
import { send, verify } from '@/routes/register/otp';
import { verifyPin as verifyPinAction } from '@/routes/verification';
import { google as redirectTo } from '@/routes/social';
import { useForm, usePage, router, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    initialStep?: number;
}>();

const step = ref<number>(props.initialStep ?? 1);
const history = ref<number[]>([]);
const query = ref('');
const isSearching = ref(false);
const searchError = ref('');
const users = ref<any[]>([]);
const matchType = ref<string>('email');
const formattedPhone = ref<string | null>(null);
const selectedUser = ref<any>(null);
const password = ref('');
const name = ref('');
const email = ref('');
const phone = ref('');
const pin = ref('');
const birthday = ref('');
const postalCode = ref('');
const guardianContact = ref('');
const guardianFound = ref<any>(null);
const isSearchingGuardian = ref(false);
const guardianError = ref('');
const invitedBy = ref<any>(null);
const relationship = ref('');

const isSendingOtp = ref(false);
const isVerifyingOtp = ref(false);
const otpError = ref('');

const allowNewUsers = ref(false);
const multipleUsersPerPhone = ref(false);

const page = usePage();
const custom = (page.props as any).custom;
if (custom) {
    allowNewUsers.value = custom.allow_new_users;
    multipleUsersPerPhone.value = custom.multiple_users_per_phone;
}

const authUser = (page.props as any).auth?.user;
if (authUser && !authUser.email_verified_at && step.value === 1) {
    step.value = 10;
}

const needsGuardian = computed(() => {
    if (!birthday.value) return false;
    const birthDate = new Date(birthday.value);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age < (custom?.user_younger_than_need_guardian ?? 16);
});

const isGuardian = computed(() => {
    if (!invitedBy.value || !birthday.value) return false;
    const birthDate = new Date(birthday.value);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age >= (custom?.guardian_user_must_be_older_than ?? 25);
});

function next(i = 1): void {
    if (step.value === 6) {
        if (needsGuardian.value) {
            history.value.push(step.value);
            step.value = 11; // Go to guardian contact step
            return;
        } else if (isGuardian.value) {
            history.value.push(step.value);
            step.value = 12; // Go to relationship confirmation step
            return;
        }
    }
    if (step.value === 11 || step.value === 12) {
        history.value.push(step.value);
        step.value = 7; // Both lead to Postal Code
        return;
    }
    history.value.push(step.value);
    step.value = step.value + i;
}

function back(): void {
    if (history.value.length > 0) {
        step.value = history.value.pop()!;
    }
}

async function search(): Promise<void> {
    if (!query.value) return;

    isSearching.value = true;
    searchError.value = '';
    try {
        const response = await axios.get(lookup.url(), {
            params: { q: query.value },
        });
        users.value = response.data.users;
        matchType.value = response.data.matchType;
        formattedPhone.value = response.data.formattedPhone;
        invitedBy.value = response.data.invitedBy;

        if (users.value.length === 1 && (matchType.value !== 'phone' || !multipleUsersPerPhone.value)) {
            selectUser(users.value[0]);
        } else if (users.value.length > 0) {
            next(1); // Go to Step 2: Select Account
        } else if (allowNewUsers.value) {
            goToRegister();
        } else {
            history.value.push(step.value);
            step.value = 9; // Go to Step 9: No Results
        }
    } catch (error: any) {
        if (error.response?.status === 422) {
            searchError.value = error.response.data.errors?.q?.[0] || error.response.data.message;
        } else {
            console.error('Search failed', error);
        }
    } finally {
        isSearching.value = false;
    }
}

function selectUser(user: any): void {
    selectedUser.value = user;
    if (user.hasPassword) {
        history.value.push(step.value);
        step.value = 3; // Go to password step
    } else if (user.hasGoogle) {
        // Maybe suggest Google login?
        goToSocialiteGoogle();
    } else {
        // No password, maybe forgot password or something?
        // For now just stay or go to a forgot password step if implemented
    }
}

function loginWithPassword(): void {
    const form = useForm({
        email: selectedUser.value.email,
        password: password.value,
        remember: true,
    });

    form.post(loginStore.url(), {
        onFinish: () => (password.value = ''),
    });
}

const registrationForm = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    birthday: '',
    postal_code: '',
    guardian_contact: '',
    relationship: '',
});

function goToRegister(): void {
    name.value = '';
    email.value = query.value.includes('@') ? query.value : '';
    phone.value = formattedPhone.value || (!query.value.includes('@') ? query.value : '');
    history.value.push(step.value);
    step.value = 4;
}

async function sendOtp(): Promise<void> {
    isSendingOtp.value = true;
    otpError.value = '';
    try {
        await axios.post(send.url(), {
            email: email.value,
            phone: phone.value,
        });
        next(); // Go to Step 5: Verify OTP
    } catch (error: any) {
        otpError.value = error.response?.data?.message || 'Failed to send OTP';
    } finally {
        isSendingOtp.value = false;
    }
}

async function verifyRegistrationOtp(): Promise<void> {
    isVerifyingOtp.value = true;
    otpError.value = '';
    try {
        await axios.post(verify.url(), {
            pin: pin.value,
        });
        next(); // Go to Step 6: Birthdate
    } catch (error: any) {
        otpError.value = error.response?.data?.errors?.pin?.[0] || error.response?.data?.message || 'Invalid OTP';
    } finally {
        isVerifyingOtp.value = false;
    }
}

async function searchGuardian(): Promise<void> {
    if (!guardianContact.value) return;

    isSearchingGuardian.value = true;
    guardianError.value = '';
    guardianFound.value = null;
    try {
        const response = await axios.get(lookup.url(), {
            params: { q: guardianContact.value },
        });
        const users = response.data.users;
        if (users.length > 0) {
            guardianFound.value = users[0];
        }
        next();
    } catch (error: any) {
        if (error.response?.status === 422) {
            guardianError.value = error.response.data.errors?.q?.[0] || error.response.data.message;
        } else {
            console.error('Guardian search failed', error);
        }
    } finally {
        isSearchingGuardian.value = false;
    }
}

function register(): void {
    registrationForm.name = name.value;
    registrationForm.email = email.value;
    registrationForm.phone = phone.value;
    registrationForm.password = password.value;
    registrationForm.password_confirmation = password.value;
    registrationForm.birthday = birthday.value;
    registrationForm.postal_code = postalCode.value;
    registrationForm.guardian_contact = guardianContact.value;
    registrationForm.relationship = relationship.value;

    registrationForm.post(registerStore.url(), {
        onFinish: () => {
            password.value = '';
            registrationForm.password = '';
            registrationForm.password_confirmation = '';
        },
    });
}

const verificationForm = useForm({
    pin: '',
});

function verifyPin(): void {
    verificationForm.pin = pin.value;
    verificationForm.post(verifyPinAction.url(), {
        onSuccess: () => {
            pin.value = '';
        },
    });
}

function goToSocialiteGoogle(): void {
    // Use a full page redirect instead of an XHR visit to avoid CORS/preflight issues
    window.location.href = redirectTo.url();
}
</script>

<template>
    <div :class="cn('flex flex-col gap-6', props.class)">
        <Card class="overflow-hidden p-0">
            <CardContent class="grid p-0" v-if="step === 1">
                <form class="p-6 md:p-8" v-on:submit.prevent="search">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.search_account') }}</h1>
                        </div>
                        <Field class="flex justify-center">
                            <Button variant="outline" type="button" @click.prevent="goToSocialiteGoogle">
                                <FontAwesomeIcon :icon="faGoogle" />
                                <span>{{ trans('pages.auth.login.continue_google') }}</span>
                            </Button>
                        </Field>
                        <FieldSeparator class="*:data-[slot=field-separator-content]:bg-card"> {{ trans('pages.auth.login.or') }} </FieldSeparator>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <p class="text-sm text-balance text-muted-foreground">
                                {{ trans('pages.auth.login.description') }}
                            </p>
                        </div>
                        <Field>
                            <FieldLabel for="input"> {{ trans('pages.auth.login.input_label') }} </FieldLabel>
                            <Input id="input" v-model="query" type="text" :placeholder="trans('pages.auth.login.input_placeholder')" required />
                            <div v-if="searchError" class="mt-1 text-sm text-red-600">{{ searchError }}</div>
                        </Field>
                        <Field>
                            <Button type="submit" :disabled="isSearching">
                                {{ isSearching ? trans('pages.auth.login.messages.searching') : trans('pages.auth.login.find_account') }}
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>
            </CardContent>

            <!-- Step 2: Select Account -->
            <CardContent class="grid p-0" v-if="step === 2">
                <div class="space-y-4 p-6 md:p-8">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.select_account') }}</h1>
                        <p class="text-sm text-muted-foreground">{{ trans('pages.auth.login.multiple_found') }}</p>
                    </div>
                    <div class="max-h-64 space-y-2 overflow-y-auto pr-1">
                        <Button
                            v-for="user in users"
                            :key="user.id"
                            variant="outline"
                            class="h-auto w-full justify-start p-4"
                            @click="selectUser(user)"
                        >
                            <div class="flex flex-col items-start">
                                <span class="font-bold">{{ user.name }}</span>
                                <span class="text-xs text-muted-foreground">{{ user.email }}</span>
                            </div>
                        </Button>
                    </div>

                    <div v-if="matchType === 'phone' && multipleUsersPerPhone" class="border-t pt-2">
                        <Button variant="default" class="w-full" @click="goToRegister">
                            {{ trans('pages.auth.login.create_new_for_phone') }}
                        </Button>
                    </div>

                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>

            <!-- Step 3: Password -->
            <CardContent class="grid p-0" v-if="step === 3">
                <div class="space-y-4 p-6 md:p-8">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.welcome_back', { name: selectedUser?.name }) }}</h1>
                        <p class="text-sm text-muted-foreground">{{ trans('pages.auth.login.enter_password') }}</p>
                    </div>
                    <form class="space-y-4" @submit.prevent="loginWithPassword">
                        <Field>
                            <FieldLabel for="password">{{ trans('pages.auth.login.password_label') }}</FieldLabel>
                            <Input id="password" type="password" v-model="password" required />
                        </Field>
                        <Button type="submit" class="w-full">{{ trans('pages.auth.login.login_button') }}</Button>
                    </form>
                    <div class="mt-4 text-center">
                        <Link :href="create.url()" class="text-sm text-muted-foreground hover:underline">
                            {{ trans('pages.auth.forgot_password.title') }}
                        </Link>
                    </div>
                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>

            <!-- Step 4: Register - Initial -->
            <CardContent class="grid p-0" v-if="step === 4">
                <div class="space-y-4 p-6 md:p-8">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.create_account') }}</h1>
                        <p class="text-sm text-muted-foreground" v-if="query">
                            {{ trans('pages.auth.login.no_account_found', { query: formattedPhone || query }) }}
                        </p>
                    </div>
                    <form class="space-y-4" @submit.prevent="sendOtp">
                        <Field>
                            <FieldLabel for="name">{{ trans('pages.auth.login.name_label') }}</FieldLabel>
                            <Input id="name" type="text" v-model="name" required />
                            <div v-if="registrationForm.errors.name" class="text-sm text-red-600">{{ registrationForm.errors.name }}</div>
                        </Field>
                        <Field>
                            <FieldLabel for="email">{{ trans('pages.auth.login.email_label') }}</FieldLabel>
                            <Input id="email" type="email" v-model="email" required />
                            <div v-if="registrationForm.errors.email" class="text-sm text-red-600">{{ registrationForm.errors.email }}</div>
                        </Field>
                        <Field>
                            <FieldLabel for="phone">{{ trans('pages.auth.login.phone_label') }}</FieldLabel>
                            <Input id="phone" type="text" v-model="phone" />
                            <div v-if="registrationForm.errors.phone" class="text-sm text-red-600">{{ registrationForm.errors.phone }}</div>
                        </Field>
                        <Field>
                            <FieldLabel for="reg_password">{{ trans('pages.auth.login.password_label') }}</FieldLabel>
                            <Input id="reg_password" type="password" v-model="password" required />
                            <div v-if="registrationForm.errors.password" class="text-sm text-red-600">{{ registrationForm.errors.password }}</div>
                        </Field>
                        <Button type="submit" class="w-full" :disabled="isSendingOtp">
                            {{ isSendingOtp ? trans('pages.auth.login.messages.sending_otp') : trans('pages.auth.login.send_otp_button') }}
                        </Button>
                    </form>
                    <div v-if="otpError" class="text-center text-sm text-red-600">{{ otpError }}</div>
                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>

            <!-- Step 5: OTP Verification -->
            <CardContent class="grid p-0" v-if="step === 5">
                <div class="space-y-6 p-6 text-center md:p-8">
                    <div class="flex flex-col items-center gap-2">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.verify_email_title') }}</h1>
                        <p class="text-sm text-muted-foreground">
                            {{ trans('pages.auth.login.verify_email_desc', { email: email }) }}
                        </p>
                    </div>

                    <form @submit.prevent="verifyRegistrationOtp" class="flex flex-col items-center gap-6">
                        <InputOTP v-model="pin" :maxlength="6">
                            <InputOTPGroup>
                                <InputOTPSlot :index="0" />
                                <InputOTPSlot :index="1" />
                                <InputOTPSlot :index="2" />
                            </InputOTPGroup>
                            <InputOTPSeparator />
                            <InputOTPGroup>
                                <InputOTPSlot :index="3" />
                                <InputOTPSlot :index="4" />
                                <InputOTPSlot :index="5" />
                            </InputOTPGroup>
                        </InputOTP>

                        <div v-if="otpError" class="text-sm text-red-600">
                            {{ otpError }}
                        </div>

                        <Button type="submit" class="w-full" :disabled="isVerifyingOtp || pin.length < 6">
                            {{ isVerifyingOtp ? trans('pages.auth.login.messages.verifying') : trans('pages.auth.login.verify_button') }}
                        </Button>
                    </form>
                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>

            <!-- Step 6: Birthdate -->
            <CardContent class="grid p-0" v-if="step === 6">
                <div class="space-y-4 p-6 md:p-8">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.set_birthday') }}</h1>
                        <p class="text-sm text-muted-foreground">{{ trans('pages.auth.login.birthday_desc') }}</p>
                    </div>
                    <form class="space-y-4" @submit.prevent="next()">
                        <Field>
                            <FieldLabel for="birthday">{{ trans('pages.auth.login.birthday_label') }}</FieldLabel>
                            <Input id="birthday" type="date" v-model="birthday" required />
                        </Field>
                        <Button type="submit" class="w-full">{{ trans('pages.auth.login.next_button') }}</Button>
                    </form>
                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>

            <!-- Step 7: Postal Code -->
            <CardContent class="grid p-0" v-if="step === 7">
                <div class="space-y-4 p-6 md:p-8">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.set_postal_code') }}</h1>
                        <p class="text-sm text-muted-foreground">{{ trans('pages.auth.login.postal_code_desc') }}</p>
                    </div>
                    <form class="space-y-4" @submit.prevent="next()">
                        <Field>
                            <FieldLabel for="postal_code">{{ trans('pages.auth.login.postal_code_label') }}</FieldLabel>
                            <Input id="postal_code" type="text" v-model="postalCode" required />
                        </Field>
                        <Button type="submit" class="w-full">{{ trans('pages.auth.login.next_button') }}</Button>
                    </form>
                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>

            <!-- Step 8: Summary -->
            <CardContent class="grid p-0" v-if="step === 8">
                <div class="space-y-4 p-6 md:p-8">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.summary_title') }}</h1>
                        <p class="text-sm text-muted-foreground">{{ trans('pages.auth.login.summary_desc') }}</p>
                    </div>
                    <div class="space-y-2 rounded-md border p-4 text-sm">
                        <div class="flex justify-between">
                            <span class="font-semibold">{{ trans('pages.auth.login.name_label') }}:</span>
                            <span>{{ name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">{{ trans('pages.auth.login.email_label') }}:</span>
                            <span>{{ email }}</span>
                        </div>
                        <div v-if="phone" class="flex justify-between">
                            <span class="font-semibold">{{ trans('pages.auth.login.phone_label') }}:</span>
                            <span>{{ phone }}</span>
                        </div>
                        <div v-if="guardianContact" class="flex justify-between">
                            <span class="font-semibold">{{ trans('pages.auth.login.guardian_label') }}:</span>
                            <div class="text-right">
                                <div>{{ guardianContact }}</div>
                                <div v-if="guardianFound" class="text-xs text-muted-foreground">{{ guardianFound.name }}</div>
                                <div v-else class="text-xs text-muted-foreground">{{ trans('pages.auth.login.guardian_not_found_note') }}</div>
                            </div>
                        </div>
                        <div v-if="relationship" class="flex justify-between">
                            <span class="font-semibold">{{ trans('pages.auth.login.relationship_label') }}:</span>
                            <span>{{ relationship }}</span>
                        </div>
                        <div v-if="invitedBy" class="flex justify-between">
                            <span class="font-semibold">{{ trans('pages.auth.login.guardian_for_label') }}:</span>
                            <span>{{ invitedBy.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">{{ trans('pages.auth.login.birthday_label') }}:</span>
                            <span>{{ birthday }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">{{ trans('pages.auth.login.postal_code_label') }}:</span>
                            <span>{{ postalCode }}</span>
                        </div>
                    </div>
                    <form class="space-y-4" @submit.prevent="register">
                        <div v-if="Object.keys(registrationForm.errors).length > 0" class="rounded-md bg-destructive/15 p-3">
                            <ul class="list-inside list-disc text-sm text-destructive">
                                <li v-for="(error, field) in registrationForm.errors" :key="field">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                        <Button type="submit" class="w-full" :disabled="registrationForm.processing">
                            {{
                                registrationForm.processing
                                    ? trans('pages.auth.login.messages.registering')
                                    : trans('pages.auth.login.confirm_registration')
                            }}
                        </Button>
                    </form>
                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>

            <!-- Step 9: No Results -->
            <CardContent class="grid p-0" v-if="step === 9">
                <div class="space-y-4 p-6 text-center md:p-8">
                    <div class="flex flex-col items-center gap-2">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.no_records_found_title') }}</h1>
                        <p class="text-sm text-muted-foreground" v-if="!allowNewUsers">
                            {{ trans('pages.auth.login.no_records_found_desc', { query: formattedPhone || query }) }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-else>
                            {{ trans('pages.auth.login.no_account_found', { query: formattedPhone || query }) }}
                        </p>
                    </div>
                    <div v-if="allowNewUsers" class="pt-2">
                        <Button variant="default" class="w-full" @click="goToRegister">
                            {{ trans('pages.auth.login.create_account') }}
                        </Button>
                    </div>
                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>

            <!-- Step 10: Email Verification (PIN) for logged in users -->
            <CardContent class="grid p-0" v-if="step === 10">
                <div class="space-y-6 p-6 text-center md:p-8">
                    <div class="flex flex-col items-center gap-2">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.verify_email_title') }}</h1>
                        <p class="text-sm text-muted-foreground">
                            {{ trans('pages.auth.login.verify_email_desc', { email: authUser?.email }) }}
                        </p>
                    </div>

                    <form @submit.prevent="verifyPin" class="flex flex-col items-center gap-6">
                        <InputOTP v-model="pin" :maxlength="6">
                            <InputOTPGroup>
                                <InputOTPSlot :index="0" />
                                <InputOTPSlot :index="1" />
                                <InputOTPSlot :index="2" />
                            </InputOTPGroup>
                            <InputOTPSeparator />
                            <InputOTPGroup>
                                <InputOTPSlot :index="3" />
                                <InputOTPSlot :index="4" />
                                <InputOTPSlot :index="5" />
                            </InputOTPGroup>
                        </InputOTP>

                        <div v-if="verificationForm.errors.pin" class="text-sm text-red-600">
                            {{ verificationForm.errors.pin }}
                        </div>

                        <Button type="submit" class="w-full" :disabled="verificationForm.processing || pin.length < 6">
                            {{ verificationForm.processing ? trans('pages.auth.login.messages.verifying') : trans('pages.auth.login.verify_button') }}
                        </Button>
                    </form>

                    <form @submit.prevent="router.post(logoutAction.url())">
                        <Button variant="ghost" class="w-full" type="submit">
                            {{ trans('pages.auth.login.logout_button') }}
                        </Button>
                    </form>
                </div>
            </CardContent>

            <!-- Step 11: Guardian Contact -->
            <CardContent class="grid p-0" v-if="step === 11">
                <div class="space-y-4 p-6 md:p-8">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.set_guardian') }}</h1>
                        <p class="text-sm text-muted-foreground">{{ trans('pages.auth.login.guardian_desc') }}</p>
                    </div>
                    <form class="space-y-4" @submit.prevent="searchGuardian">
                        <Field>
                            <FieldLabel for="guardian_contact">{{ trans('pages.auth.login.guardian_label') }}</FieldLabel>
                            <Input
                                id="guardian_contact"
                                type="text"
                                v-model="guardianContact"
                                :placeholder="trans('pages.auth.login.guardian_placeholder')"
                                required
                            />
                            <div v-if="guardianError" class="mt-1 text-sm text-red-600">{{ guardianError }}</div>
                        </Field>
                        <Field>
                            <FieldLabel for="minor_relationship">{{ trans('pages.auth.login.relationship_label') }}</FieldLabel>
                            <Input
                                id="minor_relationship"
                                type="text"
                                v-model="relationship"
                                :placeholder="trans('pages.auth.login.relationship_placeholder')"
                                required
                            />
                        </Field>
                        <Button type="submit" class="w-full" :disabled="isSearchingGuardian">
                            {{ isSearchingGuardian ? trans('pages.auth.login.messages.searching') : trans('pages.auth.login.next_button') }}
                        </Button>
                    </form>
                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>

            <!-- Step 12: Relationship Confirmation -->
            <CardContent class="grid p-0" v-if="step === 12">
                <div class="space-y-4 p-6 md:p-8">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.login.confirm_relationship_title') }}</h1>
                        <p class="text-sm text-muted-foreground">
                            {{ trans('pages.auth.login.confirm_relationship_desc', { name: invitedBy?.name || guardianFound?.name }) }}
                        </p>
                    </div>
                    <form class="space-y-4" @submit.prevent="next()">
                        <Field>
                            <FieldLabel for="relationship">{{ trans('pages.auth.login.relationship_label') }}</FieldLabel>
                            <Input
                                id="relationship"
                                type="text"
                                v-model="relationship"
                                :placeholder="trans('pages.auth.login.relationship_placeholder')"
                                required
                            />
                            <div v-if="registrationForm.errors.relationship" class="text-sm text-red-600">
                                {{ registrationForm.errors.relationship }}
                            </div>
                        </Field>
                        <Button type="submit" class="w-full">{{ trans('pages.auth.login.next_button') }}</Button>
                    </form>
                    <Button variant="ghost" class="w-full" @click="back">{{ trans('pages.auth.login.back_button') }}</Button>
                </div>
            </CardContent>
        </Card>
        <FieldDescription class="px-6 text-center" v-if="step !== 10">
            <span
                v-html="
                    trans('pages.auth.login.legal', {
                        terms: `<a href='${showTerms.url()}' class='underline hover:text-foreground'>${trans('pages.auth.login.terms')}</a>`,
                        privacy: `<a href='${showPrivacy.url()}' class='underline hover:text-foreground'>${trans('pages.auth.login.privacy')}</a>`,
                    })
                "
            ></span>
        </FieldDescription>
    </div>
</template>

<style scoped></style>
