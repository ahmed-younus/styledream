<x-mail::message>
# Welcome to Stylely!

Hi {{ $user->name }},

Your free trial has been activated! We've added **5 free credits** to your account.

**What you can do now:**

- Try on any outfit virtually
- Save your favorite looks
- Share your style with friends

<x-mail::button :url="config('app.url') . '/studio'">
Start Trying On
</x-mail::button>

---

**Your Account:**

- **Credits:** 5 free credits
- **Card:** Saved for future use (not charged)

Your card will only be charged if you choose to subscribe or purchase more credits. You're under no obligation.

**Ready for more?**

Check out our [subscription plans]({{ config('app.url') }}/pricing) for unlimited access to premium features and more credits.

Need help? Contact us at info@stylely.ai.

Happy styling!<br>
The {{ config('app.name') }} Team
</x-mail::message>
