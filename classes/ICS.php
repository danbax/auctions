<?php
if (!defined('Access')) {
	die('Silence is gold');
}


/**
 *
 * SUMMARY
 *
 */
class ICS {

	/** @var ics vars */
	private $startDate,
            $endDate,
            $location,
            $summary;

	/**
	 * constructor.
	 */
	public function __construct($startDate,$endDate,$summary,$location="לא ידוע") {
        $this->startDate = date('Ymd',strtotime($startDate)).'T'.date('His',strtotime($startDate));
        $this->endDate = date('Ymd',strtotime($endDate)).'T'.date('His',strtotime($endDate));
        $this->summary = $summary;
        $this->location = $location;
	}

	/**
     * get html
	 */
	public function getFileData() {
        $fileData ="";
        $fileData .="BEGIN:VCALENDAR"."\n";
        $fileData .="VERSION:2.0"."\n";
        $fileData .="CALSCALE:GREGORIAN"."\n";
        $fileData .="BEGIN:VEVENT"."\n";
        $fileData .="SUMMARY:$this->summary"."\n";
        $fileData .="DTSTART;TZID=Asia/Jerusalem:$this->startDate"."\n";
        $fileData .="DTEND;TZID=Asia/Jerusalem:$this->endDate"."\n";
        $fileData .="BEGIN:VALARM"."\n";
        $fileData .="TRIGGER:-PT10M"."\n";
        $fileData .="DESCRIPTION:Reminder"."\n";
        $fileData .="ACTION:DISPLAY"."\n";
        $fileData .="END:VALARM"."\n";
        $fileData .="END:VEVENT"."\n";
        $fileData .="END:VCALENDAR"."\n";

        return $fileData;
	}


}