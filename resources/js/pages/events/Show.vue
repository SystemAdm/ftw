<script setup lang="ts">
import { index, signup as signupRoute, cancelSignup as cancelSignupRoute } from '@/actions/App/http/controllers/EventsController';
import { loginForm } from '@/actions/App/http/controllers/auth/UsersController';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/components/layouts/PublicLayout.vue';
import { trans } from 'laravel-vue-i18n';
import { Calendar, MapPin, Users, CheckCircle, Clock, AlertTriangle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

const page = usePage<PageProps>();

const props = defineProps<{
    event: any;
    isSignedUp: boolean;
}>();

const form = useForm({});

function signup() {
    form.post(signupRoute.url(props.event.id));
}

function cancelSignup() {
    form.delete(cancelSignupRoute.url(props.event.id));
}

function formatDate(date: string) {
    return new Date(date).toLocaleString(page.props.i18n.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const signupStatus = computed(() => {
    if (props.isSignedUp) return 'signed_up';

    const now = new Date();
    if (props.event.signup_start && new Date(props.event.signup_start) > now) return 'not_started';
    if (props.event.signup_end && new Date(props.event.signup_end) < now) return 'ended';

    if (props.event.number_of_seats === 0) return 'not_required';
    if (props.event.number_of_seats && props.event.number_of_seats !== -1 && props.event.reservations_count >= props.event.number_of_seats) return 'full';

    return 'available';
});

</script>

<template>
    <Head :title="event.title" />

    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <Link :href="index.url()" class="mb-6 inline-flex items-center text-sm font-medium text-muted-foreground hover:text-foreground">
                &larr; {{ trans('pages.events.back_to_list') }}
            </Link>

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-8">
                    <div v-if="event.image_path" class="overflow-hidden rounded-xl border bg-muted shadow-sm">
                        <img :src="event.image_path.startsWith('Http') ? event.image_path : `/storage/${event.image_path}`" class="h-auto w-full max-h-[500px] object-cover" alt="Event image" />
                    </div>

                    <div class="space-y-4">
                        <h1 class="text-4xl font-black tracking-tight text-foreground">{{ event.title }}</h1>
                        <p v-if="event.excerpt" class="text-xl text-muted-foreground">{{ event.excerpt }}</p>
                    </div>

                    <div class="prose dark:prose-invert max-w-none whitespace-pre-wrap">
                        {{ event.description }}
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 class="mb-6 text-xl font-bold">{{ trans('pages.events.details_title') }}</h2>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <Calendar class="mt-0.5 h-5 w-5 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-semibold">{{ trans('pages.events.fields.when') }}</p>
                                    <p class="text-sm text-muted-foreground">{{ formatDate(event.event_start) }}</p>
                                    <p v-if="event.event_end" class="text-sm text-muted-foreground">
                                        {{ trans('pages.events.fields.to') }} {{ formatDate(event.event_end) }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="event.location" class="flex items-start gap-3">
                                <MapPin class="mt-0.5 h-5 w-5 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-semibold">{{ trans('pages.events.fields.where') }}</p>
                                    <p class="text-sm text-muted-foreground">{{ event.location.name }}</p>
                                    <p v-if="event.location.street_address" class="text-xs text-muted-foreground">
                                        {{ event.location.street_address }} {{ event.location.street_number }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="event.number_of_seats !== null" class="flex items-start gap-3">
                                <Users class="mt-0.5 h-5 w-5 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-semibold">{{ trans('pages.events.fields.capacity') }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ event.reservations_count }} / {{ event.number_of_seats === -1 ? '∞' : event.number_of_seats }} {{ trans('pages.events.fields.seats_taken') }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="event.age_min || event.age_max" class="flex items-start gap-3">
                                <AlertTriangle class="mt-0.5 h-5 w-5 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-semibold">{{ trans('pages.events.fields.age_limit') }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        <template v-if="event.age_min && event.age_max">
                                            {{ event.age_min }} - {{ event.age_max }} {{ trans('pages.events.fields.years') }}
                                        </template>
                                        <template v-else-if="event.age_min">
                                            {{ trans('pages.events.fields.min_age', { age: event.age_min }) }}
                                        </template>
                                        <template v-else-if="event.age_max">
                                            {{ trans('pages.events.fields.max_age', { age: event.age_max }) }}
                                        </template>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="event.signup_needed" class="mt-8 pt-6 border-t">
                            <div v-if="signupStatus === 'signed_up'" class="space-y-4">
                                <div class="rounded-lg bg-green-500/10 p-4 text-center text-green-600 dark:text-green-400">
                                    <CheckCircle class="mx-auto mb-2 h-8 w-8" />
                                    <p class="font-bold">{{ trans('pages.events.signup.already_signed_up') }}</p>
                                </div>
                                <Button variant="outline" class="w-full" @click="cancelSignup" :disabled="form.processing">
                                    {{ trans('pages.events.signup.cancel') }}
                                </Button>
                            </div>

                            <div v-else-if="signupStatus === 'not_started'" class="rounded-lg bg-muted p-4 text-center text-muted-foreground">
                                <Clock class="mx-auto mb-2 h-8 w-8" />
                                <p class="font-bold">{{ trans('pages.events.signup.not_started') }}</p>
                                <p v-if="event.signup_start" class="text-xs mt-1">
                                    {{ trans('pages.events.signup.starts_at', { date: formatDate(event.signup_start) }) }}
                                </p>
                            </div>

                            <div v-else-if="signupStatus === 'ended'" class="rounded-lg bg-red-500/10 p-4 text-center text-red-600 dark:text-red-400">
                                <AlertTriangle class="mx-auto mb-2 h-8 w-8" />
                                <p class="font-bold">{{ trans('pages.events.signup.ended') }}</p>
                            </div>

                            <div v-else-if="signupStatus === 'full'" class="rounded-lg bg-yellow-500/10 p-4 text-center text-yellow-600 dark:text-yellow-400">
                                <Users class="mx-auto mb-2 h-8 w-8" />
                                <p class="font-bold">{{ trans('pages.events.signup.full') }}</p>
                            </div>

                            <div v-else-if="signupStatus === 'not_required'" class="rounded-lg bg-blue-500/10 p-4 text-center text-blue-600 dark:text-blue-400">
                                <CheckCircle class="mx-auto mb-2 h-8 w-8" />
                                <p class="font-bold">{{ trans('pages.events.signup.not_required') }}</p>
                            </div>

                            <div v-else class="space-y-4">
                                <p v-if="event.signup_end" class="text-center text-xs text-muted-foreground">
                                    {{ trans('pages.events.signup.ends_at', { date: formatDate(event.signup_end) }) }}
                                </p>
                                <Button v-if="page.props.auth.user" class="w-full h-12 text-lg font-bold" @click="signup" :disabled="form.processing">
                                    {{ form.processing ? trans('pages.events.signup.signing_up') : trans('pages.events.signup.button') }}
                                </Button>
                                <div v-else class="text-center">
                                    <Link :href="loginForm.url()" class="block">
                                        <Button variant="outline" class="w-full">
                                            {{ trans('pages.events.signup.login_to_signup') }}
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
