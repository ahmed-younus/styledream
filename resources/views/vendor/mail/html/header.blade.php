<tr>
    <td class="header" style="background: linear-gradient(135deg, #635bff 0%, #7c74ff 100%); padding: 30px 0; text-align: center;">
        <a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
            @if (trim($slot) === 'Laravel')
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 50" width="160" height="40">
                    <g fill="#FFFFFF">
                        <path d="M15.813 31.904L15 34.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L8.25 28l2.846-.813a4.5 4.5 0 003.09-3.09L15 21.25l.813 2.846a4.5 4.5 0 003.09 3.09L21.75 28l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        <path d="M24.259 20.715L24 21.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L20.25 18l1.036-.259a3.375 3.375 0 002.455-2.456L24 14.25l.259 1.035a3.375 3.375 0 002.456 2.456L27.75 18l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
                        <path d="M22.894 36.567L22.5 37.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L19.5 34.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
                        <text x="38" y="33" font-family="Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="22" font-weight="600">Stylely</text>
                    </g>
                </svg>
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
