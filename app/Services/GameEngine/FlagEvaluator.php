<?php

namespace App\Services\GameEngine;

class FlagEvaluator
{
    protected StateManager $stateManager;

    public function __construct(StateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    /**
     * Evaluate flag conditions
     * Supports: AND, OR, NOT, comparison operators
     *
     * Example:
     * [
     *   'AND' => [
     *     ['flag' => 'clue_found', 'equals' => true],
     *     ['flag' => 'door_opened', 'equals' => false]
     *   ]
     * ]
     */
    public function evaluate(array $conditions): bool
    {
        if (empty($conditions)) {
            return true;
        }

        // Check for logical operators
        if (isset($conditions['AND'])) {
            return $this->evaluateAnd($conditions['AND']);
        }

        if (isset($conditions['OR'])) {
            return $this->evaluateOr($conditions['OR']);
        }

        if (isset($conditions['NOT'])) {
            return !$this->evaluate($conditions['NOT']);
        }

        // Single condition
        return $this->evaluateCondition($conditions);
    }

    /**
     * Evaluate AND conditions (all must be true)
     */
    protected function evaluateAnd(array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if (!$this->evaluate($condition)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Evaluate OR conditions (at least one must be true)
     */
    protected function evaluateOr(array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if ($this->evaluate($condition)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Evaluate a single condition
     */
    protected function evaluateCondition(array $condition): bool
    {
        // Flag-based condition
        if (isset($condition['flag'])) {
            return $this->evaluateFlagCondition($condition);
        }

        // Variable-based condition
        if (isset($condition['variable'])) {
            return $this->evaluateVariableCondition($condition);
        }

        // Clue-based condition
        if (isset($condition['has_clue'])) {
            return $this->stateManager->hasClue($condition['has_clue']);
        }

        // Scene visited condition
        if (isset($condition['visited_scene'])) {
            return $this->stateManager->hasVisitedScene($condition['visited_scene']);
        }

        // Inventory condition
        if (isset($condition['has_item'])) {
            return $this->stateManager->hasInInventory($condition['has_item']);
        }

        return false;
    }

    /**
     * Evaluate flag condition
     */
    protected function evaluateFlagCondition(array $condition): bool
    {
        $flagKey = $condition['flag'];
        $flagValue = $this->stateManager->getFlag($flagKey);

        // Equals
        if (isset($condition['equals'])) {
            return $flagValue === $condition['equals'];
        }

        // Not equals
        if (isset($condition['not_equals'])) {
            return $flagValue !== $condition['not_equals'];
        }

        // Greater than
        if (isset($condition['greater_than'])) {
            return $flagValue > $condition['greater_than'];
        }

        // Less than
        if (isset($condition['less_than'])) {
            return $flagValue < $condition['less_than'];
        }

        // Greater than or equal
        if (isset($condition['gte'])) {
            return $flagValue >= $condition['gte'];
        }

        // Less than or equal
        if (isset($condition['lte'])) {
            return $flagValue <= $condition['lte'];
        }

        // In array
        if (isset($condition['in'])) {
            return in_array($flagValue, $condition['in']);
        }

        // Not in array
        if (isset($condition['not_in'])) {
            return !in_array($flagValue, $condition['not_in']);
        }

        // Exists (is not null)
        if (isset($condition['exists'])) {
            return ($flagValue !== null) === $condition['exists'];
        }

        // Default: check if flag exists and is truthy
        return (bool) $flagValue;
    }

    /**
     * Evaluate variable condition
     */
    protected function evaluateVariableCondition(array $condition): bool
    {
        $varKey = $condition['variable'];
        $varValue = $this->stateManager->getVariable($varKey);

        // Same operators as flag conditions
        if (isset($condition['equals'])) {
            return $varValue === $condition['equals'];
        }

        if (isset($condition['not_equals'])) {
            return $varValue !== $condition['not_equals'];
        }

        if (isset($condition['greater_than'])) {
            return $varValue > $condition['greater_than'];
        }

        if (isset($condition['less_than'])) {
            return $varValue < $condition['less_than'];
        }

        if (isset($condition['gte'])) {
            return $varValue >= $condition['gte'];
        }

        if (isset($condition['lte'])) {
            return $varValue <= $condition['lte'];
        }

        if (isset($condition['in'])) {
            return in_array($varValue, $condition['in']);
        }

        if (isset($condition['not_in'])) {
            return !in_array($varValue, $condition['not_in']);
        }

        if (isset($condition['exists'])) {
            return ($varValue !== null) === $condition['exists'];
        }

        return (bool) $varValue;
    }
}
