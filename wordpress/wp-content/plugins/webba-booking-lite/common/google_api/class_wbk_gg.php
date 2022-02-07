<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WBK_Google
{
    protected  $client ;
    protected  $calendar_id ;
    protected  $gg_calendar_id ;
    public function init( $calendar_id )
    {
        return FALSE;
    }
    
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }
    
    // connect to google and get calendar authorization status
    // 0 - no token set
    // 1 - authorization success
    // 2 - authorization failed
    public function connect()
    {
        return array( 2, 'not connected, premium feature not available' );
    }
    
    public function renderCalendarBlock()
    {
        $auth_status = $this->connect();
        $html = '';
        return $html;
    }
    
    public function processAuthCode( $authCode )
    {
        return 0;
    }
    
    protected function getAccessToken()
    {
        return '';
    }
    
    protected function getGGCalendarId()
    {
        return 0;
    }
    
    protected function saveAccessToken( $access_token )
    {
    }
    
    public function getCalendarName()
    {
        return '';
    }
    
    public function getCalendarMode()
    {
        return '';
    }
    
    public function clearToken()
    {
        $this->saveAccessToken( '' );
    }
    
    public function insertEvent(
        $title,
        $description,
        $start,
        $end,
        $time_zone,
        $calendar_id = '',
        $use_current_time_zone = false
    )
    {
        return FALSE;
    }
    
    public function updateEvent(
        $event_id,
        $title,
        $description,
        $start = null,
        $end = null,
        $time_zone = null
    )
    {
        return FALSE;
    }
    
    public function deleteEvent( $event_id )
    {
        return FALSE;
    }
    
    public function initCalendarByAuthcode( $code )
    {
        return FALSE;
    }
    
    public function getEventsTimeRanges( $start, $end )
    {
        $result = FALSE;
        return $result;
    }
    
    public function doCache( $start )
    {
        return FALSE;
    }
    
    protected function getCacheTime()
    {
        $value = '';
        return $value;
    }
    
    protected function getCacheContent()
    {
        $value = '';
        return $value;
    }
    
    protected function saveCache( $cache_content )
    {
        $value = '';
        return $value;
    }
    
    protected function try_to_get_from_cache( $start, $end )
    {
        return FALSE;
    }

}