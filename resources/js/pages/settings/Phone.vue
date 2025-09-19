<script setup lang="ts">
import PhoneController from '@/actions/App/Http/Controllers/Settings/PhoneController';
import { edit as profileEdit } from '@/routes/profile';
import { Form, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { type BreadcrumbItem } from '@/types';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faPlus, faStar, faTrashCan } from '@fortawesome/free-solid-svg-icons';

interface Phone {
    id: number;
    e164: string;
    raw?: string | null;
    primary: boolean;
    verified_at?: string | null;
}

interface Props {
    phones: Phone[];
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Profile settings', href: profileEdit().url },
    { title: 'Phone numbers', href: PhoneController.edit().url },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Phone settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Phone numbers" description="Manage your phone numbers" />

                <Form v-bind="PhoneController.store.form()" class="flex items-end gap-3" v-slot="{ errors, processing }">
                    <div class="grid flex-1 gap-2">
                        <Label for="phone">Add phone number</Label>
                        <Input id="phone" name="phone" placeholder="e.g. +47 412 34 567" />
                        <InputError class="mt-2" :message="errors.phone" />
                    </div>
                    <Button :disabled="processing"><FontAwesomeIcon :icon="faPlus" />Add</Button>
                </Form>

                <div v-if="props.phones.length === 0" class="text-sm text-muted-foreground">You have no phone numbers yet.</div>

                <ul v-else class="divide-y rounded-md border">
                    <li v-for="p in props.phones" :key="p.id" class="flex items-center justify-between p-3">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ p.e164 }}</span>
                            <span v-if="p.primary" class="rounded bg-yellow-500 px-2 py-0.5 text-xs text-black">Primary</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Form v-if="!p.primary" v-bind="PhoneController.makePrimary.form(p.id)">
                                <Button variant="secondary" size="sm"><FontAwesomeIcon :icon="faStar" class="text-yellow-500" />Make primary</Button>
                            </Form>
                            <AlertDialog v-if="!p.primary">
                                <AlertDialogTrigger as-child>
                                    <Button variant="destructive" size="sm"><FontAwesomeIcon :icon="faTrashCan" />Remove</Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Remove phone number?</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            This action cannot be undone. This will permanently remove the phone number {{ p.e164 }} from your
                                            profile.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <Form v-bind="PhoneController.destroy.form(p.id)">
                                            <AlertDialogAction as-child>
                                                <Button type="submit" variant="destructive" size="sm" class="text-white">Yes, remove</Button>
                                            </AlertDialogAction>
                                        </Form>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                            <Button v-else disabled variant="destructive" size="sm" class="opacity-50 cursor-not-allowed"><FontAwesomeIcon :icon="faTrashCan" />Remove</Button>
                        </div>
                    </li>
                </ul>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
