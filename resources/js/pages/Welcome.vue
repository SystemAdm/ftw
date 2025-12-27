<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/components/layouts/PublicLayout.vue'
</script>

<template>
    <Head title="FTW • Play. Compete. Win.">
        <meta
            name="description"
            content="FTW is where gamers squad up, climb leaderboards, and win rewards. Join matches, track stats, and show your skills."
        />
    </Head>

    <PublicLayout>
        <!-- Hero -->
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
            <section class="relative overflow-hidden rounded-2xl border bg-background text-foreground p-5 sm:p-8 md:p-10 lg:p-14">
                                <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-6 md:gap-8 text-center md:text-left">
                    <div class="md:w-1/2">
                        <p
                            class="mb-2 inline-flex items-center gap-2 rounded-full border border-green-400/20 bg-green-500/10 px-3 py-1 text-xs tracking-wider text-green-300 uppercase"
                        >
                            JUL & NYTT ÅR
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-green-400"></span>
                        </p>
                        <h1 class="text-2xl sm:text-3xl md:text-5xl leading-tight font-black tracking-tight">
                            Vi ønsker deg og dine en riktig
                            <span class="bg-linear-to-r from-green-400 to-red-400 bg-clip-text md:text-nowrap text-transparent">God Jul</span> og <span class="bg-linear-to-r from-yellow-200 to-yellow-900 bg-clip-text text-transparent">Godt nytt år</span>
                        </h1>
                        <p class="mt-3 sm:mt-4 text-sm/relaxed text-foreground md:text-base/relaxed">
                            Vi må dessverre holde stengt i romjulen,<br /> siste åpningsdag: <span class="md:text-nowrap font-bold">19. desember 2025</span><br />
                            åpner igjen: <span class="md:text-nowrap font-bold">9. januar 2026</span>
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
                        <h2 class="text-2xl sm:text-3xl md:text-5xl leading-tight font-black tracking-tight">
                            Vi forbereder oss på flytting til
                            <span class="bg-linear-to-r from-blue-400 to-blue-950 bg-clip-text text-transparent">Sandvika</span>.
                        </h2>
                        <p class="mt-3 sm:mt-4 text-sm/relaxed text-foreground md:text-base/relaxed">
                            Ingen dato er fastsatt, men vi jobber for fullt med å flytte til ett litt mer sentralt strøk, Sandvika.
                        </p>
                        <p class="text-muted-foreground">Vi er fremdeles veldig tidlig i prosessen.</p>
                    </div>
                </div>
            </section>
            <!-- Features -->
            <section id="features" class="mx-auto grid grid-cols-1 gap-4 py-12 sm:grid-cols-2 lg:grid-cols-2">
                <div class="rounded-xl border bg-background p-6 text-center">
                    <div class="flex flex-col items-center p-0">
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-fuchsia-500/15 text-fuchsia-300">
                            <img src="/images/UFB_logo.png" alt="UFB logo" class="h-6 w-6" />
                        </div>
                        <h3 class="font-semibold text-foreground">Torsdagsåpent!</h3>
                    </div>
                    <div class="p-0">
                        <p class="mt-1 text-sm text-muted-foreground">
                            Vi holder åpent sammen med
                            <a href="https://www.ufbinfo.no" class="font-bold text-foreground"><abbr title="Ungdom og Fritid i Bærum kommune">UFB</abbr></a
                            >.<br />Åpningstid: 15:00 - 21:00<br />Åpent for alle over 13 år
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
