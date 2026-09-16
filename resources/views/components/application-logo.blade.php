@php $siteLogo = \App\Models\Setting::get('logo_path'); @endphp
<img src="{{ $siteLogo ? asset('storage/' . $siteLogo) : 'https://visiontech.com.bd/wp-content/uploads/2017/11/vision-logo.png' }}" alt="Logo" {{ $attributes }}>
