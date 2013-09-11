<?php

namespace Spy\Timeline\Aggregation;

use Spy\Timeline\Model\TimelineInterface;
use Spy\Timeline\Aggregation\AggregationConstraintInterface;

use Spy\Timeline\Aggregation\Constraint\ConstraintResolver;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Aggregator
 *
 * @author Daniel Griffin <dan@contagious.nu>
 */
class Aggregator
{
    /**
     * @var array<AggregationConstraintInterface>
     */
    protected $constraints = array();

    /**
     * @var boolean
     */
    protected $sorted = true;

    /**
     * @param AggregationConstraintInterface $constraint constraint class
     */
    public function add(AggregationConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;
        $this->sorted    = false;
    }
    
    /**
     * Eventually plan to outsource this to a class that can be set into config and 
     * removes the need to sort in the aggregator (for custom sorting regardless of 
     * if you're using the the aggregator or not).
     * For now we sort by date. 
     * 
     * Also note, actions which are aggregated will always stick with the most
     * recent activity timestamp.
     * 
     * @param array $actions
     * @return array
     */
    public function sortActions(ArrayCollection $actions) 
    { 
        $iterator = $actions->getIterator();
        $iterator->uasort(function ($first, $second) {
            if ($first === $second) {
                return 0;
            }
            
            if ($first instanceof TimelineInterface) {
                $first = $first->getAction();
            }
            
            if ($second instanceof TimelineInterface) {
                $second = $second->getAction();
            }
         
            return (float) $first->getCreatedAt()->getTimestamp() > (float) $second->getCreatedAt()->getTimestamp() ? -1 : 1;
        });
    
        return $iterator;
    }

    /**
     * @param array $collection collection
     *
     * @return array
     */
    public function aggregate($collection)
    {
        if (!$this->sorted) {
            $this->sortConstraints();
        }
        
        //Filter through the collection and check to see if we should aggregate and of the actions, if so, then we go ahead and 
        //Sort them into individual groups based on how we aggregate.
        
        $constrained_actions = array();
        $actions = new ArrayCollection();
        
        foreach ($collection as $key => $action)
        {
            if ($action instanceof TimelineInterface) {
                $action = $action->getAction();
            }
            
            if($response = $this->matchesConstraint($action))
            { 
                list($resolver_response, $constraint) = $response;
                
                $constrainted_component = $action->getComponent($resolver_response->getConstraintComponent())->getId();
                if(empty($constrained_actions[$constraint->getName()][$constrainted_component]["action"] )) {
                    $constrained_actions[$constraint->getName()][$constrainted_component]["action"] = $collection[$key]; //Always replace with the most up to date action
                }
                foreach($resolver_response->getCollectionComponents() as $component_name) //Loop in components...
                { 
                    $collected_component = $action->getComponent($component_name);
                    
                    //Thinking there is some error to force us to see if this mehod exists...
                    if(method_exists($collected_component, "getData"))
                    {
                        $collected_component = $collected_component->getData();
                    }
                    
                    if(empty($constrained_actions[$constraint->getName()][$constrainted_component]['componentsCollections'][$component_name])) 
                    { 
                        $constrained_actions[$constraint->getName()][$constrainted_component]['componentsCollections'][$component_name] = new ArrayCollection();
                    }
                    $constrained_actions[$constraint->getName()][$constrainted_component]['componentsCollections'][$component_name]->add($collected_component);
                }
            } else { 
                $actions->set($collection[$key]->getId(), $collection[$key]);
            }
            
        }
        
        return $this->mergeAggregatedActions($actions, $constrained_actions);
    }
    
    /**
     * Matches the action with the most relevant constraint if applicable.
     * 
     * @param ActionInterface $action
     * @return void
     */
    public function matchesConstraint($action)
    { 
        foreach ($this->constraints as $constraint) {
            $resolver_response = $constraint->shouldAggregate($action, new ConstraintResolver());
            
            if($resolver_response->getStatus() == ConstraintResolver::ACCEPTED_AGGREGATION)
            {   
                return array($resolver_response, $constraint);
            }
        }
        
        return false;
    }
    
    /**
     * @param array $actions
     * @param array $aggregated_actions
     * @return array
     */
    public function mergeAggregatedActions($actions, $aggregated_actions)
    { 
        //File actions down to include the most up to date version as the first keys 
        //And fills the components with componentcollections of each component)
        foreach($aggregated_actions as $constraint_name => $constraint)
        { 
            foreach($constraint as $c)
            {
                $action = $c['action'];
                $actions->add($action);
                
                if ($action instanceof TimelineInterface) {
                    $action = $action->getAction();
                }                
                
                foreach($c['componentsCollections'] as $name => $componentCollection)
                { 
                    $action->addComponentCollection($name, $componentCollection);
                }
            }
        }
        
        //The last step is to order these actions and then we send them back to Timeline.
        return $this->sortActions($actions);
    }

    /**
     * Sort constraints by priority.
     *
     * @return null
     */
    protected function sortConstraints()
    {
        usort($this->constraints, function($a, $b) {
            $a = $a->getPriority();
            $b = $b->getPriority();

            if ($a == $b) {
                return 0;
            }

            return $a < $b ? -1 : 1;
        });

        $this->sorted = true;
    }
}
