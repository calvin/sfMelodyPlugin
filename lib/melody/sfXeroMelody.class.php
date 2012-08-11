<?php

class sfXeroMelody extends sfMelody1
{
  protected function initialize($config)
  {
    $this->setRequestTokenUrl('https://api.xero.com/oauth/RequestToken');
    $this->setRequestAuthUrl('https://api.xero.com/oauth/Authorize');
    $this->setAccessTokenUrl('https://api.xero.com/oauth/AccessToken');

    $this->setNamespace('default', 'https://api.xero.com/api.xro/2.0');
  }

  protected function setExpire(&$token)
  {
    $token->setExpire(time() + $token->getParam('oauth_expires_in'));
  }

  public function getIdentifier()
  {
    return $this->getToken()->getParam('xero_org_muid');
  }

  protected function formatResult($response)
  {
      $xml = @simplexml_load_string($response);

      if ($xml !== false and $xml instanceof SimpleXMLElement)
          return $xml;

      if (strstr($response, 'auth_'))
      {
          parse_str($response, $ret);
          return $ret;
      }

      return $response;
  }

  public function put($action, $aliases = null, $parameters = array())
  {
      if (array_key_exists('xml', $parameters))
      {
          $xml = $parameters['xml'];

          if ($xml instanceof SimpleXMLElement)
              $parameters['xml'] = $xml->asXML();
      }

      return parent::put($action, $aliases, $parameters);
  }

  public function post($action, $aliases = null, $parameters = array())
  {
      if (array_key_exists('xml', $parameters))
      {
          $xml = $parameters['xml'];

          if ($xml instanceof SimpleXMLElement)
              $parameters['xml'] = $xml->asXML();
      }

      return parent::post($action, $aliases, $parameters);
  }

  public function __call($method, $arguments)
  {
    $params = explode('_', sfInflector::tableize($method));

    $callable = array($this, array_shift($params));
    array_unshift($arguments, sfInflector::camelize(implode('_', $params)));

    if(is_callable($callable))
    {
      return call_user_func_array($callable, $arguments);
    }
    else throw new sfException(sprintf('method "%s" does not exists in "%s" class', $callable[1], get_class($this)));
  }

}
