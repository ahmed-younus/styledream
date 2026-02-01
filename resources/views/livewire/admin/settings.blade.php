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
            <button wire:click="$set('activeTab', 'email')" class="pb-3 px-1 whitespace-nowrap {{ $activeTab === 'email' ? 'border-b-2 border-[#635bff] text-[#1a1f36]' : 'text-[#697386] hover:text-[#1a1f36]' }} font-medium transition-colors text-sm">
                Email Templates
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

    {{-- Email Templates --}}
    @if($activeTab === 'email')
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-semibold text-[#1a1f36]">Email Templates</h3>
                    <p class="text-sm text-[#697386] mt-1">Customize email templates sent to users and admins</p>
                </div>
            </div>

            {{-- Template List --}}
            <div class="space-y-4">
                @foreach($emailTemplates as $key => $template)
                    <div class="border border-[#e3e8ee] rounded-lg p-4 hover:border-[#635bff]/30 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h4 class="font-medium text-[#1a1f36]">{{ $template['name'] ?? ucfirst(str_replace('-', ' ', $key)) }}</h4>
                                    @if($template['is_active'] ?? true)
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">Disabled</span>
                                    @endif
                                    @if($template['send_admin_copy'] ?? false)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">Admin Copy</span>
                                    @endif
                                </div>
                                <p class="text-sm text-[#697386] mt-1 truncate">{{ $template['subject'] ?? '' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    wire:click="toggleEmailActive('{{ $key }}')"
                                    class="p-2 text-[#697386] hover:text-[#1a1f36] hover:bg-gray-100 rounded-lg transition-colors"
                                    title="{{ ($template['is_active'] ?? true) ? 'Disable' : 'Enable' }}"
                                >
                                    @if($template['is_active'] ?? true)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    @endif
                                </button>
                                @if(!in_array($key, ['contact-notification', 'brand-registration']))
                                    <button
                                        wire:click="toggleAdminCopy('{{ $key }}')"
                                        class="p-2 {{ ($template['send_admin_copy'] ?? false) ? 'text-blue-600' : 'text-[#697386]' }} hover:text-[#1a1f36] hover:bg-gray-100 rounded-lg transition-colors"
                                        title="{{ ($template['send_admin_copy'] ?? false) ? 'Disable admin copy' : 'Enable admin copy' }}"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </button>
                                @endif
                                <button
                                    wire:click="openTestEmailModal('{{ $key }}')"
                                    class="p-2 text-[#697386] hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                    title="Send Test Email"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                                <button
                                    wire:click="editEmailTemplate('{{ $key }}')"
                                    class="p-2 text-[#697386] hover:text-[#635bff] hover:bg-[#635bff]/10 rounded-lg transition-colors"
                                    title="Edit"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button
                                    wire:click="resetEmailTemplate('{{ $key }}')"
                                    wire:confirm="Reset this template to default? This will undo any customizations."
                                    class="p-2 text-[#697386] hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Reset to default"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Variables Reference --}}
            <div class="mt-6 pt-6 border-t border-[#e3e8ee]">
                <h4 class="text-sm font-medium text-[#1a1f36] mb-3">Available Variables</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium text-[#1a1f36] mb-1">Common</p>
                        <p class="text-[#697386]"><code class="bg-gray-200 px-1 rounded">@{{app_url}}</code> <code class="bg-gray-200 px-1 rounded">@{{user_name}}</code></p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium text-[#1a1f36] mb-1">Purchase</p>
                        <p class="text-[#697386]"><code class="bg-gray-200 px-1 rounded">@{{credits}}</code> <code class="bg-gray-200 px-1 rounded">@{{amount}}</code> <code class="bg-gray-200 px-1 rounded">@{{currency}}</code></p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium text-[#1a1f36] mb-1">Subscription</p>
                        <p class="text-[#697386]"><code class="bg-gray-200 px-1 rounded">@{{plan_name}}</code> <code class="bg-gray-200 px-1 rounded">@{{credits_per_month}}</code></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        @if($editingTemplate)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelEditTemplate"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-[#1a1f36]">
                                    Edit: {{ $emailTemplates[$editingTemplate]['name'] ?? ucfirst(str_replace('-', ' ', $editingTemplate)) }}
                                </h3>
                                <button wire:click="cancelEditTemplate" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Subject Line</label>
                                    <input type="text" wire:model="editSubject" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Email Body (Markdown)</label>
                                    <textarea wire:model="editBody" rows="12" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none text-sm font-mono" placeholder="# Heading&#10;&#10;Your email content here..."></textarea>
                                </div>

                                <div class="flex flex-wrap gap-4">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="editIsActive" class="w-4 h-4 text-[#635bff] border-[#e3e8ee] rounded focus:ring-[#635bff]/20">
                                        <span class="text-sm text-[#1a1f36]">Email Active</span>
                                    </label>
                                    @if(!in_array($editingTemplate, ['contact-notification', 'brand-registration']))
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" wire:model="editSendAdminCopy" class="w-4 h-4 text-[#635bff] border-[#e3e8ee] rounded focus:ring-[#635bff]/20">
                                            <span class="text-sm text-[#1a1f36]">Send Admin Copy</span>
                                        </label>
                                    @endif
                                </div>

                                @if(isset($emailTemplates[$editingTemplate]['variables']))
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <p class="text-sm font-medium text-[#1a1f36] mb-1">Available Variables:</p>
                                        <p class="text-sm text-[#697386]">
                                            @foreach($emailTemplates[$editingTemplate]['variables'] as $var)
                                                <code class="bg-gray-200 px-1 rounded mr-1">&#123;&#123;{{ $var }}&#125;&#125;</code>
                                            @endforeach
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button wire:click="saveEmailTemplate" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-[#635bff] text-white text-sm font-medium hover:bg-[#5248f0] focus:outline-none sm:w-auto">
                                Save Changes
                            </button>
                            <button wire:click="cancelEditTemplate" class="mt-3 w-full inline-flex justify-center rounded-lg border border-[#e3e8ee] shadow-sm px-4 py-2 bg-white text-[#1a1f36] text-sm font-medium hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Test Email Modal --}}
        @if($testingTemplate)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="test-modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelTestEmail"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-[#1a1f36]">
                                    Send Test Email
                                </h3>
                                <button wire:click="cancelTestEmail" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <p class="text-sm text-blue-800">
                                        <strong>Template:</strong> {{ $emailTemplates[$testingTemplate]['name'] ?? ucfirst(str_replace('-', ' ', $testingTemplate)) }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Send test email to:</label>
                                    <input
                                        type="email"
                                        wire:model="testEmailAddress"
                                        class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none text-sm"
                                        placeholder="Enter email address"
                                    >
                                    @error('testEmailAddress')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <p class="text-xs text-[#697386]">
                                    Test data will be used for variables like user name, credits, etc.
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button
                                wire:click="sendTemplateTestEmail"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-white text-sm font-medium hover:bg-green-700 focus:outline-none sm:w-auto"
                            >
                                <span wire:loading.remove wire:target="sendTemplateTestEmail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <svg wire:loading wire:target="sendTemplateTestEmail" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="sendTemplateTestEmail">Send Test Email</span>
                                <span wire:loading wire:target="sendTemplateTestEmail">Sending...</span>
                            </button>
                            <button wire:click="cancelTestEmail" class="mt-3 w-full inline-flex justify-center rounded-lg border border-[#e3e8ee] shadow-sm px-4 py-2 bg-white text-[#1a1f36] text-sm font-medium hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
