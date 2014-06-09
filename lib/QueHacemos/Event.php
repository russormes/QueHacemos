<?php
namespace QueHacemos;

/**
 * Class Event
 * @package QueHacemos
 * @author Russell Ormes
 * 
 * A class for storing individual events.
 * 
 */
class Event
{
  /**
   * @var array - Holds the associative array that contains the event data.
   */
  protected $dataArray;
  
  /**
   * Creates an event object.
   *
   * @param array $dataAsArray
   * The constructor takes an associative array of key value
   * pairs of event information with the following structure:
   * 
   * array {
   *   [name] => "Event name",
   *   [when] => array {
   *               [start_date] => "31/12/2014"
   *               [start_time] => "00:00:00",
   *               [end_date] => "31/12/2014"
   *               [end_time] => "00:00:00",
   *             },
   *   [where] => "Event address",
   *   [price] => array {
   *                [0] => "First price",
   *                [1] => "Second price",
   *              },
   *   [owner_url] => "Url for event organiser",
   *   [photo_url] => "Url to event photo",
   * }
   *
   * @throws Exception when the parameter is not an array and
   * does not contain the required fields for name, where and when. 
   */
  public function __construct($dataAsArray)
  {
    if (checkDataArray($dataAsArray)) {
      $this->dataArray = $dataAsArray;
    } else {
      throw new \Exception("Event class constructor argument is not " .
                           "correctly formed. Please see class documentation");
    }
  }
  
  /**
   * Helper function to check the structure of the event data array.
   * TO DO.
   * $expectedKeys = array(
   *                   'name', 'when', 'where', 'price',
   *                   'owner_url', 'photo_url'
   *                 );
   *
   * @return booean
   *
   */
  private function checkDataArray($arrayToCheck)
  {
    if (!is_array($arrayToCheck)) {
      return false; 
    } else {
      if (   isset($arrayToCheck['name'])
          && isset($arrayToCheck['when'])
          && isset($arrayToCheck['where'])
      ) { 
        return true;
      } else {
        return false;
      }// End_ifElse
    }// End_ifElse
  }// End function.

}//End class  
?>