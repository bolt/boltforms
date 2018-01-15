<?php

namespace Example;

/**
 * Class to demonstrate static calls for option choice management.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class StaticChoice
{
    const ITEM_1 = 'item_1';
    const ITEM_2 = 'item_2';
    const ITEM_11 = 'item_11';
    const ITEM_12 = 'item_12';

    /**
     * Singleton constructor.
     */
    private function __construct()
    {
    }

    /**
     * Returns the string "value" for each choice.
     *
     * This is used in the value attribute in HTML and submitted in the POST/PUT
     * requests. You don't normally need to worry about this, but it might be
     * handy when processing an API request (since you can configure the value
     * that will be sent in the API request).
     *
     * @param string     $choicesValue Value in 'choices' array
     * @param string|int $key          Array key of value in 'choices' array
     * @param string     $index        The result of value()
     *
     * @return string
     */
    public static function choiceValue($choicesValue, $key = null, $index = null)
    {
        return 'your_prefix_' . $choicesValue;
    }

    /**
     * Label text that's shown to the user.
     *
     * @param string     $choicesValue Value in 'choices' array
     * @param string|int $key          Array key of value in 'choices' array
     * @param string     $index        The result of value()
     *
     * @return string
     */
    public static function choiceLabel($choicesValue, $key, $index)
    {
        $labels = [
            static::ITEM_1  => 'Item One',
            static::ITEM_2  => 'Item Two',
            // …and others
            static::ITEM_11 => 'Item Eleven',
            static::ITEM_12 => 'Item Twelve',
        ];

        if (isset($labels[$choicesValue])) {
            return $labels[$choicesValue];
        }

        return ucwords($choicesValue);
    }

    /**
     * Add additional HTML attributes to each choice.
     *
     * @param string     $choicesValue Value in 'choices' array
     * @param string|int $key          Array key of value in 'choices' array
     * @param string     $index        The result of value()
     *
     * @return array
     */
    public static function choiceAttr($choicesValue, $key, $index)
    {
        return [
            'class' => 'doing_' . strtolower($key),
        ];
    }

    /**
     * Choice values grouping.
     *
     * @param string     $choicesValue Value in 'choices' array
     * @param string|int $key          Array key of value in 'choices' array
     * @param string     $index        The result of value()
     *
     * @return string|null
     */
    public static function groupBy($choicesValue, $key, $index)
    {
        // Assign items into groups
        $itemGrouping = [
            static::ITEM_1  => 'group_a',
            static::ITEM_2  => 'group_a',
            // …and others
            static::ITEM_11 => 'group_b',
            static::ITEM_12 => 'group_b',
            // …and others
        ];
        // Assign groups labels
        $groupLabels = [
            'group_a' => 'Group Aye',
            'group_b' => 'Group Bee',
            // …and others
        ];

        if (isset($itemGrouping[$choicesValue])) {
            $groupLabelKey = $itemGrouping[$choicesValue];

            return $groupLabels[$groupLabelKey];
        }

        return null;
    }

    /**
     * Allows you to move certain choices to the top of your list with a visual
     * separator between them and the rest of the options.
     *
     * @param string     $choicesValue Value in 'choices' array
     * @param string|int $key          Array key of value in 'choices' array
     *
     * @return bool
     */
    public static function preferredChoices($choicesValue, $key)
    {
        if ($choicesValue === static::ITEM_12) {
            return true;
        }

        return false;
    }

    /**
     * Simple array of choices.
     *
     * @return array
     */
    public static function choices()
    {
        return [ 'item_1', 'item_2', 'item_11', 'item_12', 'koala_bear' ];
    }
}
