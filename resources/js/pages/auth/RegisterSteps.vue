<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { User as UserIcon, Calendar as CalendarIcon } from 'lucide-vue-next';
import { Checkbox } from '@/components/ui/checkbox';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarHeader,
    CalendarHeading,
} from '@/components/ui/calendar';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { CalendarDate, DateValue, getLocalTimeZone, today, parseDate } from '@internationalized/date';
import { CalendarRoot, useDateFormatter } from 'reka-ui';
import { createYear, toDate } from 'reka-ui/date';
import IdentifierStep from '@/components/auth/IdentifierStep.vue';
import CandidateSelectStep from '@/components/auth/CandidateSelectStep.vue';
import { NumberField, NumberFieldContent, NumberFieldInput } from '@/components/ui/number-field';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { request as passwordRequest } from '@/routes/password';

const props = defineProps<{
    step?: number;
    googleSignup?: any;
    errorsBag?: Record<string, string>;
    status?: string;
}>();

// Ensure `step` is a number. Inertia query params often arrive as strings
const step = ref<1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9>((props.step !== undefined && props.step !== null ? (Number(props.step) as any) : 1));
const identifier = ref('');
const loading = ref(false);
const localError = ref('');
const state = reactive<{ mode: 'select' | 'create' | null; candidates: Array<any>; canCreate: boolean }>({
    mode: null,
    candidates: [],
    canCreate: false,
});

const selectedUser = ref<{ id: number; name: string; email?: string; username?: string; avatar?: string; google_id?: string } | null>(null);
const needsPassword = ref<boolean>(true);
const tosAgreed = ref<boolean>(false);

// Guardian step (step 6) state
const guardianIdentifier = ref('');
const guardianLoading = ref(false);
const guardianError = ref('');
const guardianState = reactive<{ mode: 'select' | 'create' | null; candidates: Array<any>; canCreate: boolean }>({
    mode: null,
    candidates: [],
    canCreate: false,
});
const selectedGuardian = ref<any | null>(null);

// Guardian relationship selection
const guardianRelationship = ref<string>('');
const relationshipOptions = [
    { value: 'father', label: 'Father' },
    { value: 'mother', label: 'Mother' },
    { value: 'uncle', label: 'Uncle' },
    { value: 'aunt', label: 'Aunt' },
    { value: 'grand_father', label: 'Grand father' },
    { value: 'grand_mother', label: 'Grand mother' },
    { value: 'other', label: 'Other' },
];

const guardianRelationshipLabel = computed(() => {
    const found = relationshipOptions.find((o) => o.value === guardianRelationship.value);
    return found ? found.label : '';
});

// Step 9 (summary & submit)
const summaryConfirm = ref<boolean>(false);
const submitError = ref<string>('');
const serverErrors = reactive<Record<string, string>>({});

// Step 5 fields (moved earlier to avoid use-before-declare in googleSignup prefill)
const firstName = ref<string>('');
const lastName = ref<string>('');

// If redirected from Google OAuth, prefill name from Google data
if (props.googleSignup) {
    try {
        // Prefill identifier as email from Google
        if (props.googleSignup.email) {
            identifier.value = String(props.googleSignup.email);
        }
        // Split Google display name into first/last name guesses
        const name = String(props.googleSignup.name || '').trim();
        if (name) {
            const parts = name.split(/\s+/);
            firstName.value = parts.shift() || '';
            lastName.value = parts.join(' ');
        }
    } catch {
        // ignore
    }
}

// Step 8 (guardian invite) fields
const guardianFirstName = ref<string>('');
const guardianLastName = ref<string>('');
const guardianErrors = reactive<{ firstName?: string; lastName?: string; relationship?: string }>({});

const isGuardianEmailIdentifier = computed(() => guardianIdentifier.value.includes('@'));
const detectedGuardianEmail = computed(() => (isGuardianEmailIdentifier.value ? guardianIdentifier.value.trim() : ''));
const detectedGuardianPhone = computed(() => (!isGuardianEmailIdentifier.value && guardianIdentifier.value.trim() ? guardianIdentifier.value.trim() : ''));

