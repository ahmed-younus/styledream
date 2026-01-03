<div>
    {{-- Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex gap-4">
            <button wire:click="$set('activeTab', 'api')" class="pb-3 px-1 {{ $activeTab === 'api' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-500 hover:text-gray-700' }} font-medium transition-colors">
                API Keys
            </button>
            <button wire:click="$set('activeTab', 'smtp')" class="pb-3 px-1 {{ $activeTab === 'smtp' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-500 hover:text-gray-700' }} font-medium transition-colors">
                SMTP / Email
            </button>
            <button wire:click="$set('activeTab', 'general')" class="pb-3 px-1 {{ $activeTab === 'general' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-500 hover:text-gray-700' }} font-medium transition-colors">
                General
            </button>
        </nav>
    </div>

    {{-- API Settings --}}
    @if($activeTab === 'api')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">API Configuration</h3>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Google AI API Key</label>
                    <input type="password" wire:model="settings.google_ai_api_key" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="AIza...">
                    <p class="text-xs text-gray-500 mt-1">Used for virtual try-on generation</p>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-4">Stripe Configuration</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Public Key</label>
                            <input type="password" wire:model="settings.stripe_public_key" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="pk_...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secret Key</label>
                            <input type="password" wire:model="settings.stripe_secret_key" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="sk_...">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook Secret</label>
                            <input type="password" wire:model="settings.stripe_webhook_secret" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="whsec_...">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button wire:click="saveApiSettings" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Save API Settings
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- SMTP Settings --}}
    @if($activeTab === 'smtp')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">SMTP Configuration</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Host</label>
                    <input type="text" wire:model="settings.smtp_host" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="smtp.example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Port</label>
                    <input type="number" wire:model="settings.smtp_port" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="587">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                    <input type="text" wire:model="settings.smtp_username" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <input type="password" wire:model="settings.smtp_password" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Address</label>
                    <input type="email" wire:model="settings.mail_from_address" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="hello@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Name</label>
                    <input type="text" wire:model="settings.mail_from_name" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="StyleDream">
                </div>
            </div>

            <div class="pt-6">
                <button wire:click="saveSmtpSettings" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    Save SMTP Settings
                </button>
            </div>
        </div>
    @endif

    {{-- General Settings --}}
    @if($activeTab === 'general')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">General Settings</h3>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Name</label>
                    <input type="text" wire:model="settings.site_name" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model="settings.maintenance_mode" id="maintenance" class="w-4 h-4 text-purple-600 rounded">
                    <label for="maintenance" class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Maintenance Mode</label>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-4">Credit Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Free Credits on Signup</label>
                            <input type="number" wire:model="settings.signup_credits" min="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Daily Free Credits</label>
                            <input type="number" wire:model="settings.daily_free_credits" min="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button wire:click="saveGeneralSettings" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Save General Settings
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
