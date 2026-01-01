<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/components/layouts/PublicLayout.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { Mail, Phone, Calendar, MapPin, User as UserIcon, Cog } from 'lucide-vue-next';

interface Props {
    user: {
        id: number;
        name: string;
        username: string | null;
        avatar: string | null;
        header_image: string | null;
        about: string | null;
        email: string | null;
        phone: string | null;
        birthday: string | number | null;
        birthday_visibility: string;
        postal_code?: number | null;
        city?: string | null;
        municipality?: string | null;
        country?: string | null;
        postal_code_visibility: string;
        email_public: boolean;
        phone_public: boolean;
        name_public: boolean;
    };
    isOwnProfile: boolean;
}

const props = defineProps<Props>();

const initials = computed(() => {
    return props.user.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase();
});

const formattedBirthday = computed(() => {
    if (!props.user.birthday) return null;

    if (typeof props.user.birthday === 'number') {
        return props.user.birthday + ' ' + trans('events.fields.years').toLowerCase();
    }

    if (props.user.birthday.length === 4) {
        return props.user.birthday;
    }

    return new Date(props.user.birthday).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});
</script>

<template>
    <Head :title="user.name" />

    <PublicLayout>
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <Card class="overflow-hidden">
                <div v-if="user.header_image" class="h-48 overflow-hidden">
                    <img :src="user.header_image" class="h-full w-full object-cover" alt="Header" />
                </div>
                <div v-else class="h-32 bg-linear-to-r from-primary/20 to-primary/10"></div>
                <CardContent class="relative pt-0">
                    <div class="absolute -top-12 left-6">
                        <Avatar class="h-24 w-24 border-4 border-background text-2xl">
                            <AvatarImage :src="user.avatar || ''" :alt="user.name" />
                            <AvatarFallback>{{ initials }}</AvatarFallback>
                        </Avatar>
                    </div>

                    <div class="mt-14 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div class="space-y-1">
                            <h1 class="text-3xl font-bold tracking-tight">{{ user.name }}</h1>
                            <p v-if="user.username" class="text-muted-foreground">@{{ user.username }}</p>
                        </div>

                        <div v-if="isOwnProfile" class="flex gap-2">
                            <Button as-child variant="outline" size="sm">
                                <Link href="/settings/profile">
                                    <Cog class="mr-2 h-4 w-4" />
                                    {{ trans('pages.ui.navigation.settings') }}
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-6 md:grid-cols-2">
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold">{{ trans('pages.settings.profile.title') }} Info</h3>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-sm">
                                    <UserIcon class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ user.name }}</span>
                                </div>

                                <div v-if="user.email" class="flex items-center gap-3 text-sm">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <a :href="'mailto:' + user.email" class="hover:underline">{{ user.email }}</a>
                                    <Badge v-if="user.email_public && isOwnProfile" variant="outline" class="text-[10px] uppercase tracking-wider">Public</Badge>
                                </div>
                                <div v-else-if="isOwnProfile" class="flex items-center gap-3 text-sm text-muted-foreground opacity-60">
                                    <Mail class="h-4 w-4" />
                                    <span>{{ trans('pages.settings.profile.email_private') }}</span>
                                </div>

                                <div v-if="user.phone" class="flex items-center gap-3 text-sm">
                                    <Phone class="h-4 w-4 text-muted-foreground" />
                                    <a :href="'tel:' + user.phone" class="hover:underline">{{ user.phone }}</a>
                                    <Badge v-if="user.phone_public && isOwnProfile" variant="outline" class="text-[10px] uppercase tracking-wider">Public</Badge>
                                </div>
                                <div v-else-if="isOwnProfile" class="flex items-center gap-3 text-sm text-muted-foreground opacity-60">
                                    <Phone class="h-4 w-4" />
                                    <span>{{ trans('pages.settings.profile.phone_private') }}</span>
                                </div>

                                <div v-if="formattedBirthday" class="flex items-center gap-3 text-sm">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ formattedBirthday }}</span>
                                    <Badge v-if="user.birthday_visibility !== 'off' && isOwnProfile" variant="outline" class="text-[10px] uppercase tracking-wider">Public ({{ user.birthday_visibility }})</Badge>
                                    <Badge v-else-if="isOwnProfile" variant="outline" class="text-[10px] uppercase tracking-wider">Private</Badge>
                                </div>

                                <div v-if="user.city || user.postal_code || isOwnProfile" class="flex items-center gap-3 text-sm">
                                    <MapPin class="h-4 w-4 text-muted-foreground" />
                                    <div class="flex flex-wrap gap-x-1">
                                        <span v-if="user.postal_code">{{ user.postal_code }}</span>
                                        <span v-if="user.city">{{ user.city }}</span>
                                        <span v-if="user.municipality">({{ user.municipality }})</span>
                                        <span v-if="user.country">{{ user.country }}</span>
                                        <span v-if="!user.postal_code && !user.city && isOwnProfile" class="text-muted-foreground opacity-60">{{ trans('pages.settings.profile.location_private') }}</span>
                                    </div>
                                    <Badge v-if="user.postal_code_visibility !== 'off' && isOwnProfile" variant="outline" class="text-[10px] uppercase tracking-wider">Public ({{ user.postal_code_visibility }})</Badge>
                                    <Badge v-else-if="isOwnProfile" variant="outline" class="text-[10px] uppercase tracking-wider">Private</Badge>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold">{{ trans('pages.settings.profile.about') }}</h3>
                            <p v-if="user.about" class="text-sm whitespace-pre-wrap">
                                {{ user.about }}
                            </p>
                            <p v-else class="text-sm text-muted-foreground italic">
                                {{ trans('pages.settings.profile.no_bio') }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </PublicLayout>
</template>
