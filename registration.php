<?php
/**
 * Panth Core Module Registration
 *
 * Base module for all Panth extensions providing common functionality,
 * utilities, and shared components.
 *
 * @category  Panth
 * @package   Panth_Core
 * @author    Panth Infotech
 * @copyright Copyright (c) Panth Infotech
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Panth_Core',
    __DIR__
);
