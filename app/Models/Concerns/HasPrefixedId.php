<?php

namespace App\Models\Concerns;

use App\Support\IdGenerator;

trait HasPrefixedId
{
    protected static function bootHasPrefixedId(): void
    {
        static::creating(function ($model) {
            $keyName = $model->getKeyName();

            if (empty($model->{$keyName})) {
                $model->{$keyName} = IdGenerator::make(
                    $model->getIdPrefix(),
                    $model->usesDateInId()
                );
            }
        });
    }

    protected function getIdPrefix(): string
    {
        return property_exists($this, 'idPrefix')
            ? $this->idPrefix
            : strtoupper($this->getTable());
    }

    protected function usesDateInId(): bool
    {
        return property_exists($this, 'idUsesDate') && $this->idUsesDate;
    }
}
