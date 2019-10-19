<?php

namespace Exceedone\Exment\ConditionItems;

use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Enums\ConditionTypeDetail;
use Exceedone\Exment\Enums\FilterOption;
use Exceedone\Exment\Enums\FilterType;

class WorkflowItem extends ConditionItemBase
{
    public function getFilterOption(){
        return array_get($this->viewFilter ? FilterOption::FILTER_OPTIONS() : FilterOption::FILTER_CONDITION_OPTIONS(), FilterType::SELECT);
    }
}