// Step 5 fields
const birthday = ref<string>('');
const postalCode = ref<number | null>(null);

// Step 5 validation errors
const step5Errors = reactive<{ firstName?: string; lastName?: string; birthday?: string; postalCode?: string }>({});

// Date picker state for birthday
const dob = ref<DateValue | undefined>();
const placeholder = ref<DateValue>(today(getLocalTimeZone()).subtract({ years: 10 }));
const df = new Intl.DateTimeFormat(undefined, { dateStyle: 'long' });
const formatter = useDateFormatter('en');

// Allowed year range for DOB: 1900..(today - 5 years)
const minYear = 1900;
const maxAllowedDate = computed(() => today(getLocalTimeZone()).subtract({ years: 5 }));
const maxYear = computed(() => (maxAllowedDate.value as any).year as number);
const yearOptions = computed(() => {
    const arr: number[] = [];
    const maxY = maxYear.value;
    for (let y = maxY; y >= minYear; y--) arr.push(y);
    return arr;
});

// Helper: convert @internationalized/date DateValue to JS Date for formatting
function dateValueToJS(d?: DateValue): Date {
    if (!d) return new Date();
    const anyv: any = d as any;
    if (anyv && typeof anyv.toDate === 'function') {
        try {
            return anyv.toDate(getLocalTimeZone());
        } catch {}
    }
    if (anyv && typeof anyv.year === 'number' && typeof anyv.month === 'number' && typeof anyv.day === 'number') {
        return new Date(anyv.year, anyv.month - 1, anyv.day);
    }
    return new Date();
}

watch(birthday, (val) => {
    dob.value = val ? parseDate(val) : undefined;
});

function onDobUpdate(v?: DateValue) {
    dob.value = v;
    birthday.value = v ? v.toString() : '';
}

// Determine whether the identifier looks like an email or phone and show it in Step 5
const isEmailIdentifier = computed(() => identifier.value.includes('@'));
const detectedEmail = computed(() => (isEmailIdentifier.value ? identifier.value.trim() : ''));
const detectedPhone = computed(() => (!isEmailIdentifier.value && identifier.value.trim() ? identifier.value.trim() : ''));

// Determine if user is a minor (< 18 years) based on selected date of birth
const isMinor = computed(() => {
    if (!dob.value) return false;
    const birth = dateValueToJS(dob.value);
    const now = new Date();
    let age = now.getFullYear() - birth.getFullYear();
    const monthDiff = now.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < birth.getDate())) {
        age--;
    }
    return age < 18;
});

// Compute current age in full years for display
const ageYears = computed<number | null>(() => {
    if (!dob.value) return null;
    const birth = dateValueToJS(dob.value);
    const now = new Date();
    let age = now.getFullYear() - birth.getFullYear();
    const monthDiff = now.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < birth.getDate())) {
        age--;
    }
    return age;
});

function continueFromStep5() {
    // Reset errors
    step5Errors.firstName = '';
    step5Errors.lastName = '';
    step5Errors.birthday = '';
    step5Errors.postalCode = '';

    // Validate required fields
    const first = (firstName.value || '').trim();
    const last = (lastName.value || '').trim();
    const bday = (birthday.value || '').trim();
    const pcode = postalCode.value;

    if (!first) step5Errors.firstName = 'First name is required.';
    if (!last) step5Errors.lastName = 'Last name is required.';
    if (!bday) step5Errors.birthday = 'Birthday is required.';

    const pcStr = pcode != null ? String(pcode) : '';
    if (!pcStr) {
        step5Errors.postalCode = 'Postal code is required.';
    } else if (!/^\d{4,6}$/.test(pcStr)) {
        step5Errors.postalCode = 'Postal code must be 4 to 6 digits.';
    }

    const hasErrors = !!(step5Errors.firstName || step5Errors.lastName || step5Errors.birthday || step5Errors.postalCode);
    if (hasErrors) {
        // Focus first invalid field for accessibility
        if (step5Errors.firstName) {
            document.getElementById('first_name')?.focus();
        } else if (step5Errors.lastName) {
            document.getElementById('last_name')?.focus();
        } else if (step5Errors.birthday) {
            // focus the popover trigger button (next sibling previous sibling); fallback to hidden input
            document.getElementById('birthday')?.focus();
        } else if (step5Errors.postalCode) {
            document.getElementById('postal_code')?.focus();
        }
        return;
    }

    // If the user is younger than 18, proceed to guardian step (step 6), else skip to summary (step 9)
    step.value = isMinor.value ? 6 : 9;
}

