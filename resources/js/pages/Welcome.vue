<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
</script>

<template>
    <Head title="FTW • Play. Compete. Win.">
        <meta
            name="description"
            content="FTW is where gamers squad up, climb leaderboards, and win rewards. Join matches, track stats, and show your skills."
        />
    </Head>

    <div class="min-h-screen bg-[#0b0b10] text-white selection:bg-green-500/40">
        <!-- Top nav -->
        <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-5">
            <div class="flex items-center gap-3">
                <span class="inline-grid h-9 w-9 place-items-center rounded-md bg-black bg-gradient-to-br shadow-lg shadow-green-500/20">
                    <img src="images/Spillhuset.png" alt="Spillhuset logo" />
                </span>
                <h1 class="text-xl font-bold tracking-wide">SPILLHUSET</h1>
            </div>
            <nav class="flex items-center gap-3 text-sm">
                <!--<Button @click="router.get('/login')">Login/Register</Button>-->
            </nav>
        </header>

        <!-- Hero -->
        <main class="mx-auto w-full max-w-7xl px-6">
            <section class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#0e0e14] p-8 lg:p-14">
                <!-- Glow -->
                <div class="pointer-events-none absolute -inset-20 -z-10 opacity-60" aria-hidden="true">
                    <div
                        class="absolute inset-0 bg-[radial-gradient(600px_200px_at_20%_-20%,rgba(217,70,239,.25),transparent),radial-gradient(600px_200px_at_80%_120%,rgba(244,63,94,.25),transparent)]"
                    ></div>
                </div>

                <div class="flex items-start justify-between gap-8">
                    <div class="md:w-1/2">
                        <p
                            class="mb-2 inline-flex items-center gap-2 rounded-full border border-green-400/20 bg-green-500/10 px-3 py-1 text-xs tracking-wider text-green-300 uppercase"
                        >
                            JUL & NYTT ÅR
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-green-400"></span>
                        </p>
                        <h1 class="text-3xl leading-tight font-black tracking-tight md:text-5xl">
                            Vi ønsker deg og dine en riktig
                            <span class="bg-gradient-to-r from-green-400 to-red-400 bg-clip-text text-nowrap text-transparent">God Jul</span> og <span class="bg-gradient-to-r from-yellow-200 to-yellow-900 bg-clip-text text-transparent">Godt nytt år</span>
                        </h1>
                        <p class="mt-4 text-sm/relaxed text-white md:text-base/relaxed">
                            Vi må dessverre holde stengt i romjulen,<br /> siste åpningsdag: <span class="text-nowrap font-bold">18. desember 2025</span><br />
                            åpner igjen: <span class="text-nowrap font-bold">9. januar 2026</span>
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
                        <h1 class="text-3xl leading-tight font-black tracking-tight md:text-5xl">
                            Vi forbereder oss på flytting til
                            <span class="bg-gradient-to-r from-blue-400 to-blue-950 bg-clip-text text-transparent">Sandvika</span>.
                        </h1>
                        <p class="mt-4 text-sm/relaxed text-white md:text-base/relaxed">
                            Ingen dato er fastsatt, men vi jobber for fullt med å flytte til ett litt mer sentralt strøk, Sandvika.
                        </p>
                        <p class="text-muted-foreground">Vi er fremdeles veldig tidlig i prosessen.</p>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section id="features" class="mx-auto grid grid-cols-1 gap-4 py-12 sm:grid-cols-2 lg:grid-cols-2">
                <Card class="rounded-xl border border-white/10 bg-white/5 p-6 text-center">
                    <CardHeader class="flex flex-col items-center p-0">
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-fuchsia-500/15 text-fuchsia-300">
                            <img src="images/UFB_logo.png" alt="UFB logo" />
                        </div>
                        <CardTitle class="font-semibold text-white">Torsdagsåpent!</CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        <p class="mt-1 text-sm text-white/70">
                            Vi holder åpent sammen med
                            <a href="https://www.ufbinfo.no" class="font-bold text-white"><abbr title="Ungdom og Fritid i Bærum kommune">UFB</abbr></a
                            >.<br />Åpningstid: 15:00 - 21:00<br />Åpent for alle over 13 år
                        </p>
                    </CardContent>
                </Card>
                <Card class="rounded-xl border border-white/10 bg-white/5 p-6 text-center">
                    <CardHeader class="flex flex-col items-center p-0">
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-black text-white">
                            <img src="images/Spillhuset.png" alt="Spillhuset logo" />
                        </div>
                        <CardTitle class="font-semibold text-white">Fredagsåpent</CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        <p class="mt-1 text-sm text-white/70">
                            Kun Spillhuset er på bygget.<br />Åpningstid: 18:00 - 22:00<br />Åpent for alle over 13 år
                        </p>
                    </CardContent>
                </Card>
            </section>
        </main>
        <!-- Footer -->
        <footer class="border-t border-white/10 py-8 text-center text-xs text-white/50">
            <p>Besøksadresse: Skollerudveien 5, 1353 Bærums Verk</p>
        </footer>
    </div>
</template>
