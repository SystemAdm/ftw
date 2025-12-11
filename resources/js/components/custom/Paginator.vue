<script setup lang="ts">
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    collection: {
        per_page: number;
        total: number;
        current_page: number;
        prev_page_url: string;
        next_page_url: string;
    };
}>();
</script>

<template>
    <div class="flex flex-col gap-6">
        <Pagination
            v-slot="{ page }"
            :items-per-page="props.collection.per_page"
            :total="props.collection.total"
            :default-page="props.collection.current_page"
        >
            <PaginationContent v-slot="{ items }">
                <PaginationPrevious
                    :disabled="!props.collection.prev_page_url"
                    @click="() => props.collection.prev_page_url && router.visit(props.collection.prev_page_url)"
                />
                <template v-for="(item, index) in items" :key="index">
                    <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === page">
                        {{ item.value }}
                    </PaginationItem>
                </template>
                <PaginationNext
                    :disabled="!props.collection.next_page_url"
                    @click="() => props.collection.next_page_url && router.visit(props.collection.next_page_url)"
                />
            </PaginationContent>
        </Pagination>
    </div>
</template>

<style scoped></style>
