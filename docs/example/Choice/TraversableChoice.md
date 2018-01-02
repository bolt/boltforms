TraversableChoice
=================

```php
<?php

namespace Example;

/**
 * Class to demonstrate choice value selection.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class TraversableChoice implements \Iterator
{
    /** @var string */
    private $name;
    /** @var int */
    private $position = 0;
    /** @var array */
    private $lists = [
        'group_a' => [
            'item_1', 'item_2', 'item_3', 'item_4', 'item_5'
        ],
        'group_b' => [
            'item_11', 'item_12', 'item_13', 'item_14', 'item_15'
        ],
    ];

    /**
     * Constructor.
     *
     * @param string $name The "group" key of the $lists property array
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $listName = $this->name;
        $position = $this->position;

        return $this->lists[$listName][$position];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $listName = $this->name;
        $position = $this->position;

        return isset($this->lists[$listName][$position]);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
```
