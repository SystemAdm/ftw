<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Loader2, Upload, Image as ImageIcon, Check } from 'lucide-vue-next';
import axios from 'axios';
import { trans } from 'laravel-vue-i18n';
import { images as imagesRoute, uploadImage as uploadImageRoute } from '@/actions/App/http/controllers/Admin/EventsController';

const props = defineProps<{
    modelValue: string | null;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
    (e: 'update:imageFile', value: File | null): void;
}>();

const isOpen = ref(false);
const images = ref<Array<{ path: string; url: string }>>([]);
const isLoading = ref(false);
const isUploading = ref(false);
const selectedPath = ref<string | null>(props.modelValue);

async function fetchImages() {
    isLoading.value = true;
    try {
        const response = await axios.get(imagesRoute.url());
        images.value = response.data;
    } catch (error) {
        console.error('Failed to fetch images', error);
    } finally {
        isLoading.value = false;
    }
}

async function handleFileUpload(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    isUploading.value = true;
    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await axios.post(uploadImageRoute.url(), formData);
        images.value.unshift(response.data);
        selectImage(response.data.path);
    } catch (error) {
        console.error('Upload failed', error);
    } finally {
        isUploading.value = false;
    }
}

function selectImage(path: string) {
    selectedPath.value = path;
    emit('update:modelValue', path);
    emit('update:imageFile', null);
    isOpen.value = false;
}

onMounted(() => {
    if (isOpen.value) {
        fetchImages();
    }
});

function openDialog() {
    isOpen.value = true;
    fetchImages();
}

</script>

<template>
    <div class="space-y-2">
        <div v-if="selectedPath" class="relative inline-block">
            <img :src="`/storage/${selectedPath}`" class="h-32 w-auto rounded border object-cover" />
            <Button
                type="button"
                variant="destructive"
                size="icon"
                class="absolute -right-2 -top-2 h-6 w-6"
                @click="selectImage('')"
            >
                <span class="sr-only">Remove</span>
                &times;
            </Button>
        </div>

        <div class="flex gap-2">
            <Dialog v-model:open="isOpen">
                <DialogTrigger as-child>
                    <Button type="button" variant="outline" @click="openDialog">
                        <ImageIcon class="mr-2 h-4 w-4" />
                        {{ trans('pages.settings.events.fields.image') }}
                    </Button>
                </DialogTrigger>
                <DialogContent class="max-w-3xl max-h-[80vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Select Image</DialogTitle>
                    </DialogHeader>

                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <Input
                                type="file"
                                accept="image/*"
                                class="hidden"
                                id="image-upload"
                                @change="handleFileUpload"
                            />
                            <label
                                for="image-upload"
                                class="flex cursor-pointer items-center justify-center rounded-md border border-dashed p-8 w-full hover:bg-muted/50"
                            >
                                <div v-if="isUploading" class="flex flex-col items-center">
                                    <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
                                    <span class="mt-2 text-sm text-muted-foreground">Uploading...</span>
                                </div>
                                <div v-else class="flex flex-col items-center">
                                    <Upload class="h-8 w-8 text-muted-foreground" />
                                    <span class="mt-2 text-sm text-muted-foreground">Click to upload new image</span>
                                </div>
                            </label>
                        </div>

                        <div v-if="isLoading" class="flex justify-center p-8">
                            <Loader2 class="h-8 w-8 animate-spin" />
                        </div>

                        <div v-else class="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-5">
                            <div
                                v-for="img in images"
                                :key="img.path"
                                class="group relative aspect-square cursor-pointer overflow-hidden rounded-md border bg-muted"
                                @click="selectImage(img.path)"
                            >
                                <img :src="img.url" class="h-full w-full object-cover transition-transform group-hover:scale-110" />
                                <div
                                    v-if="selectedPath === img.path"
                                    class="absolute inset-0 flex items-center justify-center bg-primary/40"
                                >
                                    <Check class="h-8 w-8 text-white" />
                                </div>
                            </div>
                        </div>

                        <div v-if="!isLoading && images.length === 0" class="py-12 text-center text-muted-foreground">
                            No images found in storage/app/public/events
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
