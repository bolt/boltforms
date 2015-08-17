<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        $boltforms->makeForm('testing_form');
    }

    public function testGetForm()
    {
        $app = $this->getApp();
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('testing_form');
        $form = $boltforms->getForm('testing_form');

        $this->assertInstanceOf('\Symfony\Component\Form\Form', $form);
    }

    public function testAddFields()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('testing_form');
        $fields = $this->formFieldConfig();

        $boltforms->addFieldArray('testing_form', $fields);

        $form = $boltforms->getForm('testing_form');

        foreach ($fields as $field => $values) {
            $this->assertTrue($form->has($field));
        }
    }

    public function testRenderForm()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $boltforms->addFieldArray('testing_form', $fields);

        $html = $boltforms->renderForm('testing_form', null, array('recaptcha' => array('enabled' => true)));
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
        $this->assertRegExp('#<button type="submit" id="testing_form_submit" name="testing_form\[submit\]">Submit</button>#', $html);
        $this->assertRegExp('#<label for="testing_form_name" class="required">Name</label>#', $html);
        $this->assertRegExp('#<input type="text" id="testing_form_name" name="testing_form\[name\]" required="required"    placeholder="Your name..." />#', $html);
        $this->assertRegExp('#<label for="testing_form_email" class="required">Email address</label>#', $html);
        $this->assertRegExp('#<input type="email" id="testing_form_email" name="testing_form\[email\]" required="required"    placeholder="Your email..." />#', $html);
        $this->assertRegExp('#<label for="testing_form_message" class="required">Your message</label>#', $html);
        $this->assertRegExp('#<textarea id="testing_form_message" name="testing_form\[message\]" required="required"    placeholder="Your message..." class="myclass"></textarea>#', $html);
        $this->assertRegExp('#<label for="testing_form_array_index">Should this test pass</label>#', $html);
        $this->assertRegExp('#<select id="testing_form_array_index" name="testing_form\[array_index\]"><option  value=""></option><option value="0">Yes</option><option value="1">No</option></select>#', $html);
        $this->assertRegExp('#<label for="testing_form_array_assoc">What is cutest</label>#', $html);
        $this->assertRegExp('#<select id="testing_form_array_assoc" name="testing_form\[array_assoc\]"><option  value=""></option><option value="kittens">Fluffy Kittens</option><option value="puppies">Cute Puppies</option></select>#', $html);
        $this->assertRegExp('#<label for="testing_form_lookup">Select a record</label>#', $html);
        $this->assertRegExp('#<select id="testing_form_lookup" name="testing_form\[lookup\]"><option  value=""></option></select>#', $html);
        $this->assertRegExp('#<input type="hidden" id="testing_form__token" name="testing_form\[_token\]" value=#', $html);
    }

    public function testRenderFormDebugOn()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $html = $app['boltforms']->renderForm('testing_form', null, array('debug' => true));
        $this->assertInstanceOf('\Twig_Markup', $html);
        $html = (string) $html;

        $this->assertRegExp('#\[Debug\] Notification debug mode enabled!#', $html);
    }

    public function testRenderFormDebugOff()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $html = $app['boltforms']->renderForm('testing_form', null, array('debug' => false));
        $this->assertInstanceOf('\Twig_Markup', $html);
        $html = (string) $html;

        $this->assertNotRegExp('#\[Debug\] Notification debug mode enabled!#', $html);
    }

    public function testProcessRequest()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testProcessRequestDateTime()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testProcessRequestFileUploadInvalid()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');

        $fs = new Filesystem();
        $fs->copy($srcFile, $tmpFile, true);

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $parameters['testing_form']['file'] = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, false);
        $app['request'] = Request::create('/', 'POST', $parameters);

        $this->setExpectedException('Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException');

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertFalse($result);
    }

    public function testProcessRequestFileUploadDisabled()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['uploads']['enabled'] = false;
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');

        $fs = new Filesystem();
        $fs->copy($srcFile, $tmpFile, true);

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $parameters['testing_form']['file'] = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testProcessRequestFileUploadEnabled()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['uploads']['enabled'] = true;
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');

        $fs = new Filesystem();
        $fs->copy($srcFile, $tmpFile, true);

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $parameters['testing_form']['file'] = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testRedirectUrl()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['feedback']['redirect']['target'] = 'http://example.com';

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
        $this->expectOutputRegex('#<meta http-equiv="refresh" content="1;url=http://example.com" />#');
    }

    public function testRedirectUrlQueryIndex()
    {
        $app = $this->getApp();
        $fields = $this->formFieldConfig();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['feedback']['redirect']['target'] = 'http://example.com';
        $this->getExtension()->config['testing_form']['feedback']['redirect']['query'] = array('name', 'email');
        $this->getExtension()->config['testing_form']['fields'] = $fields;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
        $this->expectOutputRegex('#<meta http-equiv="refresh" content="1;url=http://example.com\?name=Gawain\+Lynch&amp;email=gawain.lynch%40gmail.com" />#');
    }

    public function testRedirectUrlQueryAssoc()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['feedback']['redirect']['target'] = 'http://example.com';
        $this->getExtension()->config['testing_form']['feedback']['redirect']['query'] = array('person' => 'name', 'address' => 'email');

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
        $this->expectOutputRegex('#<meta http-equiv="refresh" content="1;url=http://example.com\?person=Gawain\+Lynch&amp;address=gawain.lynch%40gmail.com" />#');
    }

    public function testRedirectRecord()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['feedback']['redirect']['target'] = 'page/koalas';
        $this->getExtension()->config['testing_form']['feedback']['redirect']['query'] = 'name';

        $matcher = $this->getMockBuilder('\Bolt\Routing\UrlMatcher')
            ->disableOriginalConstructor()
            ->setMethods(array('match'))
            ->getMock();
        $matcher->expects($this->any())
            ->method('match')
            ->will($this->returnValue('/page/koalas'));
        $app['url_matcher'] = $matcher;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        unset($fields['array_index']);
        unset($fields['array_assoc']);
        unset($fields['lookup']);

        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
        $this->expectOutputRegex('#<meta http-equiv="refresh" content="1;url=/page/koalas\?name=Gawain\+Lynch" />#');
    }

    public function testInvalidConstraint()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $fields['email']['options']['constraints'] = 'koala';

        // Keep an eye on the logger
        $logger = $this->getMock('\Monolog\Logger', array('error', 'debug'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
            ->method('error');
        $app['logger.system'] = $logger;

        $app['boltforms']->addFieldArray('testing_form', $fields);
    }
}
