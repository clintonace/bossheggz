<?php

/*
|--------------------------------------------------------------------------
| Landing page content (admin → Pages)
|--------------------------------------------------------------------------
|
| Every public page is split into sections, top to bottom. Each section lists
| its editable fields and their default content (what the page shows until an
| admin changes it). Admin changes are stored in the page_sections table and
| merged over these defaults by App\Models\PageSection.
|
| Field types: text, textarea, url, image, icon, lines (one entry per line) and
| items (a repeatable list with its own sub-fields).
|
| Text may use the tokens {company}, {company_short} and {tagline}; they are
| replaced with the company details from config/listings.php when shown.
|
*/

$text = fn (string $label, ?string $default = null, array $options = []): array => ['type' => 'text', 'label' => $label, 'default' => $default, ...$options];
$textarea = fn (string $label, ?string $default = null, array $options = []): array => ['type' => 'textarea', 'label' => $label, 'default' => $default, ...$options];
$url = fn (string $label, ?string $default = null, array $options = []): array => ['type' => 'url', 'label' => $label, 'default' => $default, ...$options];
$image = fn (string $label, ?string $default = null, array $options = []): array => ['type' => 'image', 'label' => $label, 'default' => $default, ...$options];
$lines = fn (string $label, array $default = [], array $options = []): array => ['type' => 'lines', 'label' => $label, 'default' => $default, ...$options];
$items = fn (string $label, array $fields, array $default = [], array $options = []): array => ['type' => 'items', 'label' => $label, 'fields' => $fields, 'default' => $default, 'max_items' => 12, ...$options];
$icon = fn (string $label = 'Icon'): array => ['type' => 'icon', 'label' => $label, 'default' => 'sparkles'];

$required = ['required' => true];

$meta = fn (?string $title, string $description, ?string $titleHint = null): array => [
    'label' => 'Search & sharing',
    'description' => 'The browser-tab title and the summary search engines and social apps show.',
    'hideable' => false,
    'fields' => [
        'title' => $text('Page title', $title, ['hint' => $titleHint ?? 'Shown in the browser tab, followed by the company name.']),
        'description' => $textarea('Search description', $description, ['max' => 300, 'hint' => 'One or two sentences, up to 300 characters.']),
    ],
];

$hero = fn (string $eyebrow, string $title, string $description, ?string $heroImage = null, array $buttons = []): array => [
    'label' => 'Hero banner',
    'description' => 'The dark banner at the top of the page.',
    'hideable' => false,
    'fields' => [
        'eyebrow' => $text('Small heading', $eyebrow),
        'title' => $text('Heading', $title, $required),
        'description' => $textarea('Introduction', $description),
        'image' => $image('Background image', $heroImage, ['hint' => 'A wide photo works best. It is darkened so the text stays readable.']),
        ...$buttons,
    ],
];

$heroButtons = fn (?string $primaryLabel, ?string $primaryUrl, ?string $secondaryLabel = null, ?string $secondaryUrl = null): array => [
    'primary_label' => $text('Main button text', $primaryLabel, ['hint' => 'Leave empty to hide the button.']),
    'primary_url' => $url('Main button link', $primaryUrl),
    'secondary_label' => $text('Second button text', $secondaryLabel, ['hint' => 'Leave empty to hide the button.']),
    'secondary_url' => $url('Second button link', $secondaryUrl),
];

$cta = fn (string $title, ?string $description = null): array => [
    'label' => 'Call to action',
    'description' => 'The gold “Speak to an Adviser” band near the bottom of the page.',
    'fields' => [
        'title' => $text('Heading', $title, $required),
        'description' => $textarea('Supporting text', $description),
    ],
];

$heading = fn (string $eyebrow, string $title): array => [
    'eyebrow' => $text('Small heading', $eyebrow),
    'title' => $text('Heading', $title, $required),
];

$steps = fn (string $label, array $default): array => $items($label, [
    'icon' => $icon(),
    'title' => $text('Title', null, $required),
    'text' => $textarea('Text'),
], $default, ['max_items' => 8]);

