<div class="pt-20">
    <section class="py-16 sm:py-24 bg-background">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <h1 class="text-4xl sm:text-5xl font-bold text-foreground mb-4">
                    Get in Touch
                </h1>
                <p class="text-lg text-muted-foreground">
                    Have a question or feedback? We'd love to hear from you.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-12">
                {{-- Email --}}
                <div class="text-center p-6 bg-secondary rounded-2xl">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-foreground mb-1">Email</h3>
                    <a href="mailto:support@styledream.ai" class="text-muted-foreground hover:text-primary transition-colors">support@styledream.ai</a>
                </div>

                {{-- Response Time --}}
                <div class="text-center p-6 bg-secondary rounded-2xl">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-foreground mb-1">Response Time</h3>
                    <p class="text-muted-foreground">Within 24 hours</p>
                </div>

                {{-- Social --}}
                <div class="text-center p-6 bg-secondary rounded-2xl">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-foreground mb-1">Social</h3>
                    <p class="text-muted-foreground">@styledream</p>
                </div>
            </div>

            {{-- Contact Form --}}
            <div class="bg-card border border-border rounded-2xl p-6 sm:p-8">
                @if($submitted)
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-foreground mb-2">Message Sent!</h3>
                        <p class="text-muted-foreground mb-6">Thank you for reaching out. We'll get back to you soon.</p>
                        <button wire:click="resetForm" class="px-6 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                            Send Another Message
                        </button>
                    </div>
                @else
                    <form wire:submit="submit" class="space-y-6">
                        @if($error)
                            <div class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg text-destructive text-sm">
                                {{ $error }}
                            </div>
                        @endif

                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-foreground mb-2">Name</label>
                                <input type="text" id="name" wire:model="name"
                                       class="w-full px-4 py-3 bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-transparent transition-colors"
                                       placeholder="Your name">
                                @error('name') <span class="text-sm text-destructive mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-foreground mb-2">Email</label>
                                <input type="email" id="email" wire:model="email"
                                       class="w-full px-4 py-3 bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-transparent transition-colors"
                                       placeholder="you@example.com">
                                @error('email') <span class="text-sm text-destructive mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-foreground mb-2">Subject</label>
                            <input type="text" id="subject" wire:model="subject"
                                   class="w-full px-4 py-3 bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-transparent transition-colors"
                                   placeholder="What's this about?">
                            @error('subject') <span class="text-sm text-destructive mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-foreground mb-2">Message</label>
                            <textarea id="message" wire:model="message" rows="5"
                                      class="w-full px-4 py-3 bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-transparent transition-colors resize-none"
                                      placeholder="Your message..."></textarea>
                            @error('message') <span class="text-sm text-destructive mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Turnstile Widget --}}
                        @if($turnstileSiteKey)
                            <div wire:ignore>
                                <div id="turnstile-widget"></div>
                            </div>
                        @endif

                        <button type="submit"
                                class="w-full px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50">
                            <span wire:loading.remove wire:target="submit">Send Message</span>
                            <span wire:loading wire:target="submit" class="flex items-center gap-2">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sending...
                            </span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    {{-- Turnstile Script --}}
    @if($turnstileSiteKey)
        @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <script>
            document.addEventListener('livewire:navigated', initTurnstile);
            document.addEventListener('DOMContentLoaded', initTurnstile);

            function initTurnstile() {
                if (typeof turnstile !== 'undefined' && document.getElementById('turnstile-widget')) {
                    turnstile.render('#turnstile-widget', {
                        sitekey: '{{ $turnstileSiteKey }}',
                        callback: function(token) {
                            @this.set('turnstileToken', token);
                        },
                    });
                }
            }

            Livewire.on('resetTurnstile', () => {
                if (typeof turnstile !== 'undefined') {
                    turnstile.reset('#turnstile-widget');
                }
            });
        </script>
        @endpush
    @endif
</div>
