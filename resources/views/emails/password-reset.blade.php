<x-mail::message>
# Reset Your Password

Hi {{ $userName }},

We received a request to reset your password for your Stylely account.

Click the button below to set a new password:

<x-mail::button :url="$resetUrl" color="primary">
Reset Password
</x-mail::button>

<x-mail::panel>
This link will expire in 60 minutes for security reasons.
</x-mail::panel>

If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.

Need help? Contact us at info@stylely.ai.

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
