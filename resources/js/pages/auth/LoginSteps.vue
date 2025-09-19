<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { request as passwordRequest } from '@/routes/password';
import { faGoogle } from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle, User as UserIcon } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

const props = defineProps<{
    step: number;
    status?: string;
    canResetPassword: boolean;
    // Step 1
    errorsBag?: Record<string, string>;
    // Step 2
    mode?: 'select' | 'create';
    candidates?: Array<{
        id: number;
        name: string;
        email?: string;
        username?: string;
        avatar?: string;
        hasPassword: boolean;
        google_id?: string;
        phone?: string;
    }>;
    identifier?: string;
    canCreate?: boolean;
    // Step 3
    selectedUser?: { id: number; name: string; email?: string; username?: string; avatar?: string; phone?: string };
    needsPassword?: boolean;
}>();
</script>

<template>
    <AuthBase title="Log in to your account" description="Choose a sign-in method">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <!-- Step 1: identifier or Google -->
        <div v-if="step === 1" class="flex flex-col gap-6">
            <div class="grid gap-2">
                <Label for="identifier">Phone, email or username</Label>
                <Form :action="'/login/identify'" method="post" v-slot="{ errors, processing }" class="flex flex-col gap-2">
                    <Input
                        id="identifier"
                        name="identifier"
                        type="text"
                        required
                        autofocus
                        placeholder="e.g. +62812..., email@example.com or johndoe"
                    />
                    <InputError :message="errors.identifier || props.errorsBag?.identifier" />
                    <Button type="submit" class="mt-2 w-full" :disabled="processing">
                        <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                        Continue
                    </Button>
                </Form>
            </div>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t"></span>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-background px-2 text-muted-foreground">Or continue with</span>
                </div>
            </div>

            <div class="grid gap-2">
                <Button as="a" :href="'/auth/google'" variant="outline" class="w-full">Google</Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Forgot your password?
                <TextLink v-if="canResetPassword" :href="passwordRequest()">Reset it</TextLink>
            </div>
        </div>

        <!-- Step 2: select user or create -->
        <div v-else-if="step === 2" class="flex flex-col gap-6">
            <template v-if="mode === 'select' && candidates?.length">
                <div class="space-y-2">
                    <div class="text-sm text-muted-foreground">Select your account</div>
                    <div class="flex flex-col gap-3">
                        <Form :action="'/login/select'" method="post" class="flex flex-col gap-3">
                            <template v-for="u in candidates" :key="u.id">
                                <button
                                    type="submit"
                                    class="flex w-full items-center justify-between rounded-md border p-3 text-left hover:bg-accent disabled:opacity-60"
                                    name="user_id"
                                    :value="u.id"
                                    :disabled="!!u.google_id"
                                >
                                    <div class="flex items-center gap-3">
                                        <img v-if="u.avatar" :src="u.avatar" alt="avatar" class="h-6 w-6 rounded-full object-cover" />
                                        <UserIcon v-else class="h-6 w-6" />
                                        <div>
                                            <div class="font-medium">{{ u.name }}</div>
                                            <div class="text-xs text-muted-foreground">
                                                <span v-if="u.username">@{{ u.username }}</span>
                                                <span v-if="u.email">{{ u.email }}</span>
                                                <span v-if="u.phone">{{ u.phone }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="text-xs" v-if="u.hasPassword">Requires password</div>
                                        <div class="text-2xl" v-if="u.google_id">
                                            <TooltipProvider :delay-duration="0">
                                                <Tooltip>
                                                    <TooltipTrigger>
                                                        <div class="cursor-default" aria-label="Locked to Google account">
                                                            <FontAwesomeIcon :icon="faGoogle" />
                                                        </div>
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p>Locked to Google account</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </Form>
                        <div v-if="canCreate">
                            <Form :action="'/login/identify'" method="post" class="mt-2">
                                <input type="hidden" name="identifier" :value="identifier || ''" />
                                <input type="hidden" name="prefer_new" value="1" />
                                <Button type="submit" variant="outline" class="w-full">Create a new account instead</Button>
                            </Form>
                        </div>
                    </div>
                </div>
            </template>

            <template v-else>
                <div class="grid gap-2">
                    <div class="text-sm text-muted-foreground">Create a new account</div>
                    <Form :action="'/login/create'" method="post" v-slot="{ errors, processing }" class="flex flex-col gap-3">
                        <div>
                            <Label for="name">Name</Label>
                            <Input id="name" name="name" type="text" required />
                            <InputError :message="errors.name" />
                        </div>
                        <div>
                            <Label for="email">Email</Label>
                            <Input id="email" name="email" type="email" :value="identifier?.includes('@') ? identifier : ''" />
                            <InputError :message="errors.email" />
                        </div>
                        <div>
                            <Label for="username">Username</Label>
                            <Input id="username" name="username" type="text" :value="identifier && !identifier.includes('@') ? identifier : ''" />
                            <InputError :message="errors.username" />
                        </div>
                        <div>
                            <Label for="password">Password (optional)</Label>
                            <Input id="password" name="password" type="password" />
                            <InputError :message="errors.password" />
                        </div>
                        <Button type="submit" class="mt-2 w-full" :disabled="processing">
                            <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                            Create account
                        </Button>
                    </Form>
                </div>
            </template>
        </div>

        <!-- Step 3: confirm password (if needed) -->
        <div v-else-if="step === 3" class="flex flex-col gap-6">
            <div class="grid gap-2">
                <div class="text-sm text-muted-foreground">You're signing in as</div>
                <div class="flex items-center gap-3 rounded-md border p-3">
                    <UserIcon class="h-6 w-6" />
                    <div>
                        <div class="font-medium">{{ selectedUser?.name }}</div>
                        <div class="text-xs text-muted-foreground">
                            <span v-if="selectedUser?.username">@{{ selectedUser?.username }}</span>
                            <span v-if="selectedUser?.email">{{ selectedUser?.email }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="needsPassword" class="grid gap-2">
                <Label for="password">Enter your password</Label>
                <Form :action="'/login/confirm'" method="post" v-slot="{ errors, processing }" class="flex flex-col gap-2">
                    <Input id="password" name="password" type="password" required autofocus />
                    <InputError :message="errors.password" />
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="remember" value="1" />
                            Remember me
                        </label>
                        <TextLink v-if="canResetPassword" :href="passwordRequest()" class="text-sm">Forgot password?</TextLink>
                    </div>
                    <Button type="submit" class="mt-2 w-full" :disabled="processing">
                        <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                        Log in
                    </Button>
                </Form>
            </div>

            <div v-else class="grid gap-2">
                <Form :action="'/login/confirm'" method="post" v-slot="{ processing }">
                    <input type="hidden" name="password" value="" />
                    <Button type="submit" class="mt-2 w-full" :disabled="processing">Continue</Button>
                </Form>
            </div>
        </div>
    </AuthBase>
</template>
