<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\FileUpload;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileUpload class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class FileUploadTest extends AbstractBoltFormsUnitTest
{
    public function testConstructor()
    {
        $app = $this->getApp();
        $fileUpload = new UploadedFile(__FILE__, __FILE__, null, null, null, true);
        $boltforms = new FileUpload($app, 'testing_form', $fileUpload);

        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\FileUpload', $boltforms);
    }

    public function testBasicFunctions()
    {
        $app = $this->getApp();
        $fileUpload = new UploadedFile(__FILE__, __FILE__, null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);

        $this->assertSame(__FILE__, $upload->__toString());
        $this->assertSame(__FILE__, $upload->fullPath());
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\File\UploadedFile', $upload->getFile());
        $this->assertTrue($upload->isValid());
    }

    public function testRelativePathExceptionDisabled()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['enabled'] = false;

        $fileUpload = new UploadedFile(__FILE__, __FILE__, null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);

        $this->setExpectedException('\RuntimeException', 'The relative path is not valid when uploads are disabled!');
        $upload->relativePath();
    }

    public function testRelativePathExceptionEnabled()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['enabled'] = true;

        $fileUpload = new UploadedFile(__FILE__, __FILE__, null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);

        $this->setExpectedException('\RuntimeException', 'The relative path is not valid before the file is moved!');
        $upload->relativePath();
    }

    public function testRelativePath()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['enabled'] = true;
        $this->getExtension()->config['uploads']['base_directory'] = dirname(__FILE__);

        $fileUpload = new UploadedFile(__FILE__, __FILE__, null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);

        $path = $upload->relativePath();
        $this->assertSame($path, basename(__FILE__));
    }

    public function testMoveUploadedFileBaseDirectory()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');

        $fs = new Filesystem();
        $fs->copy($srcFile, $tmpFile, true);

        $fileUpload = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();

        $this->assertFileExists($upload->fullPath());
    }

    public function testMoveUploadedFileSubDirectory()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $this->getExtension()->config['testing_form']['uploads']['subdirectory'] = 'testing_form';

        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');

        $fs = new Filesystem();
        $fs->copy($srcFile, $tmpFile, true);

        $fileUpload = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();

        $this->assertFileExists($upload->fullPath());
    }

    public function testMoveUploadedFilePrefix()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $this->getExtension()->config['testing_form']['uploads']['subdirectory'] = 'testing_form';
        $this->getExtension()->config['uploads']['filename_handling'] = 'prefix';

        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');
        $tmpDir = sys_get_temp_dir() . '/testing_form';

        $fs = new Filesystem();
        if ($fs->exists($tmpDir)) {
            $fs->remove($tmpDir);
        }
        $fs->copy($srcFile, $tmpFile, true);

        $fileUpload = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();

        $this->assertFileExists($upload->fullPath());
        $this->assertRegExp('#\b(?:bolt-logo\.)[a-zA-Z0-9]{12}(?:\.png)\b#', basename($upload->fullPath()));
    }

    public function testMoveUploadedFileSufix()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $this->getExtension()->config['testing_form']['uploads']['subdirectory'] = 'testing_form';
        $this->getExtension()->config['uploads']['filename_handling'] = 'suffix';

        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');
        $tmpDir = sys_get_temp_dir() . '/testing_form';

        $fs = new Filesystem();
        if ($fs->exists($tmpDir)) {
            $fs->remove($tmpDir);
        }
        $fs->copy($srcFile, $tmpFile, true);

        $fileUpload = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();

        $this->assertFileExists($upload->fullPath());
        $this->assertRegExp('#\b(?:bolt-logo\.png\.)[a-zA-Z0-9]{12}\b#', basename($upload->fullPath()));
    }

    public function testMoveUploadedFileKeep()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $this->getExtension()->config['uploads']['filename_handling'] = 'keep';
        $this->getExtension()->config['testing_form']['uploads']['subdirectory'] = 'testing_form';

        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');
        $tmpDir = sys_get_temp_dir() . '/testing_form';

        $fs = new Filesystem();
        if ($fs->exists($tmpDir)) {
            $fs->remove($tmpDir);
        }
        $fs->copy($srcFile, $tmpFile, true);

        $fileUpload = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();

        $this->assertFileExists($upload->fullPath());
        $this->assertSame(basename($upload->fullPath()), basename($srcFile));
    }

    public function testMoveUploadedFileDuplicates()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $this->getExtension()->config['uploads']['filename_handling'] = 'keep';
        $this->getExtension()->config['testing_form']['uploads']['subdirectory'] = 'testing_form';

        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');
        $tmpDir = sys_get_temp_dir() . '/testing_form';

        $fs = new Filesystem();
        if ($fs->exists($tmpDir)) {
            $fs->remove($tmpDir);
        }
        $fs->copy($srcFile, $tmpFile, true);

        $fileUpload = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();

        $fs->copy($srcFile, $tmpFile, true);

        $fileUpload = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();

        $this->assertFileExists($upload->fullPath());
        $this->assertSame(basename($upload->fullPath()), 'bolt-logo(1).png');
    }

    public function testMoveUploadedFileException()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();

        $tmpFile = tempnam(sys_get_temp_dir(), 'koala');
        $e = new FileException();
        $fileUpload = $this->getMock(
            'Symfony\Component\HttpFoundation\File\UploadedFile',
            array('move'),
            array($tmpFile, 'bolt-logo.png', null, null, UPLOAD_ERR_OK, true)
        );
        $fileUpload
            ->method('move')
            ->will($this->throwException($e));
        $this->setExpectedException('\Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException');

        // Keep an eye on the logger
        $logger = $this->getMock('\Monolog\Logger', array('error', 'debug'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
            ->method('error');
        $app['logger.system'] = $logger;

        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();
    }

    public function testInvalidDirectoryException()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = '/nowhere';

        $tmpFile = tempnam(sys_get_temp_dir(), 'koala');
        $e = new FileException();
        $fileUpload = $this->getMock(
            'Symfony\Component\HttpFoundation\File\UploadedFile',
            array('move'),
            array($tmpFile, 'bolt-logo.png', null, null, UPLOAD_ERR_OK, true)
            );
        $fileUpload
        ->method('move')
        ->will($this->throwException($e));
        $this->setExpectedException('\Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException');

        // Keep an eye on the logger
        $logger = $this->getMock('\Monolog\Logger', array('error', 'debug'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
        ->method('error');
        $app['logger.system'] = $logger;

        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();
    }

    public function testUnwritableDirectoryException()
    {
        $app = $this->getApp();
        $this->getExtension()->config['uploads']['base_directory'] = '/proc';

        $tmpFile = tempnam(sys_get_temp_dir(), 'koala');
        $e = new FileException();
        $fileUpload = $this->getMock(
            'Symfony\Component\HttpFoundation\File\UploadedFile',
            array('move'),
            array($tmpFile, 'bolt-logo.png', null, null, UPLOAD_ERR_OK, true)
        );
        $fileUpload
            ->method('move')
            ->will($this->throwException($e));
        $this->setExpectedException('\Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException');

        // Keep an eye on the logger
        $logger = $this->getMock('\Monolog\Logger', array('error', 'debug'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
            ->method('error');
        $app['logger.system'] = $logger;

        $upload = new FileUpload($app, 'testing_form', $fileUpload);
        $upload->move();
    }
}
