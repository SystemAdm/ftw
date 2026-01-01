<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { trans } from 'laravel-vue-i18n';
import { useAppearance } from '@/composables/useAppearance';

type UserProps = {
  name: string;
  name_public: boolean;
  email: string;
  avatar?: string | null;
  header_image?: string | null;
  birthday?: string | null;
  birthday_visibility: 'birthdate' | 'birthyear' | 'age' | 'off';
  postal_code?: number | null;
  postal_code_visibility: 'postalcode' | 'city' | 'municipality' | 'country' | 'off';
  about?: string | null;
  appearance?: 'light' | 'dark' | 'system';
  phone_public: boolean;
  email_public: boolean;
};

type SubscriptionProps = {
  active: boolean;
  ends_at: string | null;
  on_grace_period: boolean;
  next_billing_date: string | null;
  time_left: string | null;
};

type GuardianProps = {
  id: number;
  name: string;
  email: string;
  relationship: string;
  verified_user_at: string | null;
  verified_guardian_at: string | null;
  verified_at: string | null;
};

const page = usePage<{ user: UserProps; subscription: SubscriptionProps; guardians: GuardianProps[]; minors: GuardianProps[] }>();
const user = computed(() => page.props.user);
const subscription = computed(() => page.props.subscription);
const guardians = computed(() => page.props.guardians);
const minors = computed(() => page.props.minors);

const { updateAppearance: updateClientTheme } = useAppearance();

// Appearance form
const appearanceForm = useForm({
  appearance: user.value.appearance ?? 'system',
});

function submitAppearance() {
  appearanceForm.patch('/settings/appearance', {
    onSuccess: () => {
      updateClientTheme(appearanceForm.appearance as any);
    }
  });
}

