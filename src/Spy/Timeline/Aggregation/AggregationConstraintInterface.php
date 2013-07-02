<?php

namespace Spy\Timeline\Aggregation;

use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Aggregation\Constraint\ConstraintResolver;

/**
 * AggregationConstraintInterface
 *
 * @author Daniel Griffin <dan@contagious.nu>
 */
interface AggregationConstraintInterface
{
    /**
     * @param ActionInterface $action
     * @param ConstraintResolver $ConstraintResolver
     */
    public function shouldAggregate(ActionInterface $action, ConstraintResolver $ConstraintResolver);
    
    public function getName();
}