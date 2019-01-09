<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\craft\scorecard\metrics;

use flipbox\craft\scorecard\helpers\MetricHelper;

/**
 * A Metric which utilized child Metrics to calculate a score.  This is useful when preforming complex calculations
 * or further details/breakdown are needed on a particular Metric.
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class AbstractMetricCollection extends AbstractMetric
{
    /**
     * @var MetricInterface[]
     */
    protected $metrics = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->setMetrics($this->metrics);
    }

    /**
     * @return float
     */
    protected function calculateScore(): float
    {
        // Sum of total/weight
        $total = $weights = 0;

        foreach ($this->metrics as $metric) {
            $total += $metric->getScore();
            $weights += $metric->getWeight();
        }

        return (float)($total / $weights);
    }

    /**
     * @inheritdoc
     */
    public function resetScore()
    {
        foreach ($this->metrics as $metric) {
            $metric->resetScore();
        }

        return parent::resetScore();
    }

    /**
     * @inheritdoc
     */
    public function toConfig(): array
    {
        $children = [];

        foreach ($this->metrics as $metric) {
            $children[] = $metric->toConfig();
        }

        return array_merge(
            parent::toConfig(),
            [
                'metrics' => $children
            ]
        );
    }

    /**
     * @param array $metrics
     * @return $this
     */
    public function setMetrics(array $metrics = [])
    {
        $this->metrics = [];

        foreach ($metrics as $metric) {
            if (!$metric instanceof MetricInterface) {
                $metric = $this->createMetric($metric);
            }

            $this->metrics[] = $metric;
        }

        return $this;
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param $metric
     * @return MetricInterface
     */
    protected function createMetric($metric): MetricInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return MetricHelper::create($metric);
    }
}
