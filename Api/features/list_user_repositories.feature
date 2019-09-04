Feature: List Api status
  In order to list the endpoints API
  As a api user
  I want to list the resources of the Api

  Scenario: when Api check status
    Given I do a "GET" request to "http://meteosalle.local/apiv1/status"
    Then the response code should be "200"
    And the response content type should be "Content-Type: application/json"

  Scenario: when want check routs
    Given I do a "GET" request to "http://meteosalle.local/apiv1/stat"
    Then the response code should be "404"

  Scenario: when want create User
    Given I create a user whit "POST" request to "http://meteosalle.local/apiv1/user/login/" whit userName "12344" password "a" and uuidUser "12344"
    Then the response code should be "404"


