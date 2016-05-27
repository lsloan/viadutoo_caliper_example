<?php
require_once 'vendor/autoload.php';

require_once 'Caliper/Sensor.php';
require_once 'Caliper/entities/reading/EPubVolume.php';
require_once 'Caliper/entities/reading/EPubSubChapter.php';
require_once 'Caliper/entities/reading/Frame.php';
require_once 'Caliper/entities/agent/Person.php';
require_once 'Caliper/entities/agent/SoftwareApplication.php';
require_once 'Caliper/entities/session/Session.php';
require_once 'Caliper/events/SessionEvent.php';
require_once 'Caliper/actions/Action.php';
require_once 'Caliper/entities/EntityType.php';
require_once 'Caliper/Options.php';

class CaliperExampleApp {
    /** @var SessionEvent */
    private $sessionEvent;
    /** @var Person */
    private $personEntity;

    /** @return Person */
    public function getPersonEntity() {
        return $this->personEntity;
    }

    /** @return SessionEvent */
    public function getSessionEvent() {
        return $this->sessionEvent;
    }

    function setUp() {
        $createdTimeLongAgo = new DateTime('1977-05-25T17:00:00.000Z');
        $createdTimeNow = new DateTime();
        $modifiedTime = new DateTime('2015-06-24T19:48:00.000Z');
        $sessionStartTime = new DateTime('2015-12-18T11:38:00.000Z');

        $person = new Person('https://example.edu/user/poe_dameron');
        $person->setDateCreated($createdTimeNow)
            ->setDateModified($modifiedTime);
        $this->personEntity = $person;

        $eventObj = new SoftwareApplication('https://example.com/viewer');
        $eventObj->setName('Holocron v7')
            ->setDateCreated($createdTimeLongAgo)
            ->setDateModified($modifiedTime);

        $ePubVolume = new EPubVolume('https://example.com/viewer/book/1138#epubcfi(/4/3)');
        $ePubVolume->setName('Star Wars: The Magic of Myth')
            ->setDateCreated($createdTimeLongAgo)
            ->setDateModified($modifiedTime)
            ->setVersion('1st ed.');

        $targetObj = new Frame('https://example.com/viewer/book/1138#epubcfi(/4/3/1)');
        $targetObj->setName('The Resurgence of Evil')
            ->setDateCreated($createdTimeLongAgo)
            ->setDateModified($modifiedTime)
            ->setIsPartOf($ePubVolume)
            ->setIndex(1)
            ->setVersion('1st ed.');

        $generatedObj = new Session('https://example.com/viewer/session-19440514');
        $generatedObj->setName('session-19440514')
            ->setDateCreated($createdTimeNow)
            ->setDateModified($modifiedTime)
            ->setActor($person)
            ->setStartedAtTime($sessionStartTime);

        $sessionEvent = new SessionEvent();
        $sessionEvent->setAction(new Action(Action::LOGGED_IN))
            ->setActor($person)
            ->setObject($eventObj)
            ->setTarget($targetObj)
            ->setGenerated($generatedObj)
            ->setEventTime($createdTimeNow);

        $this->sessionEvent = $sessionEvent;
    }
}

$sensor = new Sensor('id');

$authZUserOrKey = 'user_here';
$authZPasswordOrSecret = 'password_here';

$authZHeaderValue = 'Basic ' . base64_encode($authZUserOrKey . ':' . $authZPasswordOrSecret);

$options = (new Options())
    ->setHost('http://127.0.0.1:8989/')
    ->setApiKey($authZHeaderValue)
    ->setDebug(true);

$sensor->registerClient('http', new Client('clientId', $options));

$sessionTest = new CaliperExampleApp();
$sessionTest->setUp();

define('CR', "\r");

echo 'sending...' . CR;
$sensor->send($sensor, $sessionTest->getSessionEvent());
echo 'send() done' . PHP_EOL;

echo 'describing...' . CR;
$sensor->describe($sensor, $sessionTest->getPersonEntity());
echo 'describe() done' . PHP_EOL;
