<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Extension;
use Bolt\Extension\Bolt\BoltForms\Twig\BoltFormsExtension;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * FileUpload class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BoltFormsExtensionTest extends AbstractBoltFormsUnitTest
{
    public function testConstructor()
    {
        $app = $this->getApp();
        $twigExt = new BoltFormsExtension($app);

        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Twig\BoltFormsExtension', $twigExt);
        $this->assertSame($twigExt->getName(), 'boltforms.extension');
    }

    public function testInitRuntime()
    {
        $app = $this->getApp();
        $twigExt = new BoltFormsExtension($app);
        $environment = new \Twig_Environment();
        $twigExt->initRuntime($environment);
    }

    public function testFunctions()
    {
        $app = $this->getApp();
        $twigExt = new BoltFormsExtension($app);
        $functions = $twigExt->getFunctions();

        $this->assertSame('boltforms', $functions[0]->getName());
        $this->assertSame('boltforms_uploads', $functions[1]->getName());

        $this->assertInstanceOf('\Twig_SimpleFunction', $functions[0]);
        $this->assertInstanceOf('\Twig_SimpleFunction', $functions[1]);
    }

    public function testTwigBoltForms()
    {
        $app = $this->getApp();
        $this->getExtension()->config['testing_form']['fields'] = $this->formFieldConfig();
        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('testing_form');
        $fields = $this->formFieldConfig();

        $boltforms->addFieldArray('testing_form', $fields);

        $html_pre = 'This is the HTML before';
        $html_post = 'The thing was sent';
        $data = array();
        $options = array();

        $twigExt = new BoltFormsExtension($app);

        // Request a non-exist form
        $html = $twigExt->twigBoltForms('koalas');
        $this->assertInstanceOf('\Twig_Markup', $html);
        $this->assertSame("<p><strong>BoltForms is missing the configuration for the form named 'koalas'!</strong></p>", (string) $html);

        // Now a real one
        $html = $twigExt->twigBoltForms('testing_form', $html_pre, $html_post, $data, $options);
        $this->assertInstanceOf('\Twig_Markup', $html);
        $this->assertRegExp('#This is the HTML before#', (string) $html);
    }

    public function testTwigBoltFormsPost()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields'] = $this->formFieldConfig();
        $this->getExtension()->config['testing_form']['feedback']['success'] = 'Well, that worked!';

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $boltforms->addFieldArray('testing_form', $fields);
        $app['boltforms'] = $boltforms;

        $html_pre = 'This is the HTML before';
        $html_post = 'The thing was sent';
        $data = array();
        $options = array();

        $twigExt = new BoltFormsExtension($app);

        $html = $twigExt->twigBoltForms('testing_form', $html_pre, $html_post, $data, $options);
        $this->assertInstanceOf('\Twig_Markup', $html);

        $this->assertRegExp('#<p class="boltform-message">Well, that worked!</p>#', (string) $html);
        $this->assertRegExp('#The thing was sent#', (string) $html);
    }

    public function testTwigBoltFormsPostReCaptcha()
    {
        $app = $this->getApp();
        $this->getExtension()->config['recaptcha']['enabled'] = true;
        $this->getExtension()->config['recaptcha']['private_key'] = 'abc123';
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields'] = $this->formFieldConfig();
        $this->getExtension()->config['testing_form']['feedback']['success'] = 'Well, that worked!';

        $reResponse = new \ReCaptcha\Response(true);
        $recaptcha = $this->getMock('\ReCaptcha\ReCaptcha', array('verify'), array('abc123'));
        $recaptcha
            ->expects($this->any())
            ->method('verify')
            ->will($this->returnValue($reResponse));
        $app['recaptcha'] = $recaptcha;

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $boltforms->addFieldArray('testing_form', $fields);
        $app['boltforms'] = $boltforms;

        $html_pre = 'This is the HTML before';
        $html_post = 'The thing was sent';
        $data = array();
        $options = array();

        $twigExt = new BoltFormsExtension($app);

        $html = $twigExt->twigBoltForms('testing_form', $html_pre, $html_post, $data, $options);
        $this->assertInstanceOf('\Twig_Markup', $html);

        $this->assertRegExp('#<p class="boltform-message">Well, that worked!</p>#', (string) $html);
        $this->assertRegExp('#The thing was sent#', (string) $html);
    }

    public function testTwigBoltFormsUploads()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $this->getExtension()->config['testing_form']['uploads']['subdirectory'] = 'testing_form';

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('testing_form');
        $fields = $this->formFieldConfig();

        $boltforms->addFieldArray('testing_form', $fields);
        $app['boltforms'] = $boltforms;

        $twigExt = new BoltFormsExtension($app);

        // Invalid directory
        $html = $twigExt->twigBoltFormsUploads('koala');
        $this->assertInstanceOf('\Twig_Markup', $html);
        $this->assertSame('<p><strong>Invalid upload directory</strong></p>', (string) $html);

        // Valid
        $tmpDir = sys_get_temp_dir() . '/testing_form';
        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';

        $fs = new Filesystem();
        if (!$fs->exists($tmpDir)) {
            $fs->mkdir($tmpDir);
        }
        $fs->copy($srcFile, "$tmpDir/bolt-logo.png", true);

        $html = $twigExt->twigBoltFormsUploads('testing_form');
        $this->assertInstanceOf('\Twig_Markup', $html);
        $this->assertRegExp('#<a href="/boltforms/download\?file=bolt-logo.png">bolt-logo.png</a>#', (string) $html);
    }
}
