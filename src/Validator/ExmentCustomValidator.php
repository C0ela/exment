<?php
namespace Exceedone\Exment\Validator;

use Exceedone\Exment\Enums\ColumnType;
use Exceedone\Exment\Enums\SummaryCondition;
use Exceedone\Exment\Model\CustomColumn;
use Exceedone\Exment\Model\CustomRelation;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Model\Traits\ColumnOptionQueryTrait;
use Illuminate\Validation\Validator as AdminValidator;

/**
 * CAUTION:::
 * Don't please add new function. Please add new Rule.
 */
class ExmentCustomValidator extends AdminValidator
{
    use ColumnOptionQueryTrait;

    public function passes()
    {
        return parent::passes() && count($this->customMessages) == 0;
    }

    public function fails()
    {
        return parent::fails() || count($this->customMessages) > 0;
    }

    public function getMessages()
    {
        return array_merge($this->errors()->messages(), $this->customMessages);
    }

    /**
    * Validation in table
    *
    * @param $attribute
    * @param $value
    * @param $parameters
    * @return bool
    */
    public function validateUniqueInTable($attribute, $value, $parameters)
    {
        if (count($parameters) < 2) {
            return true;
        }

        // get classname for search
        $classname = $parameters[0];
        // get custom_table_id
        $custom_table_id = $parameters[1];

        // get count same value in table;
        $count = $classname::where('custom_table_id', $custom_table_id)
        ->where($attribute, $value)
        ->count();

        if ($count > 0) {
            return false;
        }

        return true;
    }

    /**
    * Validation in table
    *
    * @param $attribute
    * @param $value
    * @param $parameters
    * @return bool
    */
    public function validateSummaryCondition($attribute, $value, $parameters)
    {
        $field_name = str_replace('.view_summary_condition', '.view_column_target', $attribute);
        $view_column_target = array_get($this->data, $field_name);
        $view_column_target = static::getOptionParams($view_column_target, null);
        $column_target = array_get($view_column_target, 'column_target');
        if (is_numeric($column_target)) {
            // get column_type
            $custom_column = CustomColumn::getEloquent($column_target);
            // numeric column can select all summary condition.
            if (isset($custom_column) && $custom_column->isCalc()) {
                return true;
            }
        }
        // system column and not numeric column only select no calculate condition.
        $option = SummaryCondition::getOption(['id' => $value]);
        if (array_get($option, 'numeric')) {
            return false;
        }
        return true;
    }
    
    /**
    * Validate relations between tables are circular reference
    *
    * @param $attribute
    * @param $value
    * @param $parameters
    * @return bool
    */
    public function validateLoopRelation($attribute, $value, $parameters)
    {
        if (count($parameters) < 1) {
            return true;
        }

        // get custom_table_id
        $custom_table_id = $parameters[0];
        $relation_id = count($parameters) >= 2 ? $parameters[1] : null;

        // check lower relation;
        if (!$this->HasRelation('parent_custom_table_id', 'child_custom_table_id', $custom_table_id, $value, $relation_id)) {
            return false;
        }

        // check upper relation;
        if (!$this->HasRelation('child_custom_table_id', 'parent_custom_table_id', $custom_table_id, $value, $relation_id)) {
            return false;
        }

        return true;
    }

    /**
     * check if exists custom relation.
     */
    protected function HasRelation($attr1, $attr2, $custom_table_id, $value, $relation_id = null)
    {
        // get count reverse relation in table;
        $query = CustomRelation::where($attr1, $custom_table_id);
        if (isset($relation_id)) {
            $query = $query->where('id', '<>', $relation_id);
        }
        $rows = $query->get();

        foreach ($rows as $row) {
            $id = array_get($row, $attr2);
            if ($id == $value) {
                return false;
            } else {
                if (!$this->HasRelation($attr1, $attr2, $id, $value)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function replaceChangeFieldValue($message, $attribute, $rule, $parameters)
    {
        if (count($parameters) > 0) {
            return $parameters[count($parameters) - 1];
        }
        return $message;
    }

    /**
    * Validation regular expression
    *
    * @param $attribute
    * @param $value
    * @param $parameters
    * @return bool
    */
    public function validateRegularExpression($attribute, $value, $parameters)
    {
        set_error_handler(
            function ($severity, $message) {
                throw new \RuntimeException($message);
            }
        );
        try {
            preg_match("/$value/", '');
        } catch (\RuntimeException $e) {
            return false;
        } finally {
            restore_error_handler();
        }
    
        return true;
    }
}
