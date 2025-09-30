<script setup lang="ts">
import { LoaderCircle } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
  modelValue: string;
  errorMessage?: string | null;
  loading?: boolean;
  label?: string;
  placeholder?: string;
  googleEnabled?: boolean;
}>();

const emits = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'submit'): void;
}>();

const onSubmit = (e: Event) => {
  e.preventDefault();
  emits('submit');
};
</script>

<template>
  <div class="flex flex-col gap-6">
    <div class="grid gap-2">
      <Label for="identifier">{{ label || 'Email or phone number' }}</Label>
      <form @submit="onSubmit" class="flex flex-col gap-2">
        <Input
          id="identifier"
          :modelValue="modelValue"
          @update:modelValue="$emit('update:modelValue', $event as string)"
          name="identifier"
          type="text"
          required
          autofocus
          :placeholder="placeholder || 'e.g. +4712345678 or email@example.com'"
        />
        <InputError :message="errorMessage" />
        <Button type="submit" class="mt-2 w-full" :disabled="!!loading">
          <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin" />
          <span v-else>Continue</span>
        </Button>
      </form>
    </div>

    <div class="relative" v-if="googleEnabled !== false">
      <div class="absolute inset-0 flex items-center">
        <span class="w-full border-t"></span>
      </div>
      <div class="relative flex justify-center text-xs uppercase">
        <span class="bg-background px-2 text-muted-foreground">Or continue with</span>
      </div>
    </div>

    <div class="grid gap-2" v-if="googleEnabled !== false">
      <Button as="a" :href="'/auth/google'" variant="outline" class="w-full">Google</Button>
    </div>
  </div>
</template>
