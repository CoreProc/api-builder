<?php

namespace CoreProc\ApiBuilder\Builders;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class HttpQueryToSqlQueryBuilder
{
    protected static $operators = [
        'not',
        'in',
        'not_in',
        'lt',
        'lte',
        'gt',
        'gte',
        'contains',
        'not_contains',
        'starts_with',
        'not_starts_with',
        'ends_with',
        'not_ends_with',
    ];

    /**
     * @param Builder $query
     * @param array $requestParameters
     * @param array $allowedParams
     * @param array $dates
     *
     * @return Builder
     */
    public static function build(
        Builder $query,
        array $requestParameters = [],
        array $allowedParams = [],
        array $dates = []
    ) {
        foreach ($requestParameters as $parameter => $value) {
            // Handle sorting first
            if ($parameter === 'sort') {
                $sortValue = explode(',', $value);
                $query->orderBy($sortValue[0], (! empty($sortValue[1])) ? $sortValue[1] : 'asc');
                continue;
            }

            // Then handle the "limit" filter
            if ($parameter === 'limit') {
                $query->limit($value);
                continue;
            }

            // Check what the operator string is
            $operatorString = null;
            foreach (static::$operators as $o) {
                if (substr($parameter, -(strlen($o))) === $o) {
                    $operatorString = $o;
                    continue;
                }
            }

            // Get where operator. There is only one possible value: "or"
            $whereOperator = substr($parameter, 3) === 'or_' ? 'or' : 'and';

            if (! is_null($operatorString)) {
                // Remove the operator from the string
                $parameter = str_replace('_' . $operatorString, '', $parameter);
            }

            if ($whereOperator === 'or') {
                // Remove where operator from parameter
                $parameter = str_replace('or_', '', $parameter);
            }

            // Convert operator to symbol
            $operator = self::operatorStringToSqlOperator($operatorString);

            // Skip if not parameter is not allowed
            if (! in_array($parameter, $allowedParams)) {
                continue;
            }

            // Convert string to carbon date
            if (in_array($parameter, $dates)) {
                $value = Carbon::parse($value);
            }

            if (! is_array($value)) {
                // Append wildcards to value if ever
                $value = self::appendWildcardsToValue($value, $operatorString);

                if ($whereOperator === 'or') {
                    if ($value === 'null') {
                        $query->orWhereNull($parameter);
                    } else {
                        $query->orWhere($parameter, $operator, $value);
                    }
                } else {
                    if ($value === 'null') {
                        $query->whereNull($parameter);
                    } else {
                        $query->where($parameter, $operator, $value);
                    }
                }
            } else {
                // Handle array values here. There are only two array values: "in", and "not_in"
                switch ($operatorString) {
                    case 'in':
                        $query->whereIn($parameter, $value);
                        break;
                    case 'not_in':
                        $query->whereNotIn($parameter, $value);
                        break;
                }
            }
        }

        return $query;
    }

    private static function operatorStringToSqlOperator($operator)
    {
        switch ($operator) {
            case 'not':
                return '!=';
            case 'lt':
                return '<';
            case 'lte':
                return '<=';
            case 'gt':
                return '>';
            case 'gte':
                return '>=';
            case 'contains':
                return 'like';
            case 'not_contains':
                return 'not like';
            case 'starts_with':
                return 'like';
            case 'not_starts_with':
                return 'not like';
            case 'ends_with':
                return 'like';
            case 'not_ends_with':
                return 'not like';
            default:
                return '=';
        }
    }

    private static function appendWildcardsToValue($value, $operatorString)
    {
        switch ($operatorString) {
            case 'contains':
                return "%{$value}%";
            case 'not_contains':
                return "%{$value}%";
            case 'starts_with':
                return "{$value}%";
            case 'not_starts_with':
                return "{$value}%";
            case 'ends_with':
                return "%{$value}";
            case 'not_ends_with':
                return "%{$value}";
            default:
                return $value;
        }
    }
}
