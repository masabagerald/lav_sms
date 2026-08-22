{{-- Reusable current-user photo / initials avatar --}}
@php
    $u = Auth::user();
    $photo = $u->photo;
    $noPhoto = empty($photo) || strpos((string) $photo, 'images/user.png') !== false;
    $size = $size ?? 38;
@endphp

@if(!$noPhoto)
    <img src="{{ $photo }}" style="width:{{ $size }}px;height:{{ $size }}px;"
         class="rounded-circle {{ $class ?? '' }}" alt="photo">
@else
    @php
        $words = preg_split('/\s+/', trim($u->name));
        $initials = strtoupper(mb_substr($words[0] ?? 'U', 0, 1) . mb_substr($words[1] ?? '', 0, 1));
        $palette = ['#1e88e5', '#43a047', '#fb8c00', '#8e24aa', '#00897b', '#d81b60', '#3949ab', '#6d4c41'];
        $color = $palette[hexdec(substr(md5($u->name . $u->id), 0, 6)) % count($palette)];
    @endphp
    <span class="user-avatar {{ $class ?? '' }}"
          style="width:{{ $size }}px;height:{{ $size }}px;background:{{ $color }};font-size:{{ round($size * 0.36) }}px;">{{ $initials }}</span>
@endif
