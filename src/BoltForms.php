<?php
namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Asset\Snippet\Snippet;
use Bolt\Asset\Target;
use Bolt\Controller\Zone;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Exception\FormOptionException;
use Bolt\Extension\Bolt\BoltForms\Exception\InvalidConstraintException;
use Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsSubscriber;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Core API functions for BoltForms
 *
 * Copyright (c) 2014-2016 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class BoltForms
{
    /** @var Application */
    private $app;
    /** @var ParameterBag */
    private $config;
    /** @var array */
    private $forms;
    /** @var boolean */
    private $jsQueued;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $app['boltforms.config'];
    }

    /**
     * Initial form object constructor.
     *
     * @param string                   $formName
     * @param string|FormTypeInterface $type
     * @param mixed                    $data
     * @param array                    $options
     *
     * @throws FormOptionException
     */
    public function makeForm($formName, $type = FormType::class, $data = null, $options = [])
    {
        $options['csrf_protection'] = $this->config->isCsrf();
        /** @var Form $form */
        $form = $this->app['form.factory']
            ->createNamedBuilder($formName, $type, $data, $options)
            ->addEventSubscriber(new BoltFormsSubscriber($this->app))
            ->getForm()
        ;
        $this->forms[$formName]['form'] = $form;

        $em = $this->app['storage'];
        $dispatcher = $this->app['dispatcher'];
        $this->config->resolveForm($formName, $em, $dispatcher);

        /** @var FormConfig $formConfig */
        $formConfig = $this->config->getForm($formName);
        foreach ($formConfig->getFields()->toArray() as $key => $field) {
            $field['options'] = !empty($field['options']) ? $field['options'] : [];

            if (!isset($field['type'])) {
                throw new FormOptionException(sprintf('Missing "type" value for "%s" field in "%s" form.', $key, $formName));
            }

            $this->addField($formName, $key, $field['type'], $field['options']);
        }
    }

    /**
     * Add a field to the form.
     *
     * @param string             $formName  Name of the form
     * @param string             $fieldName
     * @param string             $type
     * @param FieldOptions|array $options
     */
    public function addField($formName, $fieldName, $type, $options)
    {
        if (is_array($options)) {
            $em = $this->app['storage'];
            $dispatcher = $this->app['dispatcher'];
            $options = new FieldOptions($formName, $fieldName, $type, $options, $em, $dispatcher);
        }

        try {
            $this->get($formName)->add($fieldName, $type, $options->toArray());
        } catch (InvalidConstraintException $e) {
            $this->app['logger.system']->error($e->getMessage(), ['event' => 'extensions']);
        }
    }

    /**
     * Get a particular form.
     *
     * @param string $formName
     *
     * @return Form
     */
    public function get($formName)
    {
        if ($this->has($formName)) {
            return $this->forms[$formName]['form'];
        }

        throw new Exception\UnknownFormException(sprintf('Unknown form requested: %s', $formName));
    }

    /**
     * @deprecated Deprecated since 3.1, to be removed in 4.0.
     */
    public function getForm($formName)
    {
        return $this->get($formName);
    }

    /**
     * Check is a form object exists.
     *
     * @param string $formName
     *
     * @return bool
     */
    public function has($formName)
    {
        return isset($this->forms[$formName]['form']);
    }

    /**
     * Set a form on the forms array.
     *
     * @param Form  $form
     * @param mixed $meta
     */
    public function set(Form $form, $meta = null)
    {
        $formName = $form->getName();
        $this->forms[$formName] = [
            'form' => $form,
            'meta' => $meta,
        ];
    }

    /**
     * Add an array of fields to the form.
     *
     * @param string $formName Name of the form
     * @param array  $fields   Associative array keyed on field name => array('type' => '', 'options => array())
     *
     * @return void
     */
    public function addFieldArray($formName, array $fields)
    {
        foreach ($fields as $fieldName => $field) {
            $field['options'] = empty($field['options']) ? [] : $field['options'];
            $this->addField($formName, $fieldName, $field['type'], $field['options']);
        }
    }

    /**
     * Render our form into HTML
     *
     * @param string $formName Name of the form
     * @param string $template A Twig template file name in Twig's path
     * @param array  $context  Associative array of key/value pairs to pass to Twig's render of $template
     * @param bool   $loadAjax Load JavaScript for AJAX form handling
     *
     * @return \Twig_Markup
     */
    public function renderForm($formName, $template = '', array $context = [], $loadAjax = false)
    {
        if (empty($template)) {
            $template = $this->config->getTemplates()->get('form');
        }

        // Add the form object for use in the template
        $context['form'] = $this->get($formName)->createView();

        // Add JavaScript if doing the AJAX dance.
        if ($loadAjax) {
            $this->queueJavaScript($context);
        }

        // Pray and do the render
        $html = $this->app['twig']->render($template, $context);

        $sessionKey = sprintf('boltforms_submit_%s', $formName);
        $this->app['session']->remove($sessionKey);

        // Return the result
        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     * Conditionally add form handling JavaScript to the end of the HTML body.
     *
     * @param array $context
     */
    private function queueJavaScript(array $context)
    {
        if ($this->jsQueued) {
            return;
        }

        $snippet = new Snippet();
        $snippet->setCallback(
                function () use ($context) {
                    return $this->app['twig']->render('_boltforms_js.twig', $context);
                }
            )
            ->setLocation(Target::END_OF_BODY)
            ->setZone(Zone::FRONTEND)
        ;

        $this->app['asset.queue.snippet']->add($snippet);
        $this->jsQueued = true;
    }
}
