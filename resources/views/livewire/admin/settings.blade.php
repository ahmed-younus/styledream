<div>
    {{-- Tabs --}}
    <div class="border-b border-[#e3e8ee] mb-6 -mx-4 px-4 sm:mx-0 sm:px-0">
        <nav class="flex gap-2 sm:gap-6 overflow-x-auto pb-px">
            <button wire:click="$set('activeTab', 'api')" class="pb-3 px-1 whitespace-nowrap {{ $activeTab === 'api' ? 'border-b-2 border-[#635bff] text-[#1a1f36]' : 'text-[#697386] hover:text-[#1a1f36]' }} font-medium transition-colors text-sm">
                API Keys
            </button>
            <button wire:click="$set('activeTab', 'smtp')" class="pb-3 px-1 whitespace-nowrap {{ $activeTab === 'smtp' ? 'border-b-2 border-[#635bff] text-[#1a1f36]' : 'text-[#697386] hover:text-[#1a1f36]' }} font-medium transition-colors text-sm">
                SMTP / Email
            </button>
            <button wire:click="$set('activeTab', 'general')" class="pb-3 px-1 whitespace-nowrap {{ $activeTab === 'general' ? 'border-b-2 border-[#635bff] text-[#1a1f36]' : 'text-[#697386] hover:text-[#1a1f36]' }} font-medium transition-colors text-sm">
                General
            </button>
            <button wire:click="$set('activeTab', 'seo')" class="pb-3 px-1 whitespace-nowrap {{ $activeTab === 'seo' ? 'border-b-2 border-[#635bff] text-[#1a1f36]' : 'text-[#697386] hover:text-[#1a1f36]' }} font-medium transition-colors text-sm">
                SEO
            </button>
        </nav>
    </div>

    {{-- API Settings --}}
    @if($activeTab === 'api')
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5 sm:p-6">
            <h3 class="text-base font-semibold text-[#1a1f36] mb-6">API Configuration</h3>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Google AI API Key</label>
                    <input type="password" wire:model="settings.google_ai_api_key" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="AIza...">
                    <p class="text-xs text-[#697386] mt-1">Used for virtual try-on generation</p>
                </div>

                <div class="border-t border-[#e3e8ee] pt-6">
                    <h4 class="text-sm font-medium text-[#1a1f36] mb-4">Stripe Configuration</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Public Key</label>
                            <input type="password" wire:model="settings.stripe_public_key" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="pk_...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Secret Key</label>
                            <input type="password" wire:model="settings.stripe_secret_key" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="sk_...">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Webhook Secret</label>
                            <input type="password" wire:model="settings.stripe_webhook_secret" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="whsec_...">
                        </div>
                    </div>
                </div>

                <div class="border-t border-[#e3e8ee] pt-6">
                    <h4 class="text-sm font-medium text-[#1a1f36] mb-2">Cloudflare Turnstile (Captcha)</h4>
                    <p class="text-sm text-[#697386] mb-4">Used for protecting forms from spam and bots</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Site Key</label>
                            <input type="text" wire:model="settings.turnstile_site_key" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="0x...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Secret Key</label>
                            <input type="password" wire:model="settings.turnstile_secret_key" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="0x...">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button wire:click="saveApiSettings" class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                        Save API Settings
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- SMTP Settings --}}
    @if($activeTab === 'smtp')
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5 sm:p-6">
            <h3 class="text-base font-semibold text-[#1a1f36] mb-6">SMTP Configuration</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">SMTP Host</label>
                    <input type="text" wire:model="settings.smtp_host" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="smtp.example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Port</label>
                    <input type="number" wire:model="settings.smtp_port" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="587">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Username</label>
                    <input type="text" wire:model="settings.smtp_username" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Password</label>
                    <input type="password" wire:model="settings.smtp_password" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">From Address</label>
                    <input type="email" wire:model="settings.mail_from_address" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="hello@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">From Name</label>
                    <input type="text" wire:model="settings.mail_from_name" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="Stylely">
                </div>
            </div>

            {{-- Admin Email Section --}}
            <div class="mt-6 pt-6 border-t border-[#e3e8ee]">
                <h4 class="text-sm font-medium text-[#1a1f36] mb-2">Notification Email</h4>
                <p class="text-sm text-[#697386] mb-4">This email will receive contact form submissions and brand registration notifications.</p>
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Admin Email</label>
                    <input type="email" wire:model="settings.admin_email" class="w-full md:w-1/2 px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="info@stylely.ai">
                </div>
            </div>

            <div class="pt-6">
                <button wire:click="saveSmtpSettings" class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                    Save SMTP Settings
                </button>
            </div>

            {{-- Test Email Section --}}
            <div class="mt-6 pt-6 border-t border-[#e3e8ee]">
                <h4 class="text-sm font-semibold text-[#1a1f36] mb-2">Test Email Configuration</h4>
                <p class="text-sm text-[#697386] mb-4">Send a test email to verify your SMTP settings are working correctly.</p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input
                            type="email"
                            wire:model="testEmailTo"
                            placeholder="Enter email address to receive test email"
                            class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm"
                        >
                        @error('testEmailTo')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button
                        type="button"
                        wire:click="sendTestEmail"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium flex items-center justify-center gap-2 whitespace-nowrap min-w-[140px]"
                    >
                        <span wire:loading.remove wire:target="sendTestEmail">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Send Test Email
                        </span>
                        <span wire:loading wire:target="sendTestEmail" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                </div>

                {{-- Test Email Result Display --}}
                @if(session()->has('testEmailResult'))
                    <div class="mt-4 p-4 rounded-lg {{ session('testEmailSuccess') ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                        <div class="flex items-start gap-3">
                            @if(session('testEmailSuccess'))
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800">Email Sent Successfully!</p>
                                    <p class="text-sm text-green-700 mt-1">{{ session('testEmailResult') }}</p>
                                </div>
                            @else
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-red-800">Email Failed to Send</p>
                                    <p class="text-sm text-red-700 mt-1">{{ session('testEmailResult') }}</p>
                                    @if(session('testEmailTip'))
                                        <div class="mt-2 p-2 bg-red-100 rounded text-sm text-red-800">
                                            <strong>Tip:</strong> {{ session('testEmailTip') }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- General Settings --}}
    @if($activeTab === 'general')
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5 sm:p-6">
            <h3 class="text-base font-semibold text-[#1a1f36] mb-6">General Settings</h3>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Site Name</label>
                    <input type="text" wire:model="settings.site_name" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model="settings.maintenance_mode" id="maintenance" class="w-4 h-4 text-[#635bff] border-[#e3e8ee] rounded focus:ring-[#635bff]/20">
                    <label for="maintenance" class="text-sm font-medium text-[#1a1f36]">Enable Maintenance Mode</label>
                </div>

                <div class="border-t border-[#e3e8ee] pt-6">
                    <h4 class="text-sm font-medium text-[#1a1f36] mb-4">Credit Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Free Credits on Signup</label>
                            <input type="number" wire:model="settings.signup_credits" min="0" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Daily Free Credits</label>
                            <input type="number" wire:model="settings.daily_free_credits" min="0" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button wire:click="saveGeneralSettings" class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                        Save General Settings
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- SEO Settings --}}
    @if($activeTab === 'seo')
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5 sm:p-6">
            <h3 class="text-base font-semibold text-[#1a1f36] mb-2">SEO Settings</h3>
            <p class="text-sm text-[#697386] mb-6">Configure meta tags for each page to improve search engine visibility.</p>

            <div class="space-y-6">
                @php
                    $pageLabels = [
                        'home' => 'Homepage',
                        'pricing' => 'Pricing',
                        'studio' => 'Try-On Studio',
                        'wardrobe' => 'Wardrobe',
                        'feed' => 'Style Feed',
                        'brands' => 'For Brands',
                        'about' => 'About Us',
                        'contact' => 'Contact',
                        'login' => 'Login',
                        'register' => 'Register',
                        'terms' => 'Terms of Service',
                        'privacy' => 'Privacy Policy',
                    ];
                @endphp

                @foreach($pageLabels as $key => $label)
                    @if(isset($seoSettings[$key]))
                        <div class="border-b border-[#e3e8ee] pb-6 last:border-0">
                            <h4 class="text-sm font-medium text-[#1a1f36] mb-4">{{ $label }}</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Meta Title</label>
                                    <input type="text" wire:model="seoSettings.{{ $key }}.meta_title" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="Page title for search engines">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Meta Description</label>
                                    <textarea wire:model="seoSettings.{{ $key }}.meta_description" rows="2" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors resize-none text-sm" placeholder="Brief description for search results"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Meta Keywords</label>
                                    <input type="text" wire:model="seoSettings.{{ $key }}.meta_keywords" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm" placeholder="keyword1, keyword2, keyword3">
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="pt-6">
                <button wire:click="saveSeoSettings" class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                    Save SEO Settings
                </button>
            </div>
        </div>
    @endif
</div>
