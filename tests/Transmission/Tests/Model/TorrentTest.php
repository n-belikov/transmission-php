<?php
namespace Transmission\Tests\Model;

use Transmission\Model\Torrent;
use Transmission\Util\PropertyMapper;
use Symfony\Component\PropertyAccess\PropertyAccess;

class TorrentTest extends \PHPUnit\Framework\TestCase
{
    protected $torrent;

    /**
     * @test
     */
    public function shouldImplementModelInterface()
    {
        $this->assertInstanceOf('Transmission\Model\ModelInterface', $this->getTorrent());
    }

    /**
     * @test
     */
    public function shouldHaveNonEmptyMapping()
    {
        $this->assertNotEmpty($this->getTorrent()->getMapping());
    }

    /**
     * @test
     */
    public function shouldBeCreatedFromMapping()
    {
        $source = (object) array(
            'id' => 1,
            'eta' => 10,
            'sizeWhenDone' => 10000,
            'name' => 'foo',
            'hashString' => 'bar',
            'status' => 0,
            'isFinished' => false,
            'rateUpload' => 10,
            'rateDownload' => 100,
            'downloadDir' => '/home/foo',
            'downloadedEver' => 1024000000,
            'uploadedEver' => 1024000000000, // 1 Tb
            'files' => array(
                (object) array()
            ),
            'peers' => array(
                (object) array(),
                (object) array()
            ),
            'peersConnected' => 10,
            'startDate' => 1427583510,
            'trackers' => array(
                (object) array(),
                (object) array(),
                (object) array()
            ),
            'trackerStats' => array(
                (object) array(),
                (object) array(),
                (object) array()
            ),
            'activityDate' => 1427583511,
            'addedDate' =>1427583512,
            'corruptEver' => 123,
            'desiredAvailable' => 1234,
            'doneDate' => 14275835103,
            'editDate' => 14275835104,
            'error' => 1,
            'errorString' => 'Some error message',
            'etaIdle' => -1,
            'haveUnchecked' => 12,
            'haveValid' => 123,
            'isStalled' => true,
            'leftUntilDone' => 1234,
            'manualAnnounceTime' => '1427583515',
            'metadataPercentComplete' => 1.0,
            'peersGettingFromUs' => 2,
            'peersSendingToUs' => 3,
            'queuePosition' => 4,
            'recheckProgress' => 0.0,
            'secondsDownloading' => 543,
            'secondsSeeding' => 123456,
            'webseedsSendingToUs' => 0
        );

        PropertyMapper::map($this->getTorrent(), $source);

        $this->assertEquals(1, $this->getTorrent()->getId());
        $this->assertEquals(10, $this->getTorrent()->getEta());
        $this->assertEquals(10000, $this->getTorrent()->getSize());
        $this->assertEquals('foo', $this->getTorrent()->getName());
        $this->assertEquals('bar', $this->getTorrent()->getHash());
        $this->assertEquals(0, $this->getTorrent()->getStatus());
        $this->assertFalse($this->getTorrent()->isFinished());
        $this->assertEquals(10, $this->getTorrent()->getUploadRate());
        $this->assertEquals(100, $this->getTorrent()->getDownloadRate());
        $this->assertEquals('/home/foo', $this->getTorrent()->getDownloadDir());
        $this->assertEquals(1024000000, $this->getTorrent()->getDownloadedEver());
        $this->assertEquals(1024000000000, $this->getTorrent()->getUploadedEver());
        $this->assertCount(1, $this->getTorrent()->getFiles());
        $this->assertCount(2, $this->getTorrent()->getPeers());
        $this->assertCount(3, $this->getTorrent()->getTrackers());
        $this->assertEquals(1427583511, $this->getTorrent()->getActivityDate());
        $this->assertEquals(1427583512, $this->getTorrent()->getAddedDate());
        $this->assertEquals(123, $this->getTorrent()->getCorruptEver());
        $this->assertEquals(1234, $this->getTorrent()->getDesiredAvailable());
        $this->assertEquals(14275835103, $this->getTorrent()->getDoneDate());
        $this->assertEquals(14275835104, $this->getTorrent()->getEditDate());
        $this->assertEquals(1, $this->getTorrent()->getErrorType());
        $this->assertEquals('Some error message', $this->getTorrent()->getErrorString());
        $this->assertEquals(-1, $this->getTorrent()->getEtaIdle());
        $this->assertEquals(12, $this->getTorrent()->getHaveUnchecked());
        $this->assertEquals(123, $this->getTorrent()->getHaveValid());
        $this->assertEquals(true, $this->getTorrent()->isStalled());
        $this->assertEquals(1234, $this->getTorrent()->getLeftUntilDone());
        $this->assertEquals(1427583515, $this->getTorrent()->getManualAnnounceTime());
        $this->assertEquals(1.0, $this->getTorrent()->getMetadataPercentComplete());
        $this->assertEquals(2, $this->getTorrent()->getPeersGettingFromUs());
        $this->assertEquals(3, $this->getTorrent()->getPeersSendingToUs());
        $this->assertEquals(4, $this->getTorrent()->getQueuePosition());
        $this->assertEquals(0.0, $this->getTorrent()->getRecheckProgress());
        $this->assertEquals(543, $this->getTorrent()->getSecondsDownloading());
        $this->assertEquals(123456, $this->getTorrent()->getSecondsSeeding());
        $this->assertEquals(0, $this->getTorrent()->getWebseedsSendingToUs());
    }

    /**
     * @test
     */
    public function shouldBeDoneWhenFinishedFlagIsSet()
    {
        $this->getTorrent()->setFinished(true);

        $this->assertTrue($this->getTorrent()->isFinished());
    }

    /**
     * @test
     */
    public function shouldBeDoneWhenPercentDoneIs100Percent()
    {
        $this->getTorrent()->setPercentDone(1);

        $this->assertTrue($this->getTorrent()->isFinished());
    }

    /**
     * @test
     * @dataProvider statusProvider
     */
    public function shouldHaveConvenienceMethods($status, $method)
    {
        $methods = array('stopped', 'checking', 'downloading', 'seeding');
        $accessor = PropertyAccess::createPropertyAccessor();
        $this->getTorrent()->setStatus($status);

        $methods = array_filter($methods, function ($value) use ($method) {
            return $method !== $value;
        });

        $this->assertTrue($accessor->getValue($this->getTorrent(), $method));
        foreach ($methods as $m) {
            $this->assertFalse($accessor->getValue($this->getTorrent(), $m), $m);
        }
    }

    public function statusProvider()
    {
        return array(
            array(0, 'stopped'),
            array(1, 'checking'),
            array(2, 'checking'),
            array(3, 'downloading'),
            array(4, 'downloading'),
            array(5, 'seeding'),
            array(6, 'seeding')
        );
    }

    public function setup(): void
    {
        $this->torrent = new Torrent();
    }

    /**
     * @return Torrent
     */
    public function getTorrent()
    {
        return $this->torrent;
    }
}
