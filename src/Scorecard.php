<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\craft\scorecard;

use craft\base\Plugin;
use flipbox\craft\ember\modules\LoggerTrait;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Scorecard extends Plugin
{
    use LoggerTrait;

    /**
     * The module category
     *
     * @var string
     */
    protected static $category = 'scorecard';
}
