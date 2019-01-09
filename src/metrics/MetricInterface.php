<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\craft\scorecard\metrics;

/**
 * A Metric is an object which performs a calculation to produce a score.  The score may be weighted (or boosted) if
 * necessary.
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
interface MetricInterface
{
    /**
     * The Metric name
     *
     * @return string
     */
    public static function displayName(): string;

    /**
     * The Metric description
     *
     * @return string
     */
    public static function description(): string;

    /**
     * The Metric score.  This should be the sum of a raw calculated score times the weight.
     *
     * @return float
     */
    public function getScore(): float;

    /**
     * @return static
     */
    public function resetScore();

    /**
     * The Metric weight.  The score should be multiplied by this number.
     *
     * @return float
     */
    public function getWeight(): float;

    /**
     * The Metric version.  Identify metric iterations.
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * The Metric represented as an array
     *
     * @return array
     */
    public function toConfig(): array;
}
