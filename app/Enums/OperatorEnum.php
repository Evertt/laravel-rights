<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self eq()
 * @method static self gt()
 * @method static self lt()
 * @method static self neq()
 * @method static self gte()
 * @method static self lte()
 * @method static self scope()
 */
final class OperatorEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'eq' => '=',
            'gt' => '>',
            'lt' => '<',
            'neq' => '!=',
            'gte' => '>=',
            'lte' => '<=',
        ];
    }
}
