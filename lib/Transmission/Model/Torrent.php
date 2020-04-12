<?php
namespace Transmission\Model;

use Transmission\Util\PropertyMapper;

/**
 * @author Ramon Kleiss <ramon@cubilon.nl>
 */
class Torrent extends AbstractModel
{
    // Possible Values of errorType
    // Everything's fine
    const ERROR_TYPE_OK = 0;
    // When we announced to the tracker, we got a warning in the response
    const ERROR_TYPE_WARNING = 1;
    // When we announced to the tracker, we got an error in the response
    const ERROR_TYPE_ERROR = 2;
    // Trouble local to the torrent client, such as disk full or permissions error
    const ERROR_TYPE_LOCAL = 3;

    // Sentinel values for eta and etaIdle
    const ETA_NOT_AVAILABLE = -1;
    const ETA_UNKNOWN = -2;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $eta;

    /**
     * @var integer
     */
    protected $size;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var boolean
     */
    protected $finished;

    /**
     * @var integer
     */
    protected $startDate;
    
    /**
     * @var integer
     */
    protected $uploadRate;

    /**
     * @var integer
     */
    protected $downloadRate;

    /**
     * @var integer
     */
    protected $peersConnected;

    /**
     * @var double
     */
    protected $percentDone;

    /**
     * @var array
     */
    protected $files = array();

    /**
     * @var array
     */
    protected $peers = array();

    /**
     * @var array
     */
    protected $trackers = array();

    /**
     * @var array
     */
    protected $trackerStats = array();

    /**
     * @var double
     */
    protected $uploadRatio;
    
    /**
     * @var string
     */
    protected $downloadDir;

    /**
     * @var integer
     */
    protected $downloadedEver;

    /**
     * @var integer
     */
    protected $uploadedEver;

    /**
     * @var integer
     */
    protected $activityDate;

    /**
     * @var integer
     */
    protected $addedDate;

    /**
     * @var float
     */
    protected $corruptEver;

    /**
     * @var float
     */
    protected $desiredAvailable;

    /**
     * @var integer
     */
    protected $doneDate;

    /**
     * @var integer
     */
    protected $editDate;

    /**
     * @var integer
     */
    protected $errorType;

    /**
     * @var string
     */
    protected $errorString;

    /**
     * @var integer
     */
    protected $etaIdle;

    /**
     * @var float
     */
    protected $haveUnchecked;

    /**
     * @var float
     */
    protected $haveValid;

    /**
     * @var bool
     */
    protected $isStalled;

    /**
     * @var float
     */
    protected $leftUntilDone;

    /**
     * @var integer
     */
    protected $manualAnnounceTime;

    /**
     * @var float
     */
    protected $metadataPercentComplete;

    /**
     * @var integer
     */
    protected $peersGettingFromUs;

    /**
     * @var integer
     */
    protected $peersSendingToUs;

    /**
     * @var integer
     */
    protected $queuePosition;

    /**
     * @var float
     */
    protected $recheckProgress;

    /**
     * @var integer
     */
    protected $secondsDownloading;

    /**
     * @var integer
     */
    protected $secondsSeeding;

    /**
     * @var integer
     */
    protected $webseedsSendingToUs;

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = (integer) $id;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $eta
     */
    public function setEta($eta)
    {
        $this->eta = (integer) $eta;
    }

    /**
     * @return integer
     */
    public function getEta()
    {
        return $this->eta;
    }

    /**
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = (integer) $size;
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = (string) $hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param integer|Status $status
     */
    public function setStatus($status)
    {
        $this->status = new Status($status);
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status->getValue();
    }

    /**
     * @param boolean $finished
     */
    public function setFinished($finished)
    {
        $this->finished = (boolean) $finished;
    }

    /**
     * @return boolean
     */
    public function isFinished()
    {
        return ($this->finished || (int) $this->getPercentDone() == 100);
    }

    /**
     * @var integer $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = (integer) $startDate;
    }

    /**
     * @return integer
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    /**
     * @var integer $rate
     */
    public function setUploadRate($rate)
    {
        $this->uploadRate = (integer) $rate;
    }

