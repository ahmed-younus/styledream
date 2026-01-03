<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-4xl mx-auto">
        <div class="bg-secondary rounded-2xl p-6 md:p-10 border border-border">
            <h1 class="text-3xl font-bold text-foreground mb-2">{{ __('privacy.title') }}</h1>
            <p class="text-muted-foreground mb-8">{{ __('privacy.last_updated') }}: {{ date('F j, Y') }}</p>

            <div class="prose prose-sm max-w-none text-foreground prose-headings:text-foreground prose-p:text-muted-foreground prose-li:text-muted-foreground prose-strong:text-foreground">

                {{-- 1. Introduction --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">1. {{ __('privacy.section1_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section1_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('privacy.section1_p2') }}</p>
                </section>

                {{-- 2. Information We Collect --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">2. {{ __('privacy.section2_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section2_p1') }}</p>

                    <h3 class="text-lg font-semibold mt-4 mb-2">{{ __('privacy.section2_sub1') }}</h3>
                    <ul class="list-disc pl-6 space-y-1 text-muted-foreground">
                        <li>{{ __('privacy.section2_li1') }}</li>
                        <li>{{ __('privacy.section2_li2') }}</li>
                        <li>{{ __('privacy.section2_li3') }}</li>
                    </ul>

                    <h3 class="text-lg font-semibold mt-4 mb-2">{{ __('privacy.section2_sub2') }}</h3>
                    <ul class="list-disc pl-6 space-y-1 text-muted-foreground">
                        <li>{{ __('privacy.section2_li4') }}</li>
                        <li>{{ __('privacy.section2_li5') }}</li>
                    </ul>

                    <h3 class="text-lg font-semibold mt-4 mb-2">{{ __('privacy.section2_sub3') }}</h3>
                    <ul class="list-disc pl-6 space-y-1 text-muted-foreground">
                        <li>{{ __('privacy.section2_li6') }}</li>
                        <li>{{ __('privacy.section2_li7') }}</li>
                        <li>{{ __('privacy.section2_li8') }}</li>
                    </ul>
                </section>

                {{-- 3. How We Use Information --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">3. {{ __('privacy.section3_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section3_p1') }}</p>
                    <ul class="list-disc pl-6 space-y-1 text-muted-foreground">
                        <li>{{ __('privacy.section3_li1') }}</li>
                        <li>{{ __('privacy.section3_li2') }}</li>
                        <li>{{ __('privacy.section3_li3') }}</li>
                        <li>{{ __('privacy.section3_li4') }}</li>
                        <li>{{ __('privacy.section3_li5') }}</li>
                    </ul>
                </section>

                {{-- 4. AI Processing --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">4. {{ __('privacy.section4_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section4_p1') }}</p>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section4_p2') }}</p>
                    <p class="text-muted-foreground">{{ __('privacy.section4_p3') }}</p>
                </section>

                {{-- 5. Data Sharing --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">5. {{ __('privacy.section5_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section5_p1') }}</p>
                    <ul class="list-disc pl-6 space-y-1 text-muted-foreground">
                        <li><strong>{{ __('privacy.section5_provider1') }}:</strong> {{ __('privacy.section5_desc1') }}</li>
                        <li><strong>{{ __('privacy.section5_provider2') }}:</strong> {{ __('privacy.section5_desc2') }}</li>
                        <li><strong>{{ __('privacy.section5_provider3') }}:</strong> {{ __('privacy.section5_desc3') }}</li>
                    </ul>
                    <p class="text-muted-foreground mt-3">{{ __('privacy.section5_p2') }}</p>
                </section>

                {{-- 6. Data Retention --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">6. {{ __('privacy.section6_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section6_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('privacy.section6_p2') }}</p>
                </section>

                {{-- 7. Your Rights --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">7. {{ __('privacy.section7_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section7_p1') }}</p>
                    <ul class="list-disc pl-6 space-y-1 text-muted-foreground">
                        <li><strong>{{ __('privacy.section7_right1') }}:</strong> {{ __('privacy.section7_desc1') }}</li>
                        <li><strong>{{ __('privacy.section7_right2') }}:</strong> {{ __('privacy.section7_desc2') }}</li>
                        <li><strong>{{ __('privacy.section7_right3') }}:</strong> {{ __('privacy.section7_desc3') }}</li>
                        <li><strong>{{ __('privacy.section7_right4') }}:</strong> {{ __('privacy.section7_desc4') }}</li>
                        <li><strong>{{ __('privacy.section7_right5') }}:</strong> {{ __('privacy.section7_desc5') }}</li>
                    </ul>
                    <p class="text-muted-foreground mt-3">{{ __('privacy.section7_p2') }}</p>
                </section>

                {{-- 8. Cookies --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">8. {{ __('privacy.section8_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section8_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('privacy.section8_p2') }}</p>
                </section>

                {{-- 9. Children's Privacy --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">9. {{ __('privacy.section9_title') }}</h2>
                    <p class="text-muted-foreground">{{ __('privacy.section9_p1') }}</p>
                </section>

                {{-- 10. International Transfers --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">10. {{ __('privacy.section10_title') }}</h2>
                    <p class="text-muted-foreground">{{ __('privacy.section10_p1') }}</p>
                </section>

                {{-- 11. Security --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">11. {{ __('privacy.section11_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section11_p1') }}</p>
                    <p class="text-muted-foreground">{{ __('privacy.section11_p2') }}</p>
                </section>

                {{-- 12. Changes --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">12. {{ __('privacy.section12_title') }}</h2>
                    <p class="text-muted-foreground">{{ __('privacy.section12_p1') }}</p>
                </section>

                {{-- 13. Contact --}}
                <section class="mb-8">
                    <h2 class="text-xl font-bold mb-3">13. {{ __('privacy.section13_title') }}</h2>
                    <p class="text-muted-foreground mb-3">{{ __('privacy.section13_p1') }}</p>
                    <p class="text-muted-foreground">
                        <strong>{{ __('privacy.email') }}:</strong> privacy@styledream.app
                    </p>
                </section>

            </div>
        </div>
    </div>
</div>
