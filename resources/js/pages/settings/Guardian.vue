<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Guardian {
    id: number;
    name: string;
    email: string;
    relationship: string;
    confirmed_guardian: boolean;
    confirmed_admin: boolean;
}

interface Props {
    guardians: Guardian[];
    minors: Guardian[];
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Guardian settings',
        href: '/settings/guardian',
    },
];

const showAddGuardianDialog = ref(false);
const showAddMinorDialog = ref(false);

const guardianForm = useForm({
    type: 'guardian',
    email: '',
    relationship: '',
});

const minorForm = useForm({
    type: 'minor',
    email: '',
    relationship: '',
});

const relationshipOptions = [
    { value: 'father', label: 'Father' },
    { value: 'mother', label: 'Mother' },
    { value: 'uncle', label: 'Uncle' },
    { value: 'aunt', label: 'Aunt' },
    { value: 'grand_mother', label: 'Grandmother' },
    { value: 'grand_father', label: 'Grandfather' },
    { value: 'other', label: 'Other' },
];

const minorRelationshipOptions = [
    { value: 'child', label: 'Child' },
    { value: 'ward', label: 'Ward' },
    { value: 'other', label: 'Other' },
];

const addGuardian = () => {
    guardianForm.post('/settings/guardian', {
        preserveScroll: true,
        onSuccess: () => {
            guardianForm.reset();
            showAddGuardianDialog.value = false;
        },
    });
};

const addMinor = () => {
    minorForm.post('/settings/guardian', {
        preserveScroll: true,
        onSuccess: () => {
            minorForm.reset();
            showAddMinorDialog.value = false;
        },
    });
};

const removeConnection = (userId: number, type: 'guardian' | 'minor') => {
    if (confirm('Are you sure you want to remove this connection?')) {
        router.delete(`/settings/guardian/${userId}`, {
            data: { type },
            preserveScroll: true,
        });
    }
};

const getConnectionStatus = (connection: Guardian) => {
    console.log('getConnectionStatus called with:', connection);
    console.log('confirmed_guardian type:', typeof connection.confirmed_guardian, 'value:', connection.confirmed_guardian);
    console.log('confirmed_admin type:', typeof connection.confirmed_admin, 'value:', connection.confirmed_admin);

    if (!connection.confirmed_guardian && !connection.confirmed_admin) {
        return { text: 'Pending Approval', variant: 'secondary' as const };
    }
    if (!connection.confirmed_guardian) {
        return { text: 'Pending Guardian', variant: 'secondary' as const };
    }
    if (!connection.confirmed_admin) {
        return { text: 'Pending Admin', variant: 'secondary' as const };
    }
    return { text: 'Active', variant: 'default' as const };
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Guardian settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-8">
                <!-- Guardians Section -->
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <HeadingSmall title="My Guardians" description="People who can manage your account" />
                        <Dialog v-model:open="showAddGuardianDialog">
                            <DialogTrigger as-child>
                                <Button variant="outline" size="sm">Add Guardian</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Add Guardian</DialogTitle>
                                    <DialogDescription>
                                        Add a guardian who can help manage your account. They will need to approve this connection.
                                    </DialogDescription>
                                </DialogHeader>
                                <form @submit.prevent="addGuardian" class="space-y-4">
                                    <div class="grid gap-2">
                                        <Label for="guardian-email">Email address</Label>
                                        <Input
                                            id="guardian-email"
                                            v-model="guardianForm.email"
                                            type="email"
                                            placeholder="guardian@example.com"
                                            required
                                        />
                                        <InputError :message="guardianForm.errors.email" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="guardian-relationship">Relationship</Label>
                                        <Select v-model="guardianForm.relationship" required>
                                            <SelectTrigger id="guardian-relationship">
                                                <SelectValue placeholder="Select relationship" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="option in relationshipOptions" :key="option.value" :value="option.value">
                                                    {{ option.label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="guardianForm.errors.relationship" />
                                    </div>
                                    <DialogFooter>
                                        <Button type="submit" :disabled="guardianForm.processing"> Add Guardian </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>

                    <div v-if="props.guardians.length === 0" class="text-sm text-muted-foreground">No guardians connected yet.</div>

                    <div v-else class="space-y-3">
                        <div v-for="guardian in props.guardians" :key="guardian.id" class="flex items-center justify-between rounded-lg border p-4">
                            <div class="flex-1">
                                <div class="font-medium">{{ guardian.name }}</div>
                                <div class="text-sm text-muted-foreground">{{ guardian.email }}</div>
                                <div class="mt-1 text-sm text-muted-foreground capitalize">
                                    {{ guardian.relationship.replace('_', ' ') }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge :variant="getConnectionStatus(guardian).variant">
                                    {{ getConnectionStatus(guardian).text }}
                                </Badge>
                                <Button variant="destructive" size="sm" @click="removeConnection(guardian.id, 'guardian')"> Remove </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Children/Minors Section -->
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <HeadingSmall title="My Children" description="Accounts you can manage as a guardian" />
                        <Dialog v-model:open="showAddMinorDialog">
                            <DialogTrigger as-child>
                                <Button variant="outline" size="sm">Add Child</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Add Child</DialogTitle>
                                    <DialogDescription> Add a child account that you can manage as a guardian. </DialogDescription>
                                </DialogHeader>
                                <form @submit.prevent="addMinor" class="space-y-4">
                                    <div class="grid gap-2">
                                        <Label for="minor-email">Email address</Label>
                                        <Input id="minor-email" v-model="minorForm.email" type="email" placeholder="child@example.com" required />
                                        <InputError :message="minorForm.errors.email" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="minor-relationship">Relationship</Label>
                                        <Select v-model="minorForm.relationship" required>
                                            <SelectTrigger id="minor-relationship">
                                                <SelectValue placeholder="Select relationship" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="option in minorRelationshipOptions" :key="option.value" :value="option.value">
                                                    {{ option.label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="minorForm.errors.relationship" />
                                    </div>
                                    <DialogFooter>
                                        <Button type="submit" :disabled="minorForm.processing"> Add Child </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>

                    <div v-if="props.minors.length === 0" class="text-sm text-muted-foreground">No children connected yet.</div>

                    <div v-else class="space-y-3">
                        <div v-for="minor in props.minors" :key="minor.id" class="flex items-center justify-between rounded-lg border p-4">
                            <div class="flex-1">
                                <div class="font-medium">{{ minor.name }}</div>
                                <div class="text-sm text-muted-foreground">{{ minor.email }}</div>
                                <div class="mt-1 text-sm text-muted-foreground capitalize">
                                    {{ minor.relationship.replace('_', ' ') }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge :variant="getConnectionStatus(minor).variant">
                                    {{ getConnectionStatus(minor).text }}
                                </Badge>
                                <Button variant="destructive" size="sm" @click="removeConnection(minor.id, 'minor')"> Remove </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