// Profile form (birthdate, postal code, about, visibility)
const profileForm = useForm({
  birthday: user.value.birthday ?? '',
  birthday_visibility: user.value.birthday_visibility ?? 'off',
  postal_code: user.value.postal_code ?? '',
  postal_code_visibility: user.value.postal_code_visibility ?? 'off',
  about: user.value.about ?? '',
  name_public: user.value.name_public ?? true,
  phone_public: user.value.phone_public ?? false,
  email_public: user.value.email_public ?? false,
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

// Header image form
const headerInput = ref<HTMLInputElement | null>(null);
const headerForm = useForm<{ header_image: File | null }>({ header_image: null });

function onPickHeader(e: Event) {
  const t = e.target as HTMLInputElement;
  if (t?.files && t.files.length > 0) {
    headerForm.header_image = t.files[0];
  }
}

function submitHeader() {
  headerForm.post('/settings/header-image', {
    forceFormData: true,
    onSuccess: () => {
      if (headerInput.value) {
        headerInput.value.value = '';
      }
      headerForm.reset('header_image');
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

// Guardian management
const guardianForm = useForm({
  email: '',
  relationship: '',
});

function addGuardian() {
  guardianForm.post('/settings/guardians', {
    onSuccess: () => guardianForm.reset(),
  });
}

function removeGuardian(id: number) {
  if (confirm('Are you sure you want to remove this guardian?')) {
    useForm({}).delete(`/settings/guardians/${id}`);
  }
}

function verifyMinor(id: number) {
  useForm({}).post(`/settings/minors/${id}/verify`);
}

function removeMinor(id: number) {
  if (confirm('Are you sure you want to remove this minor?')) {
    useForm({}).delete(`/settings/minors/${id}`);
  }
}

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.settings.profile.title'),
        href: '/settings/profile',
    },
]);
</script>

<template>
  <SidebarLayout :breadcrumbs="breadcrumbs">
    <Head :title="trans('pages.settings.profile.title')" />
    <div class="space-y-6">
      <h1 class="text-2xl font-bold tracking-tight">{{ trans('pages.settings.profile.title') }}</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 xl:grid-cols-4">
        <!-- Appearance -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">{{ trans('pages.settings.profile.appearance.title') }}</h2>
          <form @submit.prevent="submitAppearance" class="space-y-4">
            <div class="space-y-2">
              <Label for="appearance">{{ trans('pages.settings.profile.appearance.theme') }}</Label>
              <Select v-model="appearanceForm.appearance">
                <SelectTrigger id="appearance">
                  <SelectValue :placeholder="trans('pages.settings.profile.appearance.select_theme')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="system">{{ trans('pages.settings.profile.appearance.system') }}</SelectItem>
                  <SelectItem value="light">{{ trans('pages.settings.profile.appearance.light') }}</SelectItem>
                  <SelectItem value="dark">{{ trans('pages.settings.profile.appearance.dark') }}</SelectItem>
                </SelectContent>
              </Select>
              <div v-if="appearanceForm.errors.appearance" class="text-sm text-red-600">{{ appearanceForm.errors.appearance }}</div>
            </div>
            <Button type="submit" :disabled="appearanceForm.processing">
              {{ appearanceForm.processing ? trans('pages.settings.profile.actions.saving') : trans('pages.settings.profile.actions.save') }}
            </Button>
          </form>
        </Card>

        <!-- Password -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">{{ trans('pages.settings.profile.password.title') }}</h2>
          <form @submit.prevent="submitPassword" class="space-y-4">
            <div class="space-y-2">
              <Label for="current_password">{{ trans('pages.settings.profile.password.current_password') }}</Label>
              <Input id="current_password" v-model="passwordForm.current_password" type="password" autocomplete="current-password" />
              <div v-if="passwordForm.errors.current_password" class="text-sm text-red-600">{{ passwordForm.errors.current_password }}</div>
            </div>
            <div class="space-y-2">
              <Label for="password">{{ trans('pages.settings.profile.password.new_password') }}</Label>
              <Input id="password" v-model="passwordForm.password" type="password" autocomplete="new-password" />
              <div v-if="passwordForm.errors.password" class="text-sm text-red-600">{{ passwordForm.errors.password }}</div>
            </div>
            <div class="space-y-2">
              <Label for="password_confirmation">{{ trans('pages.settings.profile.password.confirm_password') }}</Label>
              <Input id="password_confirmation" v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" />
            </div>
            <Button type="submit" :disabled="passwordForm.processing">
              {{ passwordForm.processing ? trans('pages.settings.profile.password.updating') : trans('pages.settings.profile.password.update_button') }}
            </Button>
          </form>
        </Card>

        <!-- Profile basics -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">{{ trans('pages.settings.profile.title') }}</h2>
          <form @submit.prevent="submitProfile" class="space-y-4">
            <div class="space-y-2">
              <Label for="birthday">{{ trans('pages.auth.login.birthday_label') }}</Label>
              <Input id="birthday" type="date" v-model="profileForm.birthday" />
              <div v-if="profileForm.errors.birthday" class="text-sm text-red-600">{{ profileForm.errors.birthday }}</div>
            </div>
            <div class="space-y-2">
              <Label for="postal_code">{{ trans('pages.auth.login.postal_code_label') }}</Label>
              <Input id="postal_code" type="number" v-model="profileForm.postal_code" />
              <div v-if="profileForm.errors.postal_code" class="text-sm text-red-600">{{ profileForm.errors.postal_code }}</div>
            </div>
            <div class="space-y-2">
              <Label for="about">{{ trans('pages.settings.profile.about') }}</Label>
              <textarea
                id="about"
                v-model="profileForm.about"
                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              ></textarea>
              <div v-if="profileForm.errors.about" class="text-sm text-red-600">{{ profileForm.errors.about }}</div>
            </div>

            <div class="space-y-4 pt-2">
              <h3 class="text-sm font-medium">{{ trans('pages.settings.profile.visibility.title') }}</h3>
              <div class="space-y-3">
                <div class="flex items-center space-x-2">
                  <Checkbox
                    id="name_public"
                    :checked="profileForm.name_public"
                    @update:checked="(v) => profileForm.name_public = v"
                  />
                  <Label for="name_public" class="text-sm font-normal">{{ trans('pages.settings.profile.visibility.show_full_name') }}</Label>
                </div>
                <div class="flex items-center space-x-2">
                  <Checkbox
                    id="email_public"
                    :checked="profileForm.email_public"
                    @update:checked="(v) => profileForm.email_public = v"
                  />
                  <Label for="email_public" class="text-sm font-normal">{{ trans('pages.settings.profile.visibility.show_email') }}</Label>
                </div>
                <div class="flex items-center space-x-2">
                  <Checkbox
                    id="phone_public"
                    :checked="profileForm.phone_public"
                    @update:checked="(v) => profileForm.phone_public = v"
                  />
                  <Label for="phone_public" class="text-sm font-normal">{{ trans('pages.settings.profile.visibility.show_phone') }}</Label>
                </div>
                <div class="space-y-2">
                  <Label for="birthday_visibility" class="text-sm font-normal">{{ trans('pages.settings.profile.visibility.birthday_visibility') }}</Label>
                  <Select v-model="profileForm.birthday_visibility">
                    <SelectTrigger id="birthday_visibility">
                      <SelectValue :placeholder="trans('pages.settings.profile.visibility.select_visibility')" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="birthdate">{{ trans('pages.settings.profile.visibility.birthdate') }}</SelectItem>
                      <SelectItem value="birthyear">{{ trans('pages.settings.profile.visibility.birthyear') }}</SelectItem>
                      <SelectItem value="age">{{ trans('pages.settings.profile.visibility.age') }}</SelectItem>
                      <SelectItem value="off">{{ trans('pages.settings.profile.visibility.off') }}</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div class="space-y-2">
                  <Label for="postal_code_visibility" class="text-sm font-normal">{{ trans('pages.settings.profile.visibility.postal_code_visibility') }}</Label>
                  <Select v-model="profileForm.postal_code_visibility">
                    <SelectTrigger id="postal_code_visibility">
                      <SelectValue :placeholder="trans('pages.settings.profile.visibility.select_visibility')" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="postalcode">{{ trans('pages.settings.profile.visibility.postalcode') }}</SelectItem>
                      <SelectItem value="city">{{ trans('pages.settings.profile.visibility.city') }}</SelectItem>
                      <SelectItem value="municipality">{{ trans('pages.settings.profile.visibility.municipality') }}</SelectItem>
                      <SelectItem value="country">{{ trans('pages.settings.profile.visibility.country') }}</SelectItem>
                      <SelectItem value="off">{{ trans('pages.settings.profile.visibility.off') }}</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <p class="text-xs text-muted-foreground">
                <Link href="/profile" class="underline hover:text-foreground">{{ trans('pages.settings.profile.view_public') }}</Link>
              </p>
            </div>

            <Button type="submit" :disabled="profileForm.processing">
              {{ profileForm.processing ? trans('pages.settings.profile.actions.saving') : trans('pages.settings.profile.actions.save') }}
            </Button>
          </form>
        </Card>

        <!-- Header Image -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">{{ trans('pages.settings.profile.header_image.title') }}</h2>
          <form @submit.prevent="submitHeader" class="space-y-4">
            <div class="space-y-2">
              <Label for="header_image">{{ trans('pages.settings.profile.header_image.upload') }}</Label>
              <Input id="header_image" ref="headerInput" type="file" accept="image/*" @change="onPickHeader" />
              <div v-if="headerForm.errors.header_image" class="text-sm text-red-600">{{ headerForm.errors.header_image }}</div>
            </div>
            <Button type="submit" :disabled="headerForm.processing || !headerForm.header_image">
              {{ headerForm.processing ? trans('pages.settings.profile.actions.uploading') : trans('pages.settings.profile.actions.upload') }}
            </Button>
          </form>
        </Card>

        <!-- Avatar -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">{{ trans('pages.settings.profile.avatar.title') }}</h2>
          <form @submit.prevent="submitAvatar" class="space-y-4">
            <div class="space-y-2">
              <Label for="avatar">{{ trans('pages.settings.profile.avatar.upload') }}</Label>
              <Input id="avatar" ref="avatarInput" type="file" accept="image/*" @change="onPickAvatar" />
              <div v-if="avatarForm.errors.avatar" class="text-sm text-red-600">{{ avatarForm.errors.avatar }}</div>
            </div>
            <Button type="submit" :disabled="avatarForm.processing || !avatarForm.avatar">
              {{ avatarForm.processing ? trans('pages.settings.profile.actions.uploading') : trans('pages.settings.profile.actions.upload') }}
            </Button>
          </form>
        </Card>

        <!-- Guardians -->
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">{{ trans('pages.settings.profile.guardians.title') }}</h2>
          <div v-if="guardians.length > 0" class="space-y-4">
            <div v-for="guardian in guardians" :key="guardian.id" class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <p class="font-medium">{{ guardian.name }}</p>
                <p class="text-xs text-muted-foreground">{{ guardian.email }} ({{ guardian.relationship }})</p>
                <div class="flex gap-2 mt-1">
                  <span v-if="guardian.verified_guardian_at" class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded">{{ trans('pages.settings.profile.guardians.verified_by_guardian') }}</span>
                  <span v-else class="text-[10px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded">{{ trans('pages.settings.profile.guardians.pending_verification') }}</span>
                  <span v-if="guardian.verified_at" class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">{{ trans('pages.settings.profile.guardians.verified_by_admin') }}</span>
                </div>
              </div>
              <Button variant="ghost" size="sm" @click="removeGuardian(guardian.id)">{{ trans('pages.settings.profile.actions.remove') }}</Button>
            </div>
          </div>
          <div v-else class="text-sm text-muted-foreground italic">
            {{ trans('pages.settings.profile.guardians.none') }}
          </div>

          <form @submit.prevent="addGuardian" class="space-y-4 pt-4 border-t">
            <h3 class="text-sm font-medium">{{ trans('pages.settings.profile.guardians.add') }}</h3>
            <div class="space-y-2">
              <Label for="guardian_email">{{ trans('pages.settings.profile.fields.email') }}</Label>
              <Input id="guardian_email" v-model="guardianForm.email" type="email" required />
              <div v-if="guardianForm.errors.email" class="text-sm text-red-600">{{ guardianForm.errors.email }}</div>
            </div>
            <div class="space-y-2">
              <Label for="guardian_relationship">{{ trans('pages.settings.profile.fields.relationship') }}</Label>
              <Input id="guardian_relationship" v-model="guardianForm.relationship" required />
              <div v-if="guardianForm.errors.relationship" class="text-sm text-red-600">{{ guardianForm.errors.relationship }}</div>
            </div>
            <Button type="submit" :disabled="guardianForm.processing">{{ trans('pages.settings.profile.guardians.add_button') }}</Button>
          </form>
        </Card>

        <!-- Minors -->
        <Card class="p-6 space-y-4" v-if="minors.length > 0 || user.birthday">
          <h2 class="text-lg font-semibold">{{ trans('pages.settings.profile.minors.title') }}</h2>
          <div v-if="minors.length > 0" class="space-y-4">
            <div v-for="minor in minors" :key="minor.id" class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <p class="font-medium">{{ minor.name }}</p>
                <p class="text-xs text-muted-foreground">{{ minor.email }} ({{ minor.relationship }})</p>
                <div class="flex gap-2 mt-1">
                  <span v-if="minor.verified_user_at" class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded">{{ trans('pages.settings.profile.minors.verified_by_minor') }}</span>
                  <span v-if="minor.verified_guardian_at" class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded">{{ trans('pages.settings.profile.minors.verified_by_you') }}</span>
                  <span v-else class="text-[10px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded">{{ trans('pages.settings.profile.minors.verification_needed') }}</span>
                  <span v-if="minor.verified_at" class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">{{ trans('pages.settings.profile.minors.verified_by_admin') }}</span>
                </div>
              </div>
              <div class="flex gap-2">
                <Button v-if="!minor.verified_guardian_at" variant="outline" size="sm" @click="verifyMinor(minor.id)">{{ trans('pages.settings.profile.actions.verify') }}</Button>
                <Button variant="ghost" size="sm" @click="removeMinor(minor.id)">{{ trans('pages.settings.profile.actions.remove') }}</Button>
              </div>
            </div>
          </div>
          <div v-else class="text-sm text-muted-foreground italic">
            {{ trans('pages.settings.profile.minors.none') }}
          </div>
          <p class="text-xs text-muted-foreground">
            {{ trans('pages.settings.profile.minors.help') }}
          </p>
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
                </span>
              </p>
              <div v-if="subscription.time_left" class="text-xs font-medium text-gray-900 dark:text-gray-100">
                {{ trans('pages.settings.billing.time_left') }}: {{ subscription.time_left }}
              </div>
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
