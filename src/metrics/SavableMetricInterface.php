<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\scorecard\metrics;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
interface SavableMetricInterface extends MetricInterface
{
    /**
     * Saves the metric
     *
     * @return bool whether the saving succeeded.
     */
    public function save();
}
