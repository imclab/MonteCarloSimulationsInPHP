<?php
class MyTrip
{
    protected $departureTime;
    protected $meetingTime;
    protected $travelTimes;

    public function __construct() {
        $this->setDepartureTime('0640');
        $this->setMeetingTime('0900');

        // travel times in minutes between milestones
        $this->setTravelTimes(array(
            'AB' => 17,
            'BC' => 17,
            'CD' => 36,
            'DE' => 9,
            'EF' => 15,
            'FG' => 15,
            'GH' => 6
        ));
    }

    // for convenience convert time string to minutes past midnight
    protected static function convertToMinutes($timeStr) {
        if (strlen($timeStr) != 4 || !ctype_digit($timeStr)) {
            throw new InvalidArgumentException('Invalid time string');
        }
        return substr($timeStr, 0, 2) * 60 + substr($timeStr, 2, 2);
    }

    public function setDepartureTime($timeStr) {
        $this->departureTime = self::convertToMinutes($timeStr);
    }

    public function setMeetingTime($timeStr) {
        $this->meetingTime = self::convertToMinutes($timeStr);
    }

    public function setTravelTimes(array $travelTimes) {
        foreach ($travelTimes as $t) {
            if (!is_int($t)) {
                throw new InvalidArgumentException("invalid member");
            }
        }
        $this->travelTimes = $travelTimes;
    }

    protected function check($travelTime, $schoolDelay, $tiresDelay) {
        // find the total schedule baseline
        $meetingArriveTime = $this->departureTime + $travelTime + $schoolDelay +
            $tiresDelay;

        // does the traveller make the meeting on time?
        $arriveOnTime = $meetingArriveTime <= $this->meetingTime;

        return array($meetingArriveTime, $this->meetingTime, $arriveOnTime);
    }

    public function checkPlan($stopAtSchool = true, $checkTires = true) {
        // calculate the sum of travel times between milestones
        $travelTime = array_sum($this->travelTimes);

        // add delay if dropping kid off at schol
        $schoolDelay = ($stopAtSchool) ? 10 : 0;

        // add delay if checking tire pressure
        $tiresDelay = ($checkTires) ? 10 : 0;

        return $this->check($travelTime, $schoolDelay, $tiresDelay);
    }
   
    public function checkPlanRisk() {
        // adjust and sum travel times between milestones
        $travelTime = 0;
        foreach ($this->travelTimes as $t) {
            $travelTime += $t * rand(90, 125) / 100;
        }

        // decide whether to drop kid off at school and randomly set the
        // delay time
        $schoolDelay = 0;
        if (rand(1, 100) > 50) {
            $schoolDelay = 10 * rand(90, 125) / 100;
        }
        
        // ditto for checking tires
        $tiresDelay = 0;
        if (rand(1, 100) > 50) {
            $tiresDelay = 10 * rand(90, 125) / 100;
        }

        $result = $this->check($travelTime, $schoolDelay, $tiresDelay);

        return array_merge(array($schoolDelay, $tiresDelay), $result);
    }

    public function runCheckPlanRisk($numTrials) {
        $arriveOnTime = 0;
        for ($i = 1; $i <= $numTrials; $i++) {
            $result = $this->checkPlanRisk();
            if ($result[4]) {
                $arriveOnTime++;
            }

            echo "Trial: " . $i;
            echo " School delay: " . $result[0];
            echo " Tire delay: " . $result[1];
            echo " Enroute time: " . $result[2];

            if ($result[4]) {
                echo " -- Arrive ON TIME";
            }
            else {
                echo " -- Arrive late";
            }

            $confidence = $arriveOnTime / $i;
            echo "\nConfidence level: $confidence\n\n";
        }
    }
}

