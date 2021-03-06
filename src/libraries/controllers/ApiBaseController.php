<?php
/**
  * API Base controller extended by all other controllers.
  *
  * @author Jaisen Mathai <jaisen@jmathai.com>
 */
class ApiBaseController
{
  /**
   * Status constants
   */
  const statusError = 500;
  const statusSuccess = 200;
  const statusCreated = 201;
  const statusForbidden = 403;
  const statusNotFound = 404;

  public function __construct()
  {
    $this->api = getApi();
    $this->config = getConfig()->get();
    $this->plugin = getPlugin();
    $this->route = getRoute();
    $this->session = getSession();
    $this->template = getTemplate();
    $this->utility = new Utility;
    $this->url = new Url;
  }

  /**
    * Created, HTTP 202
    *
    * @param string $message A friendly message to describe the operation
    * @param mixed $result The result with values needed by the caller to take action.
    * @return string Standard JSON envelope
    */
  public function created($message, $result = null)
  {
    return self::json($message, self::statusCreated, $result);
  }

  /**
    * Server error, HTTP 500
    *
    * @param string $message A friendly message to describe the operation
    * @param mixed $result The result with values needed by the caller to take action.
    * @return string Standard JSON envelope
    */
  public function error($message, $result = null)
  {
    return self::json($message, self::statusError, $result);
  }

  /**
    * Success, HTTP 200
    *
    * @param string $message A friendly message to describe the operation
    * @param mixed $result The result with values needed by the caller to take action.
    * @return string Standard JSON envelope
    */
  public function success($message, $result = null)
  {
    return self::json($message, self::statusSuccess, $result);
  }

  /**
    * Forbidden, HTTP 403
    *
    * @param string $message A friendly message to describe the operation
    * @param mixed $result The result with values needed by the caller to take action.
    * @return string Standard JSON envelope
    */
  public function forbidden($message, $result = null)
  {
    return self::json($message, self::statusForbidden, $result);
  }

  /**
    * Not Found, HTTP 404
    *
    * @param string $message A friendly message to describe the operation
    * @param mixed $result The result with values needed by the caller to take action.
    * @return string Standard JSON envelope
    */
  public function notFound($message, $result = null)
  {
    return self::json($message, self::statusNotFound, $result);
  }

  /**
    * Internal method to enforce standard JSON envelope
    *
    * @param string $message A friendly message to describe the operation
    * @param mixed $result The result with values needed by the caller to take action.
    * @return string Standard JSON envelope
    */
  private function json($message, $code, $result = null)
  {
    $response = array('message' => $message, 'code' => $code, 'result' => $result);

    // if httpCodes is * then always return the HTTP status code
    // if httpCodes matches then we return a HTTP status code
    if(isset($_REQUEST['httpCodes']))
    {
      if($_REQUEST['httpCodes'] === '*')
      {
        $this->putHttpHeader($code);
      }
      else
      {
        $codes = (array)explode(',', $_REQUEST['httpCodes']);
        if(in_array($code, $codes))
          $this->putHttpHeader($code);
      }
    }

    // if a callback is in the request then we JSONP the response
    if(isset($_REQUEST['callback']))
      $response['__callback__'] = $_REQUEST['callback'];

    return $response;
  }

  private function putHttpHeader($code)
  {
    if($this->api->isInvoking())
      return;

    switch($code)
    {
      case '201':
        $header = 'HTTP/1.0 201 Created';
        break;
      case '403':
        $header = 'HTTP/1.0 403 Forbidden';
        break;
      case '404':
        $header = 'HTTP/1.0 404 Not Found';
        break;
      case '500':
        $header = 'HTTP/1.0 500 Internal Server Error';
        break;
      case '200':
      default:
        $header = 'HTTP/1.0 200 OK';
        break;
    }
    header($header);
  }
}
