<?php

namespace App\Services\Sales;

use App\Models\Sales\Sale;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Throwable;

class FormulaEvaluator
{
    private ExpressionLanguage $el;

    // Built-in Sale columns exposed to formulas
    private const BUILTIN_KEYS = [
        'total_points', 'agent_points', 'sale_date', 'customer_name',
        'customer_phone', 'status',
    ];

    public function __construct()
    {
        $this->el = new ExpressionLanguage();
    }

    /**
     * Evaluate a formula against a Sale + any in-flight metadata values.
     *
     * @param  string  $formula   e.g. "units * unit_price"
     * @param  Sale    $sale      the sale model (built-in columns)
     * @param  array   $meta      resolved metadata key=>value (custom fields, already coerced)
     * @return mixed              computed value, or null on error
     */
    public function evaluate(string $formula, Sale $sale, array $meta): mixed
    {
        $context = $this->buildContext($sale, $meta);

        try {
            $result = $this->el->evaluate($formula, $context);
            // Round floats to 4dp to avoid IEEE 754 imprecision
            if (is_float($result)) {
                $result = round($result, 4);
            }
            return $result;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Validate a formula string without a real Sale (uses zeroed context).
     * Returns the error message, or null if valid.
     */
    public function validate(string $formula, array $availableKeys = []): ?string
    {
        $context = array_fill_keys(
            array_merge(self::BUILTIN_KEYS, $availableKeys),
            0
        );

        try {
            $this->el->evaluate($formula, $context);
            return null;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /** Build the variable context exposed to a formula. */
    public function buildContext(Sale $sale, array $meta): array
    {
        $context = [];

        foreach (self::BUILTIN_KEYS as $key) {
            $val = $sale->{$key};
            // Decimal-cast columns come back as strings — coerce to float
            $context[$key] = is_numeric($val) ? (float) $val : $val;
        }

        // Custom field values — cast numbers to float so math works
        foreach ($meta as $key => $value) {
            $context[$key] = is_numeric($value) ? (float) $value : $value;
        }

        return $context;
    }

    public static function builtinKeys(): array
    {
        return self::BUILTIN_KEYS;
    }
}