$checklist = fn (string $eyebrow, string $title, array $points, string $formTitle, string $formDescription): array => [
    'label' => 'Checklist & enquiry form',
    'description' => 'The ticked list beside the enquiry form.',
    'fields' => [
        ...$heading($eyebrow, $title),
        'items' => $lines('Ticked points', $points, ['hint' => 'One point per line.']),
        'form_title' => $text('Form heading', $formTitle, $required),
        'form_description' => $textarea('Form introduction', $formDescription),
    ],
];

return [

    /*
    | Icons the admin can pick for cards and steps (names from <x-icon>).
    */
    'icons' => [
        'search', 'home', 'building', 'map-pin', 'key', 'leaf', 'landmark', 'briefcase', 'handshake', 'trending',
        'target', 'shield', 'check-circle', 'eye', 'file', 'book', 'message', 'mail', 'phone', 'users', 'user',
        'calendar', 'clock', 'star', 'sparkles', 'tag', 'globe', 'lock', 'image', 'info',
    ],

    'pages' => [

        'home' => [
            'meta' => $meta(null, 'Discover verified property opportunities, make informed real estate decisions and build lasting wealth with {company}.', 'Leave empty to use “{company} — {tagline}”.'),

            'hero' => [
                'label' => 'Hero',
                'description' => 'The first thing visitors see: headline, buttons and the property search.',
                'hideable' => false,
                'fields' => [
                    'eyebrow' => $text('Small heading', 'Verified property · Investment advisory'),
                    'title' => $text('Headline', 'Strategic property.', $required),
                    'title_highlight' => $text('Headline — gold second line', 'Lasting wealth.'),
                    'description' => $textarea('Introduction', 'Discover verified property opportunities, make informed real estate decisions and build lasting wealth with {company}.'),
                    ...$heroButtons('Explore Properties', '/properties', 'Explore Investment Opportunities', '/investments'),
                    'badge_title' => $text('Floating badge heading', 'Verified listings'),
                    'badge_text' => $text('Floating badge text', 'Title checks & physical inspection'),
                ],
            ],

            'featured' => [
                'label' => 'Featured properties',
                'description' => 'Shown only when at least one property is marked as featured.',
                'fields' => [...$heading('Handpicked', 'Featured properties'), 'button_label' => $text('Button text', 'View all')],
            ],

            'services' => [
                'label' => 'Services',
                'description' => 'The grid of nine services.',
                'fields' => [...$heading('Our services', 'Everything property, in one place'), 'button_label' => $text('Button text', 'All services')],
            ],

            'how_it_works' => [
                'label' => 'How it works',
                'fields' => [
                    ...$heading('How it works', 'From first look to'),
                    'title_highlight' => $text('Heading — gold ending', 'keys in hand.'),
                    'description' => $textarea('Introduction', 'Engage as a guest with just your email, or create an account to follow your progress live and get every update by email.'),
                    'steps' => $steps('Steps', [
                        ['icon' => 'search', 'title' => 'Discover', 'text' => 'Browse curated homes for sale and rent with rich photos and full details.'],
                        ['icon' => 'message', 'title' => 'Engage', 'text' => 'Like what you see? Engage in seconds — our team will reach out to you directly.'],
                        ['icon' => 'trending', 'title' => 'Track progress', 'text' => 'Signed-in engagers follow each step and receive updates in-app and by email.'],
                    ]),
                ],
            ],

            'guarantee' => [
                'label' => 'Guarantee',
                'description' => 'The dark “Why choose us” panel.',
                'fields' => [
                    ...$heading('Why choose {company}', 'The {company_short}'),
                    'title_highlight' => $text('Heading — gold ending', 'Guarantee'),
                    'description' => $textarea('Introduction', 'Every opportunity we present is backed by checks you can see.'),
                    'note' => $textarea('Small print', 'Verification status appears on a listing only once the process has been completed.'),
                    'promises' => $items('Promises', ['icon' => $icon(), 'title' => $text('Promise', null, $required)], [
                        ['icon' => 'shield', 'title' => 'Property title verification'],
                        ['icon' => 'eye', 'title' => 'Physical property inspection'],
                        ['icon' => 'file', 'title' => 'Transparent documentation'],
                        ['icon' => 'trending', 'title' => 'Investment guidance'],
                        ['icon' => 'check-circle', 'title' => 'Post-purchase support'],
                        ['icon' => 'users', 'title' => 'Dedicated client relationship management'],
                    ]),
                ],
            ],

            'investments' => [
                'label' => 'Investment opportunities',
                'fields' => [
                    ...$heading('Investment opportunities', 'Build wealth beyond a single purchase'),
                    'button_label' => $text('Button text', 'Explore Investment Opportunities'),
                    'disclaimer' => $textarea('Disclaimer', 'Any figures shown are projections, not guaranteed returns. Every opportunity page sets out its documentation and relevant risks.'),
                ],
            ],

            'latest' => [
                'label' => 'Latest listings',
                'fields' => [
                    ...$heading('Just listed', 'Latest on the market'),
                    'empty_title' => $text('When there are no listings — heading', 'New listings are on the way'),
                    'empty_text' => $textarea('When there are no listings — text', 'Our team is preparing beautiful properties. Check back very soon.'),
                ],
            ],

            'insights' => [
                'label' => 'Insights',
                'description' => 'Shown only when at least one article is published.',
                'fields' => [...$heading('Insights and resources', 'Guides and market updates'), 'button_label' => $text('Button text', 'All insights')],
            ],

            'cta' => $cta('Your Next Property Investment Starts Here.'),

            'signup' => [
                'label' => 'Create-account invitation',
                'description' => 'Shown to visitors who are not signed in.',
                'fields' => [
                    'title' => $text('Heading', 'Never miss an update on a property you love.', $required),
                    'description' => $textarea('Text', "Create a free account to see every property you've engaged with, follow its progress step by step and get notified by email the moment anything changes."),
                ],
            ],
        ],

        'properties' => [
            'meta' => $meta(null, 'Browse verified properties for sale, rent, shortlet and lease from {company}.', 'Leave empty to use the listing heading, e.g. “Properties for sale”.'),

            'header' => [
                'label' => 'Page heading',
                'hideable' => false,
                'fields' => [
                    'eyebrow' => $text('Small heading', 'Browse listings'),
                    'title' => $text('Heading when no listing type is chosen', 'All properties', $required),
                ],
            ],
        ],

        'investments' => [
            'meta' => $meta('Investment Opportunities', 'Land banking, strategic acquisitions, joint ventures and rental income opportunities — each presented with its structure, timeline, documentation and risks.'),

            'hero' => $hero(
                'Invest with clarity',
                'Investment Opportunities',
                'Land banking, strategic acquisitions, joint ventures and rental income opportunities, each presented with its structure, timeline, documentation and risks.',
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1800&q=70',
                $heroButtons('Speak to an Adviser', '/contact?type=consultation', 'Express Interest', '#enquire'),
            ),

            'disclaimer' => [
                'label' => 'Disclaimer',
                'description' => 'The note above the opportunities.',
                'fields' => ['text' => $textarea('Text', 'Any figures shown are projections, not guaranteed returns. Every opportunity page sets out its documentation and relevant risks.', $required)],
            ],

            'how_it_works' => [
                'label' => 'How it works',
                'fields' => [
                    ...$heading('How it works', 'From first look to informed decision'),
                    'steps' => $steps('Steps', [
                        ['icon' => 'search', 'title' => 'Browse', 'text' => 'Explore opportunities by category and location.'],
                        ['icon' => 'file', 'title' => 'Review', 'text' => 'Read the structure, timeline, documentation and risks.'],
                        ['icon' => 'message', 'title' => 'Express interest', 'text' => 'Send an enquiry for the opportunity you like.'],
                        ['icon' => 'users', 'title' => 'Speak to an adviser', 'text' => 'Get guidance before making any commitment.'],
                    ]),
                ],
            ],

            'checklist' => $checklist(
                'On every opportunity page',
                'Clear information, up front',
                ['Opportunity title and investment category', 'Location and description', 'Required investment and minimum participation', 'Investment structure and expected timeline', 'Relevant risks', 'Supporting documentation'],
                'Express Interest',
                'Tell us which opportunity you like and an adviser will walk you through it.',
            ),

            'cta' => $cta('Your Next Property Investment Starts Here.'),
        ],

        'land-banking' => [
            'meta' => $meta('Land Banking', 'Identify and acquire strategically located land for long-term wealth creation, with clear information on location, documentation and verification status.'),

            'hero' => $hero(
                'Long-term wealth',
                'Land Banking',
                'Identify and acquire strategically located land for long-term wealth creation, with clear information on location, documentation and verification status.',
                'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1800&q=70',
                $heroButtons('View Land Opportunities', '#land-opportunities', 'Speak to an Adviser', '/contact?type=consultation&interest=Land+banking'),
            ),

            'why' => [
                'label' => 'Why land banking',
                'fields' => [
                    ...$heading('Why land banking', 'A long-term approach to property'),
                    'reasons' => $steps('Reasons', [
                        ['icon' => 'map-pin', 'title' => 'Strategic locations', 'text' => 'Land in developing areas, with accessibility and surroundings explained.'],
                        ['icon' => 'file', 'title' => 'Documented acquisition', 'text' => 'Documentation and verification status shown on every opportunity.'],
                        ['icon' => 'users', 'title' => 'Informed decisions', 'text' => 'Investment rationale and guidance from our advisers before you commit.'],
                    ]),
                ],
            ],

            'disclaimer' => [
                'label' => 'Disclaimer',
                'description' => 'The note above the land opportunities.',
                'fields' => ['text' => $textarea('Text', "Any value or growth figures are projections, not guarantees. Review each opportunity's documentation and risks before deciding.", $required)],
            ],

            'checklist' => $checklist(
                'On every land banking page',
                'Know the land before you commit',
                ['Location, accessibility and land sizes', 'Acquisition prices', 'Estate or development information', 'Documentation and verification status', 'Investment rationale', 'Map of the location'],
                'Land Banking Enquiry',
                'Tell us what you are looking for and an adviser will share suitable land.',
            ),

            'cta' => $cta('Your Next Property Investment Starts Here.'),
        ],

        'services' => [
            'meta' => $meta('Services', 'From brokerage and land acquisition to investment advisory and property management — support for every stage of your property journey.'),

            'hero' => $hero(
                'What we do',
                'Our Services',
                'From brokerage and land acquisition to investment advisory and property management, support for every stage of your property journey.',
                'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1800&q=70',
                [
                    'primary_label' => $text('Main button text', 'Request a Consultation', ['hint' => 'Leave empty to hide the button.']),
                    'primary_url' => $url('Main button link', '/contact?type=consultation'),
                    'whatsapp_message' => $text('WhatsApp button message', "Hello, I'd like to know more about your services.", ['hint' => 'The message pre-filled when a visitor taps the WhatsApp button.']),
                ],
            ),

            'list' => [
                'label' => 'Services grid',
                'description' => 'The heading above the nine service cards.',
                'hideable' => false,
                'fields' => $heading('What we do', 'Nine ways we help you move forward'),
            ],

            'management' => [
                'label' => 'Property management',
                'fields' => [
                    ...$heading('Property management', 'For landlords and property owners'),
                    'items' => $lines('Services offered', ['Residential property management', 'Commercial property management', 'Tenant sourcing', 'Rent collection coordination', 'Property inspection', 'Maintenance coordination', 'Occupancy management'], ['hint' => 'One service per line.']),
                    'button_label' => $text('Button text', 'List Your Property'),
                ],
            ],

            'shortlet' => [
                'label' => 'Shortlet panel',
                'description' => 'Shows the featured shortlet, or the text below when none is listed.',
                'fields' => [
                    ...$heading('Shortlet and Airbnb', 'Featured shortlet'),
                    'empty_title' => $text('No shortlet listed — heading', 'Looking for a shortlet?'),
                    'empty_text' => $textarea('No shortlet listed — text', 'Tell us your dates and location and we will match you with a suitable apartment.'),
                ],
            ],

            'cta' => $cta('Not sure where to start?'),
        ],

        'insights' => [
            'meta' => $meta('Insights', 'Property investment guides, market updates and educational content to help you make informed real estate decisions.'),

            'hero' => $hero(
                'Insights and resources',
                'Insights',
                'Property investment guides, market updates and educational content to help you make informed real estate decisions.',
            ),

            'cta' => $cta('Ready to act on what you have read?'),
        ],

        'about' => [
            'meta' => $meta('About Us', 'About {company}: a real estate brokerage and investment advisory company.'),

            'hero' => $hero(
                'About us',
                'About {company}',
                'A real estate brokerage and investment advisory company committed to connecting individuals and investors with strategic property opportunities.',
            ),

            'overview' => [
                'label' => 'Company overview',
                'fields' => [
                    ...$heading('Company overview', '{tagline}'),
                    'body' => $textarea('Text', "We focus on property transactions, investment sourcing, land acquisition, property management and real estate advisory.\n\nOur approach is built around transparency, informed decision-making, property verification and long-term value creation.", ['rows' => 5, 'max' => 3000, 'hint' => 'Leave a blank line between paragraphs.']),
                    'image' => $image('Photo', 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=1400&q=70'),
                    'badge_title' => $text('Photo badge heading', 'Verification first'),
                    'badge_text' => $text('Photo badge text', 'Titles checked before we recommend'),
                ],
            ],

            'mission' => [
                'label' => 'Mission & vision',
                'fields' => [
                    'mission' => $textarea('Mission', 'To make real estate transactions more transparent, accessible and strategic while helping individuals and investors make informed property decisions.', $required),
                    'vision' => $textarea('Vision', 'To become a trusted real estate investment and asset management company with a growing portfolio of strategic and income-generating property assets.', $required),
                ],
            ],

            'values' => [
                'label' => 'Core values',
                'fields' => [
                    ...$heading('Core values', 'What guides our work'),
                    'values' => $items('Values', ['icon' => $icon(), 'title' => $text('Value', null, $required)], [
                        ['icon' => 'shield', 'title' => 'Integrity'],
                        ['icon' => 'eye', 'title' => 'Transparency'],
                        ['icon' => 'briefcase', 'title' => 'Professionalism'],
                        ['icon' => 'check-circle', 'title' => 'Accountability'],
                        ['icon' => 'sparkles', 'title' => 'Innovation'],
                        ['icon' => 'trending', 'title' => 'Long-term value creation'],
                    ]),
                ],
            ],

            'growth' => [
                'label' => 'Growth strategy',
                'description' => 'The dark panel with the company’s phases.',
                'fields' => [
                    ...$heading('Our growth strategy', 'Three phases, one direction'),
                    'phases' => $items('Phases', [
                        'label' => $text('Phase & timing', null, ['placeholder' => 'Phase 1 · 0–3 years']),
                        'title' => $text('Title', null, $required),
                        'points' => $lines('Points', [], ['hint' => 'One per line.']),
                    ], [
                        ['label' => 'Phase 1 · 0–3 years', 'title' => 'Asset-Light Real Estate Brokerage', 'points' => ['Property sales and rentals', 'Shortlet management', 'Investment advisory']],
                        ['label' => 'Phase 2 · 2–6 years', 'title' => 'Land Banking and Capital Accumulation', 'points' => ['Strategic land acquisition', 'Reinvestment into property assets', 'Selected joint ventures']],
                        ['label' => 'Phase 3 · 5–10 years', 'title' => 'Asset Ownership and Development', 'points' => ['Land and residential property ownership', 'Development and sale of properties', 'Rental apartments and shortlet portfolio']],
                    ], ['max_items' => 6]),
                ],
            ],

            'cta' => $cta('Your Next Property Investment Starts Here.'),
        ],

        'contact' => [
            'meta' => $meta('Contact Us', 'Speak to an adviser, send a property or investment enquiry, or request a consultation.'),

            'hero' => $hero(
                'We are here to help',
                'Contact Us',
                'Speak to an adviser, send a property or investment enquiry, or request a consultation.',
            ),

            'details' => [
                'label' => 'Contact details',
                'description' => 'Phone, email and Instagram come from the company settings; WhatsApp lines from Enquiries → WhatsApp numbers.',
                'hideable' => false,
                'fields' => [
                    'office_fallback' => $text('Office text when no address is set', 'Visits by appointment — contact us to arrange'),
                    'whatsapp_message' => $text('WhatsApp chat message', "Hello, I'd like to speak to an adviser.", ['hint' => 'Pre-filled when a visitor taps Chat.']),
                ],
            ],

            'form' => [
                'label' => 'Enquiry form',
                'hideable' => false,
                'fields' => [
                    'title' => $text('Form heading', 'Speak to an Adviser', $required),
                    'description' => $textarea('Form introduction', 'Choose an available property, describe what you need, or both — an adviser will get back to you.'),
                ],
            ],
        ],
    ],
];
