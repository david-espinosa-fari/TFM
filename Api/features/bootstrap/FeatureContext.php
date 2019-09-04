<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private $lastResponse;
    private $lastStatusCode;
    private $headerContentType;
    private $headerAutentication;
    private const ADDRESS_API = 'http://meteosalle.local/apiv1';
    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $httpClient;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given /^I do a "([^"]*)" request to "([^"]*)"$/
     */
    public function iDoARequestTo($method, $uri)
    {

        $client = new Client(['base_uri' => self::ADDRESS_API]);

        try {
            $request = $client->request($method, $uri);

            $this->lastResponse = json_decode($request->getBody(),true);

            $this->lastStatusCode = $request->getStatusCode();
            $this->headerContentType = $request->getHeaderLine('Content-Type');
        } catch (ClientException $exception) {
            $this->lastResponse   = $exception->getResponse()->getBody();
            $this->lastStatusCode = $exception->getResponse()->getStatusCode();
        }
    }

    /**
     * @Then /^the response code should be "([^"]*)"$/
     */
    public function theResponseCodeShouldBe($arg1)
    {
        if ($this->lastStatusCode != $arg1)
        {
            throw new Exception('Los codigos de estado no coinciden '.$arg1." vs ".$this->lastStatusCode);
        }
    }

    /**
     * @Given /^the response content type should be "([^"]*)"$/
     */
    public function theResponseContentTypeShouldBe($arg1)
    {
        if ('Content-Type: '.$this->headerContentType !== $arg1 )
        {
              throw new Exception('Los content-type.'.$arg1.' no coinciden'.$this->headerContentType);
        }
    }

    /**
     * @Given /^I create a user whit "([^"]*)" request to "([^"]*)" whit userName "([^"]*)" password "([^"]*)" and uuidUser "([^"]*)"$/
     */
    public function iCreateAUSerWhitRequestToWhitUserNamePasswordAndUuidUser($method, $uri, $user, $password, $uuidUser)
    {

        $client = new Client(['base_uri' => self::ADDRESS_API]);

        try {
            $request = $client->request($method, $uri, [
            'form_params' => [
                'userName' => $user,
                'password' => $password,
                'uuidUser' => $uuidUser,
            ]
]);

            $this->lastResponse   = $request->getBody()->getContents();
            $this->lastStatusCode = $request->getStatusCode();
        } catch (ClientException $exception) {
            $this->lastResponse   = $exception->getResponse()->getBody();
            $this->lastStatusCode = $exception->getResponse()->getStatusCode();
        }
    }

    /**
     * @Given /^the response body should have "([^"]*)"$/
     */
    public function theResponseBodyShouldHave($arg1)
    {
        if (!isset($this->lastResponse[$arg1]))
        {
            throw new Exception('No existe token en la respuesta');
        }
    }

}