function goToCreate() {
    step.value = 4;
}

function backToStep2() {
    step.value = 2;
}

function selectCandidate(u: any) {
    selectedUser.value = {
        id: u.id,
        name: u.name,
        email: u.email,
        username: u.username,
        avatar: u.avatar,
        google_id: u.google_id,
    };
    // if candidate hasPassword explicitly false, no password is needed
    needsPassword.value = !!u.hasPassword;
    step.value = 3;
}

async function onSubmitIdentify() {
    localError.value = '';
    if (!identifier.value.trim()) {
        localError.value = 'Please enter your email or phone number.';
        return;
    }
    try {
        loading.value = true;
        const resp = await fetch(`/register/identify`, {
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            method: 'POST',
            body: JSON.stringify({ identifier: identifier.value.trim() }),
        });
        if (!resp.ok) {
            throw new Error('Failed to check identifier');
        }
        const data = await resp.json();
        // Normalize candidates keys (has_password -> hasPassword) for UI, keep others intact
        const candidates = Array.isArray(data.candidates)
            ? data.candidates.map((u: any) => ({ ...u, hasPassword: u.has_password ?? u.hasPassword }))
            : [];
        state.mode = data.mode;
        state.candidates = candidates;
        state.canCreate = !!data.canCreate;
        step.value = 2;
    } catch (err: any) {
        localError.value = err?.message || 'Something went wrong. Please try again.';
    } finally {
        loading.value = false;
    }
}

function backToStep1() {
    step.value = 1;
    state.mode = null;
    state.candidates = [];
    state.canCreate = false;
    selectedUser.value = null;
    needsPassword.value = true;
}

async function onSubmitGuardianIdentify() {
    guardianError.value = '';
    const ident = guardianIdentifier.value.trim();
    if (!ident) {
        guardianError.value = 'Please enter guardian email.';
        return;
    }
    if (!ident.includes('@')) {
        guardianError.value = 'Please enter a valid guardian email address.';
        return;
    }
    if (!guardianRelationship.value) {
        guardianError.value = 'Please select the relationship.';
        return;
    }
    try {
        guardianLoading.value = true;
        const resp = await fetch(`/register/identify`, {
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            method: 'POST',
            body: JSON.stringify({ identifier: guardianIdentifier.value.trim() }),
        });
        if (!resp.ok) throw new Error('Failed to check identifier');
        const data = await resp.json();
        const candidates = Array.isArray(data.candidates)
            ? data.candidates.map((u: any) => ({ ...u, hasPassword: u.has_password ?? u.hasPassword }))
            : [];
        guardianState.mode = data.mode;
        guardianState.candidates = candidates;
        guardianState.canCreate = !!data.canCreate;
        step.value = 7;
    } catch (err: any) {
        guardianError.value = err?.message || 'Something went wrong. Please try again.';
    } finally {
        guardianLoading.value = false;
    }
}

function selectGuardianCandidate(u: any) {
    selectedGuardian.value = u;
    step.value = 9;
}

function goToCreateGuardian() {
    step.value = 8;
}

function backToStep6() {
    step.value = 6;
}

function backToStep7() {
    step.value = 7;
}

function continueFromStep8() {
    // Reset errors
    guardianErrors.firstName = '';
    guardianErrors.lastName = '';
    guardianErrors.relationship = '';

    const first = (guardianFirstName.value || '').trim();
    const last = (guardianLastName.value || '').trim();

    if (!first) guardianErrors.firstName = 'First name is required.';
    if (!last) guardianErrors.lastName = 'Last name is required.';
    if (!guardianRelationship.value) guardianErrors.relationship = 'Please select the relationship.';

    if (guardianErrors.firstName || guardianErrors.lastName || guardianErrors.relationship) {
        if (guardianErrors.firstName) {
            document.getElementById('guardian_first_name')?.focus();
        } else if (guardianErrors.lastName) {
            document.getElementById('guardian_last_name')?.focus();
        } else {
            document.getElementById('guardian_relationship')?.focus();
        }
        return;
    }

    step.value = 9;
}

