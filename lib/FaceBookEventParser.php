<?php

namespace QueHacemos\Parsers;
include 'vendor/autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;
use QueHacemos\QueryResponseParser;

/**
 * Class QueryResponseParser
 * @package QueHacemos
 * @author Russell Ormes
 * A class to take the response from a FB Graph query and parse it into
 * a data structure for use on the QueHacemos page. 
 */

class QueryResponseParser
{
  /**
   * Creates a QueryResponseParser using the data provided
   * from a FB Graph query.
   *
   * @param GraphObject $response
   */
  public function __construct($response){
    fb(get_class($response),"Class Name: ");
    if (get_class($response) != "Facebook\GraphObject") {
      throw new \Exception('Class constructor requires a FB GraphObject.');
    } else {
      $pagesArrayWithEvents =
              $response->getProperty('likes')->getProperty('data');
      //fb($pagesArrayWithEvents);
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
}
?>