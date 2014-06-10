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
   * @return array An associate array of Quehacemos\Event objects
   */
  public static function parseFaceBookQueryResponse($response)
  {
    
    if (get_class($response) != "Facebook\GraphObject") {
      throw new \Exception("parseFaceBookQueryResponse() requires " .
                             "a FB Graph Object.");
    }
    
    $events_by_page = array();
    
    fb($response, "Graph Object as returned from fb: ");
    //fb($response->getProperty('likes'), "Graph Object containing my likes: ");
    fb($response->getProperty('likes')->getProperty('data'),
                             "Graph Object containing my likes data array: ");
    fb($response->getProperty('likes')->getProperty('data')->getProperty('6'),
                             "Graph Object for fb page: ");
    fb($response->getProperty('likes')->getProperty('data')->getProperty('6')->getProperty('events')->getProperty('data'),
                             "Graph Object for fb events for a page: ");
    
    
    //We want just the array of pages from the Graph Object that is returned
    //and we need to know how many pages we have so we can step through
    //the array and call one page at a time. 
    
    $pages = $response->getProperty('likes')->getProperty('data');
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
          $parsed_event = array();
          $current_event = $events->getProperty($k);
          $parsed_event['name'] = $current_event->getProperty('name');
          $parsed_event['where'] = $current_event->getProperty('location');
          $parsed_event['fb_id'] = $current_event->getProperty('id');
          $cover = $current_event->getProperty('cover');
          if ($cover) {
            $parsed_event['photo_url'] =
              $current_event->getProperty('cover')->getProperty('source');
          } else {
            $parsed_event['photo_url'] = 'none';
          }
          $parsed_event['when'] =
              array('start_time' => $current_event->getProperty('start_time'));
          try {
            $events_by_page[$current_page->getProperty('name')][] =
                                          new Event($parsed_event);
          } catch(\Exception $e){
            fb($e, \FirePHP::ERROR);
          }//end try catch
        }//end for
      }//end if ($current_page_events)
    }//end step through pages
    
    //Debugging. To be removed.
    fb($events_by_page, "Events array:");
    echo print_r($events_by_page, true) . "</br>";
  
  }//parseFaceBookQueryResponse

}
?>