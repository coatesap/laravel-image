<?php namespace Folklore\Image\Tests;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Folklore\Image\Exception\FormatException;
use Orchestra\Testbench\TestCase;

class ImageProxyTestCase extends TestCase
{
    protected $imagePath = '/image.jpg';
    protected $imageSmallPath = '/image_small.jpg';
    protected $imageSize;
    protected $imageSmallSize;

    public function setUp()
    {
        parent::setUp();
        
        $this->image = $this->app['image'];
        $this->imageSize = getimagesize(public_path().$this->imagePath);
        $this->imageSmallSize = getimagesize(public_path().$this->imageSmallPath);
    }

    public function tearDown()
    {
        $customPath = $this->app['path.public'].'/custom';
        $this->app['config']->set('image.write_path', $customPath);
        
        $this->image->deleteManipulated($this->imagePath);
        
        parent::tearDown();
    }

    public function testProxy()
    {
        $url = $this->image->url($this->imagePath, 300, 300, [
            'crop' => true
        ]);
        $response = $this->call('GET', $url);
        $this->assertTrue($response->isOk());
    }
    
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->instance('path.public', __DIR__.'/fixture');
        
        $app['config']->set('image.host', '/proxy');
        $app['config']->set('image.proxy', true);
        $app['config']->set('image.proxy_route', '/proxy/{image_path}');
        $app['config']->set('image.proxy_filesystem', 'image_testbench');
        $app['config']->set('image.proxy_cache_filesystem', 'image_testbench_cache');
        
        $app['config']->set('filesystems.default', 'image_testbench');
        $app['config']->set('filesystems.cloud', 'image_testbench');
        
        $app['config']->set('filesystems.disks.image_testbench', [
            'driver' => 'local',
            'root' => __DIR__.'/fixture'
        ]);
        
        $app['config']->set('filesystems.disks.image_testbench_cache', [
            'driver' => 'local',
            'root' => __DIR__.'/fixture/cache'
        ]);
    }

    protected function getPackageProviders($app)
    {
        return array('Folklore\Image\ImageServiceProvider');
    }

    protected function getPackageAliases($app)
    {
        return array(
            'Image' => 'Folklore\Image\Facades\Image'
        );
    }
}