    /**
     * @return integer
     */
    public function getUploadRate()
    {
        return $this->uploadRate;
    }

    /**
     * @param integer $rate
     */
    public function setDownloadRate($rate)
    {
        $this->downloadRate = (integer) $rate;
    }

    /**
     * @param integer $peersConnected
     */
    public function setPeersConnected($peersConnected)
    {
        $this->peersConnected = (integer) $peersConnected;
    }

    /**
     * @return integer
     */
    public function getPeersConnected()
    {
        return $this->peersConnected;
    }

    /**
     * @return integer
     */
    public function getDownloadRate()
    {
        return $this->downloadRate;
    }

    /**
     * @param double $done
     */
    public function setPercentDone($done)
    {
        $this->percentDone = (double) $done;
    }

    /**
     * @return double
     */
    public function getPercentDone()
    {
        return $this->percentDone * 100.0;
    }

    /**
     * @param array $files
     */
    public function setFiles(array $files)
    {
        $this->files = array_map(function ($file) {
            return PropertyMapper::map(new File(), $file);
        }, $files);
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param array $peers
     */
    public function setPeers(array $peers)
    {
        $this->peers = array_map(function ($peer) {
            return PropertyMapper::map(new Peer(), $peer);
        }, $peers);
    }

    /**
     * @return array
     */
    public function getPeers()
    {
        return $this->peers;
    }
    /**
     * @param array $trackerStats
     */
    public function setTrackerStats(array $trackerStats)
    {
        $this->trackerStats = array_map(function ($trackerStats) {
            return PropertyMapper::map(new TrackerStats(), $trackerStats);
        }, $trackerStats);
    }

    /**
     * @return array
     */
    public function getTrackerStats()
    {
        return $this->trackerStats;
    }

    /**
     * @param array $trackers
     */
    public function setTrackers(array $trackers)
    {
        $this->trackers = array_map(function ($tracker) {
            return PropertyMapper::map(new Tracker(), $tracker);
        }, $trackers);
    }

    /**
     * @return array
     */
    public function getTrackers()
    {
        return $this->trackers;
    }

    /**
     * @param double $ratio
     */
    public function setUploadRatio($ratio)
    {
        $this->uploadRatio = (double) $ratio;
    }

    /**
     * @return double
     */
    public function getUploadRatio()
    {
        return $this->uploadRatio;
    }

    /**
     * @return boolean
     */
    public function isStopped()
    {
        return $this->status->isStopped();
    }

    /**
     * @return boolean
     */
    public function isChecking()
    {
        return $this->status->isChecking();
    }

    /**
     * @return boolean
     */
    public function isDownloading()
    {
        return $this->status->isDownloading();
    }

    /**
     * @return boolean
     */
    public function isSeeding()
    {
        return $this->status->isSeeding();
    }
    
    /**
     * @return string
     */
    public function getDownloadDir()
    {
        return $this->downloadDir;
    }

    /**
     * @param string $downloadDir
     */
    public function setDownloadDir($downloadDir)
    {
        $this->downloadDir = $downloadDir;
    }

    /**
     * @return int
     */
    public function getDownloadedEver() {
        return $this->downloadedEver;
    }

    /**
     * @param int $downloadedEver
     */
    public function setDownloadedEver($downloadedEver) {
        $this->downloadedEver = $downloadedEver;
    }

    /**
     * @return int
     */
    public function getUploadedEver() {
        return $this->uploadedEver;
    }

    /**
     * @param int $uploadedEver
     */
    public function setUploadedEver($uploadedEver) {
        $this->uploadedEver = $uploadedEver;
    }

    /**
     * @return int
     */
    public function getActivityDate()
    {
        return $this->activityDate;
    }

    /**
     * @param int $activityDate
     */
    public function setActivityDate( $activityDate)
    {
        $this->activityDate = (int) $activityDate;
    }

    /**
     * @return int
     */
    public function getAddedDate()
    {
        return $this->addedDate;
    }

    /**
     * @param int $addedDate
     */
    public function setAddedDate($addedDate)
    {
        $this->addedDate = (int) $addedDate;
    }

    /**
     * @return float
     */
    public function getCorruptEver()
    {
        return $this->corruptEver;
    }

    /**
     * @param float $corruptEver
     */
    public function setCorruptEver($corruptEver)
    {
        $this->corruptEver = (float) $corruptEver;
    }

    /**
     * @return float
     */
    public function getDesiredAvailable()
    {
        return $this->desiredAvailable;
    }

    /**
     * @param float $desiredAvailable
     */
    public function setDesiredAvailable($desiredAvailable)
    {
        $this->desiredAvailable = (float) $desiredAvailable;
    }

    /**
     * @return int
     */
    public function getDoneDate()
    {
        return $this->doneDate;
    }

    /**
     * @param int $doneDate
     */
    public function setDoneDate($doneDate)
    {
        $this->doneDate = (int) $doneDate;
    }

    /**
     * @return int
     */
    public function getEditDate()
    {
        return $this->editDate;
    }

    /**
     * @param int $editDate
     */
    public function setEditDate($editDate)
    {
        $this->editDate = (int) $editDate;
    }

    /**
     * @return int
     */
    public function getErrorType()
    {
        return $this->errorType;
    }

    /**
     * @param int $errorType
     */
    public function setErrorType($errorType)
    {
        $this->errorType = (int) $errorType;
    }

    /**
     * @return string
     */
    public function getErrorString()
    {
        return $this->errorString;
    }

    /**
     * @param string $errorString
     */
    public function setErrorString($errorString)
    {
        $this->errorString = (string) $errorString;
    }

    /**
     * @return int
     */
    public function getEtaIdle()
    {
        return $this->etaIdle;
    }

    /**
     * @param int $etaIdle
     */
    public function setEtaIdle($etaIdle)
    {
        $this->etaIdle = (int)$etaIdle;
    }

    /**
     * @return float
     */
    public function getHaveUnchecked()
    {
        return $this->haveUnchecked;
    }

    /**
     * @param float $haveUnchecked
     */
    public function setHaveUnchecked($haveUnchecked)
    {
        $this->haveUnchecked = (float) $haveUnchecked;
    }

    /**
     * @return float
     */
    public function getHaveValid()
    {
        return $this->haveValid;
    }

    /**
     * @param float $haveValid
     */
    public function setHaveValid($haveValid)
    {
        $this->haveValid = (float) $haveValid;
    }

    /**
     * @return bool
     */
    public function isStalled()
    {
        return $this->isStalled;
    }

    /**
     * @param bool $isStalled
     */
    public function setIsStalled($isStalled)
    {
        $this->isStalled = (bool) $isStalled;
    }

    /**
     * @return float
     */
    public function getLeftUntilDone()
    {
        return $this->leftUntilDone;
    }

    /**
     * @param float $leftUntilDone
     */
    public function setLeftUntilDone($leftUntilDone)
    {
        $this->leftUntilDone = (float) $leftUntilDone;
    }

    /**
     * @return int
     */
    public function getManualAnnounceTime()
    {
        return $this->manualAnnounceTime;
    }

    /**
     * @param int $manualAnnounceTime
     */
    public function setManualAnnounceTime($manualAnnounceTime)
    {
        $this->manualAnnounceTime = (int) $manualAnnounceTime;
    }

    /**
     * @return float
     */
    public function getMetadataPercentComplete()
    {
        return $this->metadataPercentComplete;
    }

    /**
     * @param float $metadataPercentComplete
     */
    public function setMetadataPercentComplete($metadataPercentComplete)
    {
        $this->metadataPercentComplete = (float) $metadataPercentComplete;
    }

    /**
     * @return int
     */
    public function getPeersGettingFromUs()
    {
        return $this->peersGettingFromUs;
    }

    /**
     * @param int $peersGettingFromUs
     */
    public function setPeersGettingFromUs($peersGettingFromUs)
    {
        $this->peersGettingFromUs = (int) $peersGettingFromUs;
    }

    /**
     * @return int
     */
    public function getPeersSendingToUs()
    {
        return $this->peersSendingToUs;
    }

    /**
     * @param int $peersSendingToUs
     */
    public function setPeersSendingToUs($peersSendingToUs)
    {
        $this->peersSendingToUs = (int) $peersSendingToUs;
    }

    /**
     * @return int
     */
    public function getQueuePosition()
    {
        return $this->queuePosition;
    }

    /**
     * @param int $queuePosition
     */
    public function setQueuePosition($queuePosition)
    {
        $this->queuePosition = (int) $queuePosition;
    }

    /**
     * @return float
     */
    public function getRecheckProgress()
    {
        return $this->recheckProgress;
    }

    /**
     * @param float $recheckProgress
     */
    public function setRecheckProgress($recheckProgress)
    {
        $this->recheckProgress = (float) $recheckProgress;
    }

    /**
     * @return int
     */
    public function getSecondsDownloading()
    {
        return $this->secondsDownloading;
    }

    /**
     * @param int $secondsDownloading
     */
    public function setSecondsDownloading($secondsDownloading)
    {
        $this->secondsDownloading = (int) $secondsDownloading;
    }

    /**
     * @return int
     */
    public function getSecondsSeeding()
    {
        return $this->secondsSeeding;
    }

    /**
     * @param int $secondsSeeding
     */
    public function setSecondsSeeding($secondsSeeding)
    {
        $this->secondsSeeding = (int) $secondsSeeding;
    }

    /**
     * @return int
     */
    public function getWebseedsSendingToUs()
    {
        return $this->webseedsSendingToUs;
    }

    /**
     * @param int $webseedsSendingToUs
     */
    public function setWebseedsSendingToUs($webseedsSendingToUs)
    {
        $this->webseedsSendingToUs = (int) $webseedsSendingToUs;
    }

    /**
     * {@inheritDoc}
     */
    public static function getMapping()
    {
        return array(
            'id' => 'id',
            'eta' => 'eta',
            'sizeWhenDone' => 'size',
            'name' => 'name',
            'status' => 'status',
            'isFinished' => 'finished',
            'rateUpload' => 'uploadRate',
            'rateDownload' => 'downloadRate',
            'percentDone' => 'percentDone',
            'files' => 'files',
            'peers' => 'peers',
            'peersConnected' => 'peersConnected',
            'trackers' => 'trackers',
            'trackerStats' => 'trackerStats',
            'startDate' => 'startDate',
            'uploadRatio' => 'uploadRatio',
            'hashString' => 'hash',
            'downloadDir' => 'downloadDir',
            'downloadedEver' => 'downloadedEver',
            'uploadedEver' => 'uploadedEver',

            'activityDate' => 'activityDate',
            'addedDate' => 'addedDate',
            'corruptEver' => 'corruptEver',
            'desiredAvailable' => 'desiredAvailable',
            'doneDate' => 'doneDate',
            'editDate' => 'editDate',
            'error' => 'errorType',
            'errorString' => 'errorString',
            'etaIdle' => 'etaIdle',
            'haveUnchecked' => 'haveUnchecked',
            'haveValid' => 'haveValid',
            'isStalled' => 'isStalled',
            'leftUntilDone' => 'leftUntilDone',
            'manualAnnounceTime' => 'manualAnnounceTime',
            'metadataPercentComplete' => 'metadataPercentComplete',
            'peersGettingFromUs' => 'peersGettingFromUs',
            'peersSendingToUs' => 'peersSendingToUs',
            'queuePosition' => 'queuePosition',
            'recheckProgress' => 'recheckProgress',
            'secondsDownloading' => 'secondsDownloading',
            'secondsSeeding' => 'secondsSeeding',
            'webseedsSendingToUs' => 'webseedsSendingToUs'
        );
    }
}
