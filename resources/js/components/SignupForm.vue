<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Field, FieldDescription, FieldGroup, FieldLabel, FieldSeparator } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { faGoogle } from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { HTMLAttributes, ref } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();


const step = ref<number>(1);
const history = ref<number[]>([]);

function next(i = 1): void {
    history.value.push(step.value);
    step.value = step.value + i;
}

function goToSocialiteGoogle(): void {
    // Use a full page redirect instead of an XHR visit to avoid CORS/preflight issues
    window.location.href = '/auth/google';
}
</script>

<template>
    <div :class="cn('flex flex-col gap-6', props.class)">
        <Card class="overflow-hidden p-0">
            <CardContent class="grid p-0" v-if="step === 1">
                <form class="p-6 md:p-8" v-on:submit.prevent="next(1)">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <h1 class="text-2xl font-bold">Search for an active account</h1>
                        </div>
                        <Field class="flex justify-center">
                            <Button variant="outline" type="button" @click.prevent="goToSocialiteGoogle">
                                <FontAwesomeIcon :icon="faGoogle" />
                                <span>Continue with Google</span>
                            </Button>
                        </Field>
                        <FieldSeparator class="*:data-[slot=field-separator-content]:bg-card"> Or </FieldSeparator>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <p class="text-sm text-balance text-muted-foreground">
                                Enter your e-mail address or phone number below to find or create your account
                            </p>
                        </div>
                        <Field>
                            <FieldLabel for="input"> Input field </FieldLabel>
                            <Input id="input" type="text" placeholder="navn@domene.no eller 99999999" required />
                        </Field>
                        <Field>
                            <Button type="submit"> Find my account </Button>
                        </Field>
                    </FieldGroup>
                </form>
            </CardContent>
        </Card>
        <FieldDescription class="px-6 text-center">
            By clicking any links, you agree to our <br /><a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
        </FieldDescription>
    </div>
</template>

<style scoped></style>