function backFromStep9() {
    if (isMinor.value) {
        if (selectedGuardian.value) {
            step.value = 7;
        } else if (guardianFirstName.value || guardianLastName.value) {
            step.value = 8;
        } else {
            step.value = 6;
        }
    } else {
        step.value = 5;
    }
}

const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

// Step 3 (login confirmation) state and handlers
const step3Password = ref<string>('');
const step3Remember = ref<boolean>(false);

function onConfirmWithPassword() {
    const uid = (selectedUser as any)?.value?.id || (selectedUser as any)?.id || null;
    router.post(
        '/login/confirm',
        { user_id: uid, password: step3Password.value, remember: step3Remember.value },
        {
            headers: { 'X-CSRF-TOKEN': csrfToken },
            preserveState: true,
            preserveScroll: true,
        }
    );
}

function onContinueNoPassword() {
    const uid = (selectedUser as any)?.value?.id || (selectedUser as any)?.id || null;
    router.post(
        '/login/confirm',
        { user_id: uid, password: '' },
        {
            headers: { 'X-CSRF-TOKEN': csrfToken },
            preserveState: true,
            preserveScroll: true,
        }
    );
}

const fullName = computed(() => `${(firstName.value || '').trim()} ${(lastName.value || '').trim()}`.trim());
const summaryEmail = computed(() => (isEmailIdentifier.value ? detectedEmail.value : ''));
const summaryPhone = computed(() => (!isEmailIdentifier.value ? detectedPhone.value : ''));

const guardianSummary = computed(() => {
    if (!isMinor.value) return null;
    if (selectedGuardian.value) {
        const name: string = selectedGuardian.value.name || '';
        const email: string = selectedGuardian.value.email || '';
        return { name, email, from: 'selected' };
    }
    const name = `${(guardianFirstName.value || '').trim()} ${(guardianLastName.value || '').trim()}`.trim();
    const email = isGuardianEmailIdentifier.value ? detectedGuardianEmail.value : '';
    const phone = !isGuardianEmailIdentifier.value ? detectedGuardianPhone.value : '';
    return { name, email, phone, from: 'invited' };
});

// Guardian final values for submission (selected or invited)
const guardianFirstNameFinal = computed(() => {
    if (!isMinor.value) return '';
    if (selectedGuardian.value) {
        const parts = String(selectedGuardian.value.name || '').trim().split(/\s+/);
        return parts[0] || '';
    }
    return (guardianFirstName.value || '').trim();
});
const guardianLastNameFinal = computed(() => {
    if (!isMinor.value) return '';
    if (selectedGuardian.value) {
        const parts = String(selectedGuardian.value.name || '').trim().split(/\s+/);
        parts.shift();
        return parts.join(' ');
    }
    return (guardianLastName.value || '').trim();
});
const guardianEmailFinal = computed(() => {
    if (!isMinor.value) return '';
    return (selectedGuardian.value?.email || (isGuardianEmailIdentifier.value ? detectedGuardianEmail.value : '')) as string;
});
</script>

