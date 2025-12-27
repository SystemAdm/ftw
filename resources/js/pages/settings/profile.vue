<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { trans } from 'laravel-vue-i18n';

type UserProps = {
  name: string;
  email: string;
  avatar?: string | null;
  birthday?: string | null;
  postal_code?: number | null;
  appearance?: 'light' | 'dark' | 'system';
};

type SubscriptionProps = {
  active: boolean;
  ends_at: string | null;
  on_grace_period: boolean;
  next_billing_date: string | null;
  time_left: string | null;
};

const page = usePage<{ user: UserProps; subscription: SubscriptionProps }>();
const user = computed(() => page.props.user);
const subscription = computed(() => page.props.subscription);

// Appearance form
const appearanceForm = useForm({
  appearance: user.value.appearance ?? 'system',
});

function submitAppearance() {
  appearanceForm.patch('/settings/appearance');
}

// Profile form (birthdate, postal code)
const profileForm = useForm({
  birthday: user.value.birthday ?? '',
  postal_code: user.value.postal_code ?? '',
});

function submitProfile() {
  profileForm.patch('/settings/profile');
}

// Password form
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

function submitPassword() {
  passwordForm.patch('/settings/password', {
    onSuccess: () => {
      passwordForm.reset('current_password', 'password', 'password_confirmation');
    },
  });
}

// Avatar form
const avatarInput = ref<HTMLInputElement | null>(null);
const avatarForm = useForm<{ avatar: File | null }>({ avatar: null });

function onPickAvatar(e: Event) {
  const t = e.target as HTMLInputElement;
  if (t?.files && t.files.length > 0) {
    avatarForm.avatar = t.files[0];
  }
}

function submitAvatar() {
  avatarForm.post('/settings/avatar', {
    forceFormData: true,
    onSuccess: () => {
      if (avatarInput.value) {
        avatarInput.value.value = '';
      }
      avatarForm.reset('avatar');
    },
  });
}
</script>

<template>
  <SidebarLayout>
    <Head title="Settings" />
    <div class="space-y-6">
      <h1 class="text-2xl font-bold tracking-tight">Settings</h1>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <!-- Appearance -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">Appearance</h2>
          <form @submit.prevent="submitAppearance" class="space-y-4">
            <div class="space-y-2">
              <Label for="appearance">Theme</Label>
              <Select v-model="appearanceForm.appearance">
                <SelectTrigger id="appearance">
                  <SelectValue placeholder="Select theme" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="system">System</SelectItem>
                  <SelectItem value="light">Light</SelectItem>
                  <SelectItem value="dark">Dark</SelectItem>
                </SelectContent>
              </Select>
              <div v-if="appearanceForm.errors.appearance" class="text-sm text-red-600">{{ appearanceForm.errors.appearance }}</div>
            </div>
            <Button type="submit" :disabled="appearanceForm.processing">
              {{ appearanceForm.processing ? 'Saving…' : 'Save' }}
            </Button>
          </form>
        </Card>

        <!-- Password -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">Password</h2>
          <form @submit.prevent="submitPassword" class="space-y-4">
            <div class="space-y-2">
              <Label for="current_password">Current password</Label>
              <Input id="current_password" v-model="passwordForm.current_password" type="password" autocomplete="current-password" />
              <div v-if="passwordForm.errors.current_password" class="text-sm text-red-600">{{ passwordForm.errors.current_password }}</div>
            </div>
            <div class="space-y-2">
              <Label for="password">New password</Label>
              <Input id="password" v-model="passwordForm.password" type="password" autocomplete="new-password" />
              <div v-if="passwordForm.errors.password" class="text-sm text-red-600">{{ passwordForm.errors.password }}</div>
            </div>
            <div class="space-y-2">
              <Label for="password_confirmation">Confirm password</Label>
              <Input id="password_confirmation" v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" />
            </div>
            <Button type="submit" :disabled="passwordForm.processing">
              {{ passwordForm.processing ? 'Updating…' : 'Update password' }}
            </Button>
          </form>
        </Card>

        <!-- Profile basics -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">Profile</h2>
          <form @submit.prevent="submitProfile" class="space-y-4">
            <div class="space-y-2">
              <Label for="birthday">Birthdate</Label>
              <Input id="birthday" type="date" v-model="profileForm.birthday" />
              <div v-if="profileForm.errors.birthday" class="text-sm text-red-600">{{ profileForm.errors.birthday }}</div>
            </div>
            <div class="space-y-2">
              <Label for="postal_code">Postal code</Label>
              <Input id="postal_code" type="number" v-model="profileForm.postal_code" />
              <div v-if="profileForm.errors.postal_code" class="text-sm text-red-600">{{ profileForm.errors.postal_code }}</div>
            </div>
            <Button type="submit" :disabled="profileForm.processing">
              {{ profileForm.processing ? 'Saving…' : 'Save' }}
            </Button>
          </form>
        </Card>

        <!-- Avatar -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">Avatar</h2>
          <form @submit.prevent="submitAvatar" class="space-y-4">
            <div class="space-y-2">
              <Label for="avatar">Upload image</Label>
              <Input id="avatar" ref="avatarInput" type="file" accept="image/*" @change="onPickAvatar" />
              <div v-if="avatarForm.errors.avatar" class="text-sm text-red-600">{{ avatarForm.errors.avatar }}</div>
            </div>
            <Button type="submit" :disabled="avatarForm.processing || !avatarForm.avatar">
              {{ avatarForm.processing ? 'Uploading…' : 'Upload' }}
            </Button>
          </form>
        </Card>

        <!-- Subscription -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">{{ trans('pages.settings.billing.membership') }}</h2>
          <div class="space-y-2">
            <div v-if="subscription.active" class="space-y-1">
              <p class="text-sm font-medium">
                {{ trans('pages.settings.billing.status') }}:
                <span :class="subscription.on_grace_period ? 'text-yellow-600' : 'text-green-600'">
                  {{ subscription.on_grace_period ? trans('pages.settings.billing.cancelling') : trans('pages.settings.billing.active') }}
                  <span v-if="subscription.time_left" class="text-gray-500 font-normal ml-1">
                    ({{ subscription.time_left }})
                  </span>
                </span>
              </p>
              <p v-if="subscription.next_billing_date" class="text-sm text-gray-500">
                {{ subscription.on_grace_period ? trans('pages.settings.billing.ends') : trans('pages.settings.billing.renews') }}:
                {{ new Date(subscription.next_billing_date).toLocaleDateString((page.props as any).i18n.locale, {
                  year: 'numeric',
                  month: '2-digit',
                  day: '2-digit',
                }) }}
              </p>
            </div>
            <div v-else class="space-y-1">
              <p class="text-sm font-medium text-gray-500">{{ trans('pages.settings.billing.not_subscribed') }}</p>
              <p class="text-sm text-gray-500 italic">{{ trans('pages.settings.billing.support_us') }}</p>
              <p class="mt-2 text-xs text-yellow-600 italic">
                {{ trans('pages.settings.billing.note') }}
              </p>
            </div>
          </div>
          <div class="pt-2">
            <Link href="/settings/billing">
              <Button variant="secondary">{{ trans('pages.settings.billing.manage_subscription') }}</Button>
            </Link>
          </div>
        </Card>
      </div>
    </div>
  </SidebarLayout>
</template>

<style scoped></style>
