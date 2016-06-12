<?php
namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Asset\Snippet\Snippet;
use Bolt\Asset\Target;
use Bolt\Controller\Zone;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Exception\FormOptionException;
use Bolt\Extension\Bolt\BoltForms\Exception\InvalidConstraintException;
use Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsSubscriber;
use Bolt\Helpers\Arr;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormTypeInterface;

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
    /** @var array */
    private $config;
    /** @var array */
    private $forms;
    /** @var FormConfig[] */
    private $formsConfig;
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
        /** @var BoltFormsExtension $extension */
        $extension = $app['extensions']->get('Bolt/BoltForms');
        $this->config = $extension->getConfig();
    }

    /**
     * Initial form object constructor.
     *
     * @param string                   $formName
     * @param string|FormTypeInterface $type
     * @param mixed                    $data
     * @param array                    $options
     * @param array                    $formConfigOverride
     *
     * @throws FormOptionException
     */
    public function makeForm($formName, $type = FormType::class, $data = null, $options = [], array $formConfigOverride = null)
    {
        // Override config options as requested
        foreach ($formConfigOverride as $key => $value) {
            $this->config[$formName]['fields'][$key]['options'] = Arr::mergeRecursiveDistinct($this->config[$formName]['fields'][$key]['options'], $value);
        }
        $options['csrf_protection'] = $this->config['csrf'];
        $this->forms[$formName] = $this->app['form.factory']
            ->createNamedBuilder($formName, $type, $data, $options)
            ->addEventSubscriber(new BoltFormsSubscriber($this->app))
            ->getForm()
        ;

        /** @var FormConfig $formConfig */
        $formConfig = $this->getFormConfig($formName);
        foreach ($formConfig->getFields()->toArray() as $key => $field) {
            $field['options'] = !empty($field['options']) ? $field['options'] : [];

            if (!isset($field['type'])) {
                throw new FormOptionException(sprintf('Missing "type" value for "%s" field in "%s" form.', $key, $formName));
            }

            $this->addField($formName, $key, $field['type'], $field['options']);
        }
    }

    /**
     * Get the configuration object for a form.
     *
     * @param string $formName
     *
     * @return FormConfig
     */
    public function getFormConfig($formName)
    {
        if (!isset($this->forms[$formName])) {
            throw new Exception\UnknownFormException(sprintf('Unknown form requested: %s', $formName));
        }

        if (isset($this->formsConfig[$formName])) {
            return $this->formsConfig[$formName];
        }

        $this->formsConfig[$formName] = new FormConfig($formName, $this->config[$formName]);

        return $this->formsConfig[$formName];
    }

    /**
     * Add a field to the form.
     *
     * @param string $formName  Name of the form
     * @param string $fieldName
     * @param string $type
     * @param array  $options
     */
    public function addField($formName, $fieldName, $type, array $options)
    {
        $em = $this->app['storage'];
        $fieldOptions = new FieldOptions($formName, $fieldName, $type, $options, $em, $this->app['dispatcher']);

        try {
            $this->getForm($formName)->add($fieldName, $type, $fieldOptions->toArray());
        } catch (InvalidConstraintException $e) {
            $this->app['logger.system']->error($e->getMessage(), ['event' => 'extensions']);
        }
    }

    /**
     * Get a particular form
     *
     * @param string $formName
     *
     * @return Form
     */
    public function getForm($formName)
    {
        if (isset($this->forms[$formName])) {
            return $this->forms[$formName];
        }

        throw new Exception\UnknownFormException(sprintf('Unknown form requested: %s', $formName));
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
            $template = $this->config['templates']['form'];
        }

        // Add the form object for use in the template
        $context['form'] = $this->getForm($formName)->createView();

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