<template>
    <AuthBase title="Create an account" description="Identify yourself to get started">
        <Head title="Register" />

        <!-- Display status message from backend (e.g., ban messages) -->
        <div v-if="status" class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
            {{ status }}
        </div>

        <!-- Step 1: Identifier entry (reused component) -->
        <div  v-if="step === 1" class="grid gap-5">
        <IdentifierStep

            v-model="identifier"
            :errorMessage="localError || props.errorsBag?.identifier"
            :loading="loading"
            label="Email or phone number"
            placeholder="e.g. +4712345678 or email@example.com"
            @submit="onSubmitIdentify"
        />
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t"></span>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-background px-2 text-muted-foreground">Or continue with</span>
            </div>
        </div>
        <div>
            <Button as="a" :href="'/auth/google'" variant="outline" class="w-full">
                Continue with Google
            </Button>
        </div>
        </div>
        <!-- Step 2: Show existing accounts and/or create option -->
        <div v-else-if="step === 2">
            <CandidateSelectStep
                :identifier="identifier"
                :mode="state.mode"
                :candidates="state.candidates"
                :canCreate="state.canCreate"
                back-label="Back to Step 1"
                @select="selectCandidate"
                @create="goToCreate"
                @back="backToStep1"
            />
        </div>

        <!-- Step 3: confirm password (if needed) within RegisterSteps.vue -->
        <div v-else-if="step === 3" class="flex flex-col gap-6">
            <div class="grid gap-2">
                <div class="text-sm text-muted-foreground">You're signing in as</div>
                <div class="flex items-center gap-3 rounded-md border p-3">
                    <UserIcon class="h-6 w-6" />
                    <div>
                        <div class="font-medium">{{ selectedUser?.name }}</div>
                        <div class="text-xs text-muted-foreground">
                            <span v-if="selectedUser?.email">{{ selectedUser?.email }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="needsPassword" class="grid gap-2">
                <Label for="password">Enter your password</Label>
                <div class="flex flex-col gap-2">
                    <Input id="password" name="password" type="password" v-model="step3Password" required autofocus />
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="remember" value="1" :checked="step3Remember" @change="step3Remember = !step3Remember" />
                            Remember me
                        </label>
                        <div class="text-sm text-muted-foreground">
                            Forgot your password?
                            <TextLink :href="passwordRequest()">Reset it</TextLink>
                        </div>
                    </div>
                    <Button type="button" class="mt-2 w-full" @click="onConfirmWithPassword">Log in</Button>
                    <Button
                        v-if="selectedUser?.google_id"
                        as="a"
                        :href="'/auth/google'"
                        variant="outline"
                        class="w-full"
                    >
                        Continue with Google
                    </Button>
                </div>
            </div>

            <div v-else class="grid gap-2">
                <Button
                    v-if="!selectedUser?.google_id"
                    type="button"
                    class="mt-2 w-full"
                    @click="onContinueNoPassword"
                >
                    Continue
                </Button>
                <Button
                    v-else
                    as="a"
                    :href="'/auth/google'"
                    class="mt-2 w-full"
                >
                    Continue with Google
                </Button>
            </div>

            <div>
                <Button type="button" variant="outline" @click="step = 2">Back</Button>
            </div>
        </div>
        <div v-else-if="step === 4" class="flex flex-col gap-6">
            <div class="space-y-2">
                <div class="text-lg font-semibold">Terms of Service</div>
                <p class="text-sm text-muted-foreground">
                    By creating an account, you agree to our
                    <a href="/terms" class="underline" target="_blank" rel="noopener">Terms of Service</a>
                    and
                    <a href="/privacy" class="underline" target="_blank" rel="noopener">Privacy Policy</a>.
                </p>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <Checkbox :modelValue="tosAgreed" @update:modelValue="tosAgreed = !tosAgreed" name="tos" required />
                <span>I agree to the Terms of Service and Privacy Policy</span>
            </label>

            <div class="flex items-center gap-2">
                <Button type="button" variant="outline" @click="backToStep2">Back</Button>
                <Button type="button" class="ml-auto" :disabled="!tosAgreed" @click="step = 5">Continue</Button>
            </div>
        </div>
        <div v-else-if="step === 5" class="flex flex-col gap-4">
            <div>
                <Label for="first_name">First name</Label>
                <Input id="first_name" name="first_name" type="text" v-model="firstName" :aria-invalid="!!step5Errors.firstName" autocomplete="given-name" required :readonly="!!googleSignup" />
                <InputError :message="step5Errors.firstName" />
            </div>
            <div>
                <Label for="last_name">Last name</Label>
                <Input id="last_name" name="last_name" type="text" v-model="lastName" :aria-invalid="!!step5Errors.lastName" autocomplete="family-name" required :readonly="!!googleSignup" />
                <InputError :message="step5Errors.lastName" />
            </div>

            <div v-if="isEmailIdentifier">
                <Label for="email">Email</Label>
                <Input id="email" name="email" type="email" :modelValue="detectedEmail" disabled />
            </div>
            <div v-else>
                <Label for="phone">Phone</Label>
                <Input id="phone" name="phone" type="tel" :modelValue="detectedPhone" disabled />
            </div>

            <div class="flex items-center gap-2 pt-2">
                <div>
                    <Label for="birthday">Birthday</Label>
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button variant="outline" :class="cn('w-[240px] ps-3 text-start font-normal', !dob && 'text-muted-foreground')">
                                <span>{{ dob ? df.format(dateValueToJS(dob)) : 'Pick a date' }}</span>
                                <CalendarIcon class="ms-auto h-4 w-4 opacity-50" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <CalendarRoot
                                v-slot="{ date, grid, weekDays }"
                                v-model:placeholder="placeholder"
                                :model-value="dob"
                                calendar-label="Date of birth"
                                initial-focus
                                :min-value="new CalendarDate(1900, 1, 1)"
                                :max-value="today(getLocalTimeZone()).subtract({ years: 5 })"
                                @update:model-value="onDobUpdate"
                                :class="cn('rounded-md border p-3')"
                            >
                                <CalendarHeader>
                                    <CalendarHeading class="flex w-full items-center justify-between gap-2">
                                        <Select
                                            :default-value="(placeholder as any)?.month?.toString?.()"
                                            @update:model-value="
                                                (v) => {
                                                    if (!v || !placeholder) return;
                                                    const num = Number(v);
                                                    // @ts-ignore
                                                    if (num === (placeholder as any).month) return;
                                                    // @ts-ignore
                                                    placeholder = (placeholder as any).set({ month: num });
                                                }
                                            "
                                        >
                                            <SelectTrigger aria-label="Select month" class="w-[60%]">
                                                <SelectValue placeholder="Select month" />
                                            </SelectTrigger>
                                            <SelectContent class="max-h-[200px]">
                                                <SelectItem
                                                    v-for="month in createYear({ dateObj: date })"
                                                    :key="month.toString()"
                                                    :value="month.month.toString()"
                                                >
                                                    {{ formatter.custom(toDate(month), { month: 'long' }) }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>

                                        <Select
                                            :default-value="(placeholder as any)?.year?.toString?.()"
                                            @update:model-value="
                                                (v) => {
                                                    if (!v || !placeholder) return;
                                                    const num = Number(v);
                                                    // @ts-ignore
                                                    if (num === (placeholder as any).year) return;
                                                    // @ts-ignore
                                                    placeholder = (placeholder as any).set({ year: num });
                                                }
                                            "
                                        >
                                            <SelectTrigger aria-label="Select year" class="w-[40%]">
                                                <SelectValue placeholder="Select year" />
                                            </SelectTrigger>
                                            <SelectContent class="max-h-[200px]">
                                                <SelectItem v-for="y in yearOptions" :key="y" :value="y.toString()">
                                                    {{ y }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </CalendarHeading>
                                </CalendarHeader>

                                <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
                                    <CalendarGrid v-for="month in grid" :key="month.value.toString()">
                                        <CalendarGridHead>
                                            <CalendarGridRow>
                                                <CalendarHeadCell v-for="day in weekDays" :key="day">
                                                    {{ day }}
                                                </CalendarHeadCell>
                                            </CalendarGridRow>
                                        </CalendarGridHead>
                                        <CalendarGridBody class="grid">
                                            <CalendarGridRow v-for="(weekDates, index) in month.rows" :key="`weekDate-${index}`" class="mt-2 w-full">
                                                <CalendarCell v-for="weekDate in weekDates" :key="weekDate.toString()" :date="weekDate">
                                                    <CalendarCellTrigger :day="weekDate" :month="month.value" />
                                                </CalendarCell>
                                            </CalendarGridRow>
                                        </CalendarGridBody>
                                    </CalendarGrid>
                                </div>
                            </CalendarRoot>
                        </PopoverContent>
                    </Popover>
                    <input type="hidden" id="birthday" name="birthday" :value="birthday" :aria-invalid="!!step5Errors.birthday" required />
                                        <InputError :message="step5Errors.birthday" />
                </div>
                <div>
                    <Label for="age">Age</Label>
                    <Input type="number" disabled :modelValue="ageYears ?? ''" id="age" />
                </div>
            </div>
            <div>
                <Label for="postal_code">Postal code</Label>
                <NumberField id="postal_code" v-model="postalCode" :min="0" :max="999999" :formatOptions="{ useGrouping: false}" :aria-invalid="!!step5Errors.postalCode" required>
                    <NumberFieldContent>
                        <NumberFieldInput />
                    </NumberFieldContent>
                </NumberField>
                <InputError :message="step5Errors.postalCode" />
            </div>

            <div class="flex items-center gap-2 pt-2">
                <Button type="button" variant="outline" @click="step = 4">Back</Button>
                <Button type="button" class="ml-auto" @click="continueFromStep5">Continue</Button>
            </div>
        </div>
        <div v-else-if="step === 6" class="flex flex-col gap-4">
            <div class="rounded-md border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-200">
                <div class="font-medium">Why we need a guardian</div>
                <p class="mt-1">
                    Because you are under 18, we need a parent or legal guardian to approve and help manage your account.
                    Please enter their email address so we can invite them to complete the setup.
                </p>
            </div>
            <div class="grid gap-2 pt-2">
                <Label for="guardian_relationship">Relationship to you</Label>
                <Select :modelValue="guardianRelationship" @update:model-value="(v:any) => (guardianRelationship = (v as string) || '')">
                    <SelectTrigger id="guardian_relationship" aria-label="Select relationship">
                        <SelectValue placeholder="Select relationship" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="opt in relationshipOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <IdentifierStep
                v-model="guardianIdentifier"
                :errorMessage="guardianError"
                :loading="guardianLoading"
                label="Guardian email"
                placeholder="e.g. parent@example.com"
                @submit="onSubmitGuardianIdentify"
            />

            <div class="flex items-center gap-2 pt-2">
                <Button type="button" variant="outline" @click="step = 5">Back</Button>
            </div>
        </div>
        <div v-else-if="step === 7">
            <CandidateSelectStep
                :identifier="guardianIdentifier"
                :mode="guardianState.mode"
                :candidates="guardianState.candidates"
                :canCreate="guardianState.canCreate"
                :guardianMode="true"
                back-label="Back to Step 6"
                @select="selectGuardianCandidate"
                @create="goToCreateGuardian"
                @back="backToStep6"
            />
        </div>
        <div v-else-if="step === 8" class="flex flex-col gap-4">
            <div class="space-y-1">
                <div class="text-lg font-semibold">Invite a guardian</div>
                <p class="text-sm text-muted-foreground">Enter your parent or legal guardian’s details. We’ll send them an invitation to approve your account.</p>
            </div>

            <div>
                <Label for="guardian_first_name">Guardian first name</Label>
                <Input id="guardian_first_name" name="guardian_first_name" type="text" v-model="guardianFirstName" :aria-invalid="!!guardianErrors.firstName" autocomplete="given-name" required />
                <InputError :message="guardianErrors.firstName" />
            </div>
            <div>
                <Label for="guardian_last_name">Guardian last name</Label>
                <Input id="guardian_last_name" name="guardian_last_name" type="text" v-model="guardianLastName" :aria-invalid="!!guardianErrors.lastName" autocomplete="family-name" required />
                <InputError :message="guardianErrors.lastName" />
            </div>

            <div>
                <Label for="guardian_email">Guardian email</Label>
                <Input id="guardian_email" name="guardian_email" type="email" :modelValue="detectedGuardianEmail" disabled />
                <p class="mt-1 text-xs text-muted-foreground">We’ll use this address to send the invitation.</p>
            </div>

            <div class="grid gap-2">
                <Label for="guardian_relationship">Relationship to you</Label>
                <Select :modelValue="guardianRelationship" @update:model-value="(v:any) => (guardianRelationship = (v as string) || '')">
                    <SelectTrigger id="guardian_relationship" aria-label="Select relationship">
                        <SelectValue placeholder="Select relationship" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="opt in relationshipOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="guardianErrors.relationship" class="text-xs text-red-600">{{ guardianErrors.relationship }}</p>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <Button type="button" variant="outline" @click="backToStep7">Back</Button>
                <Button type="button" class="ml-auto" @click="continueFromStep8">Continue</Button>
            </div>
        </div>
        <div v-else-if="step === 9" class="flex flex-col gap-6">
            <div class="space-y-1">
                <div class="text-lg font-semibold">Review your details</div>
                <p class="text-sm text-muted-foreground">Please check your information before submitting. You must confirm to proceed.</p>
            </div>

            <div class="rounded-md border p-3 text-sm">
                <div class="flex items-center gap-3 pb-3" v-if="googleSignup && googleSignup.avatar">
                    <img :src="googleSignup.avatar" alt="Profile picture" class="h-12 w-12 rounded-full object-cover" />
                    <div class="text-xs text-muted-foreground">Using your Google profile picture</div>
                </div>
                <div class="grid gap-2">
                    <div><span class="text-muted-foreground">Name:</span> <strong>{{ fullName }}</strong></div>
                    <div v-if="summaryEmail"><span class="text-muted-foreground">Email:</span> <strong>{{ summaryEmail }}</strong></div>
                    <div v-else><span class="text-muted-foreground">Phone:</span> <strong>{{ summaryPhone }}</strong></div>
                    <div><span class="text-muted-foreground">Birthday:</span> <strong>{{ birthday }}</strong> <span v-if="ageYears != null" class="text-muted-foreground">(Age {{ ageYears }})</span></div>
                    <div><span class="text-muted-foreground">Postal code:</span> <strong>{{ postalCode }}</strong></div>
                </div>
            </div>

            <div v-if="isMinor && guardianSummary" class="rounded-md border border-purple-200 bg-purple-50 p-3 text-sm text-purple-800 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-200">
                <div class="font-medium">Guardian</div>
                <div class="mt-1 grid gap-2">
                    <div><span class="opacity-80">Name:</span> <strong>{{ guardianSummary.name }}</strong></div>
                    <div v-if="guardianSummary.email"><span class="opacity-80">Email:</span> <strong>{{ guardianSummary.email }}</strong></div>
                    <div><span class="opacity-80">Relationship:</span> <strong>{{ guardianRelationshipLabel || '—' }}</strong></div>
                </div>
            </div>

            <form action="/register" method="post" class="flex flex-col gap-3">
                <input type="hidden" name="_token" :value="csrfToken" />
                <input type="hidden" name="first_name" :value="firstName" />
                <input type="hidden" name="last_name" :value="lastName" />
                <input type="hidden" name="birthday" :value="birthday" />
                <input type="hidden" name="postal_code" :value="postalCode ?? ''" />
                <input v-if="summaryEmail" type="hidden" name="email" :value="summaryEmail" />
                <input v-else type="hidden" name="phone" :value="summaryPhone" />

                <template v-if="isMinor">
                    <input v-if="selectedGuardian?.id" type="hidden" name="guardian_id" :value="selectedGuardian?.id" />
                    <input type="hidden" name="guardian_first_name" :value="guardianFirstNameFinal" />
                    <input type="hidden" name="guardian_last_name" :value="guardianLastNameFinal" />
                    <input v-if="guardianEmailFinal" type="hidden" name="guardian_email" :value="guardianEmailFinal" />
                    <input type="hidden" name="guardian_relationship" :value="guardianRelationship" />
                </template>

                <div class="flex items-center gap-2">
                    <Checkbox :modelValue="summaryConfirm" @update:modelValue="summaryConfirm = !summaryConfirm" />
                    <span class="text-sm">I confirm that the information provided is accurate.</span>
                </div>
                <InputError :message="submitError || serverErrors.base" />

                <div class="flex items-center gap-2">
                    <Button type="button" variant="outline" @click="backFromStep9">Back</Button>
                    <Button type="submit" class="ml-auto" :disabled="!summaryConfirm">Submit and create account</Button>
                </div>
            </form>
        </div>
    </AuthBase>
</template>
