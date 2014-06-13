<?php

/**Maybe needed for debuging with FirePHP*/
ob_start();

include_once 'config/global.php';
include 'vendor/autoload.php';

/**
 * As we are going to store the facebook session object in the $_SESSION
 * variable we start the session after loading the class definitons so we get
 * automatic serialisation and deserilasation of the object when stored. This
 * is done by the php session handler.
**/

session_start();

// Set up the Facebook namespace. Similar to import in Java. 
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;
use QueHacemos\Parsers\FaceBookEventParser;

FacebookSession::setDefaultApplication(APP_ID, APP_SECRET);

// see if we have a session stored in $_Session
if( isset($_SESSION['fb_session'])) {
  $session = $_SESSION['fb_session'];
  try {
    //No need to pass APP_ID and APP_SECRET here as these are included from the
    //config file via a call to setDefaultApplication. 
    $session->validate();
  } catch (FacebookRequestException $ex) {
    // Session not valid, Graph API returned an exception with the reason.
    fb::error("FacebookRequestException: " . $ex->getMessage());
  } catch (\Exception $ex) {
    // Graph API returned info, but it may mismatch the current app or have expired.
    fb::error("Validate Exception: " . $ex->getMessage());
    // So set $session to null to indicate we do not have a valid session.
    $session = null; 
  }
} else {
// We haven't got a stored session so lets attempt to get a new one from fb. 
  try {
    $helper = new FacebookRedirectLoginHelper(REDIRECT_URL);
    $session = $helper->getSessionFromRedirect();
  } catch(FacebookRequestException $ex) {
      // When Facebook returns an error
      fb::error("FacebookRequestException: " . $ex);
  } catch(\Exception $ex) {
      // When validation fails or other local issues
      fb::error("getSessionFromRedirect raised an exception: " . $ex);
  }
}

if ($session) {
   //Logged in.
   //Store the fb session object in $_SESSION
  $_SESSION['fb_session'] = $session;
  $all_events = array();
  //DEBUG
  //fb($_SESSION);
  try {
    $request_string = '/me';
    $me = (new FacebookRequest($session, 'GET', $request_string
            ))->execute()->getGraphObject(GraphUser::className());
    //fb($me);
    $request_string = '/me/likes';
    $likes = (new FacebookRequest($session, 'GET', $request_string
            ))->execute()->getGraphObject();
    $request_string =
          '/me/likes?fields=name,location,events.fields(name,+cover,+location)';
    $events_query_response = (new FacebookRequest($session,
            'GET', $request_string))->execute()->getGraphObject();
    $all_events =
        FaceBookEventParser::parseFaceBookQueryResponse($events_query_response);
    fb($events_query_response, "Events from pages I like: ");
    while(in_array("paging", $events_query_response->getPropertyNames()) &&
       in_array("next",
            $events_query_response->getProperty('paging')->getPropertyNames())
    ){
      $paging_cursors =
          $events_query_response->getProperty('paging')->getProperty('cursors');
      $request_string_next = $request_string . "&after=" .
                                        $paging_cursors->getProperty('after'); 
      $events_query_response = (new FacebookRequest($session,
              'GET', $request_string_next))->execute()->getGraphObject();
      fb($events_query_response, "Next events: ");
      try {
        $all_events = array_merge($all_events,
                                  FaceBookEventParser::parseFaceBookQueryResponse(
                                                    $events_query_response));
      } catch (\Exception $e){
        fb::error("FaceBookEventParser: " . $e);
      }
    }//End while
  } catch (FacebookRequestException $e) {
    // The Graph API returned an error
    echo "The Graph API returned an error: " . $e;
  } catch (\Exception $e) {
    // Some other error occurred
    echo "Some other error occurred: " . $e;
  }
} else {
  //We don't have a valid session so ask the user to login.
  echo '<a href="' . $helper->getLoginUrl(array('user_likes')) . '">Login with Facebook</a>';
}
fb($all_events, "All events");
?>