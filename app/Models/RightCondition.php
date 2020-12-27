<?php

namespace App\Models;

use App\Enums\OperatorEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RightCondition extends Model
{
    use HasFactory;

    protected $casts = [
        'operator' => OperatorEnum::class,
    ];

    public function __toString()
    {
        return "`$this->field` $this->operator \"$this->value\"";
    }

    public function toArray()
    {
        return [
            $this->field,
            (string) $this->operator,
            $this->value,
        ];
    }
}
