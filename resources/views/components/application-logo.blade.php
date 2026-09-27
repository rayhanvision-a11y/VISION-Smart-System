@php 
    $siteLogo = \App\Models\Setting::get('logo_path'); 
    $logoSrc = $siteLogo ? (str_starts_with($siteLogo, 'http') ? $siteLogo : asset('storage/' . ltrim($siteLogo, '/'))) : 'https://visiontech.com.bd/wp-content/uploads/2017/11/vision-logo.png';
@endphp
<img src="{{ $logoSrc }}" alt="VISION Technologies Limited" onerror="this.onerror=null; this.src='https://visiontech.com.bd/wp-content/uploads/2017/11/vision-logo.png';" {{ $attributes }}>
