<x-mail::message>
# Welcome to Stylely!

Hi {{ $user->name }},

Thank you for joining Stylely - your personal AI-powered virtual try-on assistant!

We're excited to have you on board. With Stylely, you can:

- **Try on clothes virtually** - See how outfits look on you before buying
- **Save to your wardrobe** - Build your personal digital closet
- **Discover new styles** - Get inspired by our style feed

<x-mail::panel>
**Your Free Credits:** You've received {{ config('credits.signup', 5) }} free credits to get started!
</x-mail::panel>

Ready to try your first outfit?

<x-mail::button :url="config('app.url') . '/studio'">
Start Your First Try-On
</x-mail::button>

If you have any questions, feel free to reach out to us at info@stylely.ai.

Happy styling!<br>
The {{ config('app.name') }} Team
</x-mail::message>
