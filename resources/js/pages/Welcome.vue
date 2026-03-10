<script setup lang="ts">
import PublicLayout from '@/components/layouts/PublicLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const appName = page.props.name || 'FTW';
</script>

<template>
    <Head :title="`${appName} • Play. Compete. Win.`">
        <meta
            name="description"
            :content="`${appName} is where gamers squad up, climb leaderboards, and win rewards. Join matches, track stats, and show your skills.`"
        />
    </Head>

    <PublicLayout>
        <!-- Hero -->
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
            <section class="relative overflow-hidden rounded-2xl border bg-background p-5 text-foreground sm:p-8 md:p-10 lg:p-14">
                <div class="flex flex-col items-center justify-between gap-6 text-center md:flex-row md:items-start md:gap-8 md:text-left">
                    <div class="md:w-1/2">
                        <p
                            class="mb-2 inline-flex items-center gap-2 rounded-full border border-yellow-400/20 bg-yellow-500/10 px-3 py-1 text-xs tracking-wider text-yellow-300 uppercase"
                        >
                            PÅSKE
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-yellow-400"></span>
                        </p>
                        <h1 class="text-2xl leading-tight font-black tracking-tight sm:text-3xl md:text-5xl">
                            <span class="bg-linear-to-r from-yellow-600 to-yellow-100 bg-clip-text text-transparent">
                                Påsken - håp, kjærlighet og livets seier over døden
                            </span>
                        </h1>
                        <p class="mt-3 text-sm/relaxed text-foreground sm:mt-4 md:text-base/relaxed">
                            Samlet sett er påsken en feiring av håp, kjærlighet og livets seier over døden, med dype røtter i både jødisk historie og
                            kristen tro.
                        </p>
                        <p class="mt-3 text-sm/relaxed text-foreground sm:mt-4 md:text-base/relaxed">
                            Vi ser oss nødt til å holde <strong>stengt</strong> Langfredag i påsken, <strong>fredag 3. april</strong>, da vi ønsker at
                            vårt crew skal få en velfortjent ferie.
                        </p>
                    </div>
                    <!-- Mocked game card / screenshot -->
                    <div class="md:w-1/2">
                        <p
                            class="mb-2 inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-500/10 px-3 py-1 text-xs tracking-wider text-blue-300 uppercase"
                        >
                            Løkketangen
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-blue-400"></span>
                        </p>
                        <h2 class="text-2xl leading-tight font-black tracking-tight sm:text-3xl md:text-5xl">
                            Vi forbereder oss på flytting til
                            <span class="bg-linear-to-r from-blue-400 to-blue-950 bg-clip-text text-transparent">Sandvika</span>.
                        </h2>
                        <p class="mt-3 text-sm/relaxed text-foreground sm:mt-4 md:text-base/relaxed">
                            Ingen dato er fastsatt, men vi jobber for fullt med å flytte til ett litt mer sentralt strøk, Sandvika.
                        </p>
                        <p class="text-muted-foreground">Vi er fremdeles veldig tidlig i prosessen.</p>
                    </div>
                </div>
            </section>
            <!-- Features -->
            <section id="features" class="mx-auto grid grid-cols-1 gap-4 py-12 sm:grid-cols-2 lg:grid-cols-2">
                <div class="rounded-xl border bg-red-900 p-6 text-center">
                    <div class="flex flex-col items-center p-0">
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-fuchsia-500/15 text-fuchsia-300">
                            <img src="/images/UFB_logo.png" alt="UFB logo" class="h-6 w-6" />
                        </div>
                        <h3 class="font-semibold text-foreground">Tirsdag og Torsdagsåpent!</h3>
                    </div>
                    <div class="p-0">
                        <p class="mt-1 text-xl text-foreground">
                            Flyttet til Løkketangen i Sandvika. <br />Informasjon om UFB Løkketangen finner du her:<br />
                            <Link href="https://www.ufbinfo.no/ufb-lkketangen" class="text-blue-600">UFB Løkketangen</Link>
                        </p>
                    </div>
                </div>
                <div class="rounded-xl border bg-background p-6 text-center">
                    <div class="flex flex-col items-center p-0">
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-black text-white">
                            <img src="/images/Spillhuset_logo_light.png" alt="Spillhuset logo" />
                        </div>
                        <h3 class="font-semibold text-white">Fredagsåpent</h3>
                    </div>
                    <div class="p-0">
                        <p class="mt-1 text-sm text-muted-foreground">
                            Kun Spillhuset er på bygget.<br />Åpningstid: 18:00 - 22:00<br />Åpent for alle over 13 år
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
