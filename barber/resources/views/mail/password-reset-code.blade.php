@php
    $shopName = \App\Models\BusinessSetting::get('shop_name', 'Barber Shop');
@endphp
<div style="font-family: ui-sans-serif, system-ui, sans-serif; background-color: #121212; color: #f4f4f5; padding: 28px 16px;">
    <div style="max-width: 480px; margin: 0 auto;">
        <h2 style="margin: 0 0 18px; font-size: 18px; letter-spacing: -0.01em;">{{ $shopName }} &mdash; password reset</h2>

        <p style="font-size: 14px; line-height: 1.6; color: #a1a1aa; margin: 0;">Your one-time reset code is:</p>

        <div style="font-family: ui-monospace, 'SF Mono', monospace; font-size: 30px; letter-spacing: 0.35em; font-weight: 700; color: #ccff00; margin: 10px 0 22px;">
            {{ $code }}
        </div>

        <p style="font-size: 13px; line-height: 1.6; color: #a1a1aa; margin: 0;">
            It expires in {{ $expiresInMinutes }} minutes. Enter it on the reset screen, then choose a new password. We will never ask for this code anywhere else.
        </p>

        <p style="font-size: 12px; line-height: 1.6; color: #71717a; margin: 26px 0 0;">
            If you didn't ask for this, you can ignore this email &mdash; your password hasn't changed.
        </p>
    </div>
</div>