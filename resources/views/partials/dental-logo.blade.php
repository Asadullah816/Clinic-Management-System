<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="{{ $width ?? 24 }}" height="{{ $height ?? 24 }}" class="dental-tooth-logo {{ $class ?? '' }}" fill="none" style="display: inline-block; vertical-align: middle;">
    <!-- Dental Tooth Molar SVG -->
    <path d="M12 3.5C9.8 1.8 7.2 2 5.6 3.6C3.5 5.6 3 9 4 12.3C5.2 16 7.2 19.3 8.3 21C8.8 21.8 9.7 22 10.4 21.2C11.1 20.3 11.5 18.2 12 16.5C12.5 18.2 12.9 20.3 13.6 21.2C14.3 22 15.2 21.8 15.7 21C16.8 19.3 18.8 16 20 12.3C21 9 20.5 5.6 18.4 3.6C16.8 2 14.2 1.8 12 3.5Z" fill="{{ $color ?? '#ffffff' }}"/>
    <!-- Enamel highlight reflection -->
    <path d="M6.2 6.5C5.2 7.8 5 9.5 5.5 11.2" stroke="{{ $highlight ?? '#bae6fd' }}" stroke-width="1.3" stroke-linecap="round"/>
    <!-- Healthy bright sparkle -->
    <path d="M19 2l.6 1.4 1.4.6-1.4.6-.6 1.4-.6-1.4-1.4-.6 1.4-.6z" fill="{{ $sparkle ?? '#fef08a' }}"/>
</svg>
