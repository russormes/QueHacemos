<?php

/**Maybe needed for debuging with FirePHP*/
//ob_start();

include_once 'config/config.php';
include 'vendor/autoload.php';
include 'lib/Parsers/FaceBookEventParser.php';

/**
 * As we are going to store the facebook session object in the $_SESSION
 * variable we start the session after loading the class definitons so we get
 * automatic serialisation and deserilasation of the object when stored. This
 * is done by the php session handler.
**/

session_start();

// Set up the Facebook namespace. Similar to import in true OO languages. 
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
  //DEBUG
  //fb($_SESSION);
  try {
    $me = (new FacebookRequest($session, 'GET', '/me'
            ))->execute()->getGraphObject(GraphUser::className());
    //fb($me);
    $likes = (new FacebookRequest($session, 'GET', '/me/likes'
            ))->execute()->getGraphObject();
    //fb($likes);
    $eventsQueryResponse = (new FacebookRequest($session,
            'GET', '/me?fields=likes.fields(name, events.fields(name, cover))'
            ))->execute()->getGraphObject();
    try {
      fb("we'll give it a go");
      FaceBookEventParser::parseFaceBookQueryResponse($eventsQueryResponse);
      fb("and we're out of there!");
    } catch (\Exception $e){
      fb::error("FaceBookEventParser: " . $e);
    }
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

?>