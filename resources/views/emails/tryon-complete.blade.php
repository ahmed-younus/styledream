<x-mail::message>
# Your Try-On is Ready!

Hi {{ $tryOn->user->name }},

Great news! Your virtual try-on has been completed and is ready to view.

@if($tryOn->garment_name || $tryOn->garment_brand)
**Outfit Details:**
@if($tryOn->garment_name)
- **Item:** {{ $tryOn->garment_name }}
@endif
@if($tryOn->garment_brand)
- **Brand:** {{ $tryOn->garment_brand }}
@endif
@if($tryOn->garment_category)
- **Category:** {{ ucfirst($tryOn->garment_category) }}
@endif
@endif

<x-mail::button :url="config('app.url') . '/history'">
View Your Try-On
</x-mail::button>

Love the look? Save it to your wardrobe or share it with friends!

---

**Did you know?** You can try on multiple items at once to create complete outfits. Visit the studio to explore more styles.

Happy styling!<br>
The {{ config('app.name') }} Team
</x-mail::message>
