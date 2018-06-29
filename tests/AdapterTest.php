<?php

namespace Freyo\Flysystem\QcloudCOSv5\Tests;

use Freyo\Flysystem\QcloudCOSv5\Adapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use PHPUnit\Framework\TestCase;
use Qcloud\Cos\Client;

class AdapterTest extends TestCase
{
    public function Provider()
    {
        $config = [
            'region'          => getenv('COSV5_REGION'),
            'credentials'     => [
                'appId'     => getenv('COSV5_APP_ID'),
                'secretId'  => getenv('COSV5_SECRET_ID'),
                'secretKey' => getenv('COSV5_SECRET_KEY'),
            ],
            'timeout'         => 10,
            'connect_timeout' => 10,
            'bucket'          => getenv('COSV5_BUCKET'),
            'cdn'             => getenv('COSV5_CDN'),
        ];

        $cosApi = new Client($config);

        $adapter = new Adapter($cosApi, $config);

        return [
            [$adapter, $config],
        ];
    }

    /**
     * @dataProvider Provider
     */
    public function testWrite(AdapterInterface $adapter)
    {
        $this->assertTrue((bool) $adapter->write('foo/foo.md', 'content', new Config()));
    }

    /**
     * @dataProvider Provider
     */
    public function testWriteStream(AdapterInterface $adapter)
    {
        $temp = tmpfile();
        fwrite($temp, 'writing to tempfile');
        $this->assertTrue((bool) $adapter->writeStream('foo/bar.md', $temp, new Config()));
        fclose($temp);
    }

    /**
     * @dataProvider Provider
     */
    public function testUpdate(AdapterInterface $adapter)
    {
        $this->assertTrue((bool) $adapter->update('foo/bar.md', uniqid(), new Config()));
    }

    /**
     * @dataProvider Provider
     */
    public function testUpdateStream(AdapterInterface $adapter)
    {
        $temp = tmpfile();
        fwrite($temp, 'writing to tempfile');
        $this->assertTrue((bool) $adapter->updateStream('foo/bar.md', $temp, new Config()));
        fclose($temp);
    }

    /**
     * @dataProvider Provider
     */
    public function testRename(AdapterInterface $adapter)
    {
        $this->assertTrue($adapter->rename('foo/foo.md', '/foo/rename.md'));
    }

    /**
     * @dataProvider Provider
     */
    public function testCopy(AdapterInterface $adapter)
    {
        $this->assertTrue($adapter->copy('foo/bar.md', '/foo/copy.md'));
    }

    /**
     * @dataProvider Provider
     */
    public function testDelete(AdapterInterface $adapter)
    {
        $this->assertTrue($adapter->delete('foo/rename.md'));
    }

    /**
     * @dataProvider Provider
     */
    public function testCreateDir(AdapterInterface $adapter)
    {
        $this->assertTrue((bool) $adapter->createDir('bar', new Config()));
    }

    /**
     * @dataProvider Provider
     */
    public function testDeleteDir(AdapterInterface $adapter)
    {
        $this->assertTrue($adapter->deleteDir('bar'));
    }

    /**
     * @dataProvider Provider
     */
    public function testSetVisibility(AdapterInterface $adapter)
    {
        $this->assertTrue($adapter->setVisibility('foo/copy.md', 'private'));
    }

    /**
     * @dataProvider Provider
     */
    public function testHas(AdapterInterface $adapter)
    {
        $this->assertTrue($adapter->has('foo/bar.md'));
    }

    /**
     * @dataProvider Provider
     */
    public function testRead(AdapterInterface $adapter)
    {
        $this->assertArrayHasKey('contents', $adapter->read('foo/bar.md'));
    }

    /**
     * @dataProvider Provider
     *
     * @deprecated
     */
    public function testGetUrl(AdapterInterface $adapter, $config)
    {
        $this->assertContains(
            '/foo/bar.md',
            $adapter->getUrl('foo/bar.md')
        );
    }

    /**
     * @dataProvider Provider
     *
     * @deprecated
     */
    public function testReadStream(AdapterInterface $adapter)
    {
        $this->assertSame(
            stream_get_contents(fopen($adapter->getUrl('foo/bar.md'), 'rb', false)),
            stream_get_contents($adapter->readStream('foo/bar.md')['stream'])
        );
    }

    /**
     * @dataProvider Provider
     */
    public function testListContents(AdapterInterface $adapter)
    {
        $this->assertArrayHasKey('Contents', $adapter->listContents('foo'));
    }

    /**
     * @dataProvider Provider
     */
    public function testGetMetadata(AdapterInterface $adapter)
    {
        $this->assertArrayHasKey('ContentLength', $adapter->getMetadata('foo/bar.md'));
    }

    /**
     * @dataProvider Provider
     */
    public function testGetSize(AdapterInterface $adapter)
    {
        $this->assertArrayHasKey('size', $adapter->getSize('foo/bar.md'));
    }

    /**
     * @dataProvider Provider
     */
    public function testGetMimetype(AdapterInterface $adapter)
    {
        $this->assertNotSame(['mimetype' => ''], $adapter->getMimetype('foo/bar.md'));
    }

    /**
     * @dataProvider Provider
     */
    public function testGetTimestamp(AdapterInterface $adapter)
    {
        $this->assertNotSame(['timestamp' => 0], $adapter->getTimestamp('foo/bar.md'));
    }

    /**
     * @dataProvider Provider
     */
    public function testGetVisibility(AdapterInterface $adapter)
    {
        $this->assertSame(['visibility' => 'private'], $adapter->getVisibility('foo/copy.md'));
    }
}
