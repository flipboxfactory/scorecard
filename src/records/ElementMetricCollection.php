<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\scorecard\records;

use flipbox\ember\helpers\ArrayHelper;
use flipbox\scorecard\db\ElementMetricQuery;
use flipbox\scorecard\helpers\MetricHelper;
use flipbox\scorecard\metrics\MetricInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property ElementMetric[] $children
 */
abstract class ElementMetricCollection extends ElementMetric
{
     use traits\ElementMetricCollectionTrait;

    /**
     * @return array
     */
    const METRICS = [];

    /**
     * @return array
     */
    protected function newMetrics(): array
    {
        return (array) static::METRICS;
    }

    /*******************************************
     * VALIDATE (CHILD RECORDS)
     *******************************************/

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $success = parent::beforeValidate();

        if (!$this->validateMetrics()) {
            $success = false;
        }

        return $success;
    }

    /*******************************************
     * SAVE (CHILD RECORDS)
     *******************************************/

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->addMetricsToSettings();
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->saveChildRecords();
        return parent::afterSave($insert, $changedAttributes);
    }
}
