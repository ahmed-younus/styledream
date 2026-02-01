@php
$colorStyles = [
    'primary' => 'background-color: #1a1a1a; border-color: #1a1a1a;',
    'success' => 'background-color: #10b981; border-color: #10b981;',
    'error' => 'background-color: #ef4444; border-color: #ef4444;',
];
$buttonStyle = $colorStyles[$color ?? 'primary'] ?? $colorStyles['primary'];
@endphp
<table class="action action-table" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 30px auto; padding: 0; text-align: center; width: 100%;">
    <tr>
        <td align="center">
            <table class="action-table" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <a href="{{ $url }}" class="button" target="_blank" rel="noopener" style="{{ $buttonStyle }} border-radius: 8px; color: #ffffff; display: inline-block; font-size: 16px; font-weight: 600; padding: 16px 36px; text-decoration: none; text-transform: none; min-width: 200px; text-align: center;">{{ $slot }}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
