<?php

namespace Spy\Timeline\Aggregation\Constraint;

/**
 * ConstraintResolver
 *
 * @author Daniel Griffin <dan@contagious.nu>
 */
class ConstraintResolver
{

    const ACCEPTED_AGGREGATION = "ConstraintAccepted";
    
    const DECLINED_AGGREGATION = "ConstraintDeclined";

    protected $status = null;
    
    protected $stored_components = array();
    
    protected $constraint;
    
    /**
     * Checks to see if resolver status has been called before returning a status.
     * 
     * @return const string;
     */
    public function getStatus()
    { 
        if(!$this->status)
        { 
            throw new \Exception("No aggregation choice has been set."); 
        }
        return $this->status;
    }

    /**
     * setStatus function.
     * 
     * @param mixed $status
     */
    public function setStatus($status)
    { 
        $this->status = $status;
    }
    
    /**
     * getConstraintComponent function.
     * 
     * @return string
     */
    public function getConstraintComponent()
    { 
        return $this->constraint;
    }
    
    /**
     * getCollectionComponents function. Returns the names components to store.
     * 
     * @return void
     */
    public function getCollectionComponents()
    { 
        return $this->stored_components;
    }

    /**
     * acceptAggregation function.
     * 
     * @param mixed $constraint_component Component to constrain by
     * @param array $component_collections array of names and components that should be grouped into a Collection Component
     * @return ConstraintResolver
     */
    public function acceptAggregation($constraint_component, array $component_collections)
    { 
        $this->constraint = $constraint_component;
        $this->stored_components = $component_collections;
        
        $this->setStatus(self::ACCEPTED_AGGREGATION);
        return $this;
    }
    
    /**
     * declineAggregation function. Rejects constraints status so the aggregator skips.
     *
     * @return ConstraintResolver
     */
    public function declineAggregation() 
    { 
        $this->setStatus(self::DECLINED_AGGREGATION);
        return $this;
    }
}