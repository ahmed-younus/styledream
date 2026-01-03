<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-4xl mx-auto">
        <div class="bg-secondary rounded-2xl p-6 md:p-10 border border-border">
            <h1 class="text-3xl font-bold text-foreground mb-2">{{ __('terms.title') }}</h1>
            <p class="text-muted-foreground mb-8">{{ __('terms.last_updated') }}: {{ date('F j, Y') }}</p>

            <div class="prose prose-sm max-w-none text-foreground prose-headings:text-foreground prose-p:text-muted-foreground prose-li:text-muted-foreground prose-strong:text-foreground">

                {{-- 1. Introduction --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">1. {{ __('terms.section1_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section1_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section1_p2') }}</p>
                </section>

                {{-- 2. Description of Services --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">2. {{ __('terms.section2_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section2_p1') }}</p>
                    <ul class="list-disc pl-6 space-y-1 text-muted-foreground">
                        <li>{{ __('terms.section2_li1') }}</li>
                        <li>{{ __('terms.section2_li2') }}</li>
                        <li>{{ __('terms.section2_li3') }}</li>
                        <li>{{ __('terms.section2_li4') }}</li>
                    </ul>
                </section>

                {{-- 3. User Accounts --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">3. {{ __('terms.section3_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section3_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section3_p2') }}</p>
                </section>

                {{-- 4. AI-Generated Content --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">4. {{ __('terms.section4_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section4_p1') }}</p>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section4_p2') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section4_p3') }}</p>
                </section>

                {{-- 5. Image Usage & Rights --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">5. {{ __('terms.section5_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section5_p1') }}</p>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section5_p2') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section5_p3') }}</p>
                </section>

                {{-- 6. Credits & Payments --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">6. {{ __('terms.section6_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section6_p1') }}</p>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section6_p2') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section6_p3') }}</p>
                </section>

                {{-- 7. Refund Policy --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">7. {{ __('terms.section7_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section7_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section7_p2') }}</p>
                </section>

                {{-- 8. Prohibited Conduct --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">8. {{ __('terms.section8_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section8_p1') }}</p>
                    <ul class="list-disc pl-6 space-y-1 text-muted-foreground">
                        <li>{{ __('terms.section8_li1') }}</li>
                        <li>{{ __('terms.section8_li2') }}</li>
                        <li>{{ __('terms.section8_li3') }}</li>
                        <li>{{ __('terms.section8_li4') }}</li>
                        <li>{{ __('terms.section8_li5') }}</li>
                        <li>{{ __('terms.section8_li6') }}</li>
                    </ul>
                </section>

                {{-- 9. Intellectual Property --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">9. {{ __('terms.section9_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section9_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section9_p2') }}</p>
                </section>

                {{-- 10. Third-Party Services --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">10. {{ __('terms.section10_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section10_p1') }}</p>
                    <ul class="list-disc pl-6 space-y-1 text-muted-foreground">
                        <li>{{ __('terms.section10_li1') }}</li>
                        <li>{{ __('terms.section10_li2') }}</li>
                    </ul>
                </section>

                {{-- 11. Disclaimer of Warranties --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">11. {{ __('terms.section11_title') }}</h2>
                    <p class="text-muted-foreground mb-3 uppercase font-semibold">{{ __('terms.section11_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section11_p2') }}</p>
                </section>

                {{-- 12. Limitation of Liability --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">12. {{ __('terms.section12_title') }}</h2>
                    <p class="text-muted-foreground mb-3 uppercase font-semibold">{{ __('terms.section12_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section12_p2') }}</p>
                </section>

                {{-- 13. Indemnification --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">13. {{ __('terms.section13_title') }}</h2>
                    <p class="text-muted-foreground">{{ __('terms.section13_p1') }}</p>
                </section>

                {{-- 14. Termination --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">14. {{ __('terms.section14_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section14_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section14_p2') }}</p>
                </section>

                {{-- 15. Governing Law --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">15. {{ __('terms.section15_title') }}</h2>
                    <p class="text-muted-foreground">{{ __('terms.section15_p1') }}</p>
                </section>

                {{-- 16. Dispute Resolution --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">16. {{ __('terms.section16_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section16_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('terms.section16_p2') }}</p>
                </section>

                {{-- 17. Changes to Terms --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">17. {{ __('terms.section17_title') }}</h2>
                    <p class="text-muted-foreground">{{ __('terms.section17_p1') }}</p>
                </section>

                {{-- 18. Contact --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">18. {{ __('terms.section18_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('terms.section18_p1') }}</p>
                    <p class="text-muted-foreground">
                        <strong>{{ __('terms.email') }}:</strong> support@styledream.app
                    </p>
                </section>

            </div>
        </div>
    </div>
</div>
