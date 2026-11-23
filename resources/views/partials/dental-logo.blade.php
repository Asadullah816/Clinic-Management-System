@php
    $toothFill = $color ?? '#ffffff';
    $isDarkFill = ($toothFill !== '#ffffff' && $toothFill !== '#fff');
    $grooveStroke = $groove ?? ($isDarkFill ? '#bae6fd' : '#0284c7');
    $enamelHighlight = $highlight ?? ($isDarkFill ? 'rgba(255, 255, 255, 0.45)' : '#ffffff');
    $sparkleFill = $sparkle ?? ($isDarkFill ? '#f59e0b' : '#fef08a');
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="{{ $width ?? 28 }}" height="{{ $height ?? 28 }}" class="dental-tooth-logo {{ $class ?? '' }}" fill="none" style="display: inline-block; vertical-align: middle;">
    <!-- Real Anatomical Dental Molar: Distinct Cusp Crests, Central Sulcus, Cervical Neck, and Tapering Bifurcated Roots -->
    <path d="M 50 20 C 43 14, 30 11, 22 20 C 12 31, 9 44, 15 58 C 19 66, 23 74, 27 88 C 29 93, 35 94, 38 88 C 42 79, 44 63, 50 54 C 56 63, 58 79, 62 88 C 65 94, 71 93, 73 88 C 77 74, 81 66, 85 58 C 91 44, 88 31, 78 20 C 70 11, 57 14, 50 20 Z" fill="{{ $toothFill }}"/>
    <!-- Anatomical Occlusal Groove / Cusps separation -->
    <path d="M 34 26 C 43 33, 57 33, 66 26" stroke="{{ $grooveStroke }}" stroke-width="2.5" stroke-linecap="round" fill="none" opacity="0.6"/>
    <!-- Inter-radicular root bifurcation contour -->
    <path d="M 50 54 L 50 40" stroke="{{ $grooveStroke }}" stroke-width="2" stroke-linecap="round" opacity="0.45"/>
    <!-- Enamel highlight contour -->
    <path d="M 23 28 C 17 38, 16 48, 21 57" stroke="{{ $enamelHighlight }}" stroke-width="3" stroke-linecap="round" opacity="0.9"/>
    @if(($showSparkle ?? true) !== false)
    <!-- Clean Clinic Sparkle -->
    <path d="M 82 8 L 83.5 12 L 87.5 13.5 L 83.5 15 L 82 19 L 80.5 15 L 76.5 13.5 L 80.5 12 Z" fill="{{ $sparkleFill }}"/>
    @endif
</svg>
