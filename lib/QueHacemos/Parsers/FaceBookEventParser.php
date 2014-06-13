<?php

namespace QueHacemos\Parsers;

use QueHacemos\Event; 

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
   * @return array An associate array of arrays of Quehacemos\Event objects
   * indexed by page name. 
   */
  public static function parseFaceBookQueryResponse($response)
  {
    
    if (get_class($response) != "Facebook\GraphObject") {
      throw new \Exception("parseFaceBookQueryResponse() requires " .
                             "a FB Graph Object.");
    }
    
    $events_by_page = array();
    $session = $_SESSION['fb_session'];
    
    //We want just the array of pages from the Graph Object that is returned
    //and we need to know how many pages we have so we can step through
    //the array and call one page at a time. 
    
    $pages = $response->getProperty('data');
    $no_of_pages = count($pages->getPropertyNames());
    
    //Step through the pages
    for ($i=0; $i<$no_of_pages; $i++) {
      $current_page = $pages->getProperty($i);
      //Grab the events array for that page wrapped in a Graph Object. 
      $current_page_events = $current_page->getProperty('events');
      if ($current_page_events) { //The page might not have any events.
        $events = $current_page_events->getProperty('data');
        $no_of_events = count($events->asArray());
        $events_by_page[$current_page->getProperty('name')] = array();
        for ($k=0; $k<$no_of_events; $k++){
          $current_event = $events->getProperty($k);
          $events_by_page[$current_page->getProperty('name')][] =
                        FaceBookEventParser::buildEventObject($current_event);
        }//end for
      }//end if ($current_page_events)
    }//end step through pages
    
    //Debugging. To be removed.
    echo print_r($events_by_page, true) . "</br>";
    return $events_by_page;
  
  }//parseFaceBookQueryResponse
  
  private static function buildEventObject($fb_event) {
    $parsed_event = array();
    $parsed_event['name'] = $fb_event->getProperty('name');
    $parsed_event['where'] = $fb_event->getProperty('location');
    $parsed_event['fb_id'] = $fb_event->getProperty('id');
    $parsed_event['when'] =
                   array('start_time' => $fb_event->getProperty('start_time'));
    $cover = $fb_event->getProperty('cover');
    if ($cover) {
      $parsed_event['photo_url'] =
                        $fb_event->getProperty('cover')->getProperty('source');
    } else {
      $parsed_event['photo_url'] = 'none';
    }
    
    try {
      $event_object = new Event($parsed_event);
    } catch(\Exception $e){
      fb($e, \FirePHP::ERROR);
    }//end try catch
    
    return $event_object;
  
  }
}
?>