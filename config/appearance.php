<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | Fonts the site manager can choose from. "bunny" is the Bunny Fonts
    | family slug loaded at runtime; null means the font is already bundled
    | by Vite (see vite.config.js) and needs no extra request.
    |
    */

    'fonts' => [
        'fraunces' => ['label' => 'Fraunces', 'type' => 'serif', 'stack' => "'Fraunces', ui-serif, Georgia, serif", 'bunny' => null, 'weights' => '400,500,600'],
        'playfair-display' => ['label' => 'Playfair Display', 'type' => 'serif', 'stack' => "'Playfair Display', ui-serif, Georgia, serif", 'bunny' => 'playfair-display', 'weights' => '400,500,600,400i'],
        'cormorant-garamond' => ['label' => 'Cormorant Garamond', 'type' => 'serif', 'stack' => "'Cormorant Garamond', ui-serif, Georgia, serif", 'bunny' => 'cormorant-garamond', 'weights' => '400,500,600,400i'],
        'dm-serif-display' => ['label' => 'DM Serif Display', 'type' => 'serif', 'stack' => "'DM Serif Display', ui-serif, Georgia, serif", 'bunny' => 'dm-serif-display', 'weights' => '400,400i'],
        'libre-baskerville' => ['label' => 'Libre Baskerville', 'type' => 'serif', 'stack' => "'Libre Baskerville', ui-serif, Georgia, serif", 'bunny' => 'libre-baskerville', 'weights' => '400,700,400i'],
        'lora' => ['label' => 'Lora', 'type' => 'serif', 'stack' => "'Lora', ui-serif, Georgia, serif", 'bunny' => 'lora', 'weights' => '400,500,600,400i'],
        'source-serif-4' => ['label' => 'Source Serif', 'type' => 'serif', 'stack' => "'Source Serif 4', ui-serif, Georgia, serif", 'bunny' => 'source-serif-4', 'weights' => '400,500,600,400i'],
        'instrument-sans' => ['label' => 'Instrument Sans', 'type' => 'sans', 'stack' => "'Instrument Sans', ui-sans-serif, system-ui, sans-serif", 'bunny' => null, 'weights' => '400,500,600,700'],
        'inter' => ['label' => 'Inter', 'type' => 'sans', 'stack' => "'Inter', ui-sans-serif, system-ui, sans-serif", 'bunny' => 'inter', 'weights' => '400,500,600,700'],
        'dm-sans' => ['label' => 'DM Sans', 'type' => 'sans', 'stack' => "'DM Sans', ui-sans-serif, system-ui, sans-serif", 'bunny' => 'dm-sans', 'weights' => '400,500,600,700'],
        'manrope' => ['label' => 'Manrope', 'type' => 'sans', 'stack' => "'Manrope', ui-sans-serif, system-ui, sans-serif", 'bunny' => 'manrope', 'weights' => '400,500,600,700'],
        'plus-jakarta-sans' => ['label' => 'Plus Jakarta Sans', 'type' => 'sans', 'stack' => "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif", 'bunny' => 'plus-jakarta-sans', 'weights' => '400,500,600,700'],
        'poppins' => ['label' => 'Poppins', 'type' => 'sans', 'stack' => "'Poppins', ui-sans-serif, system-ui, sans-serif", 'bunny' => 'poppins', 'weights' => '400,500,600,700'],
        'montserrat' => ['label' => 'Montserrat', 'type' => 'sans', 'stack' => "'Montserrat', ui-sans-serif, system-ui, sans-serif", 'bunny' => 'montserrat', 'weights' => '400,500,600,700'],
        'space-grotesk' => ['label' => 'Space Grotesk', 'type' => 'sans', 'stack' => "'Space Grotesk', ui-sans-serif, system-ui, sans-serif", 'bunny' => 'space-grotesk', 'weights' => '400,500,600,700'],
        'work-sans' => ['label' => 'Work Sans', 'type' => 'sans', 'stack' => "'Work Sans', ui-sans-serif, system-ui, sans-serif", 'bunny' => 'work-sans', 'weights' => '400,500,600,700'],
        'nunito-sans' => ['label' => 'Nunito Sans', 'type' => 'sans', 'stack' => "'Nunito Sans', ui-sans-serif, system-ui, sans-serif", 'bunny' => 'nunito-sans', 'weights' => '400,500,600,700'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Colour presets
    |--------------------------------------------------------------------------
    */

    'presets' => [
        'classic-brass' => ['label' => 'Classic brass', 'accent' => '#b88a41', 'primary' => '#121825', 'background' => '#faf7f2'],
        'emerald-estate' => ['label' => 'Emerald estate', 'accent' => '#0f8a6c', 'primary' => '#0f1f1b', 'background' => '#f5f8f6'],
        'navy-gold' => ['label' => 'Navy & gold', 'accent' => '#c9a227', 'primary' => '#0f1b3d', 'background' => '#f7f7f3'],
        'terracotta' => ['label' => 'Terracotta', 'accent' => '#c2603d', 'primary' => '#2a1d17', 'background' => '#fbf6f1'],
        'royal-plum' => ['label' => 'Royal plum', 'accent' => '#8b4a8f', 'primary' => '#1f1526', 'background' => '#faf7fb'],
        'ocean-blue' => ['label' => 'Ocean blue', 'accent' => '#2b6cb0', 'primary' => '#0e1726', 'background' => '#f5f8fc'],
    ],

    'font_size' => ['min' => 14, 'max' => 19, 'default' => 16],

];
