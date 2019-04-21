Feature: BioPHP demo
  I need to be able to see BioPHP demo

  Scenario: I can see BioPHP demo
    When I go to "/"
    Then I should see "BioPHP demo"

  Scenario: I can't see hello world
    When I go to "/sequence-analysis"
    Then I should not see "Hello World!"