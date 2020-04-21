<?php

namespace Exceedone\Exment\ConditionItems;

use Exceedone\Exment\Model\CustomValue;
use Exceedone\Exment\Model\Condition;
use Exceedone\Exment\Enums\ConditionTypeDetail;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Enums\WorkflowTargetSystem;

class SystemItem extends ConditionItemBase implements ConditionItemInterface
{
    use ColumnSystemItemTrait;
    
    /**
     * check if custom_value and user(organization, role) match for conditions.
     *
     * @param CustomValue $custom_value
     * @return boolean
     */
    public function isMatchCondition(Condition $condition, CustomValue $custom_value)
    {
        return false;
    }

    /**
     * get text.
     *
     * @param string $key
     * @param string $value
     * @param bool $showFilter
     * @return string
     */
    public function getText($key, $value, $showFilter = true)
    {
        $enum = WorkflowTargetSystem::getEnum($value);
        return isset($enum) ? exmtrans('common.' . $enum->lowerkey()) : null;
    }
    
    /**
     * Check has workflow authority
     *
     * @param CustomValue $custom_value
     * @return boolean
     */
    public function hasAuthority($workflow_authority, $custom_value, $targetUser)
    {
        return $workflow_authority->related_id == WorkflowTargetSystem::CREATED_USER && $custom_value->created_user_id == $targetUser->id;
    }

    public static function setConditionQuery($query, $tableName, $custom_table, $authorityTableName = SystemTableName::WORKFLOW_AUTHORITY)
    {
        $query->where($authorityTableName . '.related_id', WorkflowTargetSystem::CREATED_USER)
            ->where($authorityTableName . '.related_type', ConditionTypeDetail::SYSTEM()->lowerkey())
            ->where($tableName . '.created_user_id', \Exment::user()->base_user_id);
    }
    
    /**
     * Set Authority Targets
     *
     * @param WorkflowAuthority $workflow_authority
     * @param CustomValue $custom_value
     * @param array $userIds
     * @param array $organizationIds
     * @param array $labels
     * @return void
     */
    public function setAuthorityTargets($workflow_authority, $custom_value, &$userIds, &$organizationIds, &$labels, $options = []){
        $getAsDefine = array_get($options, 'getAsDefine', false);
        if ($getAsDefine) {
            $labels[] = exmtrans('common.' . WorkflowTargetSystem::getEnum($workflow_authority->related_id)->lowerKey());
            return;
        }

        if ($workflow_authority->related_id == WorkflowTargetSystem::CREATED_USER) {
            $userIds[] = $custom_value->created_user_id;
        }
    }
}
