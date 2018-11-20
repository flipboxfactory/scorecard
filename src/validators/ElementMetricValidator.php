<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\scorecard\validators;

use Craft;
use flipbox\scorecard\metrics\MetricInterface;
use yii\validators\Validator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ElementMetricValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $class = $model->$attribute;

        // Handles are always required, so if it's blank, the required validator will catch this.
        if ($class) {
            if (!$class instanceof MetricInterface &&
                !is_subclass_of($class, MetricInterface::class)
            ) {
                $message = Craft::t(
                    'scorecard',
                    '“{class}” is a not a valid metric.',
                    ['class' => $class]
                );
                $this->addError($model, $attribute, $message);
            }
        }
    }
}
