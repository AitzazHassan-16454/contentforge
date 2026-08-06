<?php

return [
    [
        'name' => 'Free',
        'price' => 0,
        'period' => 'free forever',
        'description' => 'Everything you need to try ContentForge and publish your first posts.',
        'features' => [
            '3 published posts',
            '25 AI generations / month',
            'Markdown editor with live preview',
            'Basic SEO title suggestions',
        ],
        'cta' => 'Start for free',
        'highlight' => false,
    ],
    [
        'name' => 'Pro',
        'price' => 12,
        'period' => 'per month',
        'description' => 'For writers who publish regularly and want the full AI toolbox.',
        'features' => [
            'Unlimited published posts',
            '500 AI generations / month',
            'SEO assistant & suggestions',
            'Scheduled publishing',
            'Analytics dashboard',
            'Priority support',
        ],
        'cta' => 'Start 14-day trial',
        'highlight' => true,
    ],
    [
        'name' => 'Teams',
        'price' => 29,
        'period' => 'per month',
        'description' => 'For teams and content teams that need shared workflows.',
        'features' => [
            'Everything in Pro',
            'Up to 5 team members',
            'Unlimited AI generations',
            'Roles & admin panel',
            'Shared media library',
            'Dedicated support',
        ],
        'cta' => 'Contact sales',
        'highlight' => false,
    ],
];
