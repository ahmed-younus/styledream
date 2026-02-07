<div>
    @if($showModal)
    <div id="subscription-modal-alpine" class="fixed inset-0 flex items-center justify-center p-4"
         style="z-index: 99999; isolation: isolate;"
         x-data="{
             stripe: null,
             elements: null,
             cardElement: null,
             upgradeCardElement: null,
             manageCardElement: null,
             creditPackCardElement: null,
             processing: false,
             error: null,
             initialized: false
         }"
         x-init="
            console.log('🔵 Alpine x-init called');
            setTimeout(() => {
                console.log('🟡 Initializing Stripe after 300ms');

                if (typeof Stripe === 'undefined') {
                    console.error('❌ Stripe.js not loaded');
                    error = 'Payment system failed to load.';
                    return;
                }
                console.log('✅ Stripe.js is loaded');

                const stripeKey = '{{ config('services.stripe.key') }}';
                console.log('🔑 Stripe key:', stripeKey ? stripeKey.substring(0, 20) + '...' : 'MISSING');

                stripe = Stripe(stripeKey);
                console.log('✅ Stripe instance created');

                const cardContainer = document.getElementById('card-element');
                console.log('📦 Card container:', cardContainer);

                if (!cardContainer) {
                    console.error('❌ Card container not found');
                    return;
                }

                if (cardContainer.children.length > 0) {
                    console.log('✅ Already mounted');
                    initialized = true;
                    return;
                }

                elements = stripe.elements({
                    appearance: {
                        theme: 'stripe',
                        variables: {
                            colorPrimary: '#8B5CF6',
                            colorBackground: '#ffffff',
                            colorText: '#1a1a1a',
                            colorDanger: '#ef4444',
                            fontFamily: 'Inter, system-ui, sans-serif',
                            borderRadius: '8px',
                        },
                    },
                });

                cardElement = elements.create('card', {
                    hidePostalCode: true,
                    style: {
                        base: {
                            fontSize: '16px',
                            color: '#1a1a1a',
                            '::placeholder': { color: '#9ca3af' },
                        },
                    },
                });

                cardElement.mount('#card-element');
                initialized = true;
                console.log('✅ Card element mounted successfully!');

                cardElement.on('change', (event) => {
                    console.log('💳 Card change:', event);
                    error = event.error ? event.error.message : null;
                });
            }, 300);
         ">
        {{-- Backdrop --}}
        <div class="fixed inset-0 cursor-pointer" style="background-color: rgba(0, 0, 0, 0.85); z-index: 99999;" wire:click="closeModal"></div>

        {{-- Modal --}}
        <div class="relative rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto shadow-2xl" style="z-index: 100000; background-color: #ffffff;">
            {{-- Close Button --}}
            <button wire:click="closeModal" class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600 transition-colors z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="p-6" style="background: #ffffff;">
                @if($mode === 'trial')
                    @include('livewire.partials.subscription-trial')
                @elseif($mode === 'new')
                    @include('livewire.partials.subscription-new')
                @elseif($mode === 'upgrade')
                    @include('livewire.partials.subscription-upgrade')
                @elseif($mode === 'downgrade')
                    @include('livewire.partials.subscription-downgrade')
                @elseif($mode === 'cancel')
                    @include('livewire.partials.subscription-cancel')
                @elseif($mode === 'manage')
                    @include('livewire.partials.subscription-manage')
                @elseif($mode === 'credit_pack')
                    @include('livewire.partials.subscription-credit-pack')
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@script
<script>
    // Define functions globally for button clicks
    window.createSubscription = async function(plan, currency) {
        const alpineData = Alpine.$data(document.getElementById('subscription-modal-alpine'));
        if (!alpineData || alpineData.processing) return;

        alpineData.processing = true;
        alpineData.error = null;

        console.log('🚀 Creating subscription...', { plan, currency });

        try {
            console.log('🔍 Checking stripe:', !!alpineData.stripe);
            console.log('🔍 Checking cardElement:', !!alpineData.cardElement);

            if (!alpineData.stripe || !alpineData.cardElement) {
                alpineData.error = 'Payment system not ready. Please refresh.';
                alpineData.processing = false;
                console.error('❌ Stripe or cardElement missing');
                return;
            }

            console.log('💳 Calling createPaymentMethod...');
            const result = await alpineData.stripe.createPaymentMethod({
                type: 'card',
                card: alpineData.cardElement,
            });
            console.log('💳 createPaymentMethod result:', result);

            const { paymentMethod, error: pmError } = result;

            if (pmError) {
                console.error('❌ Payment method error:', pmError);
                alpineData.error = pmError.message;
                alpineData.processing = false;
                return;
            }

            console.log('✅ Payment method created:', paymentMethod.id);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            console.log('🔐 CSRF Token:', csrfToken ? 'Found' : 'MISSING!');

            const response = await fetch('/subscription/create-inapp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    plan: plan,
                    currency: currency,
                    payment_method_id: paymentMethod.id,
                }),
            });

            console.log('📡 Response status:', response.status);
            const text = await response.text();
            console.log('📝 Raw response:', text.substring(0, 500));

            let data;
            try {
                data = JSON.parse(text);
            } catch(parseErr) {
                console.error('❌ JSON parse error:', parseErr);
                alpineData.error = 'Server returned invalid response';
                alpineData.processing = false;
                return;
            }
            console.log('📥 Server response:', data);

            if (data.error) {
                alpineData.error = data.error;
                alpineData.processing = false;
                return;
            }

            if (data.requires_action) {
                const { error: confirmError } = await alpineData.stripe.confirmCardPayment(data.client_secret);
                if (confirmError) {
                    alpineData.error = confirmError.message;
                    alpineData.processing = false;
                    return;
                }
            }

            if (data.success) {
                console.log('✅ Subscription created!');
                window.location.href = '/pricing?subscription_success=1';
            }
        } catch (e) {
            console.error('❌ Error:', e);
            alpineData.error = 'An unexpected error occurred.';
        }

        alpineData.processing = false;
    };

    // Initialize card element for upgrade modal when user clicks edit
    window.initUpgradeCardElement = function() {
        const alpineData = Alpine.$data(document.getElementById('subscription-modal-alpine'));
        if (!alpineData) return;

        console.log('🔧 Initializing upgrade card element...');

        // Check if already initialized
        const upgradeCardContainer = document.getElementById('upgrade-card-element');
        if (!upgradeCardContainer) {
            console.error('❌ Upgrade card container not found');
            return;
        }

        // Clear any existing content
        upgradeCardContainer.innerHTML = '';

        // Make sure Stripe is initialized
        if (!alpineData.stripe) {
            const stripeKey = '{{ config('services.stripe.key') }}';
            alpineData.stripe = Stripe(stripeKey);
        }

        // Create new elements instance for upgrade card
        const elements = alpineData.stripe.elements({
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#8B5CF6',
                    colorBackground: '#ffffff',
                    colorText: '#1a1a1a',
                    colorDanger: '#ef4444',
                    fontFamily: 'Inter, system-ui, sans-serif',
                    borderRadius: '8px',
                },
            },
        });

        alpineData.upgradeCardElement = elements.create('card', {
            hidePostalCode: true,
            style: {
                base: {
                    fontSize: '16px',
                    color: '#1a1a1a',
                    '::placeholder': { color: '#9ca3af' },
                },
            },
        });

        alpineData.upgradeCardElement.mount('#upgrade-card-element');
        console.log('✅ Upgrade card element mounted!');

        alpineData.upgradeCardElement.on('change', (event) => {
            alpineData.error = event.error ? event.error.message : null;
        });
    };

    window.processUpgrade = async function(plan, currency) {
        const alpineData = Alpine.$data(document.getElementById('subscription-modal-alpine'));
        if (!alpineData || alpineData.processing) return;

        alpineData.processing = true;
        alpineData.error = null;

        try {
            let newPaymentMethodId = null;

            // Check if user entered a new card
            if (alpineData.upgradeCardElement) {
                console.log('💳 Creating new payment method from upgrade card...');
                const { paymentMethod, error: pmError } = await alpineData.stripe.createPaymentMethod({
                    type: 'card',
                    card: alpineData.upgradeCardElement,
                });

                if (pmError) {
                    alpineData.error = pmError.message;
                    alpineData.processing = false;
                    return;
                }

                newPaymentMethodId = paymentMethod.id;
                console.log('✅ New payment method created:', newPaymentMethodId);
            }

            const response = await fetch('/subscription/upgrade-inapp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    plan,
                    currency,
                    payment_method_id: newPaymentMethodId
                }),
            });

            const data = await response.json();

            if (data.error) {
                alpineData.error = data.error;
                alpineData.processing = false;
                return;
            }

            if (data.requires_action && alpineData.stripe) {
                const { error: confirmError } = await alpineData.stripe.confirmCardPayment(data.client_secret);
                if (confirmError) {
                    alpineData.error = confirmError.message;
                    alpineData.processing = false;
                    return;
                }
            }

            if (data.success) {
                window.location.href = '/pricing?upgrade_success=1';
            }
        } catch (e) {
            alpineData.error = 'An unexpected error occurred.';
        }

        alpineData.processing = false;
    };

    // Initialize card element for manage modal when user clicks edit
    window.initManageCardElement = function() {
        const alpineData = Alpine.$data(document.getElementById('subscription-modal-alpine'));
        if (!alpineData) return;

        console.log('🔧 Initializing manage card element...');

        const manageCardContainer = document.getElementById('manage-card-element');
        if (!manageCardContainer) {
            console.error('❌ Manage card container not found');
            return;
        }

        manageCardContainer.innerHTML = '';

        if (!alpineData.stripe) {
            const stripeKey = '{{ config('services.stripe.key') }}';
            alpineData.stripe = Stripe(stripeKey);
        }

        const elements = alpineData.stripe.elements({
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#8B5CF6',
                    colorBackground: '#ffffff',
                    colorText: '#1a1a1a',
                    colorDanger: '#ef4444',
                    fontFamily: 'Inter, system-ui, sans-serif',
                    borderRadius: '8px',
                },
            },
        });

        alpineData.manageCardElement = elements.create('card', {
            hidePostalCode: true,
            style: {
                base: {
                    fontSize: '16px',
                    color: '#1a1a1a',
                    '::placeholder': { color: '#9ca3af' },
                },
            },
        });

        alpineData.manageCardElement.mount('#manage-card-element');
        console.log('✅ Manage card element mounted!');

        alpineData.manageCardElement.on('change', (event) => {
            alpineData.error = event.error ? event.error.message : null;
        });
    };

    window.updatePaymentMethod = async function() {
        const alpineData = Alpine.$data(document.getElementById('subscription-modal-alpine'));
        if (!alpineData || alpineData.processing || !alpineData.manageCardElement) return;

        alpineData.processing = true;
        alpineData.error = null;

        try {
            console.log('💳 Creating new payment method...');
            const { paymentMethod, error: pmError } = await alpineData.stripe.createPaymentMethod({
                type: 'card',
                card: alpineData.manageCardElement,
            });

            if (pmError) {
                alpineData.error = pmError.message;
                alpineData.processing = false;
                return;
            }

            console.log('✅ Payment method created:', paymentMethod.id);

            const response = await fetch('/subscription/update-payment-method', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payment_method_id: paymentMethod.id
                }),
            });

            const data = await response.json();

            if (data.error) {
                alpineData.error = data.error;
                alpineData.processing = false;
                return;
            }

            if (data.success) {
                console.log('✅ Payment method updated!');
                window.location.reload();
            }
        } catch (e) {
            console.error('❌ Error:', e);
            alpineData.error = 'An unexpected error occurred.';
        }

        alpineData.processing = false;
    };

    window.activateTrial = async function() {
        const alpineData = Alpine.$data(document.getElementById('subscription-modal-alpine'));
        if (!alpineData || alpineData.processing) return;

        alpineData.processing = true;
        alpineData.error = null;

        try {
            // Get setup intent first
            const setupResponse = await fetch('/trial/setup-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const setupData = await setupResponse.json();
            if (setupData.error) {
                alpineData.error = setupData.error;
                alpineData.processing = false;
                return;
            }

            const { setupIntent, error } = await alpineData.stripe.confirmCardSetup(
                setupData.client_secret,
                { payment_method: { card: alpineData.cardElement } }
            );

            if (error) {
                alpineData.error = error.message;
                alpineData.processing = false;
                return;
            }

            if (setupIntent.status === 'succeeded') {
                const activateResponse = await fetch('/trial/activate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ setup_intent_id: setupIntent.id }),
                });

                const activateData = await activateResponse.json();
                if (activateData.success) {
                    window.location.href = activateData.redirect_url || '/pricing?trial_success=1';
                } else {
                    alpineData.error = activateData.error;
                }
            }
        } catch (e) {
            alpineData.error = 'An unexpected error occurred.';
        }

        alpineData.processing = false;
    };

    // Initialize card element for credit pack purchase when user clicks to use new card
    window.initCreditPackCardElement = function() {
        const alpineData = Alpine.$data(document.getElementById('subscription-modal-alpine'));
        if (!alpineData) return;

        console.log('🔧 Initializing credit pack card element...');

        const creditPackCardContainer = document.getElementById('credit-pack-card-element');
        if (!creditPackCardContainer) {
            console.error('❌ Credit pack card container not found');
            return;
        }

        creditPackCardContainer.innerHTML = '';

        if (!alpineData.stripe) {
            const stripeKey = '{{ config('services.stripe.key') }}';
            alpineData.stripe = Stripe(stripeKey);
        }

        const elements = alpineData.stripe.elements({
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#8B5CF6',
                    colorBackground: '#ffffff',
                    colorText: '#1a1a1a',
                    colorDanger: '#ef4444',
                    fontFamily: 'Inter, system-ui, sans-serif',
                    borderRadius: '8px',
                },
            },
        });

        alpineData.creditPackCardElement = elements.create('card', {
            hidePostalCode: true,
            style: {
                base: {
                    fontSize: '16px',
                    color: '#1a1a1a',
                    '::placeholder': { color: '#9ca3af' },
                },
            },
        });

        alpineData.creditPackCardElement.mount('#credit-pack-card-element');
        console.log('✅ Credit pack card element mounted!');

        alpineData.creditPackCardElement.on('change', (event) => {
            alpineData.error = event.error ? event.error.message : null;
        });
    };

    // Purchase credit pack
    window.purchaseCreditPack = async function(pack, currency, useNewCard) {
        const alpineData = Alpine.$data(document.getElementById('subscription-modal-alpine'));
        if (!alpineData || alpineData.processing) return;

        alpineData.processing = true;
        alpineData.error = null;

        console.log('🛒 Purchasing credit pack...', { pack, currency, useNewCard });

        try {
            let paymentMethodId = null;

            if (!alpineData.stripe) {
                const stripeKey = '{{ config('services.stripe.key') }}';
                alpineData.stripe = Stripe(stripeKey);
            }

            // If using new card, create payment method from card element
            if (useNewCard) {
                let cardElement = alpineData.creditPackCardElement || alpineData.cardElement;

                if (!cardElement) {
                    alpineData.error = 'Please enter card details first.';
                    alpineData.processing = false;
                    return;
                }

                console.log('💳 Creating payment method from new card...');
                const { paymentMethod, error: pmError } = await alpineData.stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                });

                if (pmError) {
                    console.error('❌ Payment method error:', pmError);
                    alpineData.error = pmError.message;
                    alpineData.processing = false;
                    return;
                }

                paymentMethodId = paymentMethod.id;
                console.log('✅ Payment method created:', paymentMethodId);
            } else {
                console.log('💳 Using saved payment method...');
            }

            // Send to server
            const response = await fetch('/credits/purchase-inapp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    pack: pack,
                    currency: currency,
                    payment_method_id: paymentMethodId,
                    use_saved_card: !useNewCard && !paymentMethodId,
                }),
            });

            const data = await response.json();
            console.log('📥 Server response:', data);

            if (data.error) {
                alpineData.error = data.error;
                alpineData.processing = false;
                return;
            }

            // Handle 3D Secure if needed
            if (data.requires_action) {
                console.log('🔐 3D Secure required...');
                const { error: confirmError, paymentIntent } = await alpineData.stripe.confirmCardPayment(data.client_secret);

                if (confirmError) {
                    alpineData.error = confirmError.message;
                    alpineData.processing = false;
                    return;
                }

                // Confirm with server after 3D Secure
                if (paymentIntent.status === 'succeeded') {
                    const confirmResponse = await fetch('/credits/confirm-inapp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            payment_intent_id: data.payment_intent_id,
                        }),
                    });

                    const confirmData = await confirmResponse.json();
                    if (confirmData.success) {
                        console.log('✅ Credit pack purchased!');
                        window.location.href = '/pricing?credits_success=1';
                        return;
                    } else {
                        alpineData.error = confirmData.error;
                        alpineData.processing = false;
                        return;
                    }
                }
            }

            if (data.success) {
                console.log('✅ Credit pack purchased!');
                window.location.href = '/pricing?credits_success=1';
            }
        } catch (e) {
            console.error('❌ Error:', e);
            alpineData.error = 'An unexpected error occurred.';
        }

        alpineData.processing = false;
    };
</script>
@endscript
