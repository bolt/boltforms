<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

/**
 * Legacy ArrayAccess trait.
 *
 * @deprecated Since 3.1 and will be removed in 4.0
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
trait LegacyArrayAccessTrait
{
    public function offsetSet($name, $value)
    {
        $this->getParameters()[$name] = $value;
    }
    public function offsetExists($name)
    {
        return isset($this->getParameters()[$name]);
    }
    public function offsetUnset($name)
    {
        unset($this->getParameters()[$name]);
    }
    public function offsetGet($name)
    {
        return isset($this->getParameters()[$name]) ? $this->getParameters()[$name] : null;
    }

    abstract protected function getParameters();
}
