<?php

namespace App\Models\Builders;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class CategoryBuilder extends Builder
{
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $mappedColumn = $this->mapTypeColumn($column);
        if (is_string($mappedColumn)) {
            if ($value === null) {
                return parent::where($mappedColumn, '=', Category::typeId((string) $operator), $boolean);
            }

            if (is_string($value)) {
                $value = Category::typeId($value);
            }

            return parent::where($mappedColumn, $operator, $value, $boolean);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $mappedColumn = $this->mapTypeColumn($column);
        if (is_string($mappedColumn)) {
            $rawValues = is_array($values) ? $values : [$values];
            $ids = Category::typeIds(array_map(fn ($v) => (string) $v, $rawValues));

            return parent::whereIn($mappedColumn, $ids !== [] ? $ids : [-1], $boolean, $not);
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }

    private function mapTypeColumn($column): ?string
    {
        if (! is_string($column)) {
            return null;
        }

        if ($column === 'type') {
            return 'category_type_id';
        }

        if (str_ends_with($column, '.type')) {
            return substr($column, 0, -5).'.category_type_id';
        }

        return null;
    }
}
