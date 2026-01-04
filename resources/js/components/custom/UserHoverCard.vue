<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/components/ui/hover-card';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { CalendarDays, Shield, Users, HeartHandshake, Baby } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { AppPageProps } from '@/types';

interface Role {
    id: number;
    name: string;
    team_id: number;
}

interface Team {
    id: number;
    name: string;
}

interface Relation {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email?: string | null;
    avatar?: string | null;
    created_at?: string | null;
    birthday?: string | null;
    roles?: Role[];
    teams?: Team[];
    guardians?: Relation[];
    minors?: Relation[];
}

const props = defineProps<{
    user: User;
}>();

const page = usePage<AppPageProps>();

const isAdmin = computed(() => {
    return page.props.auth?.roles?.includes('Admin') || false;
});

const age = computed(() => {
    if (!props.user.birthday) return null;
    const birthDate = new Date(props.user.birthday);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
});

const displayName = computed(() => {
    if (isAdmin.value) {
        return props.user.name;
    }

    const parts = props.user.name.trim().split(/\s+/).filter(Boolean);
    if (parts.length <= 1) return props.user.name;

    const givenName = parts.slice(0, -1).join(' ');
    const familyName = parts[parts.length - 1];
    const firstLetterOfFamilyName = familyName.charAt(0).toUpperCase();

    return `${givenName} ${firstLetterOfFamilyName}.`;
});

const initials = computed(() => {
    const parts = props.user.name.trim().split(/\s+/).filter(Boolean);
    const first = parts[0]?.[0] ?? '';
    const last = parts.length > 1 ? (parts[parts.length - 1][0] ?? '') : '';
    return (first + last).toUpperCase() || '?';
});

const globalRoles = computed(() => {
    return props.user.roles?.filter((r) => r.team_id === 0) ?? [];
});

const teamRoles = computed(() => {
    return props.user.roles?.filter((r) => r.team_id !== 0) ?? [];
});
</script>

<template>
    <HoverCard>
        <HoverCardTrigger as-child>
            <span class="cursor-pointer hover:underline">
                {{ displayName }}
            </span>
        </HoverCardTrigger>
        <HoverCardContent class="w-80">
            <div class="space-y-4">
                <div class="flex items-start justify-between">
                    <div class="flex gap-3">
                        <Avatar>
                            <AvatarImage :src="user.avatar || ''" />
                            <AvatarFallback>{{ initials }}</AvatarFallback>
                        </Avatar>
                        <div class="space-y-1">
                            <h4 class="text-sm font-semibold">
                                {{ displayName }}
                                <span v-if="age" class="ml-1 text-xs font-normal text-muted-foreground">({{ age }})</span>
                            </h4>
                            <p v-if="isAdmin && user.email" class="text-xs text-muted-foreground">
                                {{ user.email }}
                            </p>
                            <div v-if="user.created_at" class="flex items-center text-xs text-muted-foreground">
                                <CalendarDays class="mr-1 h-3 w-3" />
                                Joined {{ user.created_at }}
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="globalRoles.length > 0" class="space-y-1.5">
                    <div class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                        <Shield class="h-3 w-3" />
                        Global Roles
                    </div>
                    <div class="flex flex-wrap gap-1">
                        <Badge v-for="role in globalRoles" :key="role.id" variant="secondary" class="text-[10px] font-normal">
                            {{ trans('pages.roles.' + role.name) }}
                        </Badge>
                    </div>
                </div>

                <div v-if="user.teams && user.teams.length > 0" class="space-y-1.5">
                    <div class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                        <Users class="h-3 w-3" />
                        Teams
                    </div>
                    <div class="space-y-2">
                        <div v-for="team in user.teams" :key="team.id" class="space-y-1">
                            <div class="text-[11px] font-medium leading-none">{{ team.name }}</div>
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="role in teamRoles.filter(r => r.team_id === team.id)"
                                    :key="role.id"
                                    variant="outline"
                                    class="text-[10px] font-normal"
                                >
                                    {{ trans('pages.roles.' + role.name) }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <template v-if="isAdmin">
                    <div v-if="user.guardians && user.guardians.length > 0" class="space-y-1.5">
                        <div class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                            <HeartHandshake class="h-3 w-3" />
                            Guardians
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <Badge v-for="g in user.guardians" :key="g.id" variant="outline" class="text-[10px] font-normal">
                                {{ g.name }}
                            </Badge>
                        </div>
                    </div>

                    <div v-if="user.minors && user.minors.length > 0" class="space-y-1.5">
                        <div class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                            <Baby class="h-3 w-3" />
                            Minors
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <Badge v-for="m in user.minors" :key="m.id" variant="outline" class="text-[10px] font-normal">
                                {{ m.name }}
                            </Badge>
                        </div>
                    </div>
                </template>
            </div>
        </HoverCardContent>
    </HoverCard>
</template>
