<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Symfony\Component\HttpFoundation\Request;

/**
 * BoltForms class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BoltFormsTest extends AbstractBoltFormsUnitTest
{
    public function testConstructor()
    {
        $app = $this->getApp();
        $boltforms = new BoltForms($app);

        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\BoltForms', $boltforms);
    }

    public function testMakeForm()
    {
        $app = $this->getApp();
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('contact');
    }

    public function testGetForm()
    {
        $app = $this->getApp();
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('contact');
        $form = $boltforms->getForm('contact');

        $this->assertInstanceOf('\Symfony\Component\Form\Form', $form);
    }

    public function testAddFields()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('contact');
        $fields = $this->formValues();

        $boltforms->addFieldArray('contact', $fields);

        $form = $boltforms->getForm('contact');

        foreach ($fields as $field => $values) {
            $this->assertTrue($form->has($field));
        }
    }

    public function testRenderForm()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('contact');
        $fields = $this->formValues();
        $boltforms->addFieldArray('contact', $fields);

        $html = $boltforms->renderForm('contact', null, array('recaptcha' => array('enabled' => true)));
        $this->assertInstanceOf('\Twig_Markup', $html);
        $html = (string) $html;

        $this->assertRegExp('#<link href="/extensions/vendor/bolt/boltforms/css/boltforms.css" rel="stylesheet" type="text/css" />#', $html);
        $this->assertRegExp('#<div class="boltform">#', $html);
        $this->assertRegExp('#var RecaptchaOptions =#', $html);
        $this->assertRegExp('#<form method="post" action="" name="" enctype="multipart/form-data">#', $html);
        $this->assertRegExp('#<ul class="boltform-error">#', $html);
        $this->assertRegExp('#<li class="boltform-errors"></li>#', $html);
        $this->assertRegExp('#<label for="form_message" class="required"></label>#', $html);
        $this->assertRegExp('#<script src="https://www.google.com/recaptcha/api.js\?hl=en-GB" async defer></script>#', $html);
        $this->assertRegExp('#<div class="g-recaptcha" data-sitekey=""></div>#', $html);
        $this->assertRegExp('#<div><button type="submit" id="contact_submit" name="contact\[submit\]">Submit</button></div>#', $html);
        $this->assertRegExp('#<label for="contact_name" class="required">Name</label>#', $html);
        $this->assertRegExp('#<input type="text" id="contact_name" name="contact\[name\]" required="required"    placeholder="Your name..." />#', $html);
        $this->assertRegExp('#<label for="contact_email" class="required">Email address</label>#', $html);
        $this->assertRegExp('#<input type="email" id="contact_email" name="contact\[email\]" required="required"    placeholder="Your email..." />#', $html);
        $this->assertRegExp('#<label for="contact_message" class="required">Your message</label>#', $html);
        $this->assertRegExp('#<textarea id="contact_message" name="contact\[message\]" required="required"    placeholder="Your message..." class="myclass"></textarea>#', $html);
        $this->assertRegExp('#<label for="contact_array_index">Should this test pass</label>#', $html);
        $this->assertRegExp('#<select id="contact_array_index" name="contact\[array_index\]"><option  value=""></option><option value="0">Yes</option><option value="1">No</option></select>#', $html);
        $this->assertRegExp('#<label for="contact_array_assoc">What is cutest</label>#', $html);
        $this->assertRegExp('#<select id="contact_array_assoc" name="contact\[array_assoc\]"><option  value=""></option><option value="kittens">Fluffy Kittens</option><option value="puppies">Cute Puppies</option></select>#', $html);
        $this->assertRegExp('#<label for="contact_lookup">Select a record</label>#', $html);
        $this->assertRegExp('#<select id="contact_lookup" name="contact\[lookup\]"><option  value=""></option></select>#', $html);
        $this->assertRegExp('#<input type="hidden" id="contact__token" name="contact\[_token\]" value=#', $html);
    }

    public function testProcessRequest()
    {
        $app = $this->getApp();
        $this->getExtension($app)->config['csrf'] = false;
        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');
        $fields = $this->formValues();
        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $boltforms->processRequest('contact', array('success' => true));

        $this->assertTrue($result);
    }

    public function testRedirectUrl()
    {
        $app = $this->getApp();
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['contact']['feedback']['redirect']['target'] = 'http://example.com';

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');
        $fields = $this->formValues();
        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $boltforms->processRequest('contact', array('success' => true));

        $this->assertTrue($result);
        $this->expectOutputRegex('#<meta http-equiv="refresh" content="1;url=http://example.com" />#');
    }

    public function testRedirectUrlQueryIndex()
    {
        $app = $this->getApp();
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['contact']['feedback']['redirect']['target'] = 'http://example.com';
        $this->getExtension($app)->config['contact']['feedback']['redirect']['query'] = array('name', 'email');

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');
        $fields = $this->formValues();
        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $boltforms->processRequest('contact', array('success' => true));

        $this->assertTrue($result);
        $this->expectOutputRegex('#<meta http-equiv="refresh" content="1;url=http://example.com\?name=Gawain\+Lynch&amp;email=gawain.lynch%40gmail.com" />#');
    }

    public function testRedirectUrlQueryAssoc()
    {
        $app = $this->getApp();
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['contact']['feedback']['redirect']['target'] = 'http://example.com';
        $this->getExtension($app)->config['contact']['feedback']['redirect']['query'] = array('person' => 'name', 'address' => 'email');

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');
        $fields = $this->formValues();
        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $boltforms->processRequest('contact', array('success' => true));

        $this->assertTrue($result);
        $this->expectOutputRegex('#<meta http-equiv="refresh" content="1;url=http://example.com\?person=Gawain\+Lynch&amp;address=gawain.lynch%40gmail.com" />#');
    }

    public function testRedirectRecord()
    {
        $app = $this->getApp();
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['contact']['feedback']['redirect']['target'] = 'page/koalas';
        $this->getExtension($app)->config['contact']['feedback']['redirect']['query'] = 'name';

        $content = $this->getMock('\Bolt\Content', array('link'), array($app));
        $content->expects($this->any())
            ->method('link')
            ->will($this->returnValue('/page/koalas'));
        $storage = $this->getMock('\Bolt\Storage', array('getContent'), array($app));
        $storage->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($content));
        $app['storage'] = $storage;

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');
        $fields = $this->formValues();
        unset($fields['array_index']);
        unset($fields['array_assoc']);
        unset($fields['lookup']);

        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $boltforms->processRequest('contact', array('success' => true));

        $this->assertTrue($result);
        $this->expectOutputRegex('#<meta http-equiv="refresh" content="1;url=/page/koalas\?name=Gawain\+Lynch" />#');
    }
}
