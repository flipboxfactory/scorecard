<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\scorecard\tests;

use Codeception\Test\Unit;
use flipbox\scorecard\Scorecard;

class ScorecardTest extends Unit
{
    /**
     * @var Scorecard
     */
    private $module;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Scorecard('scorecard');
    }
}
