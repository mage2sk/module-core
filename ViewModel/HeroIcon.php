<?php
/**
 * Copyright (c) Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\ViewModel;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * HeroIcon ViewModel - Provides SVG icons compatible with Tailwind CSS / Hyva themes
 * Migrated from Panth\Theme\ViewModel\HeroIcon
 */
class HeroIcon implements ArgumentInterface
{
    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * Icon SVG path data keyed by icon name.
     *
     * Each entry is an array with:
     *  - 'paths': SVG path markup (no outer <svg> tag)
     *  - 'fill': 'none' for outline icons, 'currentColor' for solid/brand icons
     *  - 'stroke': true if icon uses stroke, false otherwise
     */
    private const ICONS = [
        // Navigation & UI
        'menu' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'close' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'search' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'user' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'shopping-cart' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'heart' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'chevron-down' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'chevron-up' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'chevron-right' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'arrow-up' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>',
            'fill' => 'none',
            'stroke' => true,
        ],

        // Communication
        'mail' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'phone' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'chat' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],

        // Social Media (brand icons using fill)
        'facebook' => [
            'paths' => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
            'fill' => 'currentColor',
            'stroke' => false,
        ],
        'twitter' => [
            'paths' => '<path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>',
            'fill' => 'currentColor',
            'stroke' => false,
        ],
        'instagram' => [
            'paths' => '<path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/>',
            'fill' => 'currentColor',
            'stroke' => false,
        ],
        'linkedin' => [
            'paths' => '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>',
            'fill' => 'currentColor',
            'stroke' => false,
        ],
        'youtube' => [
            'paths' => '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>',
            'fill' => 'currentColor',
            'stroke' => false,
        ],
        'pinterest' => [
            'paths' => '<path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/>',
            'fill' => 'currentColor',
            'stroke' => false,
        ],
        'whatsapp' => [
            'paths' => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>',
            'fill' => 'currentColor',
            'stroke' => false,
        ],

        // Status & Feedback
        'check' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'check-circle' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'x-circle' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'exclamation' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],

        // Location & Info
        'location' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],
        'clock' => [
            'paths' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'fill' => 'none',
            'stroke' => true,
        ],

        // Spinner
        'spinner' => [
            'paths' => '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>',
            'fill' => 'none',
            'stroke' => false,
        ],
    ];

    /**
     * Get icon HTML
     *
     * @param string $name Icon name
     * @param string $class CSS classes
     * @param string $type Icon type (outline or solid) - reserved for future use
     * @return string
     */
    public function getIcon(string $name, string $class = 'w-6 h-6', string $type = 'outline'): string
    {
        $iconData = self::ICONS[$name] ?? self::ICONS['exclamation'];
        $escapedClass = $this->escaper->escapeHtmlAttr($class);

        $strokeAttr = $iconData['stroke'] ? ' stroke="currentColor"' : '';
        $extraClass = ($name === 'spinner') ? ' animate-spin' : '';

        return '<svg class="' . $escapedClass . $extraClass . '"'
            . ' fill="' . $iconData['fill'] . '"'
            . $strokeAttr
            . ' viewBox="0 0 24 24">'
            . $iconData['paths']
            . '</svg>';
    }

    /**
     * Check if an icon exists
     *
     * @param string $name
     * @return bool
     */
    public function hasIcon(string $name): bool
    {
        return isset(self::ICONS[$name]);
    }

    /**
     * Get all available icon names
     *
     * @return string[]
     */
    public function getAvailableIcons(): array
    {
        return array_keys(self::ICONS);
    }

    // Quick access methods for common icons

    public function menu(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('menu', $class);
    }

    public function close(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('close', $class);
    }

    public function search(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('search', $class);
    }

    public function user(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('user', $class);
    }

    public function shoppingCart(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('shopping-cart', $class);
    }

    public function heart(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('heart', $class);
    }

    public function mail(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('mail', $class);
    }

    public function phone(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('phone', $class);
    }

    public function chat(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('chat', $class);
    }

    public function check(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('check', $class);
    }

    public function location(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('location', $class);
    }

    public function clock(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('clock', $class);
    }

    public function arrowUp(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('arrow-up', $class);
    }

    public function chevronDown(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('chevron-down', $class);
    }

    public function chevronUp(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('chevron-up', $class);
    }

    public function whatsapp(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('whatsapp', $class);
    }

    public function spinner(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('spinner', $class);
    }

    public function facebook(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('facebook', $class);
    }

    public function twitter(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('twitter', $class);
    }

    public function instagram(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('instagram', $class);
    }

    public function linkedin(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('linkedin', $class);
    }

    public function youtube(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('youtube', $class);
    }

    public function pinterest(string $class = 'w-6 h-6'): string
    {
        return $this->getIcon('pinterest', $class);
    }
}
