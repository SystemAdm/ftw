<script setup lang="ts">
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/components/ui/hover-card';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { CalendarDays } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { show as showTeam } from '@/routes/teams/index';

interface Team {
    id: number;
    name: string;
    slug?: string | null;
    description?: string | null;
    logo?: string | null;
    created_at?: string | null;
}

defineProps<{
    team: Team;
}>();
</script>

<template>
    <HoverCard>
        <HoverCardTrigger as-child>
            <Link :href="showTeam.url(team.id)" class="block transition-opacity hover:opacity-80">
                <Badge variant="secondary" class="cursor-pointer">
                    {{ team.slug ?? team.name }}
                </Badge>
            </Link>
        </HoverCardTrigger>
        <HoverCardContent class="w-80">
            <div class="flex justify-between space-x-4">
                <Avatar>
                    <AvatarImage :src="team.logo || ''" />
                    <AvatarFallback>{{ (team.slug ?? team.name).substring(0, 2).toUpperCase() }}</AvatarFallback>
                </Avatar>
                <div class="space-y-1 text-left">
                    <h4 class="text-sm font-semibold">
                        {{ team.name }}
                    </h4>
                    <p class="text-sm text-muted-foreground">
                        {{ team.description || trans('pages.crew.teams.no_description') }}
                    </p>
                    <div v-if="team.created_at" class="flex items-center pt-2">
                        <CalendarDays class="mr-2 h-4 w-4 opacity-70" />
                        <span class="text-xs text-muted-foreground">
                            {{ team.created_at }}
                        </span>
                    </div>
                </div>
            </div>
        </HoverCardContent>
    </HoverCard>
</template>
