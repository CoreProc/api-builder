<?php

namespace CoreProc\ApiBuilder\Builders;

use Carbon\Carbon;

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

    public $query;

    public function __construct($query, $requestParameters, $allowedParams, $dates)
    {
        foreach ($requestParameters as $parameter => $value) {
            // Handle sorting first
            if ($parameter === 'sort') {
                $sortValue = explode(',', $value);
                $query->orderBy($sortValue[0], (! empty($sortValue[1])) ? $sortValue[1] : 'asc');
                continue;
            }

            // Get last of exploded parameter by _
            $parameterArray = explode('_', $parameter);

            $operatorString = $parameterArray[count($parameterArray) - 1];

            // Get where operator. There is only one possible value: "or"
            $whereOperator = $parameterArray[0] === 'or' ? 'or' : 'and';

            if (in_array($operatorString, self::$operators)) {
                // Remove the operator from the string
                unset($parameterArray[count($parameterArray) - 1]);
            }

            if ($parameterArray[0] === 'or') {
                // Remove where operator from parameter
                unset($parameterArray[0]);
            }

            // Build back the parameter
            $parameter = implode('_', $parameterArray);

            // Convert operator to symbol
            $operator = $this->operatorStringToSqlOperator($operatorString);

            if (! in_array($parameter, $allowedParams)) {
                continue;
            }

            if (in_array($parameter, $dates)) {
                $value = Carbon::parse($value);
            }

            if (! is_array($value)) {
                // Append wildcards to value if ever
                $value = $this->appendWildcardsToValue($value, $operatorString);

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

        $this->query = $query;
    }

    private function operatorStringToSqlOperator($operator)
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

    private function appendWildcardsToValue($value, $operatorString)
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
