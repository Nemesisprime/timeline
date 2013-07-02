<?php

namespace Spy\Timeline\Filter;

use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Model\TimelineInterface;

use Spy\Timeline\Aggregation\Aggregator;
use Spy\Timeline\Aggregation\ActionAggregationInterface;

/**
 * Defined on "Resources/doc/filter.markdown"
 * This filter connects the aggregation service to the global filter chain. 
 * We don't want to reinvent the wheel.
 *
 * @uses AbstractFilter
 * @uses FilterInterface
 * @author Daniel Griffin <dan@contagious.nu>
 */
class AggregationFilter extends AbstractFilter implements FilterInterface
{
    
    /**
     * @var Aggregator
     */
    protected $aggregator;
    

    public function __construct(Aggregator $aggregator) 
    { 
        $this->aggregator = $aggregator;
    }
    
    /**
     * getAggregator function.
     * 
     * @return Aggregation
     */
    public function getAggregator()
    { 
        return $this->aggregator;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($collection)
    {
        
    
        return $this->getAggregator()->aggregate($collection);
    }
}
