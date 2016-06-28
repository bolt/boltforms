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
    protected $parameters;

    public function offsetSet($name, $value)
    {
        $this->parameters[$name] = $value;
    }
    public function offsetExists($name)
    {
        return isset($this->parameters[$name]);
    }
    public function offsetUnset($name)
    {
        unset($this->parameters[$name]);
    }
    public function offsetGet($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }
}
