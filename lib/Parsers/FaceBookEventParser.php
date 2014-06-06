<?php

namespace QueHacemos\Parsers;

//include_once (__ROOT__ . '/config/config.php');
include(__ROOT__ . '/vendor/autoload.php');

//use Facebook\GraphUser;

/**
 * Class FaceBookEventParser
 * @package QueHacemos
 * @author Russell Ormes
 * A class to take the response from a FB Graph query and parse it into
 * a data structure for use on the QueHacemos page. 
 */

class FaceBookEventParser
{

  /**
   * Static method used to parse the array returned from a FB Graph query.
   *
   * @param GraphObject $response
   *
   * @return array An associate array of Quehacemos\Event objects
   */
  public static function parseFaceBookQueryResponse($response)
  {
    if (get_class($response) != "Facebook\GraphObject") {
      throw new \Exception("parseFaceBookQueryResponse() requires " .
                             "a FB Graph Object.");
    } 
    $pagesArrayWithEvents =
            $response->getProperty('likes')->getProperty('data');
    
    $pageCount = count($pagesArrayWithEvents->getPropertyNames());
    for ($i=0; $i<$pageCount; $i++) {
      $page = $pagesArrayWithEvents->getProperty($i);
      $currentPageEvents = $page->getProperty('events');
      if ($currentPageEvents) {
        $eventsArray = $currentPageEvents->getProperty('data');
        $noOfEvents = count($eventsArray->asArray());
        echo "Page " . $page->getProperty('name') . " has " .
                                                  $noOfEvents . " event(s) </br>";
        for ($k=0; $k<$noOfEvents; $k++){
          $currentEvent = $eventsArray->getProperty($k);
          echo "Event " . ($k+1) ." is called " .
              $currentEvent->getProperty('name') . "</br>";
        }
      }
    }
  }

}
?>