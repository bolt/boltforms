<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractCascadingBag extends ParameterBag
{
    /** @var Config */
    protected $rootConfig;

    /**
     * Constructor.
     *
     * @param array       $parameters
     * @param Config|null $rootConfig
     */
    public function __construct(array $parameters = [], Config $rootConfig = null)
    {
        parent::__construct($parameters);
        $this->rootConfig = $rootConfig;
    }

    /**
     * If there is a root configuration supplied, return its value as a default.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function getHierarchicalValue($key)
    {
        if ($this->rootConfig === null) {
            return $this->get($key);
        }

        return $this->get($key) ?: $this->getRootSection()->get($key);
    }

    /**
     * @return ParameterBag
     */
    abstract protected function getRootSection();
}
